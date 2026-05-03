<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        /**
         * Directive Blade @auth_pseudo / @endauth_pseudo
         * Vérifie si un joueur est connecté (a un player_id en session).
         */
        Blade::if('auth_pseudo', function () {
            return session()->has('player_id');
        });
    }
}