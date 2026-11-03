<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ResponPengajuanRequest;
use App\Http\Requests\ResponProgresBabakRequest;
use App\Http\Requests\StoreProgresBabakRequest;
use App\Http\Requests\UpdateLogbookRequest;
use App\Http\Requests\ValidasiLogbookRequest;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Logbook;
use App\Models\Lomba;
use App\Models\PengajuanBimbingan;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;
use App\Services\DosenRecommendationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BimbinganController extends Controller
{
    /**
     * Ambil data pengguna yang sedang login (Laravel Auth) dalam bentuk array
     * dengan format yang sama seperti sesi login lama: id, nama, email, role, extra.
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
     * Halaman Utama Modul Bimbingan
     * Alur:
     * 1. Cek login aktor.
     * 2. Jika Mahasiswa: periksa apakah sudah bergabung ke tim lomba.
     *    - Jika BELUM ikut lomba: tampilkan pesan prasyarat & daftar lomba yang bisa diikuti.
     *    - Jika SUDAH ikut lomba: tampilkan detail tim, status pembimbing, logbook, & progres babak.
     * 3. Jika Dosen: tampilkan daftar tim bimbingan, verifikasi pengajuan, dan validasi logbook.
     * 4. Jika Admin: tampilkan rekapitulasi seluruh aktivitas bimbingan lomba JTI.
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Anda terlebih dahulu untuk mengakses Modul Bimbingan.');
        }

        $role = $authUser['role'];

        if ($role === 'mahasiswa') {
            $nim = $authUser['id'];

            // Cari seluruh tim di mana mahasiswa ini adalah Ketua atau Anggota yang diterima dalam 1 query tunggal
            $timList = Tim::where(function ($q) use ($nim) {
                $q->where('nim', $nim)
                    ->orWhereHas('anggotaDiterima', function ($sub) use ($nim) {
                        $sub->where('nim', $nim);
                    });
            })
                ->with(['lomba', 'ketua', 'bimbinganDisetujui.dosen', 'pengajuanBimbingan.dosen', 'anggotaDiterima.mahasiswa', 'anggota.mahasiswa'])
                ->get();

            $hasLomba = $timList->isNotEmpty();

            // KONDISI 1: Belum terdaftar dalam tim lomba manapun
            if (! $hasLomba) {
                $lombaTersedia = Lomba::terverifikasi()
                    ->where('tenggat', '>=', Carbon::today())
                    ->orderBy('tenggat', 'asc')
                    ->take(6)
                    ->get();

                if ($lombaTersedia->isEmpty()) {
                    $lombaTersedia = Lomba::terverifikasi()->latest()->take(6)->get();
                }

                return view('bimbingan.index', [
                    'authUser' => $authUser,
                    'role' => 'mahasiswa',
                    'hasLomba' => false,
                    'lombaTersedia' => $lombaTersedia,
                    'timList' => collect(),
                ]);
            }

            // KONDISI 2: Sudah terdaftar dalam tim lomba
            $selectedTimId = $request->query('tim_id', $timList->first()->id_tim);
            $selectedTim = $timList->firstWhere('id_tim', (int) $selectedTimId) ?? $timList->first();

            // Ambil pengajuan bimbingan untuk tim yang dipilih
            $pengajuan = PengajuanBimbingan::where('id_tim', $selectedTim->id_tim)
                ->with([
                    'dosen',
                    'logbook' => function ($q) {
                        $q->orderBy('tanggal', 'desc');
                    },
                    'progresBabak' => function ($q) {
                        $q->orderBy('tanggal_update', 'desc');
                    },
                ])
                ->latest()
                ->first();

            // Daftar dosen yang tersedia untuk pengajuan pembimbing
            $dosenList = Dosen::with('pengajuanBimbingan')->get();

            // Rekomendasi Dosen Pembimbing berbasis AI (Gemini) sesuai bidang lomba tim
            $aiRekomendasi = [
                'map' => [],
                'top' => [],
                'source' => 'none',
            ];

            if ($selectedTim && $selectedTim->lomba) {
                /** @var DosenRecommendationService $recService */
                $recService = app(DosenRecommendationService::class);
                $aiRekomendasi = $recService->recommend($selectedTim->lomba, $dosenList);

                // Urutkan daftar dosen agar rekomendasi AI teratas tampil paling awal
                $dosenList = $dosenList->sortByDesc(function (Dosen $d) use ($aiRekomendasi) {
                    $skor = $aiRekomendasi['map'][(string) $d->nidn]['skor'] ?? 0;

                    // Dosen yang memiliki kuota diprioritaskan
                    return ($d->sisa_kuota > 0 ? 1000 : 0) + $skor;
                })->values();
            }

            $isKetua = ($selectedTim->nim === $authUser['id']);

            return view('bimbingan.index', [
                'authUser' => $authUser,
                'role' => 'mahasiswa',
                'hasLomba' => true,
                'timList' => $timList,
                'selectedTim' => $selectedTim,
                'pengajuan' => $pengajuan,
                'dosenList' => $dosenList,
                'aiRekomendasi' => $aiRekomendasi,
                'isKetua' => $isKetua,
            ]);
        }

        if ($role === 'dosen') {
            $nidn = $authUser['id'];
            $dosen = Dosen::where('nidn', $nidn)->first();

            $search = $request->query('search');
            $statusFilter = $request->query('status');
            $lombaFilter = $request->query('lomba_id');

            $query = PengajuanBimbingan::where('nidn', $nidn)
                ->with([
                    'tim.lomba',
                    'tim.ketua',
                    'tim.anggotaDiterima.mahasiswa',
                    'logbook' => function ($q) {
                        $q->orderBy('tanggal', 'desc');
                    },
                    'progresBabak' => function ($q) {
                        $q->orderBy('tanggal_update', 'desc')->orderBy('id_progres', 'desc');
                    },
                ]);

            if ($search) {
                $query->whereHas('tim', function ($q) use ($search) {
                    $q->where('nama_tim', 'like', "%{$search}%")
                        ->orWhereHas('ketua', function ($sq) use ($search) {
                            $sq->where('nama', 'like', "%{$search}%")
                                ->orWhere('nim', 'like', "%{$search}%");
                        });
                });
            }

            if ($statusFilter && in_array($statusFilter, ['menunggu', 'disetujui', 'ditolak'])) {
                $query->where('status', $statusFilter);
            }

            if ($lombaFilter && $lombaFilter !== 'all') {
                $query->whereHas('tim', function ($q) use ($lombaFilter) {
                    $q->where('id_lomba', (int) $lombaFilter);
                });
            }

            $pengajuanList = $query->latest()->paginate(10)->withQueryString();

            // Statistik Ringkasan Dosen
            $totalSesiLogbook = Logbook::whereHas('pengajuan', fn ($q) => $q->where('nidn', $nidn))->count();
            $logbookMenungguValidasi = Logbook::whereHas('pengajuan', fn ($q) => $q->where('nidn', $nidn))
                ->where('status_validasi', 'menunggu')
                ->count();
            $timAktifCount = PengajuanBimbingan::where('nidn', $nidn)->where('status', 'disetujui')->count();
            $pengajuanMenungguCount = PengajuanBimbingan::where('nidn', $nidn)->where('status', 'menunggu')->count();
            $riwayatLamaCount = PengajuanBimbingan::where('nidn', $nidn)->where('status', 'ditolak')->count();

            // Daftar opsi lomba unik yang pernah diajukan ke dosen ini untuk dropdown filter
            $lombaOptions = Lomba::whereHas('tim.pengajuanBimbingan', function ($q) use ($nidn) {
                $q->where('nidn', $nidn);
            })->get(['id_lomba', 'nama_lomba']);

            return view('bimbingan.index', [
                'authUser' => $authUser,
                'role' => 'dosen',
                'dosen' => $dosen,
                'pengajuanList' => $pengajuanList,
                'hasLomba' => true,
                'totalSesiLogbook' => $totalSesiLogbook,
                'logbookMenungguValidasi' => $logbookMenungguValidasi,
                'timAktifCount' => $timAktifCount,
                'pengajuanMenungguCount' => $pengajuanMenungguCount,
                'riwayatLamaCount' => $riwayatLamaCount,
                'lombaOptions' => $lombaOptions,
                'search' => $search,
                'statusFilter' => $statusFilter,
                'lombaFilter' => $lombaFilter,
            ]);
        }

        // Role Admin
        $pengajuanList = PengajuanBimbingan::with([
            'dosen',
            'tim.lomba',
            'tim.ketua',
            'tim.anggotaDiterima.mahasiswa',
            'logbook',
            'progresBabak',
        ])
            ->latest()
            ->get();

        return view('bimbingan.index', [
            'authUser' => $authUser,
            'role' => 'admin',
            'pengajuanList' => $pengajuanList,
            'hasLomba' => true,
        ]);
    }

    /**
     * Mahasiswa Mengajukan Dosen Pembimbing untuk Tim
     */
    public function ajukan(Request $request)
    {
        $validated = $request->validate([
            'id_tim' => 'required|exists:tim,id_tim',
            'nidn' => 'required|exists:dosen,nidn',
            'catatan' => 'nullable|string|max:500',
        ]);

        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa ketua/anggota tim yang dapat mengajukan bimbingan.');
        }

        $tim = Tim::findOrFail($validated['id_tim']);

        // Verifikasi bahwa user adalah ketua tim
        if ($tim->nim !== $authUser['id']) {
            return back()->with('error', 'Hanya Ketua Tim yang memiliki hak wewenang untuk memilih dan mengajukan Dosen Pembimbing.');
        }

        // Cek jika sudah ada pengajuan bimbingan aktif atau menunggu
        $existing = PengajuanBimbingan::where('id_tim', $tim->id_tim)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Tim ini sudah memiliki pengajuan bimbingan aktif dengan Dosen '.($existing->dosen->nama ?? '').'.');
        }

        // Cek kuota bimbingan dosen
        $dosen = Dosen::where('nidn', $validated['nidn'])->first();
        if ($dosen && $dosen->sisa_kuota <= 0) {
            return back()->with('error', 'Kuota bimbingan untuk '.$dosen->nama.' sudah penuh. Silakan pilih dosen lain.');
        }

        PengajuanBimbingan::create([
            'id_tim' => $tim->id_tim,
            'nidn' => $validated['nidn'],
            'tgl_pengajuan' => Carbon::now()->toDateString(),
            'status' => 'menunggu',
            'catatan' => $validated['catatan'] ?? 'Pengajuan bimbingan persiapan kompetisi.',
        ]);

        return back()->with('success', 'Pengajuan bimbingan berhasil dikirim ke '.($dosen->nama ?? 'dosen').'. Mohon menunggu persetujuan.');
    }

    /**
     * Mahasiswa Menambahkan Catatan Logbook Bimbingan
     */
    public function storeLogbook(Request $request)
    {
        $validated = $request->validate([
            'id_pengajuan' => 'required|exists:pengajuan_bimbingan,id_pengajuan',
            'tanggal' => 'required|date',
            'materi' => 'required|string|max:1000',
            'tindak_lanjut' => 'nullable|string|max:1000',
        ]);

        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya anggota tim mahasiswa yang dapat menambahkan logbook bimbingan.');
        }

        $pengajuan = PengajuanBimbingan::with(['tim.anggotaDiterima'])->findOrFail($validated['id_pengajuan']);

        if ($pengajuan->status !== 'disetujui') {
            return back()->with('error', 'Logbook hanya dapat ditambahkan setelah bimbingan resmi disetujui oleh Dosen Pembimbing.');
        }

        $isMember = ($pengajuan->tim->nim === $authUser['id']) ||
            $pengajuan->tim->anggotaDiterima->contains('nim', $authUser['id']);

        if (! $isMember) {
            return back()->with('error', 'Anda bukan anggota resmi dari tim ini.');
        }

        Logbook::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => $validated['tanggal'],
            'materi' => $validated['materi'],
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? '-',
            'status_validasi' => 'menunggu',
        ]);

        return back()->with('success', 'Catatan logbook bimbingan berhasil dicatat!');
    }

    /**
     * Dosen Memvalidasi Logbook (Setujui / Revisi dengan Catatan)
     */
    public function validasiLogbook(ValidasiLogbookRequest $request, $id)
    {
        $validated = $request->validated();

        $authUser = $this->authUser();
        if (! $authUser || ! in_array($authUser['role'], ['dosen', 'admin'])) {
            return back()->with('error', 'Hanya Dosen Pembimbing atau Administrator yang berhak memvalidasi logbook.');
        }

        $logbook = Logbook::with('pengajuan')->findOrFail($id);

        if ($authUser['role'] === 'dosen' && $logbook->pengajuan->nidn !== $authUser['id']) {
            return back()->with('error', 'Anda hanya dapat memvalidasi logbook untuk tim bimbingan Anda.');
        }

        $logbook->validasi($validated['status_validasi'], $validated['catatan_dosen'] ?? null);

        $pesan = $validated['status_validasi'] === 'disetujui'
            ? 'Logbook bimbingan berhasil disetujui.'
            : 'Status logbook ditandai memerlukan revisi mahasiswa beserta catatan feedback.';

        return back()->with('success', $pesan);
    }

    /**
     * Mahasiswa Memperbarui Entri Logbook yang Memerlukan Revisi
     */
    public function updateLogbook(UpdateLogbookRequest $request, $id)
    {
        $validated = $request->validated();

        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya anggota tim mahasiswa yang dapat memperbarui logbook bimbingan.');
        }

        $logbook = Logbook::with(['pengajuan.tim.anggotaDiterima'])->findOrFail($id);

        $isMember = ($logbook->pengajuan->tim->nim === $authUser['id']) ||
            $logbook->pengajuan->tim->anggotaDiterima->contains('nim', $authUser['id']);

        if (! $isMember) {
            return back()->with('error', 'Anda bukan anggota resmi dari tim ini.');
        }

        if ($logbook->status_validasi === 'disetujui') {
            return back()->with('error', 'Logbook yang sudah disetujui oleh Dosen Pembimbing tidak dapat diubah.');
        }

        $logbook->update([
            'tanggal' => $validated['tanggal'],
            'materi' => $validated['materi'],
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? '-',
            'status_validasi' => 'menunggu',
        ]);

        return back()->with('success', 'Catatan logbook berhasil diperbarui dan diajukan ulang untuk validasi dosen.');
    }

    /**
     * Mahasiswa Melaporkan Capaian Progres Babak Kompetisi
     */
    public function storeProgres(StoreProgresBabakRequest $request)
    {
        $validated = $request->validated();
        $authUser = $this->authUser();

        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya anggota tim mahasiswa yang dapat melaporkan progres babak.');
        }

        $pengajuan = PengajuanBimbingan::with(['tim.anggotaDiterima'])->findOrFail($validated['id_pengajuan']);

        if ($pengajuan->status !== 'disetujui') {
            return back()->with('error', 'Laporan progres babak hanya dapat diajukan setelah bimbingan resmi disetujui oleh Dosen Pembimbing.');
        }

        $isMember = ($pengajuan->tim->nim === $authUser['id']) ||
            $pengajuan->tim->anggotaDiterima->contains('nim', $authUser['id']);

        if (! $isMember) {
            return back()->with('error', 'Anda bukan anggota resmi dari tim ini.');
        }

        ProgresBabak::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => $validated['babak'],
            'tanggal_update' => $validated['tanggal_update'],
            'hasil_akhir' => $validated['hasil_akhir'],
            'status_setuju' => 'menunggu',
        ]);

        return back()->with('success', 'Laporan progres babak kompetisi berhasil dikirimkan! Menunggu verifikasi Dosen Pembimbing.');
    }

    /**
     * Dosen Memvalidasi / Merespon Progres Babak (Setujui / Tolak dengan Catatan)
     */
    public function responProgresBabak(ResponProgresBabakRequest $request, $id)
    {
        $validated = $request->validated();
        $authUser = $this->authUser();

        if (! $authUser || ! in_array($authUser['role'], ['dosen', 'admin'])) {
            return back()->with('error', 'Hanya Dosen Pembimbing atau Administrator yang berhak memvalidasi progres babak.');
        }

        $progres = ProgresBabak::with('pengajuan')->findOrFail($id);

        if ($authUser['role'] === 'dosen' && $progres->pengajuan->nidn !== $authUser['id']) {
            return back()->with('error', 'Anda hanya dapat memvalidasi progres babak untuk tim bimbingan Anda.');
        }

        if ($validated['status_setuju'] === 'disetujui') {
            $progres->setujuiProgres($validated['catatan_dosen'] ?? null);
            $pesan = "Capaian babak '{$progres->babak}' berhasil disetujui.";
        } else {
            $progres->tolakProgres($validated['catatan_dosen']);
            $pesan = "Capaian babak '{$progres->babak}' ditolak dengan catatan evaluasi.";
        }

        return back()->with('success', $pesan);
    }

    /**
     * Dosen / Admin Merespon Pengajuan Bimbingan (Setujui / Tolak)
     */
    public function responPengajuan(ResponPengajuanRequest $request, $id)
    {
        $validated = $request->validated();
        $authUser = $this->authUser();

        if (! $authUser || ! in_array($authUser['role'], ['dosen', 'admin'])) {
            return back()->with('error', 'Hanya Dosen Pembimbing atau Administrator yang berhak merespons pengajuan bimbingan.');
        }

        return DB::transaction(function () use ($validated, $authUser, $id) {
            /** @var PengajuanBimbingan $pengajuan */
            $pengajuan = PengajuanBimbingan::with('tim')->lockForUpdate()->findOrFail($id);

            // 1. Otorisasi per dosen pembimbing
            if ($authUser['role'] === 'dosen' && $pengajuan->nidn !== $authUser['id']) {
                return back()->with('error', 'Anda hanya dapat merespons pengajuan bimbingan yang ditujukan kepada Anda.');
            }

            // 2. Cegah respon ganda: hanya pengajuan berstatus 'menunggu' yang boleh direspon
            if ($pengajuan->status !== 'menunggu') {
                return back()->with('error', "Pengajuan bimbingan ini sudah direspons sebelumnya dengan status '{$pengajuan->status}'.");
            }

            // 3. Jika menyetujui: cek sisa kuota bimbingan dosen dengan lockForUpdate
            if ($validated['status'] === 'disetujui') {
                $dosen = Dosen::where('nidn', $pengajuan->nidn)->lockForUpdate()->first();

                if (! $dosen) {
                    return back()->with('error', 'Data Dosen Pembimbing tidak ditemukan.');
                }

                $bimbinganAktifCount = PengajuanBimbingan::where('nidn', $dosen->nidn)
                    ->where('status', 'disetujui')
                    ->count();

                if ($bimbinganAktifCount >= $dosen->kuota_bimbingan) {
                    return back()->with('error', "Kuota bimbingan untuk {$dosen->nama} sudah penuh (Maks: {$dosen->kuota_bimbingan} tim). Tidak dapat menerima pengajuan ini.");
                }
            }

            // 4. Update status dan catatan alasan
            $pengajuan->update([
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? ($validated['status'] === 'disetujui' ? 'Bimbingan resmi diterima oleh Dosen Pembimbing.' : $pengajuan->catatan),
            ]);

            $statusTeks = $validated['status'] === 'disetujui' ? 'disetujui' : 'ditolak';

            return back()->with('success', "Pengajuan bimbingan tim {$pengajuan->tim->nama_tim} telah {$statusTeks}.");
        });
    }

    /**
     * Fitur Pendaftaran Tim Cepat untuk Mahasiswa yang Belum Ikut Lomba
     * Mempermudah alur pengujian & simulasi modul bimbingan
     */
    public function gabungLombaCepat(Request $request)
    {
        $validated = $request->validate([
            'id_lomba' => 'required|exists:lomba,id_lomba',
            'nama_tim' => 'required|string|max:150',
            'kuota_anggota' => 'nullable|integer|min:2|max:5',
        ]);

        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa yang dapat mendaftarkan tim kompetisi.');
        }

        $lomba = Lomba::findOrFail($validated['id_lomba']);

        // Buat tim baru dengan mahasiswa yang sedang login sebagai ketua tim
        $tim = Tim::create([
            'nama_tim' => $validated['nama_tim'],
            'id_lomba' => $lomba->id_lomba,
            'nim' => $authUser['id'],
            'kuota_anggota' => $validated['kuota_anggota'] ?? 3,
            'status_tim' => 'disetujui',
        ]);

        // Daftarkan ketua juga sebagai anggota tim utama dengan status diterima
        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $authUser['id'],
            'status_gabung' => 'diterima',
            'peran' => 'Ketua Tim',
        ]);

        Cache::flush();

        return redirect()->route('bimbingan.index', ['tim_id' => $tim->id_tim])
            ->with('success', "Selamat! Tim '{$tim->nama_tim}' berhasil didaftarkan untuk kompetisi '{$lomba->nama_lomba}'. Modul bimbingan Anda kini telah aktif!");
    }
}
