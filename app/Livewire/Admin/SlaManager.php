<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Area;
use App\Models\Category;
use App\Models\Priority;
use App\Models\SlaRule;
use App\Models\Subcategory;
use App\Models\TicketType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SlaManager extends Component
{
    use ChecksAdminAccess;

    public bool $showForm = false;

    public array $form = [
        'priority_id' => null,
        'area_id' => null,
        'ticket_type_id' => null,
        'category_id' => null,
        'subcategory_id' => null,
        'increment_minutes' => 0,
        'tolerance_minutes' => 0,
        'pause_suspends' => false,
        'active' => true,
        'notes' => '',
    ];

    public ?SlaRule $editing = null;

    public array $types = [];
    public array $categories = [];
    public array $subcategories = [];

    protected $listeners = [
        'admin-tab-changed' => 'handleAdminTabChanged',
        'catalog-data-updated' => 'refreshLookups',
    ];

    protected $rules = [
        'form.priority_id' => 'required|exists:priorities,id',
        'form.area_id' => 'nullable|exists:areas,id',
        'form.ticket_type_id' => 'nullable|exists:ticket_types,id',
        'form.category_id' => 'nullable|exists:categories,id',
        'form.subcategory_id' => 'nullable|exists:subcategories,id',
        'form.increment_minutes' => 'required|integer|min:0',
        'form.tolerance_minutes' => 'required|integer|min:0',
        'form.pause_suspends' => 'boolean',
        'form.active' => 'boolean',
        'form.notes' => 'nullable|string|max:500',
    ];

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function render()
    {
        return view('livewire.admin.sla-manager', [
            'rules' => SlaRule::query()
                ->with(['priority:id,name,slug', 'area:id,name', 'type:id,name', 'category:id,name', 'subcategory:id,name'])
                ->orderByDesc('active')
                ->orderBy('priority_id')
                ->get(),
            'priorities' => Priority::query()->orderByDesc('weight')->orderBy('name')->get(['id','name','slug'])->toArray(),
            'areas' => Area::query()->orderBy('name')->get(['id','name'])->toArray(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $ruleId): void
    {
        $rule = SlaRule::findOrFail($ruleId);
        $this->editing = $rule;
        $this->form = Arr::only($rule->toArray(), array_keys($this->form));
        $this->setupRelatedOptions();
        $this->showForm = true;
    }

    public function updatedFormAreaId($value): void
    {
        $this->form['area_id'] = $value ?: null;
        $this->form['ticket_type_id'] = null;
        $this->form['category_id'] = null;
        $this->form['subcategory_id'] = null;
        $this->setupRelatedOptions();
    }

    public function updatedFormTicketTypeId($value): void
    {
        $this->form['ticket_type_id'] = $value ?: null;
        $this->setupCategories();
        $this->form['category_id'] = null;
        $this->form['subcategory_id'] = null;
    }

    public function updatedFormCategoryId($value): void
    {
        $this->form['category_id'] = $value ?: null;
        $this->setupSubcategories();
        $this->form['subcategory_id'] = null;
    }

    public function save(): void
    {
        $validated = $this->validate();
        $payload = $validated['form'];

        if ($this->hasDuplicatedScope($payload)) {
            $this->addError('form.priority_id', 'Já existe uma regra com esta combinação de filtros.');
            return;
        }

        if ($this->editing) {
            $this->editing->update($payload);
        } else {
            $this->editing = SlaRule::create($payload);
        }

        Cache::tags('sla_rules')->flush();

        $this->showForm = false;
        $this->editing = null;
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Regra de SLA salva',
            'toast' => true,
        ]);
    }

    public function confirmDelete(int $ruleId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover regra de SLA?',
            'text' => 'Esta ação não pode ser desfeita.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'deleteRule',
            'payload' => ['rule_id' => $ruleId],
            'componentId' => $this->getId(),
        ]);
    }

    public function deleteRule($payload = null): void
    {
        $ruleId = is_array($payload) ? ($payload['rule_id'] ?? null) : $payload;
        if (!$ruleId) {
            return;
        }

        SlaRule::query()->whereKey($ruleId)->delete();
        Cache::tags('sla_rules')->flush();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Regra removida',
            'toast' => true,
        ]);
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->editing = null;
    }

    public function handleAdminTabChanged($payload = null): void
    {
        $tab = is_array($payload) ? ($payload['tab'] ?? null) : $payload;

        if ($tab !== 'sla') {
            return;
        }

        $this->refreshLookups();
    }

    public function refreshLookups(): void
    {
        $this->setupRelatedOptions();
        $this->dispatch('$refresh');
    }

    public function toggleActive(int $ruleId): void
    {
        $rule = SlaRule::findOrFail($ruleId);
        $rule->active = ! $rule->active;
        $rule->save();

        Cache::tags('sla_rules')->flush();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $rule->active ? 'Regra ativada' : 'Regra desativada',
            'toast' => true,
        ]);
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->form = [
            'priority_id' => null,
            'area_id' => null,
            'ticket_type_id' => null,
            'category_id' => null,
            'subcategory_id' => null,
            'increment_minutes' => 0,
            'tolerance_minutes' => 0,
            'pause_suspends' => false,
            'active' => true,
            'notes' => '',
        ];
        $this->types = [];
        $this->categories = [];
        $this->subcategories = [];
    }

    private function setupRelatedOptions(): void
    {
        $this->setupTypes();
        $this->setupCategories();
        $this->setupSubcategories();
    }

    private function setupTypes(): void
    {
        if ($this->form['area_id']) {
            $this->types = TicketType::query()
                ->where('active', true)
                ->where('area_id', $this->form['area_id'])
                ->orderBy('name')
                ->get(['id','name'])
                ->toArray();
        } else {
            $this->types = [];
        }
    }

    private function setupCategories(): void
    {
        if ($this->form['area_id']) {
            $this->categories = Category::query()
                ->where('active', true)
                ->where('area_id', $this->form['area_id'])
                ->orderBy('name')
                ->get(['id','name'])
                ->toArray();
        } else {
            $this->categories = [];
        }
    }

    private function setupSubcategories(): void
    {
        if ($this->form['category_id']) {
            $this->subcategories = Subcategory::query()
                ->where('active', true)
                ->where('category_id', $this->form['category_id'])
                ->orderBy('name')
                ->get(['id','name'])
                ->toArray();
        } else {
            $this->subcategories = [];
        }
    }

    private function hasDuplicatedScope(array $payload): bool
    {
        $query = SlaRule::query()
            ->where('priority_id', $payload['priority_id']);

        $scopeFields = ['area_id', 'ticket_type_id', 'category_id', 'subcategory_id'];

        foreach ($scopeFields as $field) {
            $value = $payload[$field] ?? null;

            $query->when(
                filled($value),
                fn ($q) => $q->where($field, $value),
                fn ($q) => $q->whereNull($field)
            );
        }

        if ($this->editing) {
            $query->whereKeyNot($this->editing->getKey());
        }

        return $query->exists();
    }
}
