<?php

namespace App\Providers;

use App\Auth\MultiActorUserProvider;
use App\Models\Tim;
use Illuminate\Support\Facades\Auth;
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

        View::composer('*', function ($view) {
            $nim = Tim::currentUserMahasiswaNim();
            $view->with('isKetuaTim', ! empty($nim) && Tim::ketuaDisetujui($nim));
        });
    }
}
