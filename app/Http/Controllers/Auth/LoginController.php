<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
            throw ValidationException::withMessages([
                'login' => 'Admin masuk menggunakan email.',
            ]);
        }

        // 'role' ikut dicocokkan: akun dosen tidak bisa masuk lewat pilihan Mahasiswa, dan sebaliknya.
        $credentials = [
            $field => $request->input('login'),
            'password' => $request->input('password'),
            'role' => $role->value,
        ];

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => 'Akun tidak ditemukan untuk peran '.$role->label().', atau kata sandi salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}