<?php

namespace App\Livewire\Tickets;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
// Models que já assumimos existir no sicodeSK (Postgres):
use App\Models\Area;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\TicketType;
use App\Models\Ticket;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\TicketAttachment;
use App\Models\TicketEvent;
use App\Services\TicketCodeService;

#[Layout('layouts.app')]
class CreateWizard extends Component
{
    use WithFileUploads;

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

    /** Attachments */
    public array $attachmentUploads = [];
    public array $pendingAttachments = [];
    public array $attachmentPreview = [];

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
            $this->validateAttachments();
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

        $pendingUploads = $this->pendingAttachments;

        $ticket = null;

        DB::connection('pgsql')->transaction(function () use (&$ticket, $requesterSicodeId, $dueAt) {
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

            // >>> GERAÇÃO DO CÓDIGO SEGURO (usa UPSERT no Postgres por trás)
            $area = Area::findOrFail($this->areaId);
            $code = TicketCodeService::nextCode($area);

            $ticket = Ticket::create([
                'code'                => $code,              // <<< AQUI
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
                    'code' => $code, // útil no histórico
                    'priority' => $this->priority,
                    'area_id'  => $this->areaId,
                    'ticket_type_id' => $this->ticketTypeId,
                ]),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        });

        if ($ticket && !empty($pendingUploads ?? [])) {
            $this->persistAttachments($ticket, $pendingUploads, $requesterSicodeId);
        }

        $this->resetAttachmentState();

        session()->flash('status', 'Ticket created successfully.');
        $this->redirectRoute('dashboard', navigate: true);
    }

    public function requestAttachmentRemoval(string $key): void
    {
        if (!isset($this->pendingAttachments[$key])) {
            return;
        }

        $this->dispatch('sweet-confirm', [
            'title' => 'Remover anexo?',
            'text' => 'Este arquivo será removido antes do envio do ticket.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'removePendingAttachment',
            'payload' => ['key' => $key],
            'componentId' => $this->getId(),
        ]);
    }

    public function requestSubmit(): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Deseja criar o ticket?',
            'text' => 'Confirme para enviar o ticket e acompanhar na dashboard.',
            'icon' => 'question',
            'confirmButtonText' => 'Criar ticket',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'confirmCreateTicket',
            'componentId' => $this->getId(),
        ]);
    }

    public function confirmCreateTicket(): void
    {
        $this->submit();
    }

    public function removePendingAttachment($payload = null): void
    {
        $key = is_array($payload) ? ($payload['key'] ?? null) : $payload;

        if (!$key || !isset($this->pendingAttachments[$key])) {
            return;
        }

        unset($this->pendingAttachments[$key], $this->attachmentPreview[$key]);
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Anexo removido',
            'text' => 'O arquivo foi retirado da lista temporária.',
            'toast' => true,
        ]);
    }

    public function updatedAttachmentUploads(): void
    {
        foreach ($this->attachmentUploads as $upload) {
            if (!$upload) {
                continue;
            }

            $id = Str::uuid()->toString();
            $this->pendingAttachments[$id] = $upload;
            $this->attachmentPreview[$id] = [
                'name' => $upload->getClientOriginalName(),
                'size' => $this->formatBytes($upload->getSize()),
                'mime' => $upload->getMimeType(),
            ];
        }

        $this->attachmentUploads = [];
    }


    public function render()
    {
        return view('livewire.tickets.create-wizard');
    }

    private function validateAttachments(): void
    {
        if (empty($this->pendingAttachments)) {
            return;
        }

        $payload = ['attachments' => array_values($this->pendingAttachments)];
        validator($payload, [
            'attachments.*' => 'file|max:10240',
        ])->validate();
    }

    private function persistAttachments(Ticket $ticket, array $uploads, string $actorId): void
    {
        $disk = 'public';
        $directory = "tickets/{$ticket->code}";
        $existingCount = TicketAttachment::where('ticket_id', $ticket->id)->count();

        $index = 0;
        foreach (array_values($uploads) as $file) {
            if (!$file) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $sequence = str_pad((string) ($existingCount + (++$index)), 3, '0', STR_PAD_LEFT);
            $suffix = Str::upper(Str::random(6));
            $filename = sprintf('%s-%s-%s.%s', $ticket->code, $sequence, $suffix, $extension);

            $storedPath = Storage::disk($disk)->putFileAs($directory, $file, $filename);

            $attachment = TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'uploader_sicode_id' => $actorId,
                'filename' => $filename,
                'disk' => $disk,
                'path' => $storedPath,
                'mime' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ]);

            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'actor_sicode_id' => $actorId,
                'type' => 'attachment_added',
                'payload_json' => [
                    'filename' => $attachment->filename,
                    'original_name' => $file->getClientOriginalName(),
                ],
            ]);
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return sprintf('%s %s', number_format($value, $power === 0 ? 0 : 1), $units[$power]);
    }

    private function resetAttachmentState(): void
    {
        $this->pendingAttachments = [];
        $this->attachmentPreview = [];
        $this->attachmentUploads = [];
    }
}
