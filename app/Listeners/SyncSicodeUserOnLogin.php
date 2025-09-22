<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\SicodeUser;
use App\Models\User;
use App\Models\Role;
use App\Services\AuthorizationService;
use Illuminate\Support\Facades\Cache;

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
            $local = User::firstOrCreate(
                ['sicode_uuid' => $sicodeUser->id]
            );

            Cache::forget("user:{$sicodeUser->id}:roles");

            $roleSlug = ($sicodeUser->superadm ?? false)
                ? AuthorizationService::ROLE_ADMIN
                : AuthorizationService::ROLE_REQUESTER;

            $role = Role::where('slug', $roleSlug)->first();

            if ($role) {
                $local->roles()->syncWithoutDetaching([$role->id]);
            }

            if (!$sicodeUser->superadm && $roleSlug !== AuthorizationService::ROLE_REQUESTER) {
                $local->roles()->detach(Role::where('slug', AuthorizationService::ROLE_ADMIN)->pluck('id'));
            }
        }
    }
}
