<?php

namespace App\Livewire\Tickets;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
// Models que já assumimos existir no sicodeSK (Postgres):
use App\Models\Area;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\TicketType;
use App\Models\Ticket;
use App\Models\Workflow;
use App\Models\WorkflowStep;

#[Layout('layouts.app')]
class CreateWizard extends Component
{
    /** UI state */
    public int $step = 1;

    /** Selections */
    public ?int $areaId = null;
    public ?int $ticketTypeId = null;
    public ?int $categoryId = null;
    public ?int $subcategoryId = null;
    public string $priority = 'medium'; // low|medium|high|urgent

    /** Content */
    public string $title = '';
    public string $description = '';

    /** Combos */
    public array $areas = [];
    public array $ticketTypes = [];
    public array $categories = [];
    public array $subcategories = [];

    /** Preview */
    public ?string $slaPreview = null;

    public function mount(): void
    {
        $this->loadAreas();
    }

    /* ---------- Loaders ---------- */

    protected function loadAreas(): void
    {
        $this->areas = Area::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn ($a) => ['id' => $a->id,'name' => $a->name])
            ->toArray();
    }

    public function updatedAreaId(): void
    {
        $this->ticketTypeId = null;
        $this->categoryId = null;
        $this->subcategoryId = null;

        $this->ticketTypes = TicketType::query()
            ->where('active', true)
            ->where('area_id', $this->areaId)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn ($t) => ['id' => $t->id,'name' => $t->name])
            ->toArray();

        $this->categories = Category::query()
            ->where('active', true)
            ->where('area_id', $this->areaId)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn ($c) => ['id' => $c->id,'name' => $c->name])
            ->toArray();

        $this->subcategories = [];
        $this->computeSlaPreview();
    }

    public function updatedCategoryId(): void
    {
        $this->subcategoryId = null;

        if ($this->categoryId) {
            $this->subcategories = Subcategory::query()
                ->where('active', true)
                ->where('category_id', $this->categoryId)
                ->orderBy('name')
                ->get(['id','name'])
                ->map(fn ($s) => ['id' => $s->id,'name' => $s->name])
                ->toArray();
        } else {
            $this->subcategories = [];
        }
        $this->computeSlaPreview();
    }

    public function updatedTicketTypeId(): void
    {
        $this->computeSlaPreview();
    }

    public function updatedPriority(): void
    {
        $this->computeSlaPreview();
    }

    /* ---------- Steps ---------- */

    public function next(): void
    {
        $this->validateStep($this->step);

        $this->step = min(4, $this->step + 1);
    }

    public function prev(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    protected function validateStep(int $step): void
    {
        if ($step === 1) {
            $this->validate([
                'areaId' => ['required', Rule::exists('areas', 'id')->where('active', true)],
                'ticketTypeId' => ['required', Rule::exists('ticket_types', 'id')->where('active', true)],
            ], [], [
                'areaId' => 'area',
                'ticketTypeId' => 'ticket type',
            ]);
        }

        if ($step === 2) {
            $this->validate([
                'categoryId' => ['nullable', Rule::exists('categories', 'id')],
                'subcategoryId' => ['nullable', Rule::exists('subcategories', 'id')->where('category_id', $this->categoryId)],
                'priority' => ['required', Rule::in(['low','medium','high','urgent'])],
            ]);
        }

        if ($step === 3) {
            $this->validate([
                'title' => ['required','string','min:6','max:160'],
                'description' => ['nullable','string','max:20000'],
            ]);
        }
    }

    /* ---------- SLA (preview e cálculo) ---------- */

    protected function computeSlaMinutes(string $priority, ?int $areaId, ?int $ticketTypeId): int
    {
        // TODO: consultar sla_matrices e/ou overrides por area/tipo
        // Fallback simples:
        return match ($priority) {
            'low'    => 48 * 60,
            'medium' => 24 * 60,
            'high'   => 8  * 60,
            'urgent' => 4  * 60,
            default  => 24 * 60,
        };
    }

    protected function computeSlaPreview(): void
    {
        if (!$this->priority || !$this->areaId || !$this->ticketTypeId) {
            $this->slaPreview = null;
            return;
        }

        $mins = $this->computeSlaMinutes($this->priority, $this->areaId, $this->ticketTypeId);
        $hours = intdiv($mins, 60);
        $rest = $mins % 60;
        $this->slaPreview = $rest ? "{$hours}h {$rest}m" : "{$hours}h";
    }

    /* ---------- Submit ---------- */

    public function submit(): void
    {
        $this->validateStep(1);
        $this->validateStep(2);
        $this->validateStep(3);

        $user = Auth::user(); // SicodeUser (MariaDB)
        $requesterSicodeId = $user->id; // uuid

        $slaMinutes = $this->computeSlaMinutes($this->priority, $this->areaId, $this->ticketTypeId);
        $dueAt = now()->addMinutes($slaMinutes);

        DB::connection('pgsql')->transaction(function () use ($requesterSicodeId, $dueAt) {
            // (opcional) workflow por área
            $workflowId = Workflow::query()
                ->where('area_id', $this->areaId)
                ->where('active', true)
                ->value('id');

            $firstStepId = null;
            if ($workflowId) {
                $firstStepId = WorkflowStep::query()
                    ->where('workflow_id', $workflowId)
                    ->orderBy('order')
                    ->value('id');
            }

            $ticket = Ticket::create([
                'area_id'             => $this->areaId,
                'ticket_type_id'      => $this->ticketTypeId,
                'category_id'         => $this->categoryId,
                'subcategory_id'      => $this->subcategoryId,
                'workflow_id'         => $workflowId,
                'step_id'             => $firstStepId,

                'priority'            => $this->priority,
                'title'               => $this->title,
                'description'         => $this->description,
                'status'              => 'open',

                'requester_sicode_id' => $requesterSicodeId,
                'manager_sicode_id'   => null,
                'executor_sicode_id'  => null,

                'sla_due_at'          => $dueAt,
                'is_late'             => false,
            ]);

            // evento inicial
            DB::table('ticket_events')->insert([
                'ticket_id'        => $ticket->id,
                'actor_sicode_id'  => $requesterSicodeId,
                'type'             => 'created',
                'payload_json'     => json_encode([
                    'priority' => $this->priority,
                    'area_id'  => $this->areaId,
                    'ticket_type_id' => $this->ticketTypeId,
                ]),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        });

        session()->flash('status', 'Ticket created successfully.');
        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.tickets.create-wizard');
    }
}
