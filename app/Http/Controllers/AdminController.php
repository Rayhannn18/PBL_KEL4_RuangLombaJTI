<?php

namespace App\Http\Controllers;

use App\Enums\ModePelaksanaan;
use App\Enums\TingkatLomba;
use App\Enums\TipePeserta;
use App\Http\Requests\Admin\StoreLombaRequest;
use App\Http\Requests\Admin\UpdateLombaRequest;
use App\Models\Admin;
use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Logbook;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PrestasiMahasiswa;
use App\Models\ProgresBabak;
use App\Models\Tim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public const PRODI_OPTIONS = [
        'D-IV Sistem Informasi Bisnis',
        'D-IV Teknik Informatika',
    ];

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

                'total_prestasi' => PrestasiMahasiswa::count(),
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
            $timQuery = Tim::with(['lomba', 'ketua', 'admin'])->latest();

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
        $mhsRole = $request->query('mhs_role', 'semua');
        $mahasiswaList = collect();

        if ($currentTab === 'mahasiswa') {
            $mahasiswaQuery = Mahasiswa::withCount([
                'timKetua',
                'keanggotaan',
                'timKetua as tim_ketua_disetujui_count' => function ($q) {
                    $q->where('status_tim', 'disetujui');
                },
            ])
                ->with([
                    'timKetua.lomba',
                    'timKetua.pengajuanBimbingan.progresBabak' => function ($q) {
                        $q->where('status_setuju', 'disetujui')->orderByDesc('tanggal_update');
                    },
                    'keanggotaan' => function ($q) {
                        $q->where('status_gabung', 'diterima');
                    },
                    'keanggotaan.tim.lomba',
                    'keanggotaan.tim.pengajuanBimbingan.progresBabak' => function ($q) {
                        $q->where('status_setuju', 'disetujui')->orderByDesc('tanggal_update');
                    },
                ])
                ->orderBy('nim');

            if ($mhsProdi !== 'semua') {
                $mahasiswaQuery->where('prodi', $mhsProdi);
            }

            if ($mhsAngkatan !== 'semua') {
                $mahasiswaQuery->where('angkatan', (int) $mhsAngkatan);
            }

            if ($mhsRole === 'ketua_tim') {
                $mahasiswaQuery->whereHas('timKetua', function ($q) {
                    $q->where('status_tim', 'disetujui');
                });
            } elseif ($mhsRole === 'mahasiswa') {
                $mahasiswaQuery->whereDoesntHave('timKetua', function ($q) {
                    $q->where('status_tim', 'disetujui');
                });
            }

            if (! empty($mhsSearch)) {
                $mahasiswaQuery->where(function ($q) use ($mhsSearch) {
                    $q->where('nama', 'like', '%'.$mhsSearch.'%')
                        ->orWhere('nim', 'like', '%'.$mhsSearch.'%')
                        ->orWhere('email_kampus', 'like', '%'.$mhsSearch.'%');
                });
            }

            $hasPortofolioCol = Schema::hasColumn('mahasiswa', 'portofolio');

            $mahasiswaList = $mahasiswaQuery->get()->map(function (Mahasiswa $mhs) use ($hasPortofolioCol) {
                $isKetuaDisetujui = ((int) ($mhs->tim_ketua_disetujui_count ?? 0)) > 0;
                $mhs->setAttribute('role_label', $isKetuaDisetujui ? 'Ketua Tim' : 'Mahasiswa');

                // Normalisasi daftar portofolio (maksimal 3 tautan valid)
                $rawPortofolio = $hasPortofolioCol
                    ? $mhs->portofolio
                    : session("profil_portofolio_{$mhs->nim}", []);

                if (is_string($rawPortofolio)) {
                    $rawPortofolio = json_decode($rawPortofolio, true) ?: [];
                }

                $portofolioItems = [];
                if (is_array($rawPortofolio)) {
                    foreach (array_slice($rawPortofolio, 0, 3) as $item) {
                        if (is_array($item) && ! empty($item['url'])) {
                            $portofolioItems[] = [
                                'label' => trim((string) ($item['label'] ?? '')),
                                'url' => trim((string) $item['url']),
                            ];
                        }
                    }
                }
                $mhs->setAttribute('portofolio_items', $portofolioItems);

                // Susun daftar tim yang diikuti (sebagai Ketua Tim atau Anggota diterima)
                $timMap = [];
                $timModelsDisetujui = [];

                foreach ($mhs->timKetua as $tim) {
                    $timMap[$tim->id_tim] = [
                        'id_tim' => $tim->id_tim,
                        'nama_tim' => $tim->nama_tim,
                        'nama_lomba' => $tim->lomba?->nama_lomba ?? 'Kompetisi JTI',
                        'tingkat_lomba' => $tim->lomba?->tingkat ?? '',
                        'kategori_lomba' => $tim->lomba?->kategori ?? '',
                        'peran' => 'Ketua',
                        'detail_peran' => null,
                        'status_tim' => $tim->status_tim,
                    ];

                    if ($tim->status_tim === 'disetujui') {
                        $timModelsDisetujui[$tim->id_tim] = $tim;
                    }
                }

                foreach ($mhs->keanggotaan as $keanggotaan) {
                    $tim = $keanggotaan->tim;
                    if (! $tim || isset($timMap[$tim->id_tim])) {
                        continue;
                    }

                    $detailPeran = trim((string) ($keanggotaan->posisi_dilamar ?: $keanggotaan->peran));
                    if (in_array(strtolower($detailPeran), ['', 'anggota', 'ketua tim', 'ketua'], true)) {
                        $detailPeran = null;
                    }

                    $timMap[$tim->id_tim] = [
                        'id_tim' => $tim->id_tim,
                        'nama_tim' => $tim->nama_tim,
                        'nama_lomba' => $tim->lomba?->nama_lomba ?? 'Kompetisi JTI',
                        'tingkat_lomba' => $tim->lomba?->tingkat ?? '',
                        'kategori_lomba' => $tim->lomba?->kategori ?? '',
                        'peran' => 'Anggota',
                        'detail_peran' => $detailPeran,
                        'status_tim' => $tim->status_tim,
                    ];

                    if ($tim->status_tim === 'disetujui') {
                        $timModelsDisetujui[$tim->id_tim] = $tim;
                    }
                }

                $mhs->setAttribute('tim_diikuti_items', array_values($timMap));

                // Susun daftar prestasi juara terverifikasi dari tim yang sudah disetujui
                $prestasiItems = [];
                foreach ($timModelsDisetujui as $tim) {
                    foreach ($tim->pengajuanBimbingan as $pengajuan) {
                        foreach ($pengajuan->progresBabak as $progres) {
                            if ($progres->status_setuju === 'disetujui' && $progres->isJuara()) {
                                $prestasiItems[] = [
                                    'id_progres' => $progres->id_progres,
                                    'hasil_akhir' => $progres->hasil_akhir,
                                    'babak' => $progres->babak,
                                    'nama_tim' => $tim->nama_tim,
                                    'nama_lomba' => $tim->lomba?->nama_lomba ?? '-',
                                    'tanggal_update' => $progres->tanggal_update ? $progres->tanggal_update->format('d M Y') : '-',
                                ];
                            }
                        }
                    }
                }

                $mhs->setAttribute('prestasi_items', $prestasiItems);

                return $mhs;
            });
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
        $prodiOptions = self::PRODI_OPTIONS;
        $kategoriLombaOptions = Lomba::kategoriOptions();

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
            'mhsRole',
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
    public function updateLomba(Request $request, $id)
    {
        $lomba = Lomba::findOrFail($id);

        if (($lomba->status_verifikasi ?? $lomba->status) === 'ditolak') {
            return redirect()->route('admin.index', ['tab' => 'lomba'])
                ->with('error', 'Lomba yang sudah ditolak tidak dapat diubah.');
        }

        $request = app(UpdateLombaRequest::class);
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
            $updateData['poster'] = null;
        } elseif ($request->boolean('hapus_poster')) {
            if ($lomba->poster_path && Storage::disk('public')->exists($lomba->poster_path)) {
                Storage::disk('public')->delete($lomba->poster_path);
            }
            $updateData['poster_path'] = null;
            $updateData['poster'] = null;
        }

        if ($request->hasFile('buku_pedoman')) {
            if ($lomba->buku_pedoman_path && Storage::disk('public')->exists($lomba->buku_pedoman_path)) {
                Storage::disk('public')->delete($lomba->buku_pedoman_path);
            }
            $updateData['buku_pedoman_path'] = $request->file('buku_pedoman')->store('lomba/pedoman', 'public');
        } elseif ($request->boolean('hapus_buku_pedoman')) {
            if ($lomba->buku_pedoman_path && Storage::disk('public')->exists($lomba->buku_pedoman_path)) {
                Storage::disk('public')->delete($lomba->buku_pedoman_path);
            }
            $updateData['buku_pedoman_path'] = null;
        }

        $lomba->update($updateData);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'lomba'])
            ->with('success', 'Data lomba "'.$lomba->nama_lomba.'" berhasil diperbarui!');
    }

    /**
     * Hapus Berkas Pamflet Lomba
     */
    public function destroyPoster($id)
    {
        $lomba = Lomba::findOrFail($id);

        if ($lomba->poster_path && Storage::disk('public')->exists($lomba->poster_path)) {
            Storage::disk('public')->delete($lomba->poster_path);
        }

        $lomba->update([
            'poster_path' => null,
            'poster' => null,
        ]);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'lomba'])
            ->with('success', 'Pamflet lomba "'.$lomba->nama_lomba.'" berhasil dihapus.');
    }

    /**
     * Hapus Berkas Buku Panduan Lomba
     */
    public function destroyPedoman($id)
    {
        $lomba = Lomba::findOrFail($id);

        if ($lomba->buku_pedoman_path && Storage::disk('public')->exists($lomba->buku_pedoman_path)) {
            Storage::disk('public')->delete($lomba->buku_pedoman_path);
        }

        $lomba->update([
            'buku_pedoman_path' => null,
        ]);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'lomba'])
            ->with('success', 'Buku panduan lomba "'.$lomba->nama_lomba.'" berhasil dihapus.');
    }

    /**
     * Verifikasi atau Tolak Pengajuan Lomba (Activity Diagram: Lomba Kredibel?)
     */
    public function verifikasiLomba(Request $request, $id)
    {
        $lomba = Lomba::findOrFail($id);

        $currentStatus = $lomba->status_verifikasi ?? $lomba->status ?? 'menunggu';
        if ($currentStatus !== 'menunggu') {
            return redirect()->route('admin.index', ['tab' => 'lomba'])
                ->with('error', 'Hanya pengajuan lomba yang masih berstatus menunggu yang dapat diverifikasi atau ditolak.');
        }

        $rules = [
            'status' => ['required', 'in:terverifikasi,ditolak'],
        ];

        if ($request->input('status') === 'ditolak') {
            $rules['alasan_penolakan'] = ['required', 'string', 'min:10', 'max:500'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'status.required' => 'Status verifikasi wajib ditentukan.',
            'status.in' => 'Status verifikasi tidak valid.',
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.string' => 'Alasan penolakan harus berupa teks.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 10 karakter.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.index', ['tab' => 'lomba'])
                ->withErrors($validator)
                ->withInput(array_merge($request->all(), [
                    'tolak_lomba_id' => $lomba->id_lomba,
                    'tolak_lomba_nama' => $lomba->nama_lomba,
                ]));
        }

        $adminId = session('auth_user.id') ?? auth()->id();
        $status = $request->input('status');

        $updateData = [
            'status_verifikasi' => $status,
            'id_admin' => $adminId,
        ];

        if (Schema::hasColumn('lomba', 'status')) {
            $updateData['status'] = $status;
        }
        if (Schema::hasColumn('lomba', 'alasan_penolakan')) {
            $updateData['alasan_penolakan'] = $status === 'ditolak'
                ? trim((string) $request->input('alasan_penolakan'))
                : null;
        }
        if (Schema::hasColumn('lomba', 'verified_by')) {
            $updateData['verified_by'] = auth()->id() ?? $adminId;
        }
        if (Schema::hasColumn('lomba', 'verified_at')) {
            $updateData['verified_at'] = now();
        }

        $lomba->update($updateData);

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
        $tim = Tim::with('ketua')->findOrFail($id);
        $adminId = session('auth_user.id') ?? auth()->id();
        $status = $request->input('status');

        if ($status === 'ditolak' && $tim->status_tim !== 'menunggu_persetujuan') {
            return redirect()->route('admin.index', ['tab' => 'tim'])
                ->with('error', 'Hanya pengajuan tim yang masih berstatus menunggu persetujuan yang dapat ditolak.');
        }

        // Tolak jika status_tim sudah sama dengan yang diminta
        if ($tim->status_tim === $status) {
            $labelMap = [
                'disetujui' => 'disetujui',
                'ditolak' => 'ditolak',
                'menunggu_persetujuan' => 'menunggu persetujuan',
            ];
            $label = $labelMap[$status] ?? $status;

            return redirect()->route('admin.index', ['tab' => 'tim'])
                ->with('error', "Status tim \"{$tim->nama_tim}\" sudah {$label}, tidak ada perubahan yang dilakukan.");
        }

        $rules = [
            'status' => ['required', 'in:disetujui,ditolak'],
        ];

        if ($status === 'ditolak') {
            $rules['alasan_penolakan'] = ['required', 'string', 'min:10', 'max:500'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'status.required' => 'Status verifikasi tim wajib ditentukan.',
            'status.in' => 'Status verifikasi tim tidak valid.',
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.string' => 'Alasan penolakan harus berupa teks.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 10 karakter.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        if ($validator->fails()) {
            $ketuaInfo = ($tim->ketua ? $tim->ketua->nama : 'Ketua Tim').' ('.($tim->ketua->nim ?? $tim->nim).')';

            return redirect()->route('admin.index', ['tab' => 'tim'])
                ->withErrors($validator)
                ->withInput(array_merge($request->all(), [
                    'tolak_tim_id' => $tim->id_tim,
                    'tolak_tim_nama' => $tim->nama_tim,
                    'tolak_tim_ketua' => $ketuaInfo,
                ]));
        }

        DB::transaction(function () use ($tim, $status, $adminId, $request) {
            $updateData = [
                'status_tim' => $status,
                'id_admin' => $adminId,
            ];

            if (Schema::hasColumn('tim', 'alasan_penolakan')) {
                $updateData['alasan_penolakan'] = $status === 'ditolak'
                    ? trim((string) $request->input('alasan_penolakan'))
                    : null;
            }
            if (Schema::hasColumn('tim', 'verified_by')) {
                $updateData['verified_by'] = auth()->id() ?? $adminId;
            }
            if (Schema::hasColumn('tim', 'verified_at')) {
                $updateData['verified_at'] = now();
            }

            $tim->update($updateData);

            if ($status === 'disetujui') {
                // Mahasiswa yang mengajukan otomatis ditetapkan sebagai Ketua Tim resmi
                AnggotaTim::updateOrCreate(
                    ['id_tim' => $tim->id_tim, 'nim' => $tim->nim],
                    [
                        'status_gabung' => 'diterima',
                        'peran' => 'Ketua Tim',
                        'file_cv' => $tim->file_cv,
                    ]
                );
            } elseif ($status === 'ditolak') {
                // Pengajuan ditolak: mahasiswa tidak menjadi Ketua Tim
                AnggotaTim::where('id_tim', $tim->id_tim)
                    ->where('nim', $tim->nim)
                    ->update(['status_gabung' => 'ditolak']);
            }
        });

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

        if ($tim->status_tim === 'disetujui') {
            return redirect()->route('admin.index', ['tab' => 'tim'])
                ->with('error', 'Tim yang sudah disetujui tidak dapat dihapus.');
        }

        // Cek apakah masih ada data turunan (anggota selain pemohon awal, pengajuan dosen, atau logbook)
        $hasAnggotaTurunan = $tim->anggota()
            ->where(function ($q) use ($tim) {
                $q->where('nim', '!=', $tim->nim)
                    ->orWhere('status_gabung', 'diterima');
            })
            ->exists();

        $hasPengajuanDosen = $tim->pengajuanBimbingan()->exists();
        $hasLogbook = Logbook::whereIn(
            'id_pengajuan',
            $tim->pengajuanBimbingan()->select('id_pengajuan')
        )->exists();

        if ($hasAnggotaTurunan || $hasPengajuanDosen || $hasLogbook) {
            return redirect()->route('admin.index', ['tab' => 'tim'])
                ->with('error', 'Tim "'.$tim->nama_tim.'" tidak dapat dihapus karena masih memiliki data turunan (anggota, pengajuan dosen, atau logbook).');
        }

        $namaTim = $tim->nama_tim;

        DB::transaction(function () use ($tim) {
            AnggotaTim::where('id_tim', $tim->id_tim)->delete();
            $tim->delete();
        });

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'tim'])
            ->with('success', 'Data tim "'.$namaTim.'" berhasil dihapus.');
    }

    // ==========================================
    // MODUL 3: KELOLA DATA DOSEN PEMBIMBING
    // ==========================================

    /**
     * Tambah Dosen Baru
     * Form menggunakan field "nip" (18 digit angka), disimpan ke kolom "nidn".
     */
    public function storeDosen(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|size:18|regex:/^[0-9]{18}$/|unique:dosen,nidn',
            'nama' => 'required|string|max:150',
            'email_kampus' => 'required|email|max:150|unique:dosen,email_kampus',
            'password' => 'required|string|min:6',
            'bidang_keahlian' => 'required|string|max:255',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.size' => 'NIP harus terdiri dari tepat 18 digit angka.',
            'nip.regex' => 'NIP harus berupa 18 digit angka tanpa huruf atau spasi.',
            'nip.unique' => 'NIP tersebut sudah terdaftar di sistem.',
            'nama.required' => 'Nama lengkap dan gelar wajib diisi.',
            'nama.max' => 'Nama lengkap dan gelar maksimal 150 karakter.',
            'email_kampus.required' => 'Email Kampus Dosen wajib diisi.',
            'email_kampus.email' => 'Format Email Kampus Dosen tidak valid.',
            'email_kampus.max' => 'Email Kampus Dosen maksimal 150 karakter.',
            'email_kampus.unique' => 'Email Kampus Dosen sudah terdaftar di sistem.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.min' => 'Kata sandi awal minimal 6 karakter.',
            'bidang_keahlian.required' => 'Bidang keahlian wajib diisi.',
            'bidang_keahlian.max' => 'Bidang keahlian maksimal 255 karakter.',
        ]);

        Dosen::create([
            'nidn' => $validated['nip'],
            'nama' => $validated['nama'],
            'email_kampus' => $validated['email_kampus'],
            'password' => Hash::make($validated['password']),
            'bidang_keahlian' => $validated['bidang_keahlian'],
        ]);

        $this->clearCache();

        return redirect()->route('admin.index', ['tab' => 'dosen'])
            ->with('success', 'Dosen baru '.$validated['nama'].' (NIP: '.$validated['nip'].') berhasil ditambahkan!');
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
            'prodi' => ['required', 'string', Rule::in(self::PRODI_OPTIONS)],
            'angkatan' => 'required|integer|min:2020|max:2027',
        ], [
            'prodi.in' => 'Program studi harus berupa D-IV Sistem Informasi Bisnis atau D-IV Teknik Informatika.',
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
            'prodi' => ['required', 'string', Rule::in(self::PRODI_OPTIONS)],
            'angkatan' => 'required|integer|min:2020|max:2027',
            'password' => 'nullable|string|min:6',
        ], [
            'prodi.in' => 'Program studi harus berupa D-IV Sistem Informasi Bisnis atau D-IV Teknik Informatika.',
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
