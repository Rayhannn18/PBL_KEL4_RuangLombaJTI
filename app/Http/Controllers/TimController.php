<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TimController extends Controller
{
    /**
     * Ambil identitas pengguna login saat ini (mahasiswa / dosen / admin).
     */
    private function authUser(): ?array
    {
        if (session()->has('auth_user')) {
            return session('auth_user');
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $roleValue = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        return [
            'id' => $user->nim_nip ?? (string) $user->id,
            'nama' => $user->name,
            'email' => $user->email,
            'role' => $roleValue,
            'extra' => $user->prodi ? $user->prodi.' ('.$user->angkatan.')' : '',
        ];
    }

    /**
     * Halaman Utama Modul Tim Mahasiswa
     * Pusat pengelolaan pembentukan tim baru (Ketua Tim) dan eksplorasi cari/gabung tim (Anggota Tim).
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Anda terlebih dahulu untuk mengakses Modul Tim.');
        }

        $query = Tim::where('status_tim', 'disetujui')
            ->with(['lomba', 'ketua', 'anggotaDiterima.mahasiswa', 'anggota.mahasiswa'])
            ->latest();

        // Pencarian berdasarkan nama tim atau nama kompetisi
        if ($request->filled('q')) {
            $keyword = trim($request->query('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_tim', 'like', "%{$keyword}%")
                    ->orWhereHas('lomba', function ($lq) use ($keyword) {
                        $lq->where('nama_lomba', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filter tingkat lomba
        if ($request->filled('tingkat')) {
            $tingkat = $request->query('tingkat');
            $query->whereHas('lomba', function ($lq) use ($tingkat) {
                $lq->where('tingkat', $tingkat);
            });
        }

        // Filter kategori lomba
        if ($request->filled('kategori')) {
            $kategori = $request->query('kategori');
            $query->whereHas('lomba', function ($lq) use ($kategori) {
                $lq->where('kategori', $kategori);
            });
        }

        $allTeams = $query->get();

        // Hitung total lowongan & filter tim yang terbuka tanpa query berulang
        $openTeams = $allTeams->filter(fn ($t) => $t->sisa_kuota_anggota > 0);
        $totalLowongan = $openTeams->count();

        // Hanya tampilkan tim yang masih membuka lowongan (sisa kuota anggota > 0) secara default
        $filterStatus = $request->query('status_kuota', 'terbuka');
        $teams = ($filterStatus === 'terbuka') ? $openTeams : $allTeams;

        // Kategori dan Tingkat untuk filter dropdown (cached 1 jam untuk efisiensi koneksi)
        $kategoriList = Cache::remember('lomba_kategori_list', 3600, function () {
            return Lomba::distinct()
                ->whereNotNull('kategori')
                ->orderBy('kategori')
                ->pluck('kategori')
                ->values()
                ->all();
        });

        // Pastikan format $kategoriList selalu berupa array of string yang valid
        if (! is_array($kategoriList) || (is_object($kategoriList) && get_class($kategoriList) === '__PHP_Incomplete_Class')) {
            Cache::forget('lomba_kategori_list');
            $kategoriList = Lomba::distinct()
                ->whereNotNull('kategori')
                ->orderBy('kategori')
                ->pluck('kategori')
                ->values()
                ->all();
        } elseif ($kategoriList instanceof Collection) {
            $kategoriList = $kategoriList->values()->all();
        }
        $tingkatList = ['Nasional', 'Internasional', 'Regional', 'Kampus'];

        // Ambil tim mahasiswa yang sedang login saat ini (jika ada) untuk ringkasan di Bagian A
        $userTeams = collect();
        $pengajuanGabung = collect();
        $pengajuanKetua = collect();
        $permintaanMasukKetua = collect();
        if (in_array($authUser['role'] ?? '', ['mahasiswa', 'ketua_tim'], true)) {
            $nim = $authUser['id'];
            if (! $request->filled('q') && ! $request->filled('tingkat') && ! $request->filled('kategori')) {
                // Gunakan koleksi $allTeams yang sudah di-eager-load jika tidak ada filter pencarian aktif
                $userTeams = $allTeams->filter(function ($t) use ($nim) {
                    return $t->nim === $nim || $t->anggotaDiterima->contains('nim', $nim);
                })->values();
            } else {
                $userTeams = Tim::where('status_tim', 'disetujui')
                    ->where(function ($q) use ($nim) {
                        $q->where('nim', $nim)
                            ->orWhereHas('anggotaDiterima', function ($sq) use ($nim) {
                                $sq->where('nim', $nim);
                            });
                    })
                    ->with(['lomba', 'ketua', 'anggotaDiterima.mahasiswa'])
                    ->get();
            }

            // Ambil seluruh riwayat pengajuan pembentukan tim oleh mahasiswa ini (calon Ketua Tim)
            $pengajuanKetua = Tim::where('nim', $nim)
                ->with(['lomba', 'admin'])
                ->latest()
                ->get();

            // Ambil riwayat pengajuan gabung ke tim lain oleh mahasiswa yang sedang login
            $pengajuanGabung = AnggotaTim::where('nim', $nim)
                ->whereHas('tim', function ($q) use ($nim) {
                    $q->where('nim', '!=', $nim);
                })
                ->with(['tim.lomba', 'tim.ketua'])
                ->latest()
                ->get();

            // Ambil daftar permintaan gabung yang masuk ke tim yang diketuai mahasiswa ini
            $permintaanMasukKetua = AnggotaTim::where('status_gabung', 'menunggu')
                ->where('nim', '!=', $nim)
                ->whereHas('tim', function ($q) use ($nim) {
                    $q->where('nim', $nim)->where('status_tim', 'disetujui');
                })
                ->with(['tim.lomba', 'mahasiswa'])
                ->latest()
                ->get();
        }

        return view('tim.index', [
            'authUser' => $authUser,
            'teams' => $teams,
            'totalLowongan' => $totalLowongan,
            'kategoriList' => $kategoriList,
            'tingkatList' => $tingkatList,
            'currentSearch' => $request->query('q', ''),
            'currentTingkat' => $request->query('tingkat', ''),
            'currentKategori' => $request->query('kategori', ''),
            'currentStatusKuota' => $filterStatus,
            'userTeams' => $userTeams,
            'pengajuanGabung' => $pengajuanGabung,
            'pengajuanKetua' => $pengajuanKetua,
            'permintaanMasukKetua' => $permintaanMasukKetua,
        ]);
    }

    /**
     * Papan Lowongan Tim (Eksplorasi Tim yang Membuka Lowongan Anggota - Alias ke Modul Tim)
     */
    public function lowongan(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Formulir Pembuatan Tim Baru (Khusus Calon Ketua Tim)
     */
    public function create(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk untuk membentuk tim baru.');
        }

        if ($authUser['role'] !== 'mahasiswa') {
            return redirect()->route('dashboard.analitik')->with('error', 'Hanya mahasiswa yang dapat membentuk tim kompetisi.');
        }

        $lombaTersedia = Lomba::terverifikasi()
            ->orderBy('nama_lomba', 'asc')
            ->get();

        $selectedLombaId = $request->query('lomba_id');

        return view('tim.create', [
            'authUser' => $authUser,
            'lombaTersedia' => $lombaTersedia,
            'selectedLombaId' => $selectedLombaId,
        ]);
    }

    /**
     * Simpan Pembuatan Tim Baru oleh Ketua Tim
     * Wajib mengisi: Nama Tim, Lomba yang Ingin Diikuti, Kuota Anggota Tim, dan File CV (PDF max 2MB).
     */
    public function store(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return redirect()->route('login')->with('error', 'Hanya mahasiswa yang dapat membentuk tim kompetisi.');
        }

        $cvFile = $request->file('cv');

        // Tangani jika ada kesalahan upload file dari level PHP / server sebelum validasi
        if ($cvFile && ! $cvFile->isValid()) {
            $uploadError = $cvFile->getError();
            $errorMessage = match ($uploadError) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file CV melebihi batas upload server PHP (2 MB). Pastikan ukuran riil file tidak melebihi 2.097.152 bytes (2 MB) atau kompres file PDF Anda.',
                UPLOAD_ERR_PARTIAL => 'File CV hanya terunggah sebagian. Silakan coba unggah kembali.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file CV yang terunggah.',
                UPLOAD_ERR_NO_TMP_DIR => 'Direktori temporary upload server tidak ditemukan (UPLOAD_ERR_NO_TMP_DIR).',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file CV ke disk server (UPLOAD_ERR_CANT_WRITE).',
                default => 'File CV gagal diunggah ke server (Kode error PHP: '.$uploadError.'). Pastikan koneksi stabil dan ukuran maksimal 2 MB.',
            };

            return back()->withInput()->withErrors(['cv' => $errorMessage]);
        }

        $validator = Validator::make($request->all(), [
            'nama_tim' => 'required|string|max:150',
            'id_lomba' => 'required|exists:lomba,id_lomba',
            'kuota_anggota' => 'required|integer|min:2|max:5',
            'catatan' => 'required|string|max:500',
            'cv' => 'required|file|mimes:pdf|max:2048',
        ], [
            'nama_tim.required' => 'Nama tim wajib diisi oleh calon Ketua Tim.',
            'id_lomba.required' => 'Pilih salah satu kompetisi yang ingin diikuti.',
            'kuota_anggota.required' => 'Kuota anggota tim wajib ditentukan.',
            'kuota_anggota.min' => 'Kuota tim minimal 2 orang (termasuk Ketua Tim).',
            'kuota_anggota.max' => 'Kuota tim maksimal 5 orang.',
            'catatan.required' => 'Deskripsi tim / posisi anggota yang dicari wajib diisi.',
            'cv.required' => 'File CV Ketua Tim wajib diunggah dalam format PDF.',
            'cv.file' => 'CV harus berupa file yang valid.',
            'cv.mimes' => 'File CV harus berformat PDF.',
            'cv.max' => 'Ukuran file CV maksimal 2 MB.',
            'cv.uploaded' => 'File CV gagal diunggah ke server. Pastikan ukuran file tidak melebihi 2 MB dan koneksi stabil.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $nim = $authUser['id'];

        // Cek duplikasi pengajuan: satu mahasiswa tidak boleh punya tim aktif/menunggu
        // untuk lomba yang sama. Jika sebelumnya ditolak, boleh mengajukan lagi.
        $pengajuanAktif = Tim::where('nim', $nim)
            ->where('id_lomba', $validated['id_lomba'])
            ->whereIn('status_tim', ['menunggu_persetujuan', 'disetujui'])
            ->first();

        if ($pengajuanAktif) {
            $statusLabel = $pengajuanAktif->status_tim === 'disetujui' ? 'sudah disetujui' : 'sedang menunggu persetujuan admin';

            return back()->withInput()->withErrors([
                'id_lomba' => "Anda sudah memiliki tim \"{$pengajuanAktif->nama_tim}\" untuk lomba ini yang {$statusLabel}. Satu mahasiswa hanya dapat memimpin satu tim per lomba.",
            ]);
        }

        $lomba = Lomba::findOrFail($validated['id_lomba']);

        $cvPath = null;
        try {
            $cvPath = $request->file('cv')->store('cv', 'public');

            // Buat data tim dengan mahasiswa login sebagai ketua secara atomik
            $tim = DB::transaction(function () use ($validated, $lomba, $authUser, $cvPath) {
                $tim = Tim::create([
                    'nama_tim' => $validated['nama_tim'],
                    'id_lomba' => $lomba->id_lomba,
                    'nim' => $authUser['id'],
                    'kuota_anggota' => (int) $validated['kuota_anggota'],
                    'status_tim' => 'menunggu_persetujuan',
                    'file_cv' => $cvPath,
                    'catatan' => $validated['catatan'],
                ]);

                // Daftarkan pengajuan calon ketua tim dengan status menunggu (belum menjadi Ketua Tim resmi sebelum disetujui admin)
                AnggotaTim::create([
                    'id_tim' => $tim->id_tim,
                    'nim' => $authUser['id'],
                    'status_gabung' => 'menunggu',
                    'peran' => 'Ketua Tim',
                    'file_cv' => $cvPath,
                ]);

                return $tim;
            });

            Cache::flush();

            return redirect()->route('tim.index')
                ->with('success', "Pengajuan tim \"{$tim->nama_tim}\" untuk lomba \"{$lomba->nama_lomba}\" berhasil dikirim dan sedang menunggu persetujuan admin.");
        } catch (\Throwable $e) {
            if ($cvPath && Storage::disk('public')->exists($cvPath)) {
                Storage::disk('public')->delete($cvPath);
            }
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan tim: '.$e->getMessage());
        }
    }

    /**
     * Formulir Pengajuan Diri Bergabung ke Tim (Halaman Tersendiri)
     */
    public function formGabung($id)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Anda terlebih dahulu.');
        }

        if ($authUser['role'] !== 'mahasiswa') {
            return redirect()->route('tim.lowongan')->with('error', 'Hanya mahasiswa yang dapat mengajukan diri bergabung ke dalam tim.');
        }

        $tim = Tim::with(['lomba', 'ketua', 'anggotaDiterima.mahasiswa', 'anggota'])->findOrFail($id);
        $nim = $authUser['id'];

        // Cek 0: Tim harus sudah disetujui agar dapat menerima pelamar
        if ($tim->status_tim !== 'disetujui') {
            return redirect()->route('tim.lowongan')->with('error', 'Tim ini belum disetujui dan tidak membuka lowongan anggota.');
        }

        // Cek 1: Mahasiswa tidak boleh mendaftar ke tim yang diketuainya sendiri
        if ($tim->nim === $nim) {
            return redirect()->route('tim.lowongan')->with('error', 'Anda adalah Ketua Tim dari tim ini.');
        }

        // Cek 2: Mahasiswa sudah menjadi anggota resmi yang diterima
        if ($tim->anggotaDiterima->contains('nim', $nim)) {
            return redirect()->route('tim.lowongan')->with('error', 'Anda sudah terdaftar sebagai anggota resmi tim ini.');
        }

        // Cek 3: Periksa riwayat pengajuan sebelumnya jika masih menunggu
        $existing = $tim->anggota()->where('nim', $nim)->first();
        if ($existing && $existing->status_gabung === 'menunggu') {
            return redirect()->route('tim.lowongan')->with('error', 'Pengajuan bergabung Anda sebelumnya sedang menunggu persetujuan Ketua Tim.');
        }

        // Cek 4: Kuota tim masih tersedia
        if ($tim->sisa_kuota_anggota <= 0) {
            return redirect()->route('tim.lowongan')->with('error', 'Maaf, kuota anggota untuk tim ini sudah penuh.');
        }

        return view('tim.gabung', [
            'authUser' => $authUser,
            'tim' => $tim,
        ]);
    }

    /**
     * Alur Anggota Tim: Mengajukan Diri Bergabung ke Tim yang Membuka Lowongan
     * Form Anggota Tim HANYA meminta peran / keahlian dan CV (PDF max 2MB).
     */
    public function gabung(Request $request, $id)
    {
        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa yang dapat mengajukan diri bergabung ke dalam tim.');
        }

        $cvFile = $request->file('cv');

        // Tangani jika ada kesalahan upload file dari level PHP / server sebelum validasi
        if ($cvFile && ! $cvFile->isValid()) {
            $uploadError = $cvFile->getError();
            $errorMessage = match ($uploadError) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file CV melebihi batas upload server PHP (2 MB). Pastikan ukuran riil file tidak melebihi 2.097.152 bytes (2 MB) atau kompres file PDF Anda.',
                UPLOAD_ERR_PARTIAL => 'File CV hanya terunggah sebagian. Silakan coba unggah kembali.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file CV yang terunggah.',
                UPLOAD_ERR_NO_TMP_DIR => 'Direktori temporary upload server tidak ditemukan (UPLOAD_ERR_NO_TMP_DIR).',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file CV ke disk server (UPLOAD_ERR_CANT_WRITE).',
                default => 'File CV gagal diunggah ke server (Kode error PHP: '.$uploadError.'). Pastikan koneksi stabil dan ukuran maksimal 2 MB.',
            };

            return back()->withInput()->with('modal_tim_id', $id)->withErrors(['cv' => $errorMessage]);
        }

        $usesNewFormFields = $request->has('posisi_dilamar') || $request->has('pesan');
        $usesLegacyFields = ! $usesNewFormFields && ($request->has('peran') || $request->has('catatan'));

        if ($usesNewFormFields) {
            $validator = Validator::make($request->all(), [
                'posisi_dilamar' => 'required|string|max:60',
                'pesan' => 'nullable|string|max:300',
                'cv' => 'required|file|mimes:pdf|max:2048',
            ], [
                'posisi_dilamar.required' => 'Posisi / bidang yang dilamar wajib diisi.',
                'posisi_dilamar.max' => 'Posisi / bidang yang dilamar maksimal 60 karakter.',
                'pesan.max' => 'Pesan singkat untuk Ketua Tim maksimal 300 karakter.',
                'cv.required' => 'File CV wajib diunggah dalam format PDF.',
                'cv.file' => 'CV harus berupa file yang valid.',
                'cv.mimes' => 'File CV harus berformat PDF.',
                'cv.max' => 'Ukuran file CV maksimal 2 MB.',
                'cv.uploaded' => 'File CV gagal diunggah ke server. Pastikan ukuran file tidak melebihi 2 MB dan koneksi stabil.',
            ]);
        } elseif ($usesLegacyFields) {
            $validator = Validator::make($request->all(), [
                'peran' => 'required|string|max:100',
                'catatan' => 'required|string|max:500',
                'cv' => 'required|file|mimes:pdf|max:2048',
            ], [
                'peran.required' => 'Silakan tentukan peran / bidang keahlian yang Anda tawarkan ke tim.',
                'catatan.required' => 'Deskripsi atau pesan untuk Ketua Tim wajib diisi.',
                'cv.required' => 'File CV wajib diunggah dalam format PDF.',
                'cv.file' => 'CV harus berupa file yang valid.',
                'cv.mimes' => 'File CV harus berformat PDF.',
                'cv.max' => 'Ukuran file CV maksimal 2 MB.',
                'cv.uploaded' => 'File CV gagal diunggah ke server. Pastikan ukuran file tidak melebihi 2 MB dan koneksi stabil.',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'posisi_dilamar' => 'required|string|max:60',
                'peran' => 'required|string|max:100',
                'catatan' => 'required|string|max:500',
                'cv' => 'required|file|mimes:pdf|max:2048',
            ], [
                'posisi_dilamar.required' => 'Posisi / bidang yang dilamar wajib diisi.',
                'peran.required' => 'Silakan tentukan peran / bidang keahlian yang Anda tawarkan ke tim.',
                'catatan.required' => 'Deskripsi atau pesan untuk Ketua Tim wajib diisi.',
                'cv.required' => 'File CV wajib diunggah dalam format PDF.',
            ]);
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal_tim_id', $id);
        }

        $validated = $validator->validated();
        $posisiDilamar = trim((string) ($validated['posisi_dilamar'] ?? $validated['peran'] ?? 'Anggota'));
        $pesanSingkat = isset($validated['pesan'])
            ? (trim((string) $validated['pesan']) !== '' ? trim((string) $validated['pesan']) : null)
            : (isset($validated['catatan']) && trim((string) $validated['catatan']) !== '' ? trim((string) $validated['catatan']) : null);

        $tim = Tim::with(['anggotaDiterima', 'anggota'])->findOrFail($id);
        $nim = $authUser['id'];

        // Cek 0: Tim harus sudah disetujui agar dapat menerima pelamar
        if ($tim->status_tim !== 'disetujui') {
            return back()->with('error', 'Tim ini belum disetujui dan tidak membuka lowongan anggota.');
        }

        // Cek 1: Mahasiswa tidak boleh mendaftar ke tim yang diketuainya sendiri
        if ($tim->nim === $nim) {
            return back()->with('error', 'Anda adalah Ketua Tim dari tim ini.');
        }

        // Cek 2: Mahasiswa sudah menjadi anggota resmi yang diterima
        if ($tim->anggotaDiterima->contains('nim', $nim)) {
            return back()->with('error', 'Anda sudah terdaftar sebagai anggota resmi tim ini.');
        }

        // Cek 3: Periksa riwayat pengajuan sebelumnya
        $existing = $tim->anggota()->where('nim', $nim)->first();

        if ($existing) {
            if ($existing->status_gabung === 'menunggu') {
                return back()->with('error', 'Pengajuan bergabung Anda sebelumnya sedang menunggu persetujuan Ketua Tim.');
            }
            if ($existing->status_gabung === 'diterima') {
                return back()->with('error', 'Anda sudah terdaftar sebagai anggota resmi tim ini.');
            }
        }

        // Cek 4: Kuota tim masih tersedia
        if ($tim->sisa_kuota_anggota <= 0) {
            return back()->with('error', 'Maaf, kuota anggota untuk tim ini sudah penuh.');
        }

        $cvPath = null;
        try {
            $cvPath = $request->file('cv')->store('cv', 'public');

            // Simpan pengajuan anggota: jika sebelumnya ditolak, reset status jadi 'menunggu'
            DB::transaction(function () use ($existing, $tim, $nim, $posisiDilamar, $pesanSingkat, $cvPath) {
                $payload = [
                    'status_gabung' => 'menunggu',
                    'peran' => $posisiDilamar,
                    'file_cv' => $cvPath,
                ];

                if (Schema::hasColumn('anggota_tim', 'posisi_dilamar')) {
                    $payload['posisi_dilamar'] = Str::limit($posisiDilamar, 60, '');
                }

                if (Schema::hasColumn('anggota_tim', 'pesan')) {
                    $payload['pesan'] = $pesanSingkat !== null ? Str::limit($pesanSingkat, 300, '') : null;
                }

                if ($existing) {
                    $oldCv = $existing->file_cv;
                    $payload['created_at'] = now();
                    $existing->update($payload);

                    if ($oldCv && $oldCv !== $cvPath && Storage::disk('public')->exists($oldCv)) {
                        Storage::disk('public')->delete($oldCv);
                    }
                } else {
                    $payload['id_tim'] = $tim->id_tim;
                    $payload['nim'] = $nim;
                    AnggotaTim::create($payload);
                }
            });

            return redirect()->route('tim.lowongan')->with('success', "Pengajuan diri bergabung ke tim '{$tim->nama_tim}' sebagai {$posisiDilamar} berhasil dikirim! Menunggu persetujuan Ketua Tim.");
        } catch (\Throwable $e) {
            if ($cvPath && Storage::disk('public')->exists($cvPath)) {
                Storage::disk('public')->delete($cvPath);
            }
            report($e);

            return back()->withInput()->with('modal_tim_id', $id)->with('error', 'Terjadi kesalahan saat mengirim pengajuan gabung: '.$e->getMessage());
        }
    }

    /**
     * Ketua Tim Merespon Pengajuan Anggota (Setujui / Tolak)
     */
    public function responAnggota(Request $request, $id)
    {
        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:diterima,ditolak',
        ]);

        $anggota = AnggotaTim::with(['tim', 'mahasiswa'])->findOrFail($id);

        // Hanya ketua tim yang bersangkutan yang dapat memvalidasi pengajuan
        if ($anggota->tim->nim !== $authUser['id']) {
            return back()->with('error', 'Hanya Ketua Tim yang berhak menyetujui atau menolak calon anggota.');
        }

        // Tim harus berstatus disetujui agar bisa mengelola anggota
        if ($anggota->tim->status_tim !== 'disetujui') {
            return back()->with('error', 'Tim ini belum disetujui oleh admin, sehingga belum dapat mengelola atau menerima anggota.');
        }

        if ($validated['status'] === 'diterima') {
            // Cek apakah kuota masih cukup
            if ($anggota->tim->sisa_kuota_anggota <= 0) {
                return back()->with('error', "Kuota anggota tim sudah penuh ({$anggota->tim->kuota_anggota} anggota). Tidak dapat menerima anggota lagi.");
            }

            $anggota->setujuiAnggota();
            $namaMhs = $anggota->mahasiswa->nama ?? "NIM {$anggota->nim}";

            return back()->with('success', "Mahasiswa {$namaMhs} telah disetujui bergabung ke dalam tim sebagai {$anggota->peran}.");
        }

        $anggota->tolakAnggota();

        return back()->with('success', 'Pengajuan bergabung anggota telah ditolak.');
    }

    /**
     * Unduh atau lihat berkas CV (Ketua Tim atau Calon Anggota) dengan pemeriksaan hak akses resmi.
     */
    public function downloadCv(Request $request, string $type, $id)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            abort(403, 'Akses tidak diizinkan. Silakan login terlebih dahulu.');
        }

        $nim = $authUser['id'];
        $role = $authUser['role'];

        $fileCv = null;
        $downloadName = 'CV.pdf';

        if ($type === 'ketua') {
            $tim = Tim::with(['ketua', 'anggota'])->findOrFail($id);

            // Hanya Admin, Ketua Tim, Anggota Tim bersangkutan, atau Dosen Pembimbing yang boleh melihat
            $isKetua = ($tim->nim === $nim);
            $isAnggota = $tim->anggota()->where('nim', $nim)->exists();
            $isPembimbing = $tim->pengajuanBimbingan()->where('nidn', $nim)->exists();
            $isAdmin = ($role === 'admin');

            if (! ($isKetua || $isAnggota || $isPembimbing || $isAdmin)) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat CV Ketua Tim ini.');
            }

            $fileCv = $tim->file_cv;
            $nama = $tim->ketua->nama ?? 'Ketua';
            $downloadName = 'CV_Ketua_'.Str::slug($nama).'.pdf';
        } elseif ($type === 'anggota') {
            $anggota = AnggotaTim::with(['tim', 'mahasiswa'])->findOrFail($id);

            // Hanya Admin, Ketua Tim tempat melamar, anggota itu sendiri, sesama anggota resmi, atau Dosen Pembimbing
            $isSelf = ($anggota->nim === $nim);
            $isKetua = ($anggota->tim && $anggota->tim->nim === $nim);
            $isSesamaAnggota = ($anggota->tim && $anggota->tim->anggotaDiterima()->where('nim', $nim)->exists());
            $isPembimbing = ($anggota->tim && $anggota->tim->pengajuanBimbingan()->where('nidn', $nim)->exists());
            $isAdmin = ($role === 'admin');

            if (! ($isSelf || $isKetua || $isSesamaAnggota || $isPembimbing || $isAdmin)) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat CV pelamar ini.');
            }

            $fileCv = $anggota->file_cv;
            $nama = $anggota->mahasiswa->nama ?? 'Anggota';
            $downloadName = 'CV_Anggota_'.Str::slug($nama).'.pdf';
        } else {
            abort(404, 'Tipe CV tidak valid.');
        }

        if (! $fileCv) {
            abort(404, 'File CV belum diunggah atau tidak ditemukan.');
        }

        $disk = Storage::disk('public')->exists($fileCv) ? Storage::disk('public') : Storage::disk('local');

        if (! $disk->exists($fileCv)) {
            abort(404, 'Berkas fisik CV tidak ditemukan pada server penyimpanan.');
        }

        return $disk->response($fileCv, $downloadName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"',
        ]);
    }

    /**
     * Ambil record Mahasiswa milik akun yang sedang login (hanya untuk role Mahasiswa / Ketua Tim).
     */
    private function resolveCurrentMahasiswa(): Mahasiswa
    {
        $authUser = $this->authUser();
        if (! $authUser || ! in_array($authUser['role'] ?? '', ['mahasiswa', 'ketua_tim'], true)) {
            abort(403, 'Hanya Mahasiswa dan Ketua Tim yang dapat mengakses halaman Profil Saya.');
        }

        $nim = Tim::currentUserMahasiswaNim() ?? (string) ($authUser['id'] ?? '');
        if ($nim === '') {
            abort(403, 'Identitas mahasiswa tidak ditemukan.');
        }

        $mahasiswa = Mahasiswa::where('nim', $nim)->first();

        if (! $mahasiswa) {
            /** @var User|null $currentUser */
            $currentUser = Auth::user();
            $mahasiswa = Mahasiswa::create([
                'nim' => $nim,
                'nama' => $currentUser->name ?? ($authUser['nama'] ?? 'Mahasiswa JTI'),
                'email_kampus' => $currentUser->email ?? ($authUser['email'] ?? ($nim.'@student.polinema.ac.id')),
                'password' => $currentUser->password ?? Hash::make('password123'),
                'prodi' => $currentUser->prodi ?? ($authUser['prodi'] ?? 'D-IV Sistem Informasi Bisnis'),
                'angkatan' => (int) ($currentUser->angkatan ?? ($authUser['angkatan'] ?? 2024)),
            ]);
        }

        return $mahasiswa;
    }

    /**
     * Halaman Profil Saya (Khusus Mahasiswa dan Ketua Tim).
     */
    public function profil(): View
    {
        $mahasiswa = $this->resolveCurrentMahasiswa();
        $nim = $mahasiswa->nim;

        $roleLabel = Tim::ketuaDisetujui($nim) ? 'Ketua Tim' : 'Mahasiswa';

        // Portofolio (maks 3 tautan {label, url})
        $rawPortofolio = Schema::hasColumn('mahasiswa', 'portofolio')
            ? $mahasiswa->portofolio
            : session("profil_portofolio_{$nim}", []);

        if (is_string($rawPortofolio)) {
            $rawPortofolio = json_decode($rawPortofolio, true) ?: [];
        }

        $portofolio = [];
        if (is_array($rawPortofolio)) {
            foreach (array_slice($rawPortofolio, 0, 3) as $item) {
                if (is_array($item) && ! empty($item['url'])) {
                    $portofolio[] = [
                        'label' => trim((string) ($item['label'] ?? '')),
                        'url' => trim((string) $item['url']),
                    ];
                }
            }
        }

        // Ringkasan Aktivitas: Tim yang diikuti (sebagai Ketua Tim atau Anggota)
        // Hanya mengambil keanggotaan milik mahasiswa yang sedang login (tidak memuat NIM/email mahasiswa lain)
        $timDiikuti = Tim::query()
            ->where(function ($q) use ($nim) {
                $q->where('nim', $nim)
                    ->orWhereHas('anggotaDiterima', function ($sub) use ($nim) {
                        $sub->where('nim', $nim);
                    });
            })
            ->with([
                'lomba',
                'anggota' => function ($q) use ($nim) {
                    $q->where('nim', $nim);
                },
            ])
            ->latest()
            ->get();

        // Prestasi pada tim yang diikuti mahasiswa ini
        $timDisetujuiIds = $timDiikuti->where('status_tim', 'disetujui')->pluck('id_tim');
        $daftarPrestasi = collect();

        if ($timDisetujuiIds->isNotEmpty()) {
            $daftarPrestasi = ProgresBabak::with(['pengajuan.tim.lomba', 'pengajuan.dosen'])
                ->where('status_setuju', 'disetujui')
                ->whereHas('pengajuan', function ($q) use ($timDisetujuiIds) {
                    $q->whereIn('id_tim', $timDisetujuiIds);
                })
                ->orderByDesc('tanggal_update')
                ->get()
                ->filter(fn (ProgresBabak $progres) => $progres->isJuara())
                ->values();
        }

        return view('profil', [
            'isLimitedView' => false,
            'mahasiswa' => $mahasiswa,
            'roleLabel' => $roleLabel,
            'portofolio' => $portofolio,
            'timDiikuti' => $timDiikuti,
            'daftarPrestasi' => $daftarPrestasi,
        ]);
    }

    /**
     * Halaman Profil Terbatas Pelamar untuk Ketua Tim Tujuan (/tim/permintaan/{permintaan}/profil).
     */
    public function profilPelamar($permintaan): View
    {
        $permintaanModel = AnggotaTim::with(['tim.lomba', 'mahasiswa'])->findOrFail($permintaan);

        if (! Gate::forUser(Auth::user())->allows('view-permintaan-profil', $permintaanModel)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat profil pelamar ini.');
        }

        $pelamar = $permintaanModel->mahasiswa;
        if (! $pelamar) {
            abort(404, 'Data mahasiswa pelamar tidak ditemukan.');
        }

        $rawPortofolio = Schema::hasColumn('mahasiswa', 'portofolio')
            ? $pelamar->portofolio
            : session("profil_portofolio_{$pelamar->nim}", []);

        if (is_string($rawPortofolio)) {
            $rawPortofolio = json_decode($rawPortofolio, true) ?: [];
        }

        $portofolio = [];
        if (is_array($rawPortofolio)) {
            foreach (array_slice($rawPortofolio, 0, 3) as $item) {
                if (is_array($item) && ! empty($item['url'])) {
                    $portofolio[] = [
                        'label' => trim((string) ($item['label'] ?? '')),
                        'url' => trim((string) $item['url']),
                    ];
                }
            }
        }

        $posisiDilamar = $permintaanModel->posisi_dilamar ?: ($permintaanModel->peran ?: 'Anggota');
        $pesanPelamar = $permintaanModel->pesan;

        return view('profil', [
            'isLimitedView' => true,
            'pelamar' => $pelamar,
            'permintaan' => $permintaanModel,
            'tim' => $permintaanModel->tim,
            'portofolio' => $portofolio,
            'posisiDilamar' => $posisiDilamar,
            'pesanPelamar' => $pesanPelamar,
        ]);
    }

    /**
     * Simpan perubahan Portofolio milik mahasiswa yang sedang login.
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $mahasiswa = $this->resolveCurrentMahasiswa();

        // Filter baris portofolio yang benar-benar kosong (label & url sama-sama kosong)
        if ($request->has('portofolio') && is_array($request->input('portofolio'))) {
            $filteredPortofolio = [];
            foreach ($request->input('portofolio') as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $label = trim((string) ($item['label'] ?? ''));
                $url = trim((string) ($item['url'] ?? ''));
                if ($label === '' && $url === '') {
                    continue;
                }
                $filteredPortofolio[] = [
                    'label' => $label,
                    'url' => $url,
                ];
            }
            $request->merge(['portofolio' => array_values($filteredPortofolio)]);
        }

        $validated = $request->validate([
            'portofolio' => ['nullable', 'array', 'max:3'],
            'portofolio.*.label' => ['nullable', 'string', 'max:40'],
            'portofolio.*.url' => ['required', 'url', 'starts_with:https://', 'max:255'],
        ], [
            'portofolio.array' => 'Format daftar portofolio tidak valid.',
            'portofolio.max' => 'Portofolio maksimal terdiri dari 3 tautan.',
            'portofolio.*.label.max' => 'Label tautan portofolio maksimal 40 karakter.',
            'portofolio.*.url.required' => 'URL portofolio wajib diisi pada setiap baris yang ditambahkan.',
            'portofolio.*.url.url' => 'Format URL portofolio tidak valid.',
            'portofolio.*.url.starts_with' => 'URL portofolio wajib diawali dengan https://.',
            'portofolio.*.url.max' => 'URL portofolio maksimal 255 karakter.',
        ]);

        $portofolio = [];
        foreach ($validated['portofolio'] ?? [] as $item) {
            $portofolio[] = [
                'label' => trim((string) ($item['label'] ?? '')),
                'url' => trim((string) $item['url']),
            ];
        }

        if (Schema::hasColumn('mahasiswa', 'portofolio')) {
            $mahasiswa->update(['portofolio' => $portofolio]);
        } else {
            session(["profil_portofolio_{$mahasiswa->nim}" => $portofolio]);
        }

        return redirect()
            ->route('profil.show')
            ->with('success', 'Portofolio Anda berhasil diperbarui.');
    }

    /**
     * Ganti kata sandi akun mahasiswa yang sedang login.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $mahasiswa = $this->resolveCurrentMahasiswa();

        // Normalisasi nama field jika dikirim dengan current_password / password
        $merge = [];
        if (! $request->filled('password_lama') && $request->filled('current_password')) {
            $merge['password_lama'] = $request->input('current_password');
        }
        if (! $request->filled('password_baru') && $request->filled('password')) {
            $merge['password_baru'] = $request->input('password');
        }
        if (! $request->filled('password_baru_confirmation') && $request->filled('password_confirmation')) {
            $merge['password_baru_confirmation'] = $request->input('password_confirmation');
        }
        if (! empty($merge)) {
            $request->merge($merge);
        }

        $validator = Validator::make($request->all(), [
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password_lama.required' => 'Kata sandi lama wajib diisi.',
            'password_baru.required' => 'Kata sandi baru wajib diisi.',
            'password_baru.min' => 'Kata sandi baru minimal harus terdiri dari 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            return redirect(route('profil.show').'#kata-sandi')
                ->withErrors($validator)
                ->with('error', $validator->errors()->first());
        }

        $passwordLama = (string) $request->input('password_lama');
        $passwordBaru = (string) $request->input('password_baru');

        // Verifikasi kata sandi lama terhadap record Mahasiswa (atau User jika disinkronkan)
        $userRecord = User::where('nim_nip', $mahasiswa->nim)
            ->orWhereRaw('LOWER(email) = ?', [strtolower($mahasiswa->email_kampus)])
            ->first();

        $validOldPassword = Hash::check($passwordLama, $mahasiswa->password)
            || ($userRecord && Hash::check($passwordLama, $userRecord->password));

        if (! $validOldPassword) {
            return redirect(route('profil.show').'#kata-sandi')
                ->withErrors(['password_lama' => 'Kata sandi lama yang Anda masukkan tidak sesuai.'])
                ->with('error', 'Kata sandi lama yang Anda masukkan tidak sesuai.');
        }

        $hashedPassword = Hash::make($passwordBaru);

        $mahasiswa->update([
            'password' => $hashedPassword,
        ]);

        if ($userRecord) {
            $userRecord->update([
                'password' => $hashedPassword,
            ]);
        }

        return redirect(route('profil.show').'#kata-sandi')
            ->with('success', 'Kata sandi akun Anda berhasil diperbarui.');
    }
}
