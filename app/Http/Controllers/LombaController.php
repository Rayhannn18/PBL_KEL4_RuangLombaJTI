<?php

namespace App\Http\Controllers;

use App\Models\Lomba;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

        // 5. Filter Tipe Lomba (Individu vs Tim) — dicek ketersediaan kolom skema
        $hasTipeColumn = Schema::hasColumn('lomba', 'tipe');
        $tipe = $request->input('tipe');
        if ($hasTipeColumn && $tipe && strtolower($tipe) !== 'semua') {
            $query->whereRaw('LOWER(tipe) = ?', [strtolower($tipe)]);
        }

        // 6. Filter Mode Pelaksanaan (Online, Offline, Hybrid) — dicek ketersediaan kolom skema
        $hasModeColumn = Schema::hasColumn('lomba', 'mode');
        $mode = $request->input('mode');
        if ($hasModeColumn && $mode && strtolower($mode) !== 'semua') {
            $query->whereRaw('LOWER(mode) = ?', [strtolower($mode)]);
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

        // Opsi Kategori untuk dropdown filter
        $kategoriOptions = Lomba::terverifikasi()
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->filter()
            ->values();

        // Total Lomba untuk informasi header
        $totalAktif = Lomba::terverifikasi()->where('tenggat', '>=', $today)->count();
        $totalSemua = Lomba::terverifikasi()->count();

        return view('lomba.index', [
            'lombaList' => $lombaList,
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

        $hasTipeColumn = Schema::hasColumn('lomba', 'tipe');
        $hasModeColumn = Schema::hasColumn('lomba', 'mode');

        return view('lomba.show', [
            'lomba' => $lomba,
            'timList' => $timList,
            'lombaTerkait' => $lombaTerkait,
            'hasTipeColumn' => $hasTipeColumn,
            'hasModeColumn' => $hasModeColumn,
        ]);
    }
}
