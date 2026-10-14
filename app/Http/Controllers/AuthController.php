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
            'role' => 'nullable|string|in:mahasiswa,dosen,admin',
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $role = $request->role;
        $identifier = trim($request->identifier);
        $password = $request->password;

        $user = null;
        $detectedRole = $role;
        $extraInfo = '';

        // 1. Coba cari akun berdasarkan role yang dipilih
        if ($role === 'admin') {
            $user = Admin::where('email', $identifier)->first();
            $extraInfo = 'Kemahasiswaan JTI';
        } elseif ($role === 'dosen') {
            $user = Dosen::where('nidn', $identifier)
                ->orWhere('email_kampus', $identifier)
                ->first();
            $extraInfo = $user ? $user->bidang_keahlian : '';
        } elseif ($role === 'mahasiswa') {
            $user = Mahasiswa::where('nim', $identifier)
                ->orWhere('email_kampus', $identifier)
                ->first();
            $extraInfo = $user ? $user->prodi.' ('.$user->angkatan.')' : '';
        }

        // 2. Jika tidak ditemukan di role terpilih, cari secara otomatis ke seluruh aktor
        // (Mencegah kegagalan jika pengguna mengetik email admin/dosen/mahasiswa tanpa berpindah tab)
        if (! $user) {
            $adminCheck = Admin::where('email', $identifier)->first();
            if ($adminCheck) {
                $user = $adminCheck;
                $detectedRole = 'admin';
                $extraInfo = 'Kemahasiswaan JTI';
            }
        }

        if (! $user) {
            $dosenCheck = Dosen::where('email_kampus', $identifier)
                ->orWhere('nidn', $identifier)
                ->first();
            if ($dosenCheck) {
                $user = $dosenCheck;
                $detectedRole = 'dosen';
                $extraInfo = $dosenCheck->bidang_keahlian;
            }
        }

        if (! $user) {
            $mhsCheck = Mahasiswa::where('email_kampus', $identifier)
                ->orWhere('nim', $identifier)
                ->first();
            if ($mhsCheck) {
                $user = $mhsCheck;
                $detectedRole = 'mahasiswa';
                $extraInfo = $mhsCheck->prodi.' ('.$mhsCheck->angkatan.')';
            }
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->with('error', 'Login gagal! Email/NIM/NIDN atau kata sandi tidak sesuai.')->withInput();
        }

        // Simpan sesi autentikasi
        session([
            'auth_user' => [
                'id' => $detectedRole === 'admin' ? $user->id_admin : ($detectedRole === 'dosen' ? $user->nidn : $user->nim),
                'nama' => $user->nama,
                'email' => $user->email_kampus ?? $user->email,
                'role' => $detectedRole,
                'extra' => $extraInfo,
            ],
        ]);

        $targetRoute = $detectedRole === 'admin' ? route('admin.index') : route('dashboard.analitik');

        return redirect()->intended($targetRoute)->with('success', 'Selamat datang kembali, '.$user->nama.' ('.ucfirst($detectedRole).')!');
    }

    /**
     * Registrasi Akun Mahasiswa Baru
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:mahasiswa,nim',
            'nama' => 'required|string|max:150',
            'email_kampus' => 'required|email|max:150|unique:mahasiswa,email_kampus',
            'prodi' => 'required|string|max:100',
            'angkatan' => 'required|integer|min:2020|max:2027',
            // [DEFECT-01 / BUG REPORT PBL]: Validasi 'confirmed' dihilangkan sengaja
            // Menyebabkan password dan konfirmasi password yang tidak cocok tetap lolos validasi
            'password' => 'required|string|min:6',
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
                'extra' => $mahasiswa->prodi.' ('.$mahasiswa->angkatan.')',
            ],
        ]);

        return redirect()->route('dashboard.analitik')->with('success', 'Pendaftaran akun mahasiswa berhasil! Selamat bergabung, '.$mahasiswa->nama.'.');
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
