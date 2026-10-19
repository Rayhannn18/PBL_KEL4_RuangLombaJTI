<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'role', 'nim_nip', 'prodi', 'angkatan', 'password']);

        if ($data['role'] === UserRole::Mahasiswa->value) {
            $mahasiswa = Mahasiswa::create([
                'nim' => $data['nim_nip'],
                'nama' => $data['name'],
                'email_kampus' => $data['email'],
                'password' => Hash::make($data['password']),
                'prodi' => $data['prodi'],
                'angkatan' => (int) $data['angkatan'],
            ]);

            session([
                'auth_user' => [
                    'id' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'email' => $mahasiswa->email_kampus,
                    'role' => 'mahasiswa',
                    'prodi' => $mahasiswa->prodi,
                    'angkatan' => $mahasiswa->angkatan,
                    'extra' => $mahasiswa->prodi.' ('.$mahasiswa->angkatan.')',
                ],
            ]);

            Auth::loginUsingId($mahasiswa->nim);
        } elseif ($data['role'] === UserRole::Dosen->value) {
            $dosen = Dosen::create([
                'nidn' => $data['nim_nip'],
                'nama' => $data['name'],
                'email_kampus' => $data['email'],
                'password' => Hash::make($data['password']),
                'bidang_keahlian' => 'Dosen JTI',
                'kuota_bimbingan' => 5,
            ]);

            session([
                'auth_user' => [
                    'id' => $dosen->nidn,
                    'nama' => $dosen->nama,
                    'email' => $dosen->email_kampus,
                    'role' => 'dosen',
                    'extra' => $dosen->bidang_keahlian,
                ],
            ]);

            Auth::loginUsingId($dosen->nidn);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
