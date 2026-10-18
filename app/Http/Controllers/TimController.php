<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\Lomba;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TimController extends Controller
{
    /**
     * Ambil identitas pengguna login saat ini (mahasiswa / dosen / admin).
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
     * Papan Lowongan Tim (Eksplorasi Tim yang Membuka Lowongan Anggota)
     * Alur Anggota Tim: Melihat lowongan, kuota terisi/sisa, dan mengajukan diri.
     */
    public function lowongan(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Anda terlebih dahulu untuk melihat Papan Lowongan Tim.');
        }

        $query = Tim::where('status_tim', 'disetujui')
            ->with(['lomba', 'ketua', 'anggotaDiterima.mahasiswa', 'anggota.mahasiswa'])
            ->latest();

        // Pencarian berdasarkan nama tim atau nama kompetisi
        if ($request->filled('q')) {
            $keyword = trim($request->query('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_tim', 'like', "%{$keyword}%")
                    ->orWhereHas('lomba', function ($lq) use ($keyword) {
                        $lq->where('nama_lomba', 'like', "%{$keyword}%");
                    });
            });
        }

        // Filter tingkat lomba
        if ($request->filled('tingkat')) {
            $tingkat = $request->query('tingkat');
            $query->whereHas('lomba', function ($lq) use ($tingkat) {
                $lq->where('tingkat', $tingkat);
            });
        }

        // Filter kategori lomba
        if ($request->filled('kategori')) {
            $kategori = $request->query('kategori');
            $query->whereHas('lomba', function ($lq) use ($kategori) {
                $lq->where('kategori', $kategori);
            });
        }

        $allTeams = $query->get();

        // Hanya tampilkan tim yang masih membuka lowongan (sisa kuota anggota > 0) secara default
        $filterStatus = $request->query('status_kuota', 'terbuka');
        if ($filterStatus === 'terbuka') {
            $teams = $allTeams->filter(fn ($t) => $t->sisa_kuota_anggota > 0);
        } else {
            $teams = $allTeams;
        }

        // Kategori dan Tingkat untuk filter dropdown
        $kategoriList = Lomba::distinct()->whereNotNull('kategori')->pluck('kategori');
        $tingkatList = ['Nasional', 'Internasional', 'Regional', 'Kampus'];

        return view('tim.lowongan', [
            'authUser' => $authUser,
            'teams' => $teams,
            'totalLowongan' => $allTeams->filter(fn ($t) => $t->sisa_kuota_anggota > 0)->count(),
            'kategoriList' => $kategoriList,
            'tingkatList' => $tingkatList,
            'currentSearch' => $request->query('q', ''),
            'currentTingkat' => $request->query('tingkat', ''),
            'currentKategori' => $request->query('kategori', ''),
            'currentStatusKuota' => $filterStatus,
        ]);
    }

    /**
     * Formulir Pembuatan Tim Baru (Khusus Calon Ketua Tim)
     */
    public function create(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk untuk membentuk tim baru.');
        }

        if ($authUser['role'] !== 'mahasiswa') {
            return redirect()->route('dashboard.analitik')->with('error', 'Hanya mahasiswa yang dapat membentuk tim kompetisi.');
        }

        $lombaTersedia = Lomba::terverifikasi()
            ->orderBy('nama_lomba', 'asc')
            ->get();

        $selectedLombaId = $request->query('lomba_id');

        return view('tim.create', [
            'authUser' => $authUser,
            'lombaTersedia' => $lombaTersedia,
            'selectedLombaId' => $selectedLombaId,
        ]);
    }

    /**
     * Simpan Pembuatan Tim Baru oleh Ketua Tim
     * Wajib mengisi: Nama Tim, Lomba yang Ingin Diikuti, Kuota Anggota Tim.
     */
    public function store(Request $request)
    {
        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return redirect()->route('login')->with('error', 'Hanya mahasiswa yang dapat membentuk tim kompetisi.');
        }

        $validated = $request->validate([
            'nama_tim' => 'required|string|max:150',
            'id_lomba' => 'required|exists:lomba,id_lomba',
            'kuota_anggota' => 'required|integer|min:2|max:5',
            'catatan' => 'nullable|string|max:500',
        ], [
            'nama_tim.required' => 'Nama tim wajib diisi oleh calon Ketua Tim.',
            'id_lomba.required' => 'Pilih salah satu kompetisi yang ingin diikuti.',
            'kuota_anggota.required' => 'Kuota anggota tim wajib ditentukan.',
            'kuota_anggota.min' => 'Kuota tim minimal 2 orang (termasuk Ketua Tim).',
            'kuota_anggota.max' => 'Kuota tim maksimal 5 orang.',
        ]);

        $lomba = Lomba::findOrFail($validated['id_lomba']);

        // Buat data tim dengan mahasiswa login sebagai ketua secara atomik
        $tim = DB::transaction(function () use ($validated, $lomba, $authUser) {
            $tim = Tim::create([
                'nama_tim' => $validated['nama_tim'],
                'id_lomba' => $lomba->id_lomba,
                'nim' => $authUser['id'],
                'kuota_anggota' => (int) $validated['kuota_anggota'],
                'status_tim' => 'disetujui',
            ]);

            // Ketua tim otomatis terdaftar sebagai anggota resmi pertama
            AnggotaTim::create([
                'id_tim' => $tim->id_tim,
                'nim' => $authUser['id'],
                'status_gabung' => 'diterima',
                'peran' => 'Ketua Tim',
            ]);

            return $tim;
        });

        Cache::flush();

        return redirect()->route('bimbingan.index', ['tim_id' => $tim->id_tim])
            ->with('success', "Selamat! Tim '{$tim->nama_tim}' berhasil dibentuk untuk ajang '{$lomba->nama_lomba}'. Anda kini aktif sebagai Ketua Tim.");
    }

    /**
     * Alur Anggota Tim: Mengajukan Diri Bergabung ke Tim yang Membuka Lowongan
     * Form Anggota Tim HANYA meminta peran / keahlian (TIDAK MEMINTA Nama Tim / Kuota Anggota).
     */
    public function gabung(Request $request, $id)
    {
        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa yang dapat mengajukan diri bergabung ke dalam tim.');
        }

        $validated = $request->validate([
            'peran' => 'required|string|max:100',
            'catatan' => 'nullable|string|max:500',
        ], [
            'peran.required' => 'Silakan tentukan peran / bidang keahlian yang Anda tawarkan ke tim.',
        ]);

        $tim = Tim::with(['anggotaDiterima', 'anggota'])->findOrFail($id);
        $nim = $authUser['id'];

        // Cek 1: Mahasiswa tidak boleh mendaftar ke tim yang diketuainya sendiri
        if ($tim->nim === $nim) {
            return back()->with('error', 'Anda adalah Ketua Tim dari tim ini.');
        }

        // Cek 2: Mahasiswa sudah menjadi anggota resmi yang diterima
        if ($tim->anggotaDiterima->contains('nim', $nim)) {
            return back()->with('error', 'Anda sudah terdaftar sebagai anggota resmi tim ini.');
        }

        // Cek 3: Periksa riwayat pengajuan sebelumnya
        $existing = $tim->anggota()->where('nim', $nim)->first();

        if ($existing) {
            if ($existing->status_gabung === 'menunggu') {
                return back()->with('error', 'Pengajuan bergabung Anda sebelumnya sedang menunggu persetujuan Ketua Tim.');
            }
            if ($existing->status_gabung === 'diterima') {
                return back()->with('error', 'Anda sudah terdaftar sebagai anggota resmi tim ini.');
            }
        }

        // Cek 4: Kuota tim masih tersedia
        if ($tim->sisa_kuota_anggota <= 0) {
            return back()->with('error', 'Maaf, kuota anggota untuk tim ini sudah penuh.');
        }

        // Simpan pengajuan anggota: jika sebelumnya ditolak, reset status jadi 'menunggu'
        if ($existing) {
            $existing->update([
                'status_gabung' => 'menunggu',
                'peran' => $validated['peran'],
            ]);
        } else {
            AnggotaTim::create([
                'id_tim' => $tim->id_tim,
                'nim' => $nim,
                'status_gabung' => 'menunggu',
                'peran' => $validated['peran'],
            ]);
        }

        return back()->with('success', "Pengajuan diri bergabung ke tim '{$tim->nama_tim}' sebagai {$validated['peran']} berhasil dikirim! Menunggu persetujuan Ketua Tim.");
    }

    /**
     * Ketua Tim Merespon Pengajuan Anggota (Setujui / Tolak)
     */
    public function responAnggota(Request $request, $id)
    {
        $authUser = $this->authUser();
        if (! $authUser || $authUser['role'] !== 'mahasiswa') {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:diterima,ditolak',
        ]);

        $anggota = AnggotaTim::with(['tim', 'mahasiswa'])->findOrFail($id);

        // Hanya ketua tim yang bersangkutan yang dapat memvalidasi pengajuan
        if ($anggota->tim->nim !== $authUser['id']) {
            return back()->with('error', 'Hanya Ketua Tim yang berhak menyetujui atau menolak calon anggota.');
        }

        if ($validated['status'] === 'diterima') {
            // Cek apakah kuota masih cukup
            if ($anggota->tim->sisa_kuota_anggota <= 0) {
                return back()->with('error', "Kuota anggota tim sudah penuh ({$anggota->tim->kuota_anggota} anggota). Tidak dapat menerima anggota lagi.");
            }

            $anggota->setujuiAnggota();
            $namaMhs = $anggota->mahasiswa->nama ?? "NIM {$anggota->nim}";

            return back()->with('success', "Mahasiswa {$namaMhs} telah disetujui bergabung ke dalam tim sebagai {$anggota->peran}.");
        }

        $anggota->tolakAnggota();

        return back()->with('success', 'Pengajuan bergabung anggota telah ditolak.');
    }
}
