<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Area;
use App\Models\Category;
use App\Models\TicketType;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Workflows extends Component
{
    use ChecksAdminAccess;

    public bool $showForm = false;

    public array $workflowForm = [
        'area_id' => null,
        'name' => '',
        'active' => true,
        'ticket_type_id' => null,
        'category_id' => null,
        'steps' => [],
    ];

    public ?Workflow $editing = null;

    public array $areas = [];
    public array $types = [];
    public array $categories = [];
    public array $assignRuleOptions = [
        'manual' => 'Definição manual',
        'manager' => 'Gestor da área',
        'round_robin' => 'Distribuição autom. (round robin)',
    ];

    protected $listeners = [
        'admin-tab-changed' => 'handleTabChanged',
    ];

    public function mount(): void
    {
        $this->ensureAdminAccess();
        $this->areas = Area::query()->orderBy('name')->get(['id', 'name'])->toArray();
        $this->loadTypes();
        $this->loadCategories();
    }

    public function render()
    {
        return view('livewire.admin.workflows', [
            'workflows' => Workflow::query()
                ->with([
                    'area:id,name',
                    'ticketType:id,name',
                    'category:id,name',
                    'steps' => fn ($q) => $q->orderBy('order'),
                ])
                ->orderBy('name')
                ->get(),
            'areas' => $this->areas,
            'types' => $this->types,
            'categories' => $this->categories,
            'assignRules' => $this->assignRuleOptions,
        ]);
    }

    public function handleTabChanged($payload = null): void
    {
        $tab = is_array($payload) ? ($payload['tab'] ?? null) : $payload;

        if ($tab !== 'workflows') {
            return;
        }

        $this->areas = Area::query()->orderBy('name')->get(['id', 'name'])->toArray();
        $this->loadTypes();
        $this->loadCategories();
        $this->dispatch('$refresh');
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $workflowId): void
    {
        $workflow = Workflow::with('steps')->findOrFail($workflowId);
        $this->editing = $workflow;

        $this->workflowForm = [
            'area_id' => $workflow->area_id,
            'name' => $workflow->name,
            'active' => (bool) $workflow->active,
            'ticket_type_id' => $workflow->ticket_type_id,
            'category_id' => $workflow->category_id,
            'steps' => $workflow->steps
                ->sortBy('order')
                ->values()
                ->map(fn (WorkflowStep $step) => [
                    'key' => 'existing-'.$step->id,
                    'id' => $step->id,
                    'name' => $step->name,
                    'assign_rule' => $step->assign_rule,
                    'sla_target_minutes' => $step->sla_target_minutes,
                ])->toArray(),
        ];

        $this->loadTypes();
        $this->loadCategories();

        if (empty($this->workflowForm['steps'])) {
            $this->addStepRow();
        }

        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function addStep(): void
    {
        $this->addStepRow();
    }

    public function removeStep(string $key): void
    {
        $steps = collect($this->workflowForm['steps']);
        if ($steps->count() <= 1) {
            $this->dispatch('sweet-alert', [
                'type' => 'warning',
                'title' => 'Mínimo de uma etapa',
                'text' => 'O workflow precisa ter ao menos uma etapa.',
                'toast' => true,
            ]);
            return;
        }

        $this->workflowForm['steps'] = $steps->reject(fn ($step) => $step['key'] === $key)->values()->toArray();
    }

    public function moveStep(string $key, string $direction): void
    {
        $steps = collect($this->workflowForm['steps']);
        $index = $steps->search(fn ($step) => $step['key'] === $key);

        if ($index === false) {
            return;
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWith < 0 || $swapWith >= $steps->count()) {
            return;
        }

        $steps = $steps->values();
        $temp = $steps[$index];
        $steps[$index] = $steps[$swapWith];
        $steps[$swapWith] = $temp;

        $this->workflowForm['steps'] = $steps->toArray();
    }

    public function save(): void
    {
        $data = $this->validate([
            'workflowForm.area_id' => 'required|exists:areas,id',
            'workflowForm.name' => 'required|string|max:120',
            'workflowForm.active' => 'boolean',
            'workflowForm.ticket_type_id' => 'nullable|exists:ticket_types,id',
            'workflowForm.category_id' => 'nullable|exists:categories,id',
        ], [], [
            'workflowForm.area_id' => 'área',
            'workflowForm.name' => 'nome',
            'workflowForm.ticket_type_id' => 'tipo de ticket',
            'workflowForm.category_id' => 'categoria',
        ]);

        $steps = $this->workflowForm['steps'] ?? [];

        if (empty($steps)) {
            $this->addError('workflowForm.steps', 'Adicione ao menos uma etapa.');
            return;
        }

        $preparedSteps = [];
        $validAssignRules = array_keys($this->assignRuleOptions);

        foreach ($steps as $index => $step) {
            $name = trim($step['name'] ?? '');

            if ($name === '') {
                $this->addError("workflowForm.steps.$index.name", 'Informe o nome da etapa.');
            }

            $assignRule = $step['assign_rule'] ?? 'manual';
            if (!in_array($assignRule, $validAssignRules, true)) {
                $assignRule = 'manual';
            }

            $sla = $step['sla_target_minutes'] ?? null;
            $sla = $sla !== null && $sla !== '' ? max(0, (int) $sla) : null;

            $preparedSteps[] = [
                'key' => $step['key'],
                'id' => $step['id'] ?? null,
                'name' => $name,
                'assign_rule' => $assignRule,
                'sla_target_minutes' => $sla,
            ];
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $ticketTypeId = $this->workflowForm['ticket_type_id'] ?: null;
        $categoryId = $this->workflowForm['category_id'] ?: null;

        if ($ticketTypeId) {
            $typeExists = TicketType::query()
                ->whereKey($ticketTypeId)
                ->where('area_id', $data['workflowForm']['area_id'])
                ->exists();

            if (!$typeExists) {
                $this->addError('workflowForm.ticket_type_id', 'Selecione um tipo pertencente à área informada.');
                return;
            }
        }

        if ($categoryId) {
            $categoryExists = Category::query()
                ->whereKey($categoryId)
                ->when($ticketTypeId, fn ($q) => $q->where('ticket_type_id', $ticketTypeId))
                ->where('area_id', $data['workflowForm']['area_id'])
                ->exists();

            if (!$categoryExists) {
                $this->addError('workflowForm.category_id', 'Escolha uma categoria compatível com o tipo e a área.');
                return;
            }
        }

        DB::transaction(function () use ($preparedSteps, $data, $ticketTypeId, $categoryId) {
            $workflow = $this->editing ?? new Workflow();
            $workflow->fill([
                'area_id' => $data['workflowForm']['area_id'],
                'name' => $data['workflowForm']['name'],
                'active' => (bool) $this->workflowForm['active'],
                'ticket_type_id' => $ticketTypeId,
                'category_id' => $categoryId,
            ]);
            $workflow->save();

            $existingIds = $workflow->steps()->pluck('id')->all();
            $retained = [];

            foreach ($preparedSteps as $order => $step) {
                $payload = [
                    'name' => $step['name'],
                    'assign_rule' => $step['assign_rule'],
                    'sla_target_minutes' => $step['sla_target_minutes'],
                    'order' => $order + 1,
                ];

                if ($step['id']) {
                    $workflowStep = WorkflowStep::where('workflow_id', $workflow->id)
                        ->whereKey($step['id'])
                        ->first();

                    if ($workflowStep) {
                        $workflowStep->update($payload);
                        $retained[] = $workflowStep->id;
                        continue;
                    }
                }

                $newStep = $workflow->steps()->create($payload);
                $retained[] = $newStep->id;
            }

            $toDelete = array_diff($existingIds, $retained);
            if (!empty($toDelete)) {
                $workflow->steps()->whereIn('id', $toDelete)->delete();
            }

            $this->editing = null;
            $this->showForm = false;
            $this->workflowForm = [
                'area_id' => null,
                'name' => '',
                'active' => true,
                'ticket_type_id' => null,
                'category_id' => null,
                'steps' => [],
            ];

            $this->dispatch('sweet-alert', [
                'type' => 'success',
                'title' => 'Workflow salvo',
                'toast' => true,
            ]);
        });
    }

    public function confirmDelete(int $workflowId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Excluir workflow?',
            'text' => 'Tickets vinculados manterão o histórico, mas deixarão de usar este fluxo.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'deleteWorkflow',
            'payload' => ['workflow_id' => $workflowId],
            'componentId' => $this->getId(),
        ]);
    }

    public function deleteWorkflow($payload = null): void
    {
        $workflowId = is_array($payload) ? ($payload['workflow_id'] ?? null) : $payload;
        if (!$workflowId) {
            return;
        }

        Workflow::query()->whereKey($workflowId)->delete();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Workflow removido',
            'toast' => true,
        ]);
    }

    public function toggleActive(int $workflowId): void
    {
        $workflow = Workflow::findOrFail($workflowId);
        $workflow->active = !$workflow->active;
        $workflow->save();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $workflow->active ? 'Workflow ativado' : 'Workflow desativado',
            'toast' => true,
        ]);
    }

    public function cancel(): void
    {
        $this->editing = null;
        $this->workflowForm = [
            'area_id' => null,
            'name' => '',
            'active' => true,
            'ticket_type_id' => null,
            'category_id' => null,
            'steps' => [],
        ];
        $this->showForm = false;
        $this->resetErrorBag();
        $this->loadTypes();
        $this->loadCategories();
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->workflowForm = [
            'area_id' => null,
            'name' => '',
            'active' => true,
            'ticket_type_id' => null,
            'category_id' => null,
            'steps' => [
                $this->makeStepRow('Triagem inicial'),
                $this->makeStepRow('Execução'),
            ],
        ];
        $this->showForm = true;
        $this->resetErrorBag();
        $this->loadTypes();
        $this->loadCategories();
    }

    private function addStepRow(?string $name = null): void
    {
        $this->workflowForm['steps'][] = $this->makeStepRow($name);
    }

    private function makeStepRow(?string $name = null): array
    {
        return [
            'key' => (string) Str::uuid(),
            'id' => null,
            'name' => $name ?? '',
            'assign_rule' => 'manual',
            'sla_target_minutes' => null,
        ];
    }

    public function updatedWorkflowFormAreaId($value): void
    {
        $this->workflowForm['area_id'] = $value ?: null;
        $this->workflowForm['ticket_type_id'] = null;
        $this->workflowForm['category_id'] = null;
        $this->loadTypes();
        $this->loadCategories();
    }

    public function updatedWorkflowFormTicketTypeId($value): void
    {
        $this->workflowForm['ticket_type_id'] = $value ?: null;
        $this->workflowForm['category_id'] = null;
        $this->loadCategories();
    }

    private function loadTypes(): void
    {
        if ($this->workflowForm['area_id']) {
            $this->types = TicketType::query()
                ->where('area_id', $this->workflowForm['area_id'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        } else {
            $this->types = [];
        }
    }

    private function loadCategories(): void
    {
        $categories = [];

        if ($this->workflowForm['ticket_type_id']) {
            $categories = Category::query()
                ->where('ticket_type_id', $this->workflowForm['ticket_type_id'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        } elseif ($this->workflowForm['area_id']) {
            $categories = Category::query()
                ->where('area_id', $this->workflowForm['area_id'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        }

        $this->categories = $categories;

        if ($this->workflowForm['category_id'] && !collect($categories)->pluck('id')->contains($this->workflowForm['category_id'])) {
            $this->workflowForm['category_id'] = null;
        }
    }
}
