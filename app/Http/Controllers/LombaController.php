<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LombaController extends Controller
{
    /**
     * Menampilkan Katalog Lomba dengan Pencarian dan Filter Lengkap
     */
    public function index(Request $request)
    {
        // 1. Validasi & Sanitasi Parameter Filter
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'tingkat' => 'nullable|string|max:50',
            'tipe' => 'nullable|string|in:semua,individu,tim',
            'biaya' => 'nullable|string|in:semua,gratis,berbayar',
            'mode' => 'nullable|string|in:semua,online,offline,hybrid',
            'kategori' => 'nullable|string|max:100',
            'tenggat' => 'nullable|string|in:semua,buka,segera,tutup',
        ]);

        $query = Lomba::terverifikasi();

        // 2. Pencarian Nama Lomba, Penyelenggara, atau Deskripsi (Case-Insensitive)
        $keyword = trim((string) ($request->input('q') ?: $request->input('search')));
        if ($keyword !== '') {
            $lower = '%'.strtolower($keyword).'%';
            $query->where(function ($sub) use ($lower) {
                $sub->whereRaw('LOWER(nama_lomba) LIKE ?', [$lower])
                    ->orWhereRaw('LOWER(penyelenggara) LIKE ?', [$lower])
                    ->orWhereRaw('LOWER(deskripsi) LIKE ?', [$lower]);
            });
        }

        // 3. Filter Tingkat Lomba (Internasional, Nasional, Kampus, atau nilai eksisting seperti Regional)
        $tingkat = $request->input('tingkat');
        if ($tingkat && strtolower($tingkat) !== 'semua') {
            $query->whereRaw('LOWER(tingkat) = ?', [strtolower($tingkat)]);
        }

        // 4. Filter Biaya (Gratis vs Berbayar)
        $biaya = $request->input('biaya');
        if ($biaya === 'gratis') {
            $query->where('biaya', 0);
        } elseif ($biaya === 'berbayar') {
            $query->where('biaya', '>', 0);
        }

        // 5. Filter Tipe Lomba (Individu vs Kelompok/Tim)
        $hasTipeColumn = Schema::hasColumn('lomba', 'tipe_peserta') || Schema::hasColumn('lomba', 'tipe');
        $tipe = $request->input('tipe');
        if ($tipe && strtolower($tipe) !== 'semua') {
            $lower = strtolower($tipe);
            if (Schema::hasColumn('lomba', 'tipe_peserta')) {
                if ($lower === 'tim' || $lower === 'kelompok') {
                    $query->where(function ($sub) {
                        $sub->whereRaw('LOWER(tipe_peserta) = ?', ['kelompok'])
                            ->orWhereRaw('LOWER(tipe_peserta) = ?', ['tim']);
                    });
                } else {
                    $query->whereRaw('LOWER(tipe_peserta) = ?', [$lower]);
                }
            } elseif (Schema::hasColumn('lomba', 'tipe')) {
                $query->whereRaw('LOWER(tipe) = ?', [$lower]);
            }
        }

        // 6. Filter Mode Pelaksanaan (Online, Offline, Hybrid)
        $hasModeColumn = Schema::hasColumn('lomba', 'mode_pelaksanaan') || Schema::hasColumn('lomba', 'mode');
        $mode = $request->input('mode');
        if ($mode && strtolower($mode) !== 'semua') {
            $lower = strtolower($mode);
            if (Schema::hasColumn('lomba', 'mode_pelaksanaan')) {
                $query->whereRaw('LOWER(mode_pelaksanaan) = ?', [$lower]);
            } elseif (Schema::hasColumn('lomba', 'mode')) {
                $query->whereRaw('LOWER(mode) = ?', [$lower]);
            }
        }

        // 7. Filter Kategori Lomba
        $kategori = $request->input('kategori');
        if ($kategori && strtolower($kategori) !== 'semua') {
            $query->where('kategori', $kategori);
        }

        // 8. Filter Status Tenggat (Buka, Segera Berakhir, Tutup)
        $statusTenggat = $request->input('tenggat');
        $today = Carbon::today();
        if ($statusTenggat === 'buka') {
            $query->where('tenggat', '>=', $today);
        } elseif ($statusTenggat === 'segera') {
            $query->whereBetween('tenggat', [$today, $today->copy()->addDays(7)]);
        } elseif ($statusTenggat === 'tutup') {
            $query->where('tenggat', '<', $today);
        }

        // Urutkan: Lomba yang masih buka diprioritaskan lebih dulu, lalu diurutkan dari tenggat terdekat
        $lombaList = $query->orderByRaw('CASE WHEN tenggat >= CURRENT_DATE THEN 0 ELSE 1 END')
            ->orderBy('tenggat', 'asc')
            ->orderBy('id_lomba', 'desc')
            ->paginate(9)
            ->withQueryString();

        // Opsi Kategori untuk dropdown filter (sinkron dengan backend admin)
        $kategoriOptions = Lomba::kategoriOptions();

        // Total Lomba untuk informasi header
        $totalAktif = Lomba::terverifikasi()->where('tenggat', '>=', $today)->count();
        $totalSemua = Lomba::terverifikasi()->count();

        // Daftar pengajuan lomba milik mahasiswa / ketua tim yang sedang login
        $pengajuanSaya = collect();
        $currentUser = auth()->user();
        $userRole = $currentUser
            ? ($currentUser->role instanceof UserRole ? $currentUser->role->value : (string) $currentUser->role)
            : (session('auth_user.role') ?? null);

        if (in_array($userRole, ['mahasiswa', 'ketua_tim'], true)) {
            $authId = auth()->id() ?? session('auth_user.id');
            $mahasiswaNim = Tim::currentUserMahasiswaNim() ?? ($authId !== null ? (string) $authId : null);
            $hasSubmittedBy = Schema::hasColumn('lomba', 'submitted_by');
            $hasNim = Schema::hasColumn('lomba', 'nim');

            if ($authId !== null || $mahasiswaNim !== null) {
                $pengajuanSaya = Lomba::query()
                    ->where(function ($q) use ($hasSubmittedBy, $hasNim, $authId, $mahasiswaNim) {
                        if ($hasSubmittedBy && $authId !== null) {
                            $q->where('submitted_by', $authId);
                            if ($hasNim && $mahasiswaNim !== null) {
                                $q->orWhere('nim', $mahasiswaNim);
                            }
                        } elseif ($hasNim && $mahasiswaNim !== null) {
                            $q->where('nim', $mahasiswaNim);
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    })
                    ->orderByDesc('created_at')
                    ->orderByDesc('id_lomba')
                    ->get();
            }
        }

        return view('lomba.index', [
            'lombaList' => $lombaList,
            'pengajuanSaya' => $pengajuanSaya,
            'kategoriOptions' => $kategoriOptions,
            'totalAktif' => $totalAktif,
            'totalSemua' => $totalSemua,
            'hasTipeColumn' => $hasTipeColumn,
            'hasModeColumn' => $hasModeColumn,
            'activeFilters' => [
                'q' => $keyword,
                'tingkat' => $tingkat,
                'biaya' => $biaya,
                'tipe' => $tipe,
                'mode' => $mode,
                'kategori' => $kategori,
                'tenggat' => $statusTenggat,
            ],
        ]);
    }

    /**
     * Menyimpan Pengajuan Info Lomba Baru dari Mahasiswa / Ketua Tim
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = auth()->user();
        $userRole = $currentUser
            ? ($currentUser->role instanceof UserRole ? $currentUser->role->value : (string) $currentUser->role)
            : (session('auth_user.role') ?? null);

        if (! in_array($userRole, ['mahasiswa', 'ketua_tim'], true)) {
            abort(403, 'Hanya Mahasiswa dan Ketua Tim yang dapat mengajukan info lomba.');
        }

        // Normalisasi nama field alternatif (jika dikirim melalui alias)
        $mergeData = [];
        if (! $request->filled('tipe_peserta') && $request->filled('tipe')) {
            $mergeData['tipe_peserta'] = $request->input('tipe');
        }
        if (! $request->filled('mode_pelaksanaan') && $request->filled('mode')) {
            $mergeData['mode_pelaksanaan'] = $request->input('mode');
        }
        if (! $request->filled('tautan_daftar') && $request->filled('link_pendaftaran')) {
            $mergeData['tautan_daftar'] = $request->input('link_pendaftaran');
        }
        if (! $request->filled('deskripsi') && $request->filled('deskripsi_singkat')) {
            $mergeData['deskripsi'] = $request->input('deskripsi_singkat');
        }
        if (! $request->filled('tenggat') && $request->filled('tanggal')) {
            $mergeData['tenggat'] = $request->input('tanggal');
        }
        if ($request->has('biaya') && is_string($request->input('biaya'))) {
            $rawBiaya = trim($request->input('biaya'));
            if (preg_match('/^[Rp\s0-9.]+$/', $rawBiaya)) {
                $digits = preg_replace('/[^0-9]/', '', $rawBiaya);
                if ($digits !== '') {
                    $mergeData['biaya'] = (int) $digits;
                }
            }
        }
        if (! empty($mergeData)) {
            $request->merge($mergeData);
        }

        $validated = $request->validate([
            'nama_lomba' => ['required', 'string', 'max:255'],
            'penyelenggara' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100', Rule::in(Lomba::kategoriOptions())],
            'tingkat' => ['required', 'string', Rule::in(['Internasional', 'Nasional', 'Kampus', 'Regional', 'internasional', 'nasional', 'kampus', 'regional'])],
            'tipe_peserta' => ['required', 'string', Rule::in(['Individu', 'Kelompok', 'Tim', 'individu', 'kelompok', 'tim'])],
            'mode_pelaksanaan' => ['required', 'string', Rule::in(['Online', 'Offline', 'Hybrid', 'online', 'offline', 'hybrid'])],
            'biaya' => ['required', 'integer', 'min:0'],
            'tenggat' => ['required', 'date'],
            'tautan_daftar' => ['required', 'url', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ], [
            'nama_lomba.required' => 'Nama lomba wajib diisi.',
            'nama_lomba.max' => 'Nama lomba maksimal 255 karakter.',
            'penyelenggara.required' => 'Penyelenggara lomba wajib diisi.',
            'penyelenggara.max' => 'Penyelenggara lomba maksimal 150 karakter.',
            'kategori.required' => 'Kategori minat wajib dipilih.',
            'kategori.in' => 'Kategori minat yang dipilih tidak valid.',
            'tingkat.required' => 'Tingkat lomba wajib dipilih.',
            'tingkat.in' => 'Tingkat lomba yang dipilih tidak valid.',
            'tipe_peserta.required' => 'Tipe peserta wajib dipilih.',
            'tipe_peserta.in' => 'Tipe peserta harus Individu atau Kelompok.',
            'mode_pelaksanaan.required' => 'Mode pelaksanaan wajib dipilih.',
            'mode_pelaksanaan.in' => 'Mode pelaksanaan harus Online, Offline, atau Hybrid.',
            'biaya.required' => 'Biaya pendaftaran wajib diisi (isi 0 jika gratis).',
            'biaya.integer' => 'Biaya pendaftaran harus berupa angka bulat.',
            'biaya.min' => 'Biaya pendaftaran tidak boleh bernilai negatif.',
            'tenggat.required' => 'Tanggal / tenggat pendaftaran wajib diisi.',
            'tenggat.date' => 'Format tanggal / tenggat pendaftaran tidak valid.',
            'tautan_daftar.required' => 'Link pendaftaran wajib diisi.',
            'tautan_daftar.url' => 'Format link pendaftaran harus berupa URL yang valid (contoh: https://...).',
            'tautan_daftar.max' => 'Link pendaftaran maksimal 255 karakter.',
            'deskripsi.required' => 'Deskripsi singkat wajib diisi.',
            'deskripsi.max' => 'Deskripsi singkat maksimal 2000 karakter.',
        ]);

        // Cegah duplikat: tolak jika ada lomba dengan nama dan penyelenggara yang sama yang sudah menunggu atau terverifikasi
        $namaLower = strtolower(trim($validated['nama_lomba']));
        $penyelenggaraLower = strtolower(trim($validated['penyelenggara']));

        $hasStatusVerifikasi = Schema::hasColumn('lomba', 'status_verifikasi');
        $hasStatus = Schema::hasColumn('lomba', 'status');

        $duplicateExists = Lomba::query()
            ->whereRaw('LOWER(TRIM(nama_lomba)) = ?', [$namaLower])
            ->whereRaw('LOWER(TRIM(penyelenggara)) = ?', [$penyelenggaraLower])
            ->where(function ($q) use ($hasStatusVerifikasi, $hasStatus) {
                if ($hasStatusVerifikasi && $hasStatus) {
                    $q->whereIn('status_verifikasi', ['menunggu', 'terverifikasi'])
                        ->orWhereIn('status', ['menunggu', 'terverifikasi']);
                } elseif ($hasStatus) {
                    $q->whereIn('status', ['menunggu', 'terverifikasi']);
                } else {
                    $q->whereIn('status_verifikasi', ['menunggu', 'terverifikasi']);
                }
            })
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withErrors([
                    'nama_lomba' => 'Lomba dengan nama dan penyelenggara yang sama sudah diajukan (menunggu verifikasi) atau sudah terverifikasi.',
                ])
                ->withInput();
        }

        $tingkatFormatted = ucfirst(strtolower(trim($validated['tingkat'])));
        $tipeLower = strtolower(trim($validated['tipe_peserta']));
        $tipeFormatted = in_array($tipeLower, ['kelompok', 'tim'], true) ? 'Kelompok' : 'Individu';
        $modeFormatted = ucfirst(strtolower(trim($validated['mode_pelaksanaan'])));

        $authId = auth()->id() ?? session('auth_user.id');
        $mahasiswaNim = Tim::currentUserMahasiswaNim() ?? ($authId !== null ? (string) $authId : null);

        $lombaData = [
            'nama_lomba' => trim($validated['nama_lomba']),
            'penyelenggara' => trim($validated['penyelenggara']),
            'kategori' => $validated['kategori'],
            'tingkat' => $tingkatFormatted,
            'tipe_peserta' => $tipeFormatted,
            'mode_pelaksanaan' => $modeFormatted,
            'biaya' => (int) $validated['biaya'],
            'tenggat' => $validated['tenggat'],
            'tautan_daftar' => trim($validated['tautan_daftar']),
            'deskripsi' => trim($validated['deskripsi']),
        ];

        if ($hasStatusVerifikasi) {
            $lombaData['status_verifikasi'] = 'menunggu';
        }
        if ($hasStatus) {
            $lombaData['status'] = 'menunggu';
        }
        if (Schema::hasColumn('lomba', 'submitted_by')) {
            $lombaData['submitted_by'] = $authId;
        }
        if (Schema::hasColumn('lomba', 'nim') && $mahasiswaNim !== null && $mahasiswaNim !== '') {
            if (! Mahasiswa::where('nim', $mahasiswaNim)->exists() && $currentUser) {
                Mahasiswa::create([
                    'nim' => $mahasiswaNim,
                    'nama' => $currentUser->name ?? 'Mahasiswa JTI',
                    'email_kampus' => $currentUser->email ?? ($mahasiswaNim.'@student.polinema.ac.id'),
                    'password' => $currentUser->password ?? Hash::make('password123'),
                    'prodi' => $currentUser->prodi ?? 'D-IV Sistem Informasi Bisnis',
                    'angkatan' => (int) ($currentUser->angkatan ?? 2024),
                ]);
            }

            if (Mahasiswa::where('nim', $mahasiswaNim)->exists()) {
                $lombaData['nim'] = $mahasiswaNim;
            }
        }

        Lomba::create($lombaData);

        Cache::forget('admin_kpi_stats');

        return redirect()
            ->route('lomba.index')
            ->with('success', 'Pengajuan terkirim, menunggu verifikasi admin.');
    }

    /**
     * Menampilkan Detail Lomba
     */
    public function show($id)
    {
        $lomba = Lomba::terverifikasi()
            ->with(['pengaju', 'tim' => function ($q) {
                $q->with(['ketua', 'anggotaDiterima.mahasiswa']);
            }])
            ->findOrFail($id);

        // Ambil tim dari JTI yang sudah terdaftar pada lomba ini
        $timList = $lomba->tim()
            ->where('status_tim', 'disetujui')
            ->with(['ketua', 'anggotaDiterima.mahasiswa'])
            ->get();

        // Rekomendasi lomba lain yang relevan (kategori atau tingkat sama)
        $lombaTerkait = Lomba::terverifikasi()
            ->where('id_lomba', '!=', $lomba->id_lomba)
            ->where(function ($q) use ($lomba) {
                $q->where('kategori', $lomba->kategori)
                    ->orWhere('tingkat', $lomba->tingkat);
            })
            ->where('tenggat', '>=', Carbon::today())
            ->take(3)
            ->get();

        $hasTipeColumn = Schema::hasColumn('lomba', 'tipe_peserta') || Schema::hasColumn('lomba', 'tipe');
        $hasModeColumn = Schema::hasColumn('lomba', 'mode_pelaksanaan') || Schema::hasColumn('lomba', 'mode');

        return view('lomba.show', [
            'lomba' => $lomba,
            'timList' => $timList,
            'lombaTerkait' => $lombaTerkait,
            'hasTipeColumn' => $hasTipeColumn,
            'hasModeColumn' => $hasModeColumn,
        ]);
    }

    /**
     * Tampilkan berkas Buku Panduan Lomba (Guidebook PDF) secara inline di browser
     */
    public function viewPedoman($id)
    {
        $lomba = Lomba::findOrFail($id);

        if (! $lomba->buku_pedoman_path || ! Storage::disk('public')->exists($lomba->buku_pedoman_path)) {
            return back()->with('error', 'Buku panduan resmi belum tersedia untuk kompetisi ini.');
        }

        $path = Storage::disk('public')->path($lomba->buku_pedoman_path);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Buku_Panduan_'.Str::slug($lomba->nama_lomba).'.pdf"',
        ]);
    }

    /**
     * Unduh berkas Buku Pedoman Lomba
     */
    public function downloadPedoman($id)
    {
        $lomba = Lomba::findOrFail($id);

        if (! $lomba->buku_pedoman_path || ! Storage::disk('public')->exists($lomba->buku_pedoman_path)) {
            return back()->with('error', 'Buku panduan resmi belum tersedia untuk kompetisi ini.');
        }

        $extension = pathinfo($lomba->buku_pedoman_path, PATHINFO_EXTENSION) ?: 'pdf';
        $filename = 'Buku_Panduan_'.Str::slug($lomba->nama_lomba).'.'.$extension;

        return Storage::disk('public')->download($lomba->buku_pedoman_path, $filename);
    }
}
