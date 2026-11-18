<?php

namespace App\Providers;

use App\Auth\MultiActorUserProvider;
use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\Tim;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('multi-actor', function ($app, array $config) {
            return new MultiActorUserProvider;
        });

        Gate::define('view-permintaan-profil', function (?User $user, AnggotaTim $permintaan): bool {
            $role = $user
                ? ($user->role instanceof UserRole ? $user->role->value : (string) $user->role)
                : (session('auth_user.role') ?? null);

            if (! in_array($role, ['mahasiswa', 'ketua_tim'], true)) {
                return false;
            }

            $nim = Tim::currentUserMahasiswaNim() ?? ($user?->nim_nip ?? session('auth_user.id'));
            if (! $nim) {
                return false;
            }

            $tim = $permintaan->relationLoaded('tim') ? $permintaan->tim : $permintaan->tim()->first();
            if (! $tim || $tim->status_tim !== 'disetujui') {
                return false;
            }

            if ((string) $tim->nim !== (string) $nim) {
                return false;
            }

            return in_array($permintaan->status_gabung, ['menunggu', 'diterima'], true);
        });

        View::composer('*', function ($view) {
            $nim = Tim::currentUserMahasiswaNim();
            $view->with('isKetuaTim', ! empty($nim) && Tim::ketuaDisetujui($nim));
        });
    }
}
