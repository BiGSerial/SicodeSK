<?php

namespace App\Listeners;

use App\Livewire\Auth\Login;
use App\Models\SicodeUser;
use App\Models\User;
use IlluminateAuthEventsLogin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncSicodeUserOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
         $sicodeUser = $event->user; // este é o Auth::user(), vindo do Sicode

        if ($sicodeUser instanceof SicodeUser) {
            // Cria ou atualiza no banco Sicodesk
            User::updateOrCreate(
                ['sicode_id' => $sicodeUser->id],
                ['preferences' => []] // ou mantém o que já existe
            );
        }
    }
}
