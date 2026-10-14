<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = session('auth_user');

        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk sebagai Administrator terlebih dahulu.');
        }

        if (($authUser['role'] ?? '') !== 'admin') {
            return redirect()->route('dashboard.analitik')->with('error', 'Akses ditolak! Halaman ini hanya dapat diakses oleh Administrator Kemahasiswaan JTI.');
        }

        return $next($request);
    }
}
