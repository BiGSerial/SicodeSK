<?php

namespace App\Livewire\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

trait ChecksAdminAccess
{
    protected function ensureAdminAccess(array $additionalRoles = []): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthorizationException();
        }

        if ($user->superadm ?? false) {
            return;
        }

        if (!empty($additionalRoles)) {
            foreach ($additionalRoles as $flag) {
                if (data_get($user, $flag)) {
                    return;
                }
            }
        }

        throw new AuthorizationException();
    }
}
