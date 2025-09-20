<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter; // <-- IMPORT CORRETO
use Illuminate\Http\Request;

class RateLimiterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Limite nomeado "login": 5 tentativas por minuto por e-mail + IP
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by($email.$request->ip()),
            ];
        });
    }
}
