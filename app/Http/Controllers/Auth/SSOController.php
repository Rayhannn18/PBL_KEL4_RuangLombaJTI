<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SSOController extends Controller
{
    /**
     * Tampilkan Halaman Portal SSO Politeknik Negeri Malang (SIAKAD)
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.analitik');
        }

        // Ambil beberapa akun mahasiswa untuk demo, dengan fallback aman jika database remote lambat/timeout
        $demoMahasiswa = collect();
        try {
            $demoMahasiswa = Mahasiswa::select('nim', 'nama', 'prodi')
                ->limit(4)
                ->get();
        } catch (\Throwable $e) {
            // Fallback list mahasiswa terdaftar agar halaman portal SSO tetap tampil sempurna
            $demoMahasiswa = collect([
                (object) ['nim' => '244107060116', 'nama' => 'Sastra Maheva Zaky', 'prodi' => 'D-IV Sistem Informasi Bisnis'],
                (object) ['nim' => '244107060147', 'nama' => 'Nadya Syantika Naraya', 'prodi' => 'D-IV Sistem Informasi Bisnis'],
                (object) ['nim' => '244107060083', 'nama' => 'Gempita Fitri Nurdini', 'prodi' => 'D-IV Sistem Informasi Bisnis'],
            ]);
        }

        return view('auth.sso-polinema', compact('demoMahasiswa'));
    }

    /**
     * Proses Autentikasi Kredensial SSO SIAKAD Polinema
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'nim.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'password.required' => 'Kata sandi akun SIAKAD wajib diisi.',
        ]);

        $nim = trim($validated['nim']);
        $password = $validated['password'];

        try {
            // 1. Cari data mahasiswa di tabel mahasiswa
            $mahasiswa = Mahasiswa::where('nim', $nim)->first();

            // Fallback jika akun ada di tabel users
            if (! $mahasiswa) {
                $userMhs = User::where('role', 'mahasiswa')
                    ->where(function ($q) use ($nim) {
                        $q->where('nim_nip', $nim)
                            ->orWhereRaw('LOWER(email) = ?', [strtolower($nim)]);
                    })->first();

                if ($userMhs) {
                    if (! Hash::check($password, $userMhs->password)) {
                        return back()->withInput($request->only('nim'))->withErrors([
                            'password' => 'Kata sandi SIAKAD yang Anda masukkan salah. Silakan coba lagi.',
                        ]);
                    }

                    Auth::login($userMhs);
                    $request->session()->regenerate();
                    session([
                        'auth_user' => [
                            'id' => $userMhs->nim_nip ?? (string) $userMhs->id,
                            'nama' => $userMhs->name,
                            'email' => $userMhs->email,
                            'role' => 'mahasiswa',
                            'prodi' => $userMhs->prodi ?? 'D-IV Sistem Informasi Bisnis',
                            'angkatan' => $userMhs->angkatan ?? 2024,
                            'extra' => ($userMhs->prodi ?? 'D-IV Sistem Informasi Bisnis').' ('.($userMhs->angkatan ?? 2024).')',
                            'login_via' => 'SSO SIAKAD Polinema',
                        ],
                    ]);

                    return redirect()->intended(route('dashboard'))
                        ->with('success', 'Berhasil masuk melalui SSO SIAKAD Polinema. Selamat datang, '.$userMhs->name.'!');
                }

                return back()->withInput($request->only('nim'))->withErrors([
                    'nim' => 'NIM "'.$nim.'" tidak ditemukan dalam pangkalan data SIAKAD Politeknik Negeri Malang.',
                ]);
            }

            // 2. Verifikasi kata sandi SIAKAD
            if (! Hash::check($password, $mahasiswa->password)) {
                return back()->withInput($request->only('nim'))->withErrors([
                    'password' => 'Kata sandi SIAKAD yang Anda masukkan salah. Silakan periksa kembali kata sandi akun Anda.',
                ]);
            }

            // 3. Set sesi login untuk multi-aktor & guard Laravel
            session([
                'auth_user' => [
                    'id' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'email' => $mahasiswa->email_kampus,
                    'role' => 'mahasiswa',
                    'prodi' => $mahasiswa->prodi,
                    'angkatan' => $mahasiswa->angkatan,
                    'extra' => $mahasiswa->prodi.' ('.$mahasiswa->angkatan.')',
                    'login_via' => 'SSO SIAKAD Polinema',
                ],
            ]);

            Auth::loginUsingId($mahasiswa->nim);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Berhasil masuk melalui Single Sign-On (SSO) SIAKAD Polinema. Selamat datang, '.$mahasiswa->nama.'!');
        } catch (\Throwable $e) {
            return back()->withInput($request->only('nim'))->withErrors([
                'nim' => 'Gagal terhubung ke server SIAKAD Polinema (koneksi timeout). Silakan periksa jaringan dan coba lagi.',
            ]);
        }
    }
}
