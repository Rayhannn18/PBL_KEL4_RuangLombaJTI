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
use App\Models\RabItem;
use App\Models\RabPengajuan;
use App\Models\RabRiwayat;
use App\Models\SbmTarif;
use App\Models\Tim;
use App\Models\User;
use App\Services\DosenRecommendationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
            // Hanya tim berstatus 'disetujui' yang dianggap aktif untuk modul bimbingan
            $timList = Tim::where('status_tim', 'disetujui')
                ->where(function ($q) use ($nim) {
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

            $isKetua = ($selectedTim->nim === $authUser['id'] && Tim::ketuaDisetujui($authUser['id']));

            // Data Pengajuan RAB untuk tim yang dipilih (urut babak & terbaru)
            $rabList = RabPengajuan::where('tim_id', $selectedTim->id_tim)
                ->with(['items', 'riwayat', 'pengisiTerakhir', 'validatorDosen'])
                ->orderByRaw("CASE WHEN babak = 'semi_final' THEN 1 ELSE 2 END")
                ->get();

            $babakRabTervalidasi = $selectedTim->babakRabTervalidasi($pengajuan);
            $kompetisiSelesai = $selectedTim->isKompetisiSelesai($pengajuan);

            // Referensi tarif SBM TA 2026 (PMK 32/2025) dari tabel sbm_tarif
            $sbmTarifList = SbmTarif::where('tahun_anggaran', 2026)->get();
            $provinsiSbmList = $sbmTarifList->pluck('provinsi')->filter()->unique()->sort()->values();

            // Jumlah peserta otomatis dari susunan anggota tim yang diterima (termasuk ketua)
            $jumlahPesertaTim = max(1, $selectedTim->anggotaDiterima->pluck('nim')->push($selectedTim->nim)->unique()->count());

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
                'rabList' => $rabList,
                'babakRabTervalidasi' => $babakRabTervalidasi,
                'kompetisiSelesai' => $kompetisiSelesai,
                'sbmTarifList' => $sbmTarifList,
                'provinsiSbmList' => $provinsiSbmList,
                'jumlahPesertaTim' => $jumlahPesertaTim,
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
                    'tim.rabPengajuan' => function ($q) {
                        $q->whereIn('status', ['menunggu_validasi', 'perlu_revisi', 'disetujui'])
                            ->with(['items', 'pengisiTerakhir', 'validatorDosen'])
                            ->orderByRaw("CASE WHEN babak = 'semi_final' THEN 1 ELSE 2 END");
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
            $rabMenungguValidasiCount = RabPengajuan::where('status', 'menunggu_validasi')
                ->whereHas('tim.bimbinganDisetujui', fn ($q) => $q->where('nidn', $nidn))
                ->count();

            // Daftar opsi lomba unik yang pernah diajukan ke dosen ini untuk dropdown filter
            $lombaOptions = Lomba::whereHas('tim.pengajuanBimbingan', function ($q) use ($nidn) {
                $q->where('nidn', $nidn);
            })->get(['id_lomba', 'nama_lomba']);

            // Antrian laporan progres babak berstatus menunggu dari tim bimbingan dosen ini saja
            $progresMenunggu = ProgresBabak::with(['pengajuan.tim.lomba'])
                ->where('status_setuju', 'menunggu')
                ->whereHas('pengajuan', fn ($q) => $q->where('nidn', $nidn)->where('status', 'disetujui'))
                ->orderBy('tanggal_update')
                ->orderBy('id_progres')
                ->get();

            return view('bimbingan.index', [
                'authUser' => $authUser,
                'role' => 'dosen',
                'dosen' => $dosen,
                'progresMenunggu' => $progresMenunggu,
                'pengajuanList' => $pengajuanList,
                'hasLomba' => true,
                'totalSesiLogbook' => $totalSesiLogbook,
                'logbookMenungguValidasi' => $logbookMenungguValidasi,
                'timAktifCount' => $timAktifCount,
                'pengajuanMenungguCount' => $pengajuanMenungguCount,
                'riwayatLamaCount' => $riwayatLamaCount,
                'rabMenungguValidasiCount' => $rabMenungguValidasiCount,
                'lombaOptions' => $lombaOptions,
                'search' => $search,
                'statusFilter' => $statusFilter,
                'lombaFilter' => $lombaFilter,
            ]);
        }

        // Role Admin: Rekapitulasi Bimbingan Seluruh JTI (Read-Only)
        $search = trim((string) $request->query('search', ''));
        $lombaFilter = $request->query('lomba_id');
        $dosenFilter = $request->query('nidn');
        $logbookFilter = $request->query('status_logbook');

        // Base query: semua tim yang punya pengajuan bimbingan berstatus disetujui
        $baseDisetujuiQuery = PengajuanBimbingan::where('status', 'disetujui')
            ->whereHas('tim');

        // Statistik 4 Kartu Ringkasan (dari seluruh bimbingan berstatus disetujui)
        $totalTimBimbingan = (clone $baseDisetujuiQuery)->count();
        $totalSesiLogbook = Logbook::whereHas('pengajuan', function ($q) {
            $q->where('status', 'disetujui')->whereHas('tim');
        })->count();
        $timBelumAdaLogbook = (clone $baseDisetujuiQuery)->doesntHave('logbook')->count();
        $dosenAktifMembimbing = (clone $baseDisetujuiQuery)->distinct('nidn')->count('nidn');

        // Query daftar tim dengan eager loading & pengurutan logbook terbaru
        $adminQuery = (clone $baseDisetujuiQuery)
            ->with([
                'dosen',
                'tim.lomba',
                'tim.ketua',
                'logbook' => function ($q) {
                    $q->orderBy('tanggal', 'desc')->orderBy('id_logbook', 'desc');
                },
            ])
            ->withCount('logbook');

        // Pencarian: nama tim / ketua / NIM / dosen
        if ($search !== '') {
            $adminQuery->where(function ($q) use ($search) {
                $q->whereHas('tim', function ($tq) use ($search) {
                    $tq->where('nama_tim', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhereHas('ketua', function ($kq) use ($search) {
                            $kq->where('nama', 'like', "%{$search}%")
                                ->orWhere('nim', 'like', "%{$search}%");
                        });
                })->orWhereHas('dosen', function ($dq) use ($search) {
                    $dq->where('nama', 'like', "%{$search}%");
                });
            });
        }

        // Filter Lomba
        if ($lombaFilter && $lombaFilter !== 'all') {
            $adminQuery->whereHas('tim', function ($tq) use ($lombaFilter) {
                $tq->where('id_lomba', (int) $lombaFilter);
            });
        }

        // Filter Dosen Pembimbing
        if ($dosenFilter && $dosenFilter !== 'all') {
            $adminQuery->where('nidn', $dosenFilter);
        }

        // Filter Sudah ada logbook / Belum ada logbook
        if ($logbookFilter === 'sudah') {
            $adminQuery->has('logbook');
        } elseif ($logbookFilter === 'belum') {
            $adminQuery->doesntHave('logbook');
        }

        $pengajuanList = $adminQuery
            ->orderByDesc('tgl_pengajuan')
            ->orderByDesc('id_pengajuan')
            ->paginate(10)
            ->withQueryString();

        // Opsi dropdown filter untuk Admin
        $lombaOptions = Lomba::whereHas('tim.pengajuanBimbingan', function ($q) {
            $q->where('status', 'disetujui');
        })->orderBy('nama_lomba')->get(['id_lomba', 'nama_lomba']);

        $dosenOptions = Dosen::whereHas('pengajuanBimbingan', function ($q) {
            $q->where('status', 'disetujui');
        })->orderBy('nama')->get(['nidn', 'nama', 'bidang_keahlian']);

        return view('bimbingan.index', [
            'authUser' => $authUser,
            'role' => 'admin',
            'pengajuanList' => $pengajuanList,
            'hasLomba' => true,
            'totalTimBimbingan' => $totalTimBimbingan,
            'totalSesiLogbook' => $totalSesiLogbook,
            'timBelumAdaLogbook' => $timBelumAdaLogbook,
            'dosenAktifMembimbing' => $dosenAktifMembimbing,
            'lombaOptions' => $lombaOptions,
            'dosenOptions' => $dosenOptions,
            'search' => $search,
            'lombaFilter' => $lombaFilter,
            'dosenFilter' => $dosenFilter,
            'logbookFilter' => $logbookFilter,
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

        // Tim harus sudah disetujui admin sebelum dapat mengajukan dosen pembimbing
        if ($tim->status_tim !== 'disetujui') {
            return back()->with('error', 'Tim ini belum disetujui oleh admin. Pengajuan dosen pembimbing hanya dapat dilakukan setelah tim disetujui.');
        }

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
     * Menambahkan Catatan Logbook Bimbingan
     * Hak Akses:
     * - Mahasiswa: HANYA Ketua Tim yang berhak mengisi logbook. Anggota tim selain ketua bersifat read-only.
     * - Dosen Pembimbing: Berhak mengisi logbook untuk tim yang dibimbingnya.
     */
    public function storeLogbook(Request $request)
    {
        $validated = $request->validate([
            'id_pengajuan' => 'required|exists:pengajuan_bimbingan,id_pengajuan',
            'tanggal' => 'required|date',
            'materi' => 'required|string|max:1000',
            'tindak_lanjut' => 'nullable|string|max:1000',
            'link_dokumentasi' => 'nullable|url:https|max:500',
        ], [
            'link_dokumentasi.url' => 'Link dokumentasi harus berupa URL valid yang diawali https:// (contoh: link Google Drive).',
            'link_dokumentasi.max' => 'Link dokumentasi maksimal 500 karakter.',
        ]);

        $authUser = $this->authUser();
        if (! $authUser) {
            return back()->with('error', 'Silakan masuk ke akun Anda terlebih dahulu.');
        }

        $pengajuan = PengajuanBimbingan::with(['tim.anggotaDiterima'])->findOrFail($validated['id_pengajuan']);

        if ($pengajuan->status !== 'disetujui' || $pengajuan->tim->status_tim !== 'disetujui') {
            return back()->with('error', 'Logbook hanya dapat ditambahkan setelah bimbingan resmi disetujui oleh Dosen Pembimbing dan tim telah disetujui oleh Admin.');
        }

        if ($pengajuan->tim->isKompetisiSelesai($pengajuan)) {
            return back()->with('error', 'Kompetisi tim ini telah selesai. Catatan logbook baru tidak dapat ditambahkan.');
        }

        // Pengecekan Otorisasi:
        if ($authUser['role'] === 'mahasiswa') {
            $isKetua = ($pengajuan->tim->nim === $authUser['id'] && Tim::ketuaDisetujui($authUser['id']));
            $isAnggota = $pengajuan->tim->anggotaDiterima->contains('nim', $authUser['id']);

            if (! $isKetua) {
                if ($isAnggota) {
                    return back()->with('error', 'Akses ditolak. Anggota tim hanya memiliki hak akses baca (read-only) pada logbook bimbingan.');
                }

                return back()->with('error', 'Anda bukan anggota resmi dari tim ini.');
            }

            $statusValidasi = 'menunggu';
            $catatanDosen = null;
        } elseif ($authUser['role'] === 'dosen') {
            if ($pengajuan->nidn !== $authUser['id']) {
                return back()->with('error', 'Anda hanya dapat menambahkan logbook untuk tim yang Anda bimbing.');
            }

            $statusValidasi = 'disetujui';
            $catatanDosen = 'Dicatat langsung oleh Dosen Pembimbing.';
        } else {
            return back()->with('error', 'Hanya Ketua Tim atau Dosen Pembimbing yang memiliki wewenang untuk mengisi logbook.');
        }

        Logbook::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'tanggal' => $validated['tanggal'],
            'materi' => $validated['materi'],
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? '-',
            'link_dokumentasi' => $validated['link_dokumentasi'] ?? null,
            'status_validasi' => $statusValidasi,
            'catatan_dosen' => $catatanDosen,
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
        if (! $authUser || $authUser['role'] !== 'dosen') {
            return back()->with('error', 'Hanya Dosen Pembimbing yang berhak memvalidasi logbook.');
        }

        $logbook = Logbook::with('pengajuan')->findOrFail($id);

        if ($logbook->pengajuan->nidn !== $authUser['id']) {
            return back()->with('error', 'Anda hanya dapat memvalidasi logbook untuk tim bimbingan Anda.');
        }

        $logbook->validasi($validated['status_validasi'], $validated['catatan_dosen'] ?? null);

        $pesan = $validated['status_validasi'] === 'disetujui'
            ? 'Logbook bimbingan berhasil disetujui.'
            : 'Status logbook ditandai memerlukan revisi mahasiswa beserta catatan feedback.';

        return back()->with('success', $pesan);
    }

    /**
     * Memperbarui Entri Logbook yang Memerlukan Revisi
     * Hak Akses:
     * - Mahasiswa: HANYA Ketua Tim yang dapat memperbarui logbook.
     *   Anggota tim selain ketua bersifat read-only dan dilarang mengubah logbook.
     * - Dosen Pembimbing: Berhak memperbarui logbook dari tim yang dibimbingnya.
     */
    public function updateLogbook(UpdateLogbookRequest $request, $id)
    {
        $validated = $request->validated();

        $authUser = $this->authUser();
        if (! $authUser) {
            return back()->with('error', 'Silakan masuk ke akun Anda terlebih dahulu.');
        }

        $logbook = Logbook::with(['pengajuan.tim.anggotaDiterima'])->findOrFail($id);

        if ($logbook->pengajuan->tim->status_tim !== 'disetujui') {
            return back()->with('error', 'Tim belum disetujui oleh Admin.');
        }

        if ($authUser['role'] === 'mahasiswa') {
            $isKetua = ($logbook->pengajuan->tim->nim === $authUser['id'] && Tim::ketuaDisetujui($authUser['id']));
            $isAnggota = $logbook->pengajuan->tim->anggotaDiterima->contains('nim', $authUser['id']);

            if (! $isKetua) {
                if ($isAnggota) {
                    return back()->with('error', 'Akses ditolak. Anggota tim hanya memiliki hak akses baca (read-only) dan tidak dapat mengubah logbook.');
                }

                return back()->with('error', 'Anda bukan anggota resmi dari tim ini.');
            }

            if ($logbook->status_validasi === 'disetujui') {
                return back()->with('error', 'Logbook yang sudah disetujui oleh Dosen Pembimbing tidak dapat diubah.');
            }

            $logbook->update([
                'tanggal' => $validated['tanggal'],
                'materi' => $validated['materi'],
                'tindak_lanjut' => $validated['tindak_lanjut'] ?? '-',
                'link_dokumentasi' => $validated['link_dokumentasi'] ?? null,
                'status_validasi' => 'menunggu',
            ]);
        } elseif ($authUser['role'] === 'dosen') {
            if ($logbook->pengajuan->nidn !== $authUser['id']) {
                return back()->with('error', 'Anda hanya dapat memperbarui logbook untuk tim yang Anda bimbing.');
            }

            $logbook->update([
                'tanggal' => $validated['tanggal'],
                'materi' => $validated['materi'],
                'tindak_lanjut' => $validated['tindak_lanjut'] ?? '-',
                'link_dokumentasi' => $validated['link_dokumentasi'] ?? null,
            ]);
        } else {
            return back()->with('error', 'Hanya Ketua Tim atau Dosen Pembimbing yang berhak mengedit logbook.');
        }

        return back()->with('success', 'Catatan logbook berhasil diperbarui dan diajukan ulang untuk validasi dosen.');
    }

    /**
     * Menghapus Entri Logbook
     * Hak Akses:
     * - Mahasiswa: HANYA Ketua Tim yang dapat menghapus logbook (jika belum disetujui).
     *   Anggota tim selain ketua tidak dapat menghapus logbook.
     * - Dosen Pembimbing: Berhak menghapus logbook dari tim yang dibimbingnya.
     */
    public function destroyLogbook($id)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            return back()->with('error', 'Silakan masuk ke akun Anda terlebih dahulu.');
        }

        $logbook = Logbook::with(['pengajuan.tim.anggotaDiterima'])->findOrFail($id);

        if ($logbook->pengajuan->tim->status_tim !== 'disetujui') {
            return back()->with('error', 'Tim belum disetujui oleh Admin.');
        }

        if ($authUser['role'] === 'mahasiswa') {
            $isKetua = ($logbook->pengajuan->tim->nim === $authUser['id'] && Tim::ketuaDisetujui($authUser['id']));
            $isAnggota = $logbook->pengajuan->tim->anggotaDiterima->contains('nim', $authUser['id']);

            if (! $isKetua) {
                if ($isAnggota) {
                    return back()->with('error', 'Akses ditolak. Anggota tim hanya memiliki hak akses baca (read-only) dan tidak dapat menghapus logbook.');
                }

                return back()->with('error', 'Anda bukan anggota resmi dari tim ini.');
            }

            if ($logbook->status_validasi === 'disetujui') {
                return back()->with('error', 'Logbook yang sudah disetujui oleh Dosen Pembimbing tidak dapat dihapus.');
            }

            $logbook->delete();
        } elseif ($authUser['role'] === 'dosen') {
            if ($logbook->pengajuan->nidn !== $authUser['id']) {
                return back()->with('error', 'Anda hanya dapat menghapus logbook untuk tim yang Anda bimbing.');
            }

            $logbook->delete();
        } else {
            return back()->with('error', 'Hanya Ketua Tim atau Dosen Pembimbing yang berhak menghapus logbook.');
        }

        return back()->with('success', 'Catatan logbook berhasil dihapus.');
    }

    /**
     * Tentukan nama babak final yang disimpan (Babak Lainnya memakai teks singkat dari ketua).
     *
     * @param  array<string, mixed>  $validated
     */
    private function resolveNamaBabak(array $validated): string
    {
        if ($validated['babak'] === ProgresBabak::BABAK_LAINNYA) {
            return trim((string) $validated['babak_lainnya']);
        }

        return $validated['babak'];
    }

    /**
     * Cek aturan: per tim dan lomba hanya boleh ada SATU laporan peringkat Final
     * (Juara 1/2/3, Harapan/Favorit, Finalis) DAN SATU laporan Penghargaan Khusus
     * yang aktif (menunggu atau disetujui). Laporan yang ditolak tidak dihitung.
     * Mengembalikan pesan galat bila terjadi konflik, atau null jika aman.
     */
    private function cekKonflikLaporanFinal(PengajuanBimbingan $pengajuan, string $babak, string $hasil, ?int $kecualiIdProgres = null): ?string
    {
        if ($babak !== ProgresBabak::BABAK_FINAL) {
            return null;
        }

        $kategori = ProgresBabak::kategoriFinal($hasil);
        if ($kategori === null) {
            return null;
        }

        $idPengajuanTim = PengajuanBimbingan::where('id_tim', $pengajuan->id_tim)->pluck('id_pengajuan');

        $laporanAktif = ProgresBabak::whereIn('id_pengajuan', $idPengajuanTim)
            ->where('babak', ProgresBabak::BABAK_FINAL)
            ->whereIn('status_setuju', ['menunggu', 'disetujui'])
            ->when($kecualiIdProgres, fn ($q) => $q->where('id_progres', '!=', $kecualiIdProgres))
            ->get();

        $konflik = $laporanAktif->first(fn (ProgresBabak $item) => ProgresBabak::kategoriFinal($item->hasil_akhir) === $kategori);

        if (! $konflik) {
            return null;
        }

        return $kategori === 'penghargaan_khusus'
            ? 'Tim ini sudah memiliki laporan Penghargaan Khusus babak Final yang menunggu verifikasi atau sudah disetujui.'
            : 'Tim ini sudah memiliki laporan peringkat babak Final yang menunggu verifikasi atau sudah disetujui.';
    }

    /**
     * Mahasiswa Melaporkan Capaian Progres Babak Kompetisi
     */
    public function storeProgres(StoreProgresBabakRequest $request)
    {
        $validated = $request->validated();
        $pengajuan = PengajuanBimbingan::with(['tim.anggotaDiterima'])->findOrFail($validated['id_pengajuan']);

        Gate::forUser(Auth::user())->authorize('create-progres-babak', $pengajuan);

        if ($pengajuan->tim->isKompetisiSelesai($pengajuan)) {
            return back()->with('error', 'Kompetisi tim ini telah selesai (laporan Final atau Tidak lolos sudah disetujui dosen). Tidak dapat menambah laporan progres babak baru.');
        }

        $namaBabak = $this->resolveNamaBabak($validated);
        $isFinal = $namaBabak === ProgresBabak::BABAK_FINAL;

        $pesanKonflik = $this->cekKonflikLaporanFinal($pengajuan, $namaBabak, $validated['hasil_akhir']);
        if ($pesanKonflik) {
            return back()->withInput()->withErrors(['hasil_akhir' => $pesanKonflik]);
        }

        $pathBukti = null;
        if ($isFinal && $request->hasFile('file_bukti')) {
            $pathBukti = $request->file('file_bukti')->store('progres_bukti', ProgresBabak::DISK_BUKTI);
        }

        ProgresBabak::create([
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'babak' => $namaBabak,
            'tanggal_update' => $validated['tanggal_update'],
            'hasil_akhir' => $validated['hasil_akhir'],
            'keterangan_hasil' => $isFinal ? ($validated['keterangan_hasil'] ?? null) : null,
            'file_bukti' => $pathBukti,
            'status_setuju' => 'menunggu',
        ]);

        return back()->with('success', 'Laporan progres babak kompetisi berhasil dikirimkan! Menunggu verifikasi Dosen Pembimbing.');
    }

    /**
     * Ketua Tim Memperbaiki & Mengirim Ulang Laporan Progres Babak yang Ditolak Dosen
     */
    public function updateProgres(StoreProgresBabakRequest $request, $id)
    {
        $validated = $request->validated();
        $progres = ProgresBabak::with('pengajuan.tim.anggotaDiterima')->findOrFail($id);
        $pengajuan = $progres->pengajuan;

        Gate::forUser(Auth::user())->authorize('update-progres-babak', $progres);

        if ($pengajuan->tim->isKompetisiSelesai($pengajuan)) {
            return back()->with('error', 'Kompetisi tim ini telah selesai. Laporan progres babak tidak dapat diubah atau dikirim ulang.');
        }

        if ($progres->status_setuju !== 'ditolak') {
            return back()->with('error', 'Hanya laporan yang ditolak dosen yang dapat diperbaiki dan dikirim ulang.');
        }

        $namaBabak = $this->resolveNamaBabak($validated);
        $isFinal = $namaBabak === ProgresBabak::BABAK_FINAL;

        $pesanKonflik = $this->cekKonflikLaporanFinal($pengajuan, $namaBabak, $validated['hasil_akhir'], (int) $progres->id_progres);
        if ($pesanKonflik) {
            return back()->withInput()->withErrors(['hasil_akhir' => $pesanKonflik]);
        }

        $pathBukti = $progres->file_bukti;
        if (! $isFinal) {
            $this->hapusFileBukti($pathBukti);
            $pathBukti = null;
        } elseif ($request->hasFile('file_bukti')) {
            $this->hapusFileBukti($pathBukti);
            $pathBukti = $request->file('file_bukti')->store('progres_bukti', ProgresBabak::DISK_BUKTI);
        }

        $progres->update([
            'babak' => $namaBabak,
            'tanggal_update' => $validated['tanggal_update'],
            'hasil_akhir' => $validated['hasil_akhir'],
            'keterangan_hasil' => $isFinal ? ($validated['keterangan_hasil'] ?? null) : null,
            'file_bukti' => $pathBukti,
            'status_setuju' => 'menunggu',
            'catatan_dosen' => null,
            'alasan_penolakan' => null,
            'diverifikasi_oleh' => null,
            'diverifikasi_pada' => null,
        ]);

        return back()->with('success', 'Laporan progres babak berhasil diperbaiki dan dikirim ulang! Menunggu verifikasi Dosen Pembimbing.');
    }

    /**
     * Hapus berkas bukti progres babak dari penyimpanan (jika ada).
     */
    private function hapusFileBukti(?string $path): void
    {
        if ($path && Storage::disk(ProgresBabak::DISK_BUKTI)->exists($path)) {
            Storage::disk(ProgresBabak::DISK_BUKTI)->delete($path);
        }
    }

    /**
     * Tampilkan berkas bukti progres babak (tab baru) untuk ketua, anggota,
     * dosen pembimbing tim tersebut, dan admin.
     */
    public function lihatBuktiProgres($id)
    {
        $progres = ProgresBabak::with('pengajuan.tim.anggotaDiterima')->findOrFail($id);

        Gate::forUser(Auth::user())->authorize('view-progres-bukti', $progres);

        if (! $progres->file_bukti) {
            abort(404, 'Bukti belum diunggah untuk laporan ini.');
        }

        $disk = Storage::disk(ProgresBabak::DISK_BUKTI);

        if (! $disk->exists($progres->file_bukti)) {
            abort(404, 'Berkas bukti tidak ditemukan pada server penyimpanan.');
        }

        $ekstensi = pathinfo($progres->file_bukti, PATHINFO_EXTENSION) ?: 'pdf';
        $namaFile = 'Bukti_Progres_'.$progres->id_progres.'.'.$ekstensi;

        return $disk->response($progres->file_bukti, $namaFile, [
            'Content-Disposition' => 'inline; filename="'.$namaFile.'"',
        ]);
    }

    /**
     * Dosen Pembimbing Menyetujui / Menolak Laporan Progres Babak (hanya yang berstatus menunggu)
     */
    public function responProgresBabak(ResponProgresBabakRequest $request, $id)
    {
        $validated = $request->validated();
        $progres = ProgresBabak::with('pengajuan')->findOrFail($id);

        Gate::forUser(Auth::user())->authorize('respond-progres-babak', $progres);

        $authUser = $this->authUser();
        $verifikatorId = (string) ($authUser['id'] ?? Auth::user()?->nim_nip ?? '');

        if ($progres->status_setuju !== 'menunggu') {
            return back()->with('error', 'Hanya laporan progres babak berstatus menunggu yang dapat disetujui atau ditolak.');
        }

        if ($validated['status_setuju'] === 'disetujui') {
            $progres->setujuiProgres(null, $verifikatorId);
            $pesan = "Capaian babak '{$progres->babak}' berhasil disetujui.";
        } else {
            $progres->tolakProgres($validated['alasan_penolakan'], $verifikatorId);
            $pesan = "Capaian babak '{$progres->babak}' ditolak dan dikembalikan ke Ketua Tim beserta alasan penolakan.";
        }

        return back()->with('success', $pesan);
    }

    /**
     * Mahasiswa (Ketua Tim maupun Anggota Tim resmi) Menyimpan Draft atau Mengirim Pengajuan RAB
     * Sesuai Standar Biaya Masukan (SBM) TA 2026 (PMK Nomor 32 Tahun 2025).
     */
    public function storeOrUpdateRab(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa ketua atau anggota tim yang dapat mengajukan RAB.');
        }

        $validator = Validator::make($request->all(), [
            'tim_id' => ['required', 'exists:tim,id_tim'],
            'babak' => ['required', 'in:semi_final,final'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'kota_tujuan' => ['required', 'string', 'max:100'],
            'provinsi_tujuan' => ['required', 'string', 'max:100'],
            'link_dokumen' => ['nullable', 'url:https', 'max:500'],
            'aksi' => ['required', 'in:draft,kirim'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.kategori' => [
                'required',
                'in:uang_harian_luar_kota,uang_harian_dalam_kota,penginapan,tiket_antar_kota,transport_terminal,transport_lokal,biaya_pendaftaran,lain_lain',
            ],
            'items.*.uraian' => ['required', 'string', 'max:255'],
            'items.*.satuan' => ['nullable', 'string', 'max:30'],
            'items.*.jumlah_orang' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.frekuensi' => ['required', 'integer', 'min:1', 'max:365'],
            'items.*.tarif_satuan' => ['required', 'numeric', 'min:0'],
            'items.*.alasan_melebihi' => ['nullable', 'string', 'max:1000'],
            'items.*.bukti_link' => ['nullable', 'url:https', 'max:500'],
        ], [
            'babak.in' => 'Babak pengajuan RAB harus berupa Semi Final atau Final.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai kegiatan tidak boleh sebelum tanggal mulai.',
            'link_dokumen.url' => 'Link dokumen pendukung harus berupa URL valid diawali https:// (contoh: Google Drive).',
            'items.required' => 'Rincian komponen biaya RAB wajib diisi minimal 1 baris.',
            'items.min' => 'Rincian komponen biaya RAB wajib diisi minimal 1 baris.',
            'items.*.bukti_link.url' => 'Link bukti komponen biaya harus berupa URL valid diawali https://.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $nim = $authUser['id'];

        /** @var Tim $tim */
        $tim = Tim::with(['lomba', 'anggotaDiterima', 'bimbinganDisetujui.progresBabak'])->findOrFail($validated['tim_id']);

        if ($tim->status_tim !== 'disetujui') {
            return back()->with('error', 'Tim belum disetujui oleh Admin.');
        }

        // Policy: Hanya Ketua Tim ATAU Anggota Tim resmi yang diterima pada tim tersebut yang boleh mengisi/mengedit RAB
        if (! $tim->isAnggotaAtauKetua($nim)) {
            return back()->with('error', 'Akses ditolak. Hanya Ketua Tim atau Anggota resmi dari tim ini yang dapat mengisi atau mengubah RAB.');
        }

        $bimbingan = $tim->bimbinganDisetujui;
        if (! $bimbingan || $bimbingan->status !== 'disetujui') {
            return back()->with('error', 'Pengajuan RAB hanya tersedia setelah tim memiliki Dosen Pembimbing resmi.');
        }

        // Syarat pengajuan: progres babak tim (semi_final / final) sudah tervalidasi dosen
        $babakTervalidasi = $tim->babakRabTervalidasi($bimbingan);
        if (! in_array($validated['babak'], $babakTervalidasi, true)) {
            return back()->withInput()->with('error', 'Pengajuan RAB untuk babak ini belum tersedia. Pastikan progres babak Semi Final / Final telah dilaporkan dan divalidasi oleh Dosen Pembimbing.');
        }

        // Cek apakah sudah ada RAB untuk (tim_id, babak)
        $existingRab = RabPengajuan::where('tim_id', $tim->id_tim)
            ->where('babak', $validated['babak'])
            ->first();

        if (! $existingRab && $tim->isKompetisiSelesai($bimbingan)) {
            return back()->with('error', 'Kompetisi tim ini telah selesai. Pengajuan RAB baru tidak dapat dibuat.');
        }

        if ($existingRab && ! $existingRab->isEditable()) {
            $labelStatus = $existingRab->labelStatus();

            return back()->with('error', "RAB babak {$existingRab->labelBabak()} berstatus '{$labelStatus}' dan telah terkunci (tidak dapat diubah).");
        }

        // Ambil referensi tarif SBM TA 2026 berdasarkan provinsi tujuan & nasional
        $provinsiTujuan = trim($validated['provinsi_tujuan']);
        $sbmProvinsiMap = SbmTarif::where('tahun_anggaran', 2026)
            ->where(function ($q) use ($provinsiTujuan) {
                $q->whereRaw('LOWER(provinsi) = ?', [strtolower($provinsiTujuan)])
                    ->orWhereNull('provinsi');
            })
            ->get()
            ->keyBy('kode');

        $customErrors = [];
        $preparedItems = [];
        $totalAnggaranServer = 0;

        foreach ($validated['items'] as $idx => $itemInput) {
            $nomorBaris = $idx + 1;
            $kategori = $itemInput['kategori'];
            $metaDefault = RabItem::defaultMetaForKategori($kategori);
            $sifat = $metaDefault['sifat'];
            $satuan = ! empty($itemInput['satuan']) ? trim($itemInput['satuan']) : $metaDefault['satuan'];

            $jumlahOrang = (int) $itemInput['jumlah_orang'];
            $frekuensi = (int) $itemInput['frekuensi'];
            $tarifSatuan = (float) $itemInput['tarif_satuan'];
            $alasanMelebihi = isset($itemInput['alasan_melebihi']) ? trim((string) $itemInput['alasan_melebihi']) : null;
            $buktiLink = isset($itemInput['bukti_link']) && trim((string) $itemInput['bukti_link']) !== ''
                ? trim((string) $itemInput['bukti_link'])
                : null;

            $sbmRef = $sbmProvinsiMap->get($kategori);

            // Jika kategori adalah uang_harian_luar_kota, uang_harian_dalam_kota, atau penginapan dan tarif_satuan 0 atau kosong, isi otomatis dari sbm_tarif bila tersedia
            if (in_array($kategori, ['uang_harian_luar_kota', 'uang_harian_dalam_kota', 'penginapan'], true) && $tarifSatuan <= 0 && $sbmRef) {
                $tarifSatuan = (float) $sbmRef->tarif;
            }

            // 1. Validasi sifat batas_tertinggi: tolak jika tarif_satuan melebihi tarif di sbm_tarif (provinsi tujuan)
            if ($sifat === 'batas_tertinggi') {
                if (! $sbmRef) {
                    $customErrors["items.{$idx}.tarif_satuan"] = "Baris #{$nomorBaris}: Referensi tarif SBM untuk provinsi '{$provinsiTujuan}' belum tersedia pada database.";
                } elseif ($tarifSatuan > (float) $sbmRef->tarif) {
                    $batasFormatted = 'Rp '.number_format((float) $sbmRef->tarif, 0, ',', '.');
                    $customErrors["items.{$idx}.tarif_satuan"] = "Baris #{$nomorBaris} ({$metaDefault['label']}): Tarif satuan tidak boleh melebihi batas tertinggi SBM Provinsi {$provinsiTujuan} ({$batasFormatted} / {$sbmRef->satuan}).";
                }
            }

            // 2. Validasi sifat dapat_dilampaui: boleh melebihi tarif SBM, tetapi alasan_melebihi wajib diisi
            if ($sifat === 'dapat_dilampaui' && $sbmRef && $tarifSatuan > (float) $sbmRef->tarif) {
                if (empty($alasanMelebihi)) {
                    $batasFormatted = 'Rp '.number_format((float) $sbmRef->tarif, 0, ',', '.');
                    $customErrors["items.{$idx}.alasan_melebihi"] = "Baris #{$nomorBaris} ({$metaDefault['label']}): Tarif melebihi standar SBM ({$batasFormatted}), wajib mengisi alasan melebihi tarif.";
                }
            }

            // 3. Validasi sifat at_cost: bukti_link wajib diisi
            if ($sifat === 'at_cost') {
                if (empty($buktiLink)) {
                    $customErrors["items.{$idx}.bukti_link"] = "Baris #{$nomorBaris} ({$metaDefault['label']}): Komponen bersifat riil (at cost), wajib menyertakan link bukti pengeluaran/tarif.";
                }
            }

            $subtotal = round($jumlahOrang * $frekuensi * $tarifSatuan, 2);
            $totalAnggaranServer += $subtotal;

            $preparedItems[] = [
                'kategori' => $kategori,
                'uraian' => trim($itemInput['uraian']),
                'satuan' => $satuan,
                'jumlah_orang' => $jumlahOrang,
                'frekuensi' => $frekuensi,
                'tarif_satuan' => $tarifSatuan,
                'sifat' => $sifat,
                'subtotal' => $subtotal,
                'alasan_melebihi' => $alasanMelebihi ?: null,
                'bukti_link' => $buktiLink,
            ];
        }

        if (! empty($customErrors)) {
            throw ValidationException::withMessages($customErrors);
        }

        // Hitung jumlah peserta otomatis dari anggota tim yang diterima (termasuk ketua)
        $jumlahPesertaOtomatis = max(1, $tim->anggotaDiterima->pluck('nim')->push($tim->nim)->unique()->count());

        $aksiInput = $validated['aksi'];

        return DB::transaction(function () use ($tim, $validated, $existingRab, $preparedItems, $totalAnggaranServer, $jumlahPesertaOtomatis, $nim, $aksiInput) {
            $statusSebelum = $existingRab ? $existingRab->status : null;

            if ($aksiInput === 'kirim') {
                $statusBaru = 'menunggu_validasi';
            } else {
                // Jika sedang dalam status perlu_revisi dan klik Simpan Draft, pertahankan perlu_revisi agar konteks revisi dosen tidak hilang
                $statusBaru = ($existingRab && $existingRab->status === 'perlu_revisi') ? 'perlu_revisi' : 'draft';
            }

            $rab = RabPengajuan::updateOrCreate(
                [
                    'tim_id' => $tim->id_tim,
                    'babak' => $validated['babak'],
                ],
                [
                    'lomba_id' => $tim->id_lomba,
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'tanggal_selesai' => $validated['tanggal_selesai'],
                    'kota_tujuan' => trim($validated['kota_tujuan']),
                    'provinsi_tujuan' => trim($validated['provinsi_tujuan']),
                    'jumlah_peserta' => $jumlahPesertaOtomatis,
                    'total_anggaran' => $totalAnggaranServer,
                    'status' => $statusBaru,
                    'link_dokumen' => $validated['link_dokumen'] ?? null,
                    'diisi_terakhir_oleh' => $nim,
                ]
            );

            // Ganti rincian item dengan yang terbaru
            $rab->items()->delete();
            foreach ($preparedItems as $row) {
                $rab->items()->create($row);
            }

            // Catat ke rab_riwayat jika dikirim / dikirim ulang ke dosen
            if ($aksiInput === 'kirim') {
                $jenisAksiRiwayat = ($statusSebelum === 'perlu_revisi') ? 'dikirim_ulang' : 'dikirim';

                RabRiwayat::create([
                    'rab_pengajuan_id' => $rab->id,
                    'aksi' => $jenisAksiRiwayat,
                    'status_dari' => $statusSebelum ?? 'draft',
                    'status_ke' => 'menunggu_validasi',
                    'catatan' => $jenisAksiRiwayat === 'dikirim_ulang'
                        ? 'RAB diperbaiki dan dikirim ulang ke Dosen Pembimbing.'
                        : 'RAB diajukan ke Dosen Pembimbing untuk divalidasi.',
                    'user_id' => $nim,
                    'total_anggaran' => $totalAnggaranServer,
                    'created_at' => Carbon::now(),
                ]);
            }

            $pesan = $aksiInput === 'kirim'
                ? "Pengajuan RAB babak {$rab->labelBabak()} berhasil dikirim ke Dosen Pembimbing untuk divalidasi."
                : "Draft RAB babak {$rab->labelBabak()} berhasil disimpan.";

            return redirect()->route('bimbingan.index', ['tim_id' => $tim->id_tim])
                ->with('success', $pesan);
        });
    }

    /**
     * Dosen Pembimbing Memvalidasi (Menyetujui) atau Meminta Revisi atas Pengajuan RAB Tim Bimbingannya
     */
    public function responRab(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,perlu_revisi'],
            'catatan_dosen' => ['nullable', 'string', 'max:1000', 'required_if:status,perlu_revisi'],
        ], [
            'status.required' => 'Status keputusan validasi RAB wajib dipilih.',
            'status.in' => 'Status keputusan RAB harus berupa disetujui atau perlu revisi.',
            'catatan_dosen.required_if' => 'Catatan revisi wajib diisi apabila meminta revisi RAB.',
            'catatan_dosen.max' => 'Catatan dosen maksimal 1000 karakter.',
        ]);

        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'dosen') {
            return back()->with('error', 'Hanya Dosen Pembimbing yang berhak memvalidasi atau meminta revisi RAB.');
        }

        $nidn = $authUser['id'];

        /** @var RabPengajuan $rab */
        $rab = RabPengajuan::with('tim')->findOrFail($id);

        // Pastikan dosen adalah pembimbing resmi untuk tim yang mengajukan RAB ini
        $isPembimbingResmi = PengajuanBimbingan::where('id_tim', $rab->tim_id)
            ->where('nidn', $nidn)
            ->where('status', 'disetujui')
            ->exists();

        if (! $isPembimbingResmi) {
            return back()->with('error', 'Anda hanya dapat memvalidasi RAB dari tim mahasiswa yang resmi Anda bimbing.');
        }

        if ($rab->status === 'disetujui') {
            return back()->with('error', 'RAB ini sudah berstatus disetujui dan terkunci.');
        }

        if ($rab->status === 'draft') {
            return back()->with('error', 'RAB berstatus draft belum dikirim oleh mahasiswa untuk divalidasi.');
        }

        return DB::transaction(function () use ($rab, $validated, $nidn) {
            $statusSebelum = $rab->status;
            $statusBaru = $validated['status'];
            $catatan = isset($validated['catatan_dosen']) && trim((string) $validated['catatan_dosen']) !== ''
                ? trim((string) $validated['catatan_dosen'])
                : ($statusBaru === 'disetujui' ? 'RAB telah divalidasi dan disetujui oleh Dosen Pembimbing.' : null);

            $rab->update([
                'status' => $statusBaru,
                'catatan_dosen' => $catatan,
                'divalidasi_oleh' => $nidn,
                'divalidasi_pada' => Carbon::now(),
            ]);

            RabRiwayat::create([
                'rab_pengajuan_id' => $rab->id,
                'aksi' => $statusBaru === 'disetujui' ? 'disetujui' : 'direvisi_dosen',
                'status_dari' => $statusSebelum,
                'status_ke' => $statusBaru,
                'catatan' => $catatan,
                'user_id' => $nidn,
                'total_anggaran' => (float) $rab->total_anggaran,
                'created_at' => Carbon::now(),
            ]);

            $namaTim = $rab->tim->nama_tim ?? 'Tim';
            $labelBabak = $rab->labelBabak();

            $pesan = $statusBaru === 'disetujui'
                ? "Pengajuan RAB babak {$labelBabak} dari tim \"{$namaTim}\" berhasil divalidasi dan disetujui."
                : "Pengajuan RAB babak {$labelBabak} dari tim \"{$namaTim}\" dikembalikan kepada mahasiswa dengan status Perlu Revisi beserta catatan.";

            return back()->with('success', $pesan);
        });
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
        $nim = $authUser['id'];

        // Cek duplikasi pengajuan untuk lomba yang sama
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

        // Buat tim baru dengan mahasiswa yang sedang login sebagai ketua tim
        $tim = Tim::create([
            'nama_tim' => $validated['nama_tim'],
            'id_lomba' => $lomba->id_lomba,
            'nim' => $authUser['id'],
            'kuota_anggota' => $validated['kuota_anggota'] ?? 3,
            'status_tim' => 'menunggu_persetujuan',
        ]);

        // Daftarkan calon ketua tim dengan status menunggu (belum disetujui admin)
        AnggotaTim::create([
            'id_tim' => $tim->id_tim,
            'nim' => $authUser['id'],
            'status_gabung' => 'menunggu',
            'peran' => 'Ketua Tim',
        ]);

        Cache::flush();

        return redirect()->route('tim.index')
            ->with('success', "Pengajuan tim \"{$tim->nama_tim}\" untuk lomba \"{$lomba->nama_lomba}\" berhasil dikirim dan sedang menunggu persetujuan admin. Modul bimbingan akan aktif setelah tim disetujui.");
    }
}
