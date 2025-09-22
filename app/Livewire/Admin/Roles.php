<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Role;
use App\Models\SicodeUser;
use App\Models\User;
use App\Services\AuthorizationService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Roles extends Component
{
    use ChecksAdminAccess;

    public string $search = '';
    public array $searchResults = [];
    public ?array $selectedUser = null;
    public array $formRoles = [];

    /** @var array<string,string> */
    public array $roleLabels = [
        AuthorizationService::ROLE_REQUESTER => 'Solicitante',
        AuthorizationService::ROLE_AGENT => 'Agente',
        AuthorizationService::ROLE_AREA_MANAGER => 'Gestor de área',
        AuthorizationService::ROLE_GLOBAL_MANAGER => 'Gestor geral',
        AuthorizationService::ROLE_ADMIN => 'Administrador',
    ];

    public function mount(): void
    {
        $this->ensureAdminAccess();
        $this->resetState();
    }

    public function updatedSearch(): void
    {
        $this->searchUsers();
    }

    public function selectUser(string $sicodeId): void
    {
        $user = SicodeUser::query()
            ->select(['id', 'name', 'email', 'superadm'])
            ->find($sicodeId);

        if (!$user) {
            $this->dispatch('sweet-alert', [
                'type' => 'error',
                'title' => 'Usuário não encontrado no SICODE.',
                'toast' => true,
            ]);
            return;
        }

        $local = User::firstOrCreate(['sicode_uuid' => $user->id]);
        $local->load('roles');

        if (!$local->roles->contains('slug', AuthorizationService::ROLE_REQUESTER)) {
            $role = Role::where('slug', AuthorizationService::ROLE_REQUESTER)->first();
            if ($role) {
                $local->roles()->attach($role->id);
            }
        }

        $this->selectedUser = [
            'sicode_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'superadm' => (bool) ($user->superadm ?? false),
            'local_id' => $local->id,
        ];

        $this->formRoles = collect($this->roleLabels)
            ->mapWithKeys(fn ($label, $slug) => [$slug => $local->roles->contains('slug', $slug)])
            ->toArray();

        $this->searchResults = [];
        $this->search = $user->name;

        if ($this->selectedUser['superadm']) {
            $this->formRoles[AuthorizationService::ROLE_ADMIN] = true;
        }

        $this->dispatch('sweet-alert', [
            'type' => 'info',
            'title' => 'Usuário selecionado.',
            'toast' => true,
        ]);
    }

    public function toggleRole(string $slug): void
    {
        if (!$this->selectedUser) {
            return;
        }

        if (!array_key_exists($slug, $this->roleLabels)) {
            return;
        }

        if ($slug === AuthorizationService::ROLE_REQUESTER) {
            $this->formRoles[$slug] = true;
            return;
        }

        if ($slug === AuthorizationService::ROLE_ADMIN && ($this->selectedUser['superadm'] ?? false)) {
            $this->formRoles[$slug] = true;
            return;
        }

        $this->formRoles[$slug] = !($this->formRoles[$slug] ?? false);
    }

    public function saveRoles(): void
    {
        if (!$this->selectedUser) {
            $this->addError('selectedUser', 'Selecione um usuário.');
            return;
        }

        $local = User::with('roles')->find($this->selectedUser['local_id']);

        if (!$local) {
            $this->addError('selectedUser', 'Não foi possível carregar o usuário local.');
            return;
        }

        $payload = collect($this->formRoles)
            ->filter(fn ($enabled, $slug) => $enabled && $slug !== AuthorizationService::ROLE_REQUESTER)
            ->keys()
            ->toArray();

        $payload[] = AuthorizationService::ROLE_REQUESTER;

        if ($this->selectedUser['superadm']) {
            $payload[] = AuthorizationService::ROLE_ADMIN;
        } elseif ($this->formRoles[AuthorizationService::ROLE_ADMIN] ?? false) {
            $payload[] = AuthorizationService::ROLE_ADMIN;
        }

        $payload = collect($payload)->unique()->values()->all();

        $roles = Role::whereIn('slug', $payload)->pluck('id', 'slug');

        if ($roles->isEmpty()) {
            $this->addError('roles', 'Selecione ao menos um papel para salvar.');
            return;
        }

        $local->roles()->sync($roles->values()->all());
        Cache::forget("user:{$this->selectedUser['sicode_id']}:roles");

        $this->formRoles = collect($this->roleLabels)
            ->mapWithKeys(fn ($label, $slug) => [$slug => in_array($slug, $payload, true)])
            ->toArray();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Perfis atualizados com sucesso.',
            'toast' => true,
        ]);
    }

    public function clearSelection(): void
    {
        $this->selectedUser = null;
        $this->formRoles = collect($this->roleLabels)
            ->mapWithKeys(fn ($label, $slug) => [$slug => $slug === AuthorizationService::ROLE_REQUESTER])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.roles');
    }

    private function searchUsers(): void
    {
        $term = trim($this->search);

        if ($term === '') {
            $this->searchResults = [];
            return;
        }

        $this->validate([ 'search' => ['string', 'max:120'] ], [], ['search' => 'busca']);

        $wild = '%' . str_replace(' ', '%', $term) . '%';

        $this->searchResults = SicodeUser::query()
            ->select(['id', 'name', 'email', 'superadm'])
            ->where(function ($query) use ($wild) {
                $query->where('name', 'like', $wild)
                    ->orWhere('email', 'like', $wild);
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'superadm' => (bool) ($user->superadm ?? false),
            ])
            ->toArray();
    }

    private function resetState(): void
    {
        $this->selectedUser = null;
        $this->searchResults = [];
        $this->formRoles = collect($this->roleLabels)
            ->mapWithKeys(fn ($label, $slug) => [$slug => $slug === AuthorizationService::ROLE_REQUESTER])
            ->toArray();
    }
}
