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
     * Menggunakan query batching & in-memory collection processing
     * untuk kecepatan maksimal tanpa masalah serialisasi objek.
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

        // 2. Metrics Tim & Aktor
        $totalTim = Tim::where('status_tim', 'disetujui')->count();

        $totalMahasiswaAktif = DB::table('anggota_tim')
            ->where('status_gabung', 'diterima')
            ->distinct('nim')
            ->count('nim');

        $totalDosenAktif = DB::table('pengajuan_bimbingan')
            ->where('status', 'disetujui')
            ->distinct('nidn')
            ->count('nidn');

        // 3. Ambil Progres Babak & Prestasi dalam 1 Query Eager-Loaded
        $allProgres = ProgresBabak::with([
            'pengajuan.tim.lomba',
            'pengajuan.tim.ketua',
            'pengajuan.dosen',
        ])
            ->where('status_setuju', 'disetujui')
            ->orderByDesc('tanggal_update')
            ->get();

        // Filter in-memory tanpa query berulang
        $babakMonitoring = $allProgres->take(6);

        $daftarJuara = $allProgres->filter(function ($item) {
            return $item->isJuara();
        })->values();

        $totalPrestasi = $daftarJuara->count();

        // 4. Analisis per Program Studi (SIB vs TI)
        $prodiStats = DB::table('mahasiswa')
            ->join('anggota_tim', 'mahasiswa.nim', '=', 'anggota_tim.nim')
            ->join('tim', 'anggota_tim.id_tim', '=', 'tim.id_tim')
            ->where('anggota_tim.status_gabung', 'diterima')
            ->select(
                'mahasiswa.prodi',
                DB::raw('count(distinct anggota_tim.nim) as total_mahasiswa'),
                DB::raw('count(distinct tim.id_tim) as total_tim')
            )
            ->groupBy('mahasiswa.prodi')
            ->get();

        $prestasiPerProdi = DB::table('progres_babak')
            ->join('pengajuan_bimbingan', 'progres_babak.id_pengajuan', '=', 'pengajuan_bimbingan.id_pengajuan')
            ->join('tim', 'pengajuan_bimbingan.id_tim', '=', 'tim.id_tim')
            ->join('mahasiswa', 'tim.nim', '=', 'mahasiswa.nim')
            ->where('progres_babak.status_setuju', 'disetujui')
            ->where('progres_babak.hasil_akhir', 'like', '%Juara%')
            ->select('mahasiswa.prodi', DB::raw('count(progres_babak.id_progres) as total_juara'))
            ->groupBy('mahasiswa.prodi')
            ->pluck('total_juara', 'prodi')
            ->toArray();

        // 5. Analisis Paling Sering Juara per Angkatan (4 Angkatan Akademik)
        $targetAngkatan = [2023, 2024, 2025, 2026];

        $juaraPerAngkatanQuery = DB::table('progres_babak')
            ->join('pengajuan_bimbingan', 'progres_babak.id_pengajuan', '=', 'pengajuan_bimbingan.id_pengajuan')
            ->join('tim', 'pengajuan_bimbingan.id_tim', '=', 'tim.id_tim')
            ->join('mahasiswa', 'tim.nim', '=', 'mahasiswa.nim')
            ->where('progres_babak.status_setuju', 'disetujui')
            ->where(function ($q) {
                $q->where('progres_babak.hasil_akhir', 'like', '%Juara%')
                    ->orWhere('progres_babak.hasil_akhir', 'like', '%Gold%')
                    ->orWhere('progres_babak.hasil_akhir', 'like', '%Silver%')
                    ->orWhere('progres_babak.hasil_akhir', 'like', '%Bronze%')
                    ->orWhere('progres_babak.hasil_akhir', 'like', '%Best%')
                    ->orWhere('progres_babak.hasil_akhir', 'like', '%Pemenang%');
            })
            ->select('mahasiswa.angkatan', DB::raw('count(progres_babak.id_progres) as total_juara'))
            ->groupBy('mahasiswa.angkatan')
            ->pluck('total_juara', 'angkatan')
            ->toArray();

        $angkatanStats = collect($targetAngkatan)->map(function ($tahun) use ($juaraPerAngkatanQuery) {
            return (object) [
                'angkatan' => (string) $tahun,
                'total_juara' => (int) ($juaraPerAngkatanQuery[$tahun] ?? 0),
            ];
        });

        $angkatanTerbaik = $angkatanStats->sortByDesc('total_juara')->first();

        // 6. Tim Aktif yang sedang bertanding
        $timAktif = Tim::with([
            'lomba',
            'ketua',
            'bimbinganDisetujui.dosen',
            'anggotaDiterima.mahasiswa',
        ])
            ->where('status_tim', 'disetujui')
            ->latest()
            ->take(5)
            ->get();

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
