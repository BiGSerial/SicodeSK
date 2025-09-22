<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\SicodeUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class AuthorizationService
{
    public const ROLE_REQUESTER = 'requester';
    public const ROLE_AGENT = 'agent';
    public const ROLE_AREA_MANAGER = 'area_manager';
    public const ROLE_GLOBAL_MANAGER = 'global_manager';
    public const ROLE_ADMIN = 'admin';

    public function currentUserRoles(): Collection
    {
        $sicodeUser = Auth::user();

        if (!$sicodeUser instanceof SicodeUser) {
            return collect();
        }

        return $this->rolesFor($sicodeUser->id);
    }

    public function rolesFor(string $sicodeUuid): Collection
    {
        return Cache::remember("user:{$sicodeUuid}:roles", now()->addMinutes(30), function () use ($sicodeUuid) {
            $localUser = User::with('roles')->firstOrCreate(['sicode_uuid' => $sicodeUuid]);
            return $localUser->roles->pluck('slug');
        });
    }

    public function canCreateTicket(?Collection $roles = null): bool
    {
        $roles ??= $this->currentUserRoles();

        $allowedRoles = collect($this->policies('permissions.ticket_creation_roles', [
            self::ROLE_REQUESTER,
            self::ROLE_AGENT,
            self::ROLE_AREA_MANAGER,
            self::ROLE_GLOBAL_MANAGER,
            self::ROLE_ADMIN,
        ]));

        return $roles->intersect($allowedRoles)->isNotEmpty();
    }

    public function canComment(?Collection $roles = null): bool
    {
        $roles ??= $this->currentUserRoles();

        $allowedRoles = collect($this->policies('permissions.comment_roles', [
            self::ROLE_REQUESTER,
            self::ROLE_AGENT,
            self::ROLE_AREA_MANAGER,
            self::ROLE_GLOBAL_MANAGER,
            self::ROLE_ADMIN,
        ]));

        return $roles->intersect($allowedRoles)->isNotEmpty();
    }

    public function isAgent(?Collection $roles = null): bool
    {
        $roles ??= $this->currentUserRoles();
        return $roles->contains(self::ROLE_AGENT) || $roles->contains(self::ROLE_AREA_MANAGER) || $roles->contains(self::ROLE_GLOBAL_MANAGER) || $roles->contains(self::ROLE_ADMIN);
    }

    public function isAreaManager(?Collection $roles = null): bool
    {
        $roles ??= $this->currentUserRoles();
        return $roles->contains(self::ROLE_AREA_MANAGER) || $roles->contains(self::ROLE_GLOBAL_MANAGER) || $roles->contains(self::ROLE_ADMIN);
    }

    public function isGlobalManager(?Collection $roles = null): bool
    {
        $roles ??= $this->currentUserRoles();
        return $roles->contains(self::ROLE_GLOBAL_MANAGER) || $roles->contains(self::ROLE_ADMIN);
    }

    public function isAdmin(?Collection $roles = null): bool
    {
        $roles ??= $this->currentUserRoles();
        return $roles->contains(self::ROLE_ADMIN);
    }

    private function policies(string $key, array $default = []): array
    {
        $settings = Cache::remember('global_policies', now()->addMinutes(10), function () {
            return optional(SystemSetting::query()->where('key', 'global_policies')->first())->value ?? [];
        });

        return data_get($settings, $key, $default);
    }

}
