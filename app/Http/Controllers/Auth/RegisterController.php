<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

        // Prodi & angkatan hanya berlaku untuk mahasiswa.
        if ($data['role'] === UserRole::Dosen->value) {
            $data['prodi'] = $data['angkatan'] = null;
        }

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}