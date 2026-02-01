<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        Gate::define('capitec-admin', fn ($user) => $user->hasRole('capitec_admin'));
        Gate::define('city-reporter', fn ($user) => $user->hasRole('city_reporter'));
        Gate::define('admin', fn ($user) => $user->hasAnyRole(['capitec_admin', 'city_reporter']));
    }
}
