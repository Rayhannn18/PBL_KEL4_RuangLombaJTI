<?php

namespace App\Providers;

use App\Auth\MultiActorUserProvider;
use App\Enums\UserRole;
use App\Models\AnggotaTim;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;
use App\Policies\ProgresBabakPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Auth::provider('multi-actor', function ($app, array $config) {
            return new MultiActorUserProvider;
        });

        Gate::policy(ProgresBabak::class, ProgresBabakPolicy::class);
        Gate::define('create-progres-babak', [ProgresBabakPolicy::class, 'create']);
        Gate::define('update-progres-babak', [ProgresBabakPolicy::class, 'update']);
        Gate::define('respond-progres-babak', [ProgresBabakPolicy::class, 'respond']);
        Gate::define('view-progres-bukti', [ProgresBabakPolicy::class, 'viewBukti']);

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
