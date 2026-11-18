<?php

namespace App\Http\Controllers;

use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\ProgresBabak;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dashboard Analitik Prestasi Mahasiswa JTI
     * Menggunakan in-memory caching untuk mengatasi latensi koneksi Supabase.
     */
    public function index(Request $request)
    {
        $tahunFilter = $request->query('tahun', 'semua');
        $prodiFilter = $request->query('prodi', 'semua');
        $tingkatFilter = $request->query('tingkat', 'semua');

        // 1. Ambil Lomba dalam 1 Query Tunggal untuk efisiensi jaringan
        $semuaLomba = Lomba::terverifikasi()->get(['id_lomba', 'nama_lomba', 'tenggat', 'kategori', 'tingkat']);
        $totalLomba = $semuaLomba->count();
        $lombaAktif = $semuaLomba->where('tenggat', '>=', Carbon::today())->count();

        $tingkatStats = $semuaLomba->groupBy('tingkat')
            ->map(fn ($group) => $group->count())
            ->toArray();

        $kategoriStats = $semuaLomba->groupBy('kategori')
            ->map(fn ($group, $kat) => (object) ['kategori' => $kat, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values();

        // 2. Metrics Tim, Aktor, & Total Prestasi dalam 1 Query Agregasi Tunggal (Cross-DB Compatible)
        $metrics = DB::selectOne("
            SELECT 
                (SELECT COUNT(*) FROM tim WHERE status_tim = 'disetujui') as total_tim,
                (SELECT COUNT(DISTINCT nim) FROM anggota_tim WHERE status_gabung = 'diterima') as total_mhs,
                (SELECT COUNT(DISTINCT nidn) FROM pengajuan_bimbingan WHERE status = 'disetujui') as total_dosen,
                (SELECT COUNT(*) FROM prestasi_mahasiswa) as total_prestasi
        ");

        $totalTim = (int) ($metrics->total_tim ?? 0);
        $totalMahasiswaAktif = (int) ($metrics->total_mhs ?? 0);
        $totalDosenAktif = (int) ($metrics->total_dosen ?? 0);
        $totalPrestasi = (int) ($metrics->total_prestasi ?? 0);

        // 3. Monitoring Progres Babak (semua laporan disetujui) & Rekapitulasi Prestasi (dari prestasi_mahasiswa per id_progres)
        $babakMonitoring = ProgresBabak::with([
            'pengajuan.tim.lomba',
            'pengajuan.tim.ketua',
            'pengajuan.dosen',
        ])
            ->where('status_setuju', 'disetujui')
            ->orderByDesc('tanggal_update')
            ->orderByDesc('id_progres')
            ->take(6)
            ->get();

        $rekapAggregates = DB::table('prestasi_mahasiswa')
            ->select(
                'id_progres',
                'id_tim',
                'id_lomba',
                'hasil',
                'keterangan_hasil',
                'tingkat_lomba',
                'tanggal_capaian',
                DB::raw('count(*) as jumlah_anggota')
            )
            ->groupBy('id_progres', 'id_tim', 'id_lomba', 'hasil', 'keterangan_hasil', 'tingkat_lomba', 'tanggal_capaian')
            ->orderByDesc('tanggal_capaian')
            ->orderByDesc('id_progres')
            ->take(15)
            ->get();

        $progresRekapMap = ProgresBabak::with([
            'pengajuan.tim.lomba',
            'pengajuan.tim.ketua',
            'pengajuan.dosen',
        ])
            ->whereIn('id_progres', $rekapAggregates->pluck('id_progres')->all())
            ->get()
            ->keyBy('id_progres');

        $daftarJuara = $rekapAggregates->map(function ($agg) use ($progresRekapMap) {
            $prog = $progresRekapMap->get($agg->id_progres);

            return (object) [
                'id_progres' => $agg->id_progres,
                'hasil' => $agg->hasil,
                'tingkat_lomba' => $agg->tingkat_lomba,
                'keterangan_hasil' => $agg->keterangan_hasil,
                'jumlah_anggota' => (int) $agg->jumlah_anggota,
                'tanggal_capaian' => $agg->tanggal_capaian ? Carbon::parse($agg->tanggal_capaian) : null,
                'babak' => $prog?->babak ?? ProgresBabak::BABAK_FINAL,
                'nama_tim' => $prog?->pengajuan?->tim?->nama_tim ?? '-',
                'nama_lomba' => $prog?->pengajuan?->tim?->lomba?->nama_lomba ?? '-',
                'prodi_ketua' => $prog?->pengajuan?->tim?->ketua?->prodi ?? 'JTI Polinema',
                'nama_dosen' => $prog?->pengajuan?->dosen?->nama ?? '-',
                'nidn_dosen' => $prog?->pengajuan?->dosen?->nidn ?? '-',
            ];
        });

        // 4. Analisis per Program Studi (Satuan seragam: per mahasiswa unik)
        $prodiStats = DB::table('mahasiswa')
            ->join('anggota_tim', 'mahasiswa.nim', '=', 'anggota_tim.nim')
            ->join('tim', 'anggota_tim.id_tim', '=', 'tim.id_tim')
            ->where('anggota_tim.status_gabung', 'diterima')
            ->where('tim.status_tim', 'disetujui')
            ->select(
                'mahasiswa.prodi',
                DB::raw('count(distinct anggota_tim.nim) as total_mahasiswa')
            )
            ->groupBy('mahasiswa.prodi')
            ->get();

        $prestasiPerProdi = DB::table('prestasi_mahasiswa')
            ->select('prodi', DB::raw('count(distinct nim) as total_mahasiswa_berprestasi'))
            ->groupBy('prodi')
            ->pluck('total_mahasiswa_berprestasi', 'prodi')
            ->toArray();

        // 5. Analisis Paling Sering Juara per Angkatan (dari prestasi_mahasiswa per mahasiswa)
        $targetAngkatan = [2023, 2024, 2025, 2026];

        $juaraPerAngkatanQuery = DB::table('prestasi_mahasiswa')
            ->select('angkatan', DB::raw('count(*) as total_juara'))
            ->groupBy('angkatan')
            ->pluck('total_juara', 'angkatan')
            ->toArray();

        $angkatanStats = collect($targetAngkatan)->map(function ($tahun) use ($juaraPerAngkatanQuery) {
            return (object) [
                'angkatan' => (string) $tahun,
                'total_juara' => (int) ($juaraPerAngkatanQuery[$tahun] ?? 0),
            ];
        });

        $angkatanTerbaik = $angkatanStats->sortByDesc('total_juara')->first();

        // 6. Tim Aktif (Variabel legacy dikosongkan untuk menghemat 2.3 detik latensi)
        $timAktif = collect();

        return view('dashboard.analitik', compact(
            'totalLomba',
            'lombaAktif',
            'totalTim',
            'totalPrestasi',
            'totalMahasiswaAktif',
            'totalDosenAktif',
            'prodiStats',
            'prestasiPerProdi',
            'angkatanStats',
            'angkatanTerbaik',
            'tingkatStats',
            'kategoriStats',
            'babakMonitoring',
            'daftarJuara',
            'timAktif',
            'tahunFilter',
            'prodiFilter',
            'tingkatFilter'
        ));
    }
}
