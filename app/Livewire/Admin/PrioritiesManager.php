<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Priority;
use Illuminate\Support\Str;
use Livewire\Component;

class PrioritiesManager extends Component
{
    use ChecksAdminAccess;

    public bool $showForm = false;
    public array $form = [
        'name' => '',
        'slug' => '',
        'weight' => 0,
        'color' => '#22d3ee',
        'is_default' => false,
        'active' => true,
    ];

    public ?Priority $editing = null;

    protected $rules = [
        'form.name' => 'required|string|max:120',
        'form.slug' => 'required|string|max:120|alpha_dash',
        'form.weight' => 'required|integer|min:0|max:255',
        'form.color' => ['required', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        'form.is_default' => 'boolean',
        'form.active' => 'boolean',
    ];

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function render()
    {
        return view('livewire.admin.priorities-manager', [
            'priorities' => Priority::query()->orderByDesc('weight')->orderBy('name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $priorityId): void
    {
        $priority = Priority::findOrFail($priorityId);
        $this->editing = $priority;
        $this->form = [
            'name' => $priority->name,
            'slug' => $priority->slug,
            'weight' => $priority->weight,
            'color' => $priority->color,
            'is_default' => (bool) $priority->is_default,
            'active' => (bool) $priority->active,
        ];
        $this->showForm = true;
    }

    public function updatedFormName($value): void
    {
        if (!$this->editing) {
            $this->form['slug'] = Str::slug($value);
        }
    }

    public function save(): void
    {
        $data = $this->validate();
        $payload = $data['form'];

        $payload['slug'] = Str::slug($payload['slug']);
        $payload['color'] = strtolower($payload['color']);

        $uniqueRule = Priority::query()
            ->where('slug', $payload['slug']);

        if ($this->editing) {
            $uniqueRule->whereKeyNot($this->editing->getKey());
        }

        if ($uniqueRule->exists()) {
            $this->addError('form.slug', 'Slug já está em uso.');
            return;
        }

        $priority = $this->editing ?? new Priority();
        $priority->fill($payload);
        $priority->save();

        if ($priority->is_default) {
            Priority::query()
                ->whereKeyNot($priority->getKey())
                ->update(['is_default' => false]);
        }

        $this->showForm = false;
        $this->editing = null;
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Prioridade salva',
            'toast' => true,
        ]);
    }

    public function confirmDelete(int $priorityId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover prioridade?',
            'text' => 'Esta ação não pode ser desfeita.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'deletePriority',
            'payload' => ['priority_id' => $priorityId],
            'componentId' => $this->getId(),
        ]);
    }

    public function deletePriority($payload = null): void
    {
        $priorityId = is_array($payload) ? ($payload['priority_id'] ?? null) : $payload;
        if (!$priorityId) {
            return;
        }

        $priority = Priority::find($priorityId);

        if ($priority) {
            if ($priority->is_default) {
                $this->dispatch('sweet-alert', [
                    'type' => 'error',
                    'title' => 'Não é possível remover',
                    'text' => 'Defina outra prioridade como padrão antes da remoção.',
                ]);
                return;
            }

            $priority->delete();
        }

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Prioridade removida',
            'toast' => true,
        ]);
    }

    public function toggleActive(int $priorityId): void
    {
        $priority = Priority::findOrFail($priorityId);
        $priority->active = !$priority->active;
        $priority->save();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $priority->active ? 'Prioridade ativada' : 'Prioridade desativada',
            'toast' => true,
        ]);
    }

    public function markDefault(int $priorityId): void
    {
        $priority = Priority::findOrFail($priorityId);
        $priority->is_default = true;
        $priority->save();

        Priority::query()
            ->whereKeyNot($priorityId)
            ->update(['is_default' => false]);

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Prioridade definida como padrão',
            'toast' => true,
        ]);
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->editing = null;
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->form = [
            'name' => '',
            'slug' => '',
            'weight' => 0,
            'color' => '#22d3ee',
            'is_default' => false,
            'active' => true,
        ];
    }
}
