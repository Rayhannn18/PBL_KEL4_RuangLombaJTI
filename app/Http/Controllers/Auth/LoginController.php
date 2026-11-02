<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $role = $request->role();
        $field = $request->credentialField();

        // Admin tidak punya NIM/NIP, jadi hanya boleh masuk dengan email.
        if ($role === UserRole::Admin && $field !== 'email') {
            $msg = 'Admin hanya dapat masuk menggunakan alamat email resmi.';
            session()->flash('error', $msg);
            throw ValidationException::withMessages([
                'login' => $msg,
            ]);
        }

        // Cek apakah akun ditemukan dengan identitas dan peran yang dipilih
        $user = Auth::getProvider()->retrieveByCredentials([
            $field => $request->input('login'),
            'role' => $role->value,
        ]);

        if (! $user) {
            // Cek apakah identitas tersebut terdaftar di peran lain
            $userAnyRole = Auth::getProvider()->retrieveByCredentials([
                $field => $request->input('login'),
                'role' => null,
            ]);

            if ($userAnyRole) {
                $roleLabel = $userAnyRole->role instanceof UserRole ? $userAnyRole->role->label() : ucfirst((string) $userAnyRole->role);
                $msg = 'Akun terdaftar sebagai peran '.$roleLabel.'. Silakan pilih tab peran '.$roleLabel.'.';
                session()->flash('error', $msg);
                throw ValidationException::withMessages([
                    'login' => $msg,
                ]);
            }

            $label = $field === 'email' ? 'Email' : ($role === UserRole::Mahasiswa ? 'Email atau NIM' : 'Email atau NIP');
            $msg = $label.' salah atau belum terdaftar.';
            session()->flash('error', $msg);
            throw ValidationException::withMessages([
                'login' => $msg,
            ]);
        }

        // Akun ditemukan, verifikasi kata sandi
        $credentials = [
            $field => $request->input('login'),
            'password' => $request->input('password'),
            'role' => $role->value,
        ];

        if (! Auth::attempt($credentials)) {
            $msg = 'Kata sandi salah. Silakan periksa kembali kata sandi Anda.';
            session()->flash('error', $msg);
            throw ValidationException::withMessages([
                'password' => $msg,
                'login' => $msg,
            ]);
        }

        $request->session()->regenerate();

        /** @var User|null $authUser */
        $authUser = Auth::user();
        if ($authUser) {
            session([
                'auth_user' => [
                    'id' => $authUser->nim_nip ?? (string) $authUser->id,
                    'nama' => $authUser->name,
                    'email' => $authUser->email,
                    'role' => $role->value,
                    'prodi' => $authUser->prodi,
                    'angkatan' => $authUser->angkatan,
                    'extra' => $authUser->prodi ? $authUser->prodi.' ('.$authUser->angkatan.')' : ($role === UserRole::Admin ? 'Kemahasiswaan JTI' : ''),
                ],
            ]);
        }

        if ($role === UserRole::Admin) {
            return redirect()->intended(route('admin.index'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->forget('auth_user');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.analitik');
    }
}
