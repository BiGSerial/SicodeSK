<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\SystemSetting;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Services\AuthorizationService;
use Livewire\Component;

class GlobalPolicies extends Component
{
    use ChecksAdminAccess;

    public array $form = [];

    public array $roleOptions = [
        AuthorizationService::ROLE_REQUESTER => 'Solicitante',
        AuthorizationService::ROLE_AGENT => 'Analista / Operador',
        AuthorizationService::ROLE_AREA_MANAGER => 'Gestor / Coordenador',
        AuthorizationService::ROLE_GLOBAL_MANAGER => 'Gestor Geral',
        AuthorizationService::ROLE_ADMIN => 'Administrador',
    ];

    public ?SystemSetting $setting = null;

    public function mount(): void
    {
        $this->ensureAdminAccess();

        $this->setting = SystemSetting::firstOrCreate(
            ['key' => 'global_policies'],
            ['value' => $this->defaults()]
        );

        $this->form = $this->mergeDefaults($this->setting->value ?? []);
    }

    public function render()
    {
        return view('livewire.admin.global-policies');
    }

    public function save(): void
    {
        $rules = [
            'form.attachments.max_size_mb' => 'required|integer|min:1|max:2048',
            'form.attachments.max_per_ticket' => 'required|integer|min:1|max:50',
            'form.attachments.allowed_extensions' => 'nullable|string',
            'form.attachments.blocked_extensions' => 'nullable|string',
            'form.permissions.ticket_creation_roles' => 'required|array|min:1',
            'form.permissions.ticket_creation_roles.*' => Rule::in(array_keys($this->roleOptions)),
            'form.permissions.comment_roles' => 'required|array|min:1',
            'form.permissions.comment_roles.*' => Rule::in(array_keys($this->roleOptions)),
            'form.notifications.sla_breach_email' => 'boolean',
            'form.notifications.sla_breach_minutes_before' => 'nullable|integer|min:1|max:600',
        ];

        $messages = [];
        $attributes = [
            'form.attachments.max_size_mb' => 'tamanho máximo',
            'form.attachments.max_per_ticket' => 'quantidade máxima de anexos',
            'form.permissions.ticket_creation_roles' => 'quem pode criar tickets',
            'form.permissions.comment_roles' => 'quem pode comentar',
            'form.notifications.sla_breach_minutes_before' => 'antecedência para aviso de SLA',
        ];

        $data = $this->validate($rules, $messages, $attributes);

        $payload = $data['form'];
        $payload['attachments']['allowed_extensions'] = $this->normalizeExtensions($payload['attachments']['allowed_extensions'] ?? '');
        $payload['attachments']['blocked_extensions'] = $this->normalizeExtensions($payload['attachments']['blocked_extensions'] ?? '');

        $payload['permissions']['ticket_creation_roles'] = $this->normalizeRoles($payload['permissions']['ticket_creation_roles'] ?? []);
        $payload['permissions']['comment_roles'] = $this->normalizeRoles($payload['permissions']['comment_roles'] ?? []);

        if (empty($payload['permissions']['ticket_creation_roles'])) {
            $this->addError('form.permissions.ticket_creation_roles', 'Selecione ao menos um perfil que pode criar tickets.');
            return;
        }

        if (empty($payload['permissions']['comment_roles'])) {
            $this->addError('form.permissions.comment_roles', 'Selecione ao menos um perfil que pode comentar.');
            return;
        }

        $payload['notifications']['sla_breach_minutes_before'] = $payload['notifications']['sla_breach_minutes_before'] ?: null;

        $this->setting->update(['value' => $payload]);
        Cache::forget('global_policies');

        $this->form = $this->mergeDefaults($payload);

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Políticas atualizadas',
            'toast' => true,
        ]);
    }

    private function defaults(): array
    {
        return [
            'attachments' => [
                'max_size_mb' => 25,
                'max_per_ticket' => 10,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'],
                'blocked_extensions' => ['exe', 'bat'],
            ],
            'permissions' => [
                'ticket_creation_roles' => [AuthorizationService::ROLE_REQUESTER, AuthorizationService::ROLE_AREA_MANAGER],
                'comment_roles' => [AuthorizationService::ROLE_REQUESTER, AuthorizationService::ROLE_AGENT, AuthorizationService::ROLE_AREA_MANAGER],
            ],
            'notifications' => [
                'sla_breach_email' => true,
                'sla_breach_minutes_before' => 30,
            ],
        ];
    }

    private function mergeDefaults(array $current): array
    {
        $defaults = $this->defaults();
        $merged = array_replace_recursive($defaults, $current);

        $merged['attachments']['allowed_extensions'] = implode(', ', $merged['attachments']['allowed_extensions'] ?? []);
        $merged['attachments']['blocked_extensions'] = implode(', ', $merged['attachments']['blocked_extensions'] ?? []);

        return $merged;
    }

    private function normalizeExtensions(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($ext) => strtolower(trim($ext)))
            ->filter(fn ($ext) => $ext !== '')
            ->unique()
            ->values()
            ->toArray();
    }

    private function normalizeRoles(array $roles): array
    {
        return collect($roles)
            ->filter(fn ($role) => array_key_exists($role, $this->roleOptions))
            ->unique()
            ->values()
            ->toArray();
    }
}
