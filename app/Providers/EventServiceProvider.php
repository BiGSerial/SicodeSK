<?php

namespace App\Providers;

use App\Listeners\SyncSicodeUserOnLogin;
use App\Livewire\Auth\Login;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
     protected $listen = [
        Login::class => [
            SyncSicodeUserOnLogin::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
