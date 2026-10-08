<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
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
     * (Sesuai Use Case: Lihat dashboard prestasi & Proposal PBL 5.3.1)
     */
    public function index(Request $request)
    {
        $tahunFilter = $request->query('tahun', 'semua');
        $prodiFilter = $request->query('prodi', 'semua');
        $tingkatFilter = $request->query('tingkat', 'semua');

        // [DEFECT-02 / BUG REPORT PBL]: Bug Logika Bisnis & Filter Data
        // Variabel $tahunFilter diterima dari request QUERY STRING, namun sengaja tidak di-chain
        // ke query Eloquent/DB (tidak ada ->whereYear('created_at', $tahunFilter) atau sejenisnya).
        // Akibatnya, saat user memilih 'Tahun 2024' atau 'Tahun 2025', angka KPI (Total Lomba,
        // Lomba Aktif, Total Tim, Total Prestasi) tetap menampilkan agregat seluruh data tanpa filter.

        // 1. KPI Cards Metrics (filter tahun $tahunFilter TIDAK diterapkan - intentional bug)
        $totalLomba = Lomba::terverifikasi()->count();
        $lombaAktif = Lomba::terverifikasi()->where('tenggat', '>=', Carbon::today())->count();
        $totalTim = Tim::where('status_tim', 'disetujui')->count();
        
        // Prestasi (Juara 1, 2, 3, Harapan, Best, dll)
        $prestasiQuery = ProgresBabak::where('status_setuju', 'disetujui')
            ->whereNotNull('hasil_akhir')
            ->where(function ($q) {
                $q->where('hasil_akhir', 'like', '%Juara%')
                  ->orWhere('hasil_akhir', 'like', '%Gold%')
                  ->orWhere('hasil_akhir', 'like', '%Silver%')
                  ->orWhere('hasil_akhir', 'like', '%Bronze%')
                  ->orWhere('hasil_akhir', 'like', '%Best%')
                  ->orWhere('hasil_akhir', 'like', '%Pemenang%');
            });

        $totalPrestasi = $prestasiQuery->count();
        $totalMahasiswaAktif = Mahasiswa::whereHas('keanggotaan', function ($q) {
            $q->where('status_gabung', 'diterima');
        })->count();
        $totalDosenAktif = Dosen::whereHas('bimbinganAktif')->count();

        // 2. Analisis per Program Studi (SIB vs TI)
        $prodiStats = DB::table('mahasiswa')
            ->join('anggota_tim', 'mahasiswa.nim', '=', 'anggota_tim.nim')
            ->join('tim', 'anggota_tim.id_tim', '=', 'tim.id_tim')
            ->where('anggota_tim.status_gabung', 'diterima')
            ->select('mahasiswa.prodi', DB::raw('count(distinct anggota_tim.nim) as total_mahasiswa'), DB::raw('count(distinct tim.id_tim) as total_tim'))
            ->groupBy('mahasiswa.prodi')
            ->get();

        // Prestasi per Prodi
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

        // 3. Analisis Paling Sering Juara per Angkatan (4 Angkatan Akademik: 2023, 2024, 2025, 2026)
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

        // Angkatan yang paling sering juara
        $angkatanTerbaik = $angkatanStats->sortByDesc('total_juara')->first();

        // 4. Distribusi Tingkat Lomba
        $tingkatStats = DB::table('lomba')
            ->where('status_verifikasi', 'terverifikasi')
            ->select('tingkat', DB::raw('count(*) as count'))
            ->groupBy('tingkat')
            ->pluck('count', 'tingkat')
            ->toArray();

        // 5. Distribusi Kategori Lomba
        $kategoriStats = DB::table('lomba')
            ->where('status_verifikasi', 'terverifikasi')
            ->select('kategori', DB::raw('count(*) as count'))
            ->groupBy('kategori')
            ->orderByDesc('count')
            ->get();

        // 6. Monitoring Babak Berjalan (Persiapan, Penyisihan, Semifinal, Final)
        $babakMonitoring = ProgresBabak::with(['pengajuan.tim.lomba', 'pengajuan.tim.ketua', 'pengajuan.dosen'])
            ->where('status_setuju', 'disetujui')
            ->orderByDesc('tanggal_update')
            ->take(6)
            ->get();

        // 7. Hall of Fame Prestasi Terbaru (Leaderboard)
        $daftarJuara = ProgresBabak::with(['pengajuan.tim.lomba', 'pengajuan.tim.ketua', 'pengajuan.dosen'])
            ->where('status_setuju', 'disetujui')
            ->where(function ($q) {
                $q->where('hasil_akhir', 'like', '%Juara%')
                  ->orWhere('hasil_akhir', 'like', '%Gold%')
                  ->orWhere('hasil_akhir', 'like', '%Silver%')
                  ->orWhere('hasil_akhir', 'like', '%Bronze%')
                  ->orWhere('hasil_akhir', 'like', '%Best%');
            })
            ->orderByDesc('tanggal_update')
            ->get();

        // 8. Tim Aktif yang sedang bertanding
        $timAktif = Tim::with(['lomba', 'ketua', 'bimbinganDisetujui.dosen', 'anggotaDiterima.mahasiswa'])
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
