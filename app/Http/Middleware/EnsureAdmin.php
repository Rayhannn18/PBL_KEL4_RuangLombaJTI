<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = session('auth_user');

        if (! $authUser && Auth::check()) {
            $user = Auth::user();
            $roleVal = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
            if ($roleVal === 'admin') {
                $authUser = [
                    'id' => (int) $user->id,
                    'nama' => $user->name,
                    'email' => $user->email,
                    'role' => 'admin',
                    'extra' => 'Kemahasiswaan JTI',
                ];
                session(['auth_user' => $authUser]);
            }
        }

        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk sebagai Administrator terlebih dahulu.');
        }

        if (($authUser['role'] ?? '') !== 'admin') {
            return redirect()->route('dashboard.analitik')->with('error', 'Akses ditolak! Halaman ini hanya dapat diakses oleh Administrator Kemahasiswaan JTI.');
        }

        return $next($request);
    }
}
