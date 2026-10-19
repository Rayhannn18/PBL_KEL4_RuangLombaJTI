<?php

namespace App\Providers;

use App\Auth\MultiActorUserProvider;
use Illuminate\Support\Facades\Auth;
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
    }
}
