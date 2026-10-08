<?php

namespace App\Http\Controllers;

use App\Models\AnggotaTim;
use App\Models\Dosen;
use App\Models\Logbook;
use App\Models\Lomba;
use App\Models\Mahasiswa;
use App\Models\PengajuanBimbingan;
use App\Models\Tim;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BimbinganController extends Controller
{
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
        if (! session()->has('auth_user')) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Anda terlebih dahulu untuk mengakses Modul Bimbingan.');
        }

        $authUser = session('auth_user');
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
                ->with(['lomba', 'ketua', 'bimbinganDisetujui.dosen', 'pengajuanBimbingan.dosen', 'anggotaDiterima.mahasiswa'])
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

            return view('bimbingan.index', [
                'authUser' => $authUser,
                'role' => 'mahasiswa',
                'hasLomba' => true,
                'timList' => $timList,
                'selectedTim' => $selectedTim,
                'pengajuan' => $pengajuan,
                'dosenList' => $dosenList,
            ]);
        }

        if ($role === 'dosen') {
            $nidn = $authUser['id'];
            $dosen = Dosen::where('nidn', $nidn)->first();

            $pengajuanList = PengajuanBimbingan::where('nidn', $nidn)
                ->with([
                    'tim.lomba',
                    'tim.ketua',
                    'tim.anggotaDiterima.mahasiswa',
                    'logbook' => function ($q) {
                        $q->orderBy('tanggal', 'desc');
                    },
                    'progresBabak',
                ])
                ->latest()
                ->get();

            return view('bimbingan.index', [
                'authUser' => $authUser,
                'role' => 'dosen',
                'dosen' => $dosen,
                'pengajuanList' => $pengajuanList,
                'hasLomba' => true,
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

        $authUser = session('auth_user');
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa ketua/anggota tim yang dapat mengajukan bimbingan.');
        }

        $tim = Tim::findOrFail($validated['id_tim']);

        // Verifikasi bahwa user adalah ketua atau anggota tim
        $isMember = ($tim->nim === $authUser['id']) ||
            $tim->anggotaDiterima()->where('nim', $authUser['id'])->exists();

        if (! $isMember) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk tim ini.');
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

        $pengajuan = PengajuanBimbingan::with('tim')->findOrFail($validated['id_pengajuan']);

        if ($pengajuan->status !== 'disetujui') {
            return back()->with('error', 'Logbook hanya dapat ditambahkan setelah bimbingan resmi disetujui oleh Dosen Pembimbing.');
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
     * Dosen Memvalidasi Logbook (Setujui / Revisi)
     */
    public function validasiLogbook(Request $request, $id)
    {
        $validated = $request->validate([
            'status_validasi' => 'required|in:disetujui,revisi',
        ]);

        $logbook = Logbook::findOrFail($id);
        $logbook->update([
            'status_validasi' => $validated['status_validasi'],
        ]);

        $pesan = $validated['status_validasi'] === 'disetujui'
            ? 'Logbook berhasil divalidasi dan disetujui.'
            : 'Status logbook ditandai memerlukan revisi mahasiswa.';

        return back()->with('success', $pesan);
    }

    /**
     * Dosen / Admin Merespon Pengajuan Bimbingan (Setujui / Tolak)
     */
    public function responPengajuan(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pengajuan = PengajuanBimbingan::findOrFail($id);
        $pengajuan->update([
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? $pengajuan->catatan,
        ]);

        $statusTeks = $validated['status'] === 'disetujui' ? 'disetujui' : 'ditolak';

        return back()->with('success', "Pengajuan bimbingan tim {$pengajuan->tim->nama_tim} telah {$statusTeks}.");
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

        $authUser = session('auth_user');
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
