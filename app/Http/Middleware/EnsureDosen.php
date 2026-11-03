<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Dosen;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDosen
{
    /**
     * Handle an incoming request.
     * Memastikan pengguna telah masuk dan memiliki peran Dosen Pembimbing (atau Admin).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = session('auth_user');

        if (! $authUser && Auth::check()) {
            $user = Auth::user();
            $roleVal = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

            if (in_array($roleVal, ['dosen', 'admin'])) {
                $nidn = $user->nim_nip ?? (string) $user->id;
                $extra = 'Dosen Pembimbing';

                if ($roleVal === 'dosen') {
                    $dosen = Dosen::where('nidn', $nidn)->first();
                    $extra = $dosen->bidang_keahlian ?? 'Dosen Pembimbing';
                } else {
                    $extra = 'Kemahasiswaan JTI';
                }

                $authUser = [
                    'id' => $nidn,
                    'nama' => $user->name,
                    'email' => $user->email,
                    'role' => $roleVal,
                    'extra' => $extra,
                ];
                session(['auth_user' => $authUser]);
            }
        }

        if (! $authUser) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Dosen Pembimbing terlebih dahulu.');
        }

        if (! in_array($authUser['role'] ?? '', ['dosen', 'admin'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak! Tindakan ini khusus untuk Dosen Pembimbing.');
        }

        return $next($request);
    }
}
