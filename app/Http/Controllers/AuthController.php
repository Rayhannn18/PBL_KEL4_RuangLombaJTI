<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Login Mandiri
     */
    public function showLoginForm()
    {
        if (session()->has('auth_user')) {
            return redirect()->route('dashboard.analitik');
        }

        return view('auth.login');
    }

    /**
     * Tampilkan Halaman Registrasi Mahasiswa Mandiri
     */
    public function showRegisterForm()
    {
        if (session()->has('auth_user')) {
            return redirect()->route('dashboard.analitik');
        }

        return view('auth.register');
    }

    /**
     * Proses Login Multi-Aktor (Mahasiswa, Dosen, Admin)
     */
    public function login(Request $request)
    {
        $request->validate([
            'role' => 'required|in:mahasiswa,dosen,admin',
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $role = $request->role;
        $identifier = trim($request->identifier);
        $password = $request->password;

        $user = null;
        $extraInfo = '';

        if ($role === 'mahasiswa') {
            $user = Mahasiswa::where('nim', $identifier)
                ->orWhere('email_kampus', $identifier)
                ->first();
            $extraInfo = $user ? $user->prodi . ' (' . $user->angkatan . ')' : '';
        } elseif ($role === 'dosen') {
            $user = Dosen::where('nidn', $identifier)
                ->orWhere('email_kampus', $identifier)
                ->first();
            $extraInfo = $user ? $user->bidang_keahlian : '';
        } elseif ($role === 'admin') {
            $user = Admin::where('email', $identifier)->first();
            $extraInfo = 'Kemahasiswaan JTI';
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->with('error', 'Login gagal! Kredensial tidak sesuai atau akun tidak ditemukan.')->withInput();
        }

        // Simpan sesi autentikasi
        session([
            'auth_user' => [
                'id' => $role === 'admin' ? $user->id_admin : ($role === 'dosen' ? $user->nidn : $user->nim),
                'nama' => $user->nama,
                'email' => $user->email_kampus ?? $user->email,
                'role' => $role,
                'extra' => $extraInfo,
            ]
        ]);

        return redirect()->intended(route('dashboard.analitik'))->with('success', 'Selamat datang kembali, ' . $user->nama . ' (' . ucfirst($role) . ')!');
    }

    /**
     * Registrasi Akun Mahasiswa Baru
     */
    public function register(Request $request)
    {
        // [DEFECT-01 / BUG REPORT PBL]: Bug Validasi Form Registrasi
        // Aturan 'confirmed' sengaja dihilangkan dari validasi password.
        // Akibatnya, password dan konfirmasi password yang berbeda tetap lolos validasi
        // dan akun berhasil dibuat meski kedua field tidak cocok.
        $validated = $request->validate([
            'nim'          => 'required|string|max:20|unique:mahasiswa,nim',
            'nama'         => 'required|string|max:150',
            'email_kampus' => 'required|email|max:150|unique:mahasiswa,email_kampus',
            'prodi'        => 'required|string|max:100',
            'angkatan'     => 'required|integer|min:2020|max:2027',
            'password'     => 'required|string|min:6',
        ], [], [
            'nim'          => 'NIM',
            'email_kampus' => 'Email',
            'nama'         => 'Nama Lengkap',
            'prodi'        => 'Program Studi',
            'angkatan'     => 'Tahun Angkatan',
            'password'     => 'Kata Sandi',
        ]);

        $mahasiswa = Mahasiswa::create([
            'nim' => $validated['nim'],
            'nama' => $validated['nama'],
            'email_kampus' => $validated['email_kampus'],
            'password' => Hash::make($validated['password']),
            'prodi' => $validated['prodi'],
            'angkatan' => $validated['angkatan'],
        ]);

        // Langsung auto-login setelah pendaftaran
        session([
            'auth_user' => [
                'id' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'email' => $mahasiswa->email_kampus,
                'role' => 'mahasiswa',
                'extra' => $mahasiswa->prodi . ' (' . $mahasiswa->angkatan . ')',
            ]
        ]);

        return redirect()->route('dashboard.analitik')->with('success', 'Pendaftaran akun mahasiswa berhasil! Selamat bergabung, ' . $mahasiswa->nama . '.');
    }

    /**
     * Quick Login Demo (1-Klik untuk Kemudahan Sidang / Presentasi PBL)
     */
    public function quickLogin($role)
    {
        $user = null;
        $extraInfo = '';

        if ($role === 'mahasiswa') {
            $user = Mahasiswa::first();
            $extraInfo = $user ? $user->prodi . ' (' . $user->angkatan . ')' : '';
        } elseif ($role === 'dosen') {
            $user = Dosen::first();
            $extraInfo = $user ? $user->bidang_keahlian : '';
        } elseif ($role === 'admin') {
            $user = Admin::first();
            $extraInfo = 'Kemahasiswaan JTI';
        }

        if (! $user) {
            return redirect()->route('login')->with('error', 'Data demo untuk peran ini belum tersedia.');
        }

        session([
            'auth_user' => [
                'id' => $role === 'admin' ? $user->id_admin : ($role === 'dosen' ? $user->nidn : $user->nim),
                'nama' => $user->nama,
                'email' => $user->email_kampus ?? $user->email,
                'role' => $role,
                'extra' => $extraInfo,
            ]
        ]);

        return redirect()->route('dashboard.analitik')->with('success', 'Mode Demo Aktif: Masuk sebagai ' . ucfirst($role) . ' (' . $user->nama . ')');
    }

    /**
     * Logout Sesi
     */
    public function logout(Request $request)
    {
        session()->forget('auth_user');
        return redirect()->route('dashboard.analitik')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
