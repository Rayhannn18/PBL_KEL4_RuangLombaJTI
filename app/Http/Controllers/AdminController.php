<?php

namespace App\Http\Controllers;

use App\Enums\ModePelaksanaan;
use App\Enums\TingkatLomba;
use App\Enums\TipePeserta;
use App\Http\Requests\Admin\StoreLombaRequest;
use App\Http\Requests\Admin\UpdateLombaRequest;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\ProgresBabak;
use App\Models\Tim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Halaman Utama Panel Administrator RuangLombaJTI
     * Berdasarkan Activity Diagram:
     * 1. Menverifikasi & Mengelola Data Lomba
     * 2. Menyetujui/Menolak Ketua Tim
     * 3. Mengelola Data Dosen dan Mahasiswa
     * 4. Mengakses Dashboard Prestasi & Monitoring
     */
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'overview');

        // 1. Statistik Ringkas Sistem (Cache 60 detik untuk navigasi tab instan)
        $stats = Cache::remember('admin_kpi_stats', 60, function () {
            return [
                'total_lomba' => Lomba::count(),
                'lomba_menunggu' => Lomba::where('status_verifikasi', 'menunggu')->count(),
                'lomba_terverifikasi' => Lomba::where('status_verifikasi', 'terverifikasi')->count(),
                'lomba_ditolak' => Lomba::where('status_verifikasi', 'ditolak')->count(),

                'total_tim' => Tim::count(),
                'tim_menunggu' => Tim::where('status_tim', 'menunggu_persetujuan')->count(),
                'tim_disetujui' => Tim::where('status_tim', 'disetujui')->count(),
                'tim_ditolak' => Tim::where('status_tim', 'ditolak')->count(),

                'total_dosen' => Dosen::count(),
                'total_kuota_dosen' => (int) Dosen::sum('kuota_bimbingan'),

                'total_mahasiswa' => Mahasiswa::count(),

                'total_prestasi' => ProgresBabak::where('status_setuju', 'disetujui')
                    ->where('hasil_akhir', 'like', '%Juara%')
                    ->count(),
            ];
        });

        // 2. Data Lomba (Hanya dimuat jika tab lomba atau overview)
        $lombaStatusFilter = $request->query('lomba_status', 'semua');
        $lombaSearch = $request->query('lomba_search', '');
        $lombaList = collect();

        if ($currentTab === 'lomba') {
            $lombaQuery = Lomba::with(['pengaju', 'verifikator', 'tim'])->latest();

            if ($lombaStatusFilter !== 'semua') {
                $lombaQuery->where('status_verifikasi', $lombaStatusFilter);
            }

            if (! empty($lombaSearch)) {
                $lombaQuery->where(function ($q) use ($lombaSearch) {
                    $q->where('nama_lomba', 'like', '%'.$lombaSearch.'%')
                        ->orWhere('penyelenggara', 'like', '%'.$lombaSearch.'%')
                        ->orWhere('kategori', 'like', '%'.$lombaSearch.'%');
                });
            }

            $lombaList = $lombaQuery->get();
        } elseif ($currentTab === 'overview') {
            $lombaList = Lomba::with(['pengaju'])
                ->where('status_verifikasi', 'menunggu')
                ->latest()
                ->take(5)
                ->get();
        }

        // 3. Data Tim & Ketua Tim (Hanya dimuat jika tab tim atau overview)
        $timStatusFilter = $request->query('tim_status', 'semua');
        $timSearch = $request->query('tim_search', '');
        $timList = collect();

        if ($currentTab === 'tim') {
            $timQuery = Tim::with(['lomba', 'ketua', 'anggota.mahasiswa', 'admin', 'pengajuanBimbingan.dosen'])->latest();

            if ($timStatusFilter !== 'semua') {
                $timQuery->where('status_tim', $timStatusFilter);
            }

            if (! empty($timSearch)) {
                $timQuery->where(function ($q) use ($timSearch) {
                    $q->where('nama_tim', 'like', '%'.$timSearch.'%')
                        ->orWhereHas('lomba', function ($ql) use ($timSearch) {
                            $ql->where('nama_lomba', 'like', '%'.$timSearch.'%');
                        })
                        ->orWhereHas('ketua', function ($qk) use ($timSearch) {
                            $qk->where('nama', 'like', '%'.$timSearch.'%')
                                ->orWhere('nim', 'like', '%'.$timSearch.'%');
                        });
                });
            }

            $timList = $timQuery->get();
        } elseif ($currentTab === 'overview') {
            $timList = Tim::with(['lomba', 'ketua', 'anggota'])
                ->where('status_tim', 'menunggu_persetujuan')
                ->latest()
                ->take(5)
                ->get();
        }

        // 4. Data Dosen (Hanya dimuat jika tab dosen)
        $dosenSearch = $request->query('dosen_search', '');
        $dosenList = collect();

        if ($currentTab === 'dosen') {
            $dosenQuery = Dosen::withCount([
                'pengajuanBimbingan as bimbingan_aktif_count' => function ($q) {
                    $q->where('status', 'disetujui');
                },
            ])->orderBy('nama');

            if (! empty($dosenSearch)) {
                $dosenQuery->where(function ($q) use ($dosenSearch) {
                    $q->where('nama', 'like', '%'.$dosenSearch.'%')
                        ->orWhere('nidn', 'like', '%'.$dosenSearch.'%')
                        ->orWhere('bidang_keahlian', 'like', '%'.$dosenSearch.'%');
                });
            }

            $dosenList = $dosenQuery->get();
        }

        // 5. Data Mahasiswa (Hanya dimuat jika tab mahasiswa)
        $mhsSearch = $request->query('mhs_search', '');
        $mhsProdi = $request->query('mhs_prodi', 'semua');
        $mhsAngkatan = $request->query('mhs_angkatan', 'semua');
        $mahasiswaList = collect();

        if ($currentTab === 'mahasiswa') {
            $mahasiswaQuery = Mahasiswa::withCount(['timKetua', 'keanggotaan'])
                ->orderBy('nim');

            if ($mhsProdi !== 'semua') {
                $mahasiswaQuery->where('prodi', $mhsProdi);
            }

            if ($mhsAngkatan !== 'semua') {
                $mahasiswaQuery->where('angkatan', (int) $mhsAngkatan);
            }

            if (! empty($mhsSearch)) {
                $mahasiswaQuery->where(function ($q) use ($mhsSearch) {
                    $q->where('nama', 'like', '%'.$mhsSearch.'%')
                        ->orWhere('nim', 'like', '%'.$mhsSearch.'%')
                        ->orWhere('email_kampus', 'like', '%'.$mhsSearch.'%');
                });
            }

            $mahasiswaList = $mahasiswaQuery->get();
        }

        // 6. Riwayat Prestasi Mahasiswa (Hanya dimuat jika overview)
        $prestasiList = collect();
        if ($currentTab === 'overview') {
            $prestasiList = ProgresBabak::with([
                'pengajuan.tim.lomba',
                'pengajuan.tim.ketua',
                'pengajuan.dosen',
            ])
                ->where('status_setuju', 'disetujui')
                ->orderByDesc('tanggal_update')
                ->take(5)
                ->get();
        }

        // Opsi Program Studi & Kategori Lomba Standar
        $prodiOptions = [
            'D-IV Sistem Informasi Bisnis',
            'D-IV Teknik Informatika',
            'D-III Manajemen Informatika',
        ];

        $kategoriLombaOptions = [
            'Software Development',
            'Web Development',
            'Mobile Application',
            'UI/UX Design',
            'Data Science & AI',
            'Cyber Security',
            'Competitive Programming',
            'Karya Tulis Ilmiah',
            'Bisnis TIK / Startup',
            'Game Development',
        ];

        $tingkatOptions = TingkatLomba::options();
        $tipePesertaOptions = TipePeserta::options();
        $modePelaksanaanOptions = ModePelaksanaan::options();

        return view('admin.index', compact(
            'currentTab',
            'stats',
            'lombaList',
            'lombaStatusFilter',
            'lombaSearch',
            'timList',
            'timStatusFilter',
            'timSearch',
            'dosenList',
            'dosenSearch',
            'mahasiswaList',
            'mhsSearch',
            'mhsProdi',
            'mhsAngkatan',
            'prestasiList',
            'prodiOptions',
            'kategoriLombaOptions',
            'tingkatOptions',
            'tipePesertaOptions',
            'modePelaksanaanOptions'
        ));
    }

    /**
     * Bersihkan cache KPI statistik agar angka selalu up-to-date saat ada perubahan data
     */
    private function clearCache(): void
    {
        Cache::forget('admin_kpi_stats');
    }

    // ==========================================
    // MODUL 1: KELOLA & VERIFIKASI DATA LOMBA
    // ==========================================

    /**
     * Tambah Lomba Baru Resmi oleh Admin Kemahasiswaan
     */
    public function storeLomba(StoreLombaRequest $request)
    {
        $validated = $request->validated();
        $adminId = session('auth_user.id');

        $posterPath = null;
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('lomba/poster', 'public');
        }

        $bukuPedomanPath = null;
        if ($request->hasFile('buku_pedoman')) {
            $bukuPedomanPath = $request->file('buku_pedoman')->store('lomba/pedoman', 'public');
        }

        Lomba::create([
            'nama_lomba' => $validated['nama_lomba'],
            'kategori' => $validated['kategori'],
            'tingkat' => $validated['tingkat'],
            'tipe_peserta' => $validated['tipe_peserta'],
            'mode_pelaksanaan' => $validated['mode_pelaksanaan'],
            'penyelenggara' => $validated['penyelenggara'],
            'tenggat' => $validated['tenggat'],
            'biaya' => (int) ($validated['biaya'] ?? 0),
            'tautan_daftar' => $validated['tautan_daftar'],
            'deskripsi' => $validated['deskripsi'],
            'persyaratan' => $validated['persyaratan'],
            'benefit' => $validated['benefit'],
            'poster_path' => $posterPath,
            'buku_pedoman_path' => $bukuPedomanPath,
            'status_verifikasi' => 'terverifikasi', // Otomatis terverifikasi jika dibuat Admin
            'id_admin' => $adminId,
        ]);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'lomba'])
            ->with('success', 'Lomba baru "'.$validated['nama_lomba'].'" berhasil ditambahkan dan dipublikasikan!');
    }

    /**
     * Perbarui Data Lomba
     */
    public function updateLomba(UpdateLombaRequest $request, $id)
    {
        $lomba = Lomba::findOrFail($id);
        $validated = $request->validated();
        $adminId = session('auth_user.id');

        $updateData = [
            'nama_lomba' => $validated['nama_lomba'],
            'kategori' => $validated['kategori'],
            'tingkat' => $validated['tingkat'],
            'tipe_peserta' => $validated['tipe_peserta'],
            'mode_pelaksanaan' => $validated['mode_pelaksanaan'],
            'penyelenggara' => $validated['penyelenggara'],
            'tenggat' => $validated['tenggat'],
            'biaya' => (int) ($validated['biaya'] ?? 0),
            'tautan_daftar' => $validated['tautan_daftar'],
            'status_verifikasi' => $validated['status_verifikasi'],
            'deskripsi' => $validated['deskripsi'],
            'persyaratan' => $validated['persyaratan'],
            'benefit' => $validated['benefit'],
            'id_admin' => $adminId,
        ];

        if ($request->hasFile('poster')) {
            if ($lomba->poster_path && Storage::disk('public')->exists($lomba->poster_path)) {
                Storage::disk('public')->delete($lomba->poster_path);
            }
            $updateData['poster_path'] = $request->file('poster')->store('lomba/poster', 'public');
        }

        if ($request->hasFile('buku_pedoman')) {
            if ($lomba->buku_pedoman_path && Storage::disk('public')->exists($lomba->buku_pedoman_path)) {
                Storage::disk('public')->delete($lomba->buku_pedoman_path);
            }
            $updateData['buku_pedoman_path'] = $request->file('buku_pedoman')->store('lomba/pedoman', 'public');
        }

        $lomba->update($updateData);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'lomba'])
            ->with('success', 'Data lomba "'.$lomba->nama_lomba.'" berhasil diperbarui!');
    }

    /**
     * Verifikasi atau Tolak Pengajuan Lomba (Activity Diagram: Lomba Kredibel?)
     */
    public function verifikasiLomba(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:terverifikasi,ditolak',
        ]);

        $lomba = Lomba::findOrFail($id);
        $adminId = session('auth_user.id');
        $status = $request->status;

        $lomba->update([
            'status_verifikasi' => $status,
            'id_admin' => $adminId,
        ]);

        $pesan = $status === 'terverifikasi'
            ? 'Lomba "'.$lomba->nama_lomba.'" dinyatakan Kredibel dan berhasil dipublikasikan!'
            : 'Pengajuan lomba "'.$lomba->nama_lomba.'" berhasil ditolak.';

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'lomba'])
            ->with('success', $pesan);
    }

    /**
     * Hapus Data Lomba (Activity Diagram: Tolak & Hapus Pengajuan Lomba)
     */
    public function destroyLomba($id)
    {
        $lomba = Lomba::findOrFail($id);
        $judul = $lomba->nama_lomba;

        if ($lomba->poster_path && Storage::disk('public')->exists($lomba->poster_path)) {
            Storage::disk('public')->delete($lomba->poster_path);
        }

        if ($lomba->buku_pedoman_path && Storage::disk('public')->exists($lomba->buku_pedoman_path)) {
            Storage::disk('public')->delete($lomba->buku_pedoman_path);
        }

        $lomba->delete();

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'lomba'])
            ->with('success', 'Lomba "'.$judul.'" berhasil dihapus dari sistem.');
    }

    // ==========================================
    // MODUL 2: MENYETUJUI / MENOLAK KETUA TIM
    // ==========================================

    /**
     * Setujui atau Tolak Pengajuan Tim & Ketua Tim (Activity Diagram: Menyetujui/Menolak Ketua Tim)
     */
    public function verifikasiTim(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
        ]);

        $tim = Tim::with('ketua')->findOrFail($id);
        $adminId = session('auth_user.id');
        $status = $request->status;

        $tim->update([
            'status_tim' => $status,
            'id_admin' => $adminId,
        ]);

        $ketuaNama = $tim->ketua ? $tim->ketua->nama : 'Ketua Tim';
        $pesan = $status === 'disetujui'
            ? 'Data tim "'.$tim->nama_tim.'" dan pengajuan ketua ('.$ketuaNama.') berhasil disetujui!'
            : 'Pengajuan tim "'.$tim->nama_tim.'" berhasil ditolak.';

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'tim'])
            ->with('success', $pesan);
    }

    /**
     * Hapus Data Tim
     */
    public function destroyTim($id)
    {
        $tim = Tim::findOrFail($id);
        $namaTim = $tim->nama_tim;
        $tim->delete();

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'tim'])
            ->with('success', 'Data tim "'.$namaTim.'" berhasil dihapus.');
    }

    // ==========================================
    // MODUL 3: KELOLA DATA DOSEN PEMBIMBING
    // ==========================================

    /**
     * Tambah Dosen Baru
     */
    public function storeDosen(Request $request)
    {
        $validated = $request->validate([
            'nidn' => 'required|string|max:20|unique:dosen,nidn',
            'nama' => 'required|string|max:150',
            'email_kampus' => 'required|email|max:150|unique:dosen,email_kampus',
            'password' => 'required|string|min:6',
            'bidang_keahlian' => 'required|string|max:255',
            'kuota_bimbingan' => 'required|integer|min:1|max:20',
        ]);

        Dosen::create([
            'nidn' => $validated['nidn'],
            'nama' => $validated['nama'],
            'email_kampus' => $validated['email_kampus'],
            'password' => Hash::make($validated['password']),
            'bidang_keahlian' => $validated['bidang_keahlian'],
            'kuota_bimbingan' => $validated['kuota_bimbingan'],
        ]);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'dosen'])
            ->with('success', 'Dosen baru '.$validated['nama'].' (NIDN: '.$validated['nidn'].') berhasil ditambahkan!');
    }

    /**
     * Update Data Dosen
     */
    public function updateDosen(Request $request, $nidn)
    {
        $dosen = Dosen::findOrFail($nidn);

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'email_kampus' => 'required|email|max:150|unique:dosen,email_kampus,'.$nidn.',nidn',
            'bidang_keahlian' => 'required|string|max:255',
            'kuota_bimbingan' => 'required|integer|min:1|max:20',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'nama' => $validated['nama'],
            'email_kampus' => $validated['email_kampus'],
            'bidang_keahlian' => $validated['bidang_keahlian'],
            'kuota_bimbingan' => $validated['kuota_bimbingan'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $dosen->update($updateData);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'dosen'])
            ->with('success', 'Data dosen '.$dosen->nama.' berhasil diperbarui!');
    }

    /**
     * Hapus Data Dosen
     */
    public function destroyDosen($nidn)
    {
        $dosen = Dosen::findOrFail($nidn);
        $namaDosen = $dosen->nama;
        $dosen->delete();

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'dosen'])
            ->with('success', 'Dosen '.$namaDosen.' berhasil dihapus dari sistem.');
    }

    // ==========================================
    // MODUL 4: KELOLA DATA MAHASISWA
    // ==========================================

    /**
     * Tambah Mahasiswa Baru
     */
    public function storeMahasiswa(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswa,nim',
            'nama' => 'required|string|max:150',
            'email_kampus' => 'required|email|max:150|unique:mahasiswa,email_kampus',
            'password' => 'required|string|min:6',
            'prodi' => 'required|string|max:100',
            'angkatan' => 'required|integer|min:2020|max:2027',
        ]);

        Mahasiswa::create([
            'nim' => $validated['nim'],
            'nama' => $validated['nama'],
            'email_kampus' => $validated['email_kampus'],
            'password' => Hash::make($validated['password']),
            'prodi' => $validated['prodi'],
            'angkatan' => $validated['angkatan'],
        ]);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'mahasiswa'])
            ->with('success', 'Mahasiswa baru '.$validated['nama'].' (NIM: '.$validated['nim'].') berhasil ditambahkan!');
    }

    /**
     * Update Data Mahasiswa
     */
    public function updateMahasiswa(Request $request, $nim)
    {
        $mahasiswa = Mahasiswa::findOrFail($nim);

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'email_kampus' => 'required|email|max:150|unique:mahasiswa,email_kampus,'.$nim.',nim',
            'prodi' => 'required|string|max:100',
            'angkatan' => 'required|integer|min:2020|max:2027',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'nama' => $validated['nama'],
            'email_kampus' => $validated['email_kampus'],
            'prodi' => $validated['prodi'],
            'angkatan' => $validated['angkatan'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $mahasiswa->update($updateData);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'mahasiswa'])
            ->with('success', 'Data mahasiswa '.$mahasiswa->nama.' berhasil diperbarui!');
    }

    /**
     * Hapus Data Mahasiswa
     */
    public function destroyMahasiswa($nim)
    {
        $mahasiswa = Mahasiswa::findOrFail($nim);
        $namaMhs = $mahasiswa->nama;
        $mahasiswa->delete();

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'mahasiswa'])
            ->with('success', 'Mahasiswa '.$namaMhs.' berhasil dihapus dari sistem.');
    }
}
