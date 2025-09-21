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
    public bool $showDeleteConfirm = false;
    public array $form = [
        'name' => '',
        'slug' => '',
        'weight' => 0,
        'color' => '#22d3ee',
        'is_default' => false,
        'active' => true,
    ];

    public ?Priority $editing = null;
    public ?int $priorityIdBeingDeleted = null;

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
        session()->flash('status', 'Prioridade salva com sucesso.');
    }

    public function confirmDelete(int $priorityId): void
    {
        $this->priorityIdBeingDeleted = $priorityId;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        if (!$this->priorityIdBeingDeleted) {
            return;
        }

        $priority = Priority::find($this->priorityIdBeingDeleted);

        if ($priority) {
            if ($priority->is_default) {
                $this->addError('delete', 'Defina outra prioridade como padrão antes de remover esta.');
                return;
            }

            $priority->delete();
        }

        $this->showDeleteConfirm = false;
        $this->priorityIdBeingDeleted = null;
        session()->flash('status', 'Prioridade removida.');
    }

    public function toggleActive(int $priorityId): void
    {
        $priority = Priority::findOrFail($priorityId);
        $priority->active = !$priority->active;
        $priority->save();
    }

    public function markDefault(int $priorityId): void
    {
        $priority = Priority::findOrFail($priorityId);
        $priority->is_default = true;
        $priority->save();

        Priority::query()
            ->whereKeyNot($priorityId)
            ->update(['is_default' => false]);
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
