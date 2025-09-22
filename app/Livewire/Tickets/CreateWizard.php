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
use Carbon\CarbonInterface;
// Models que já assumimos existir no sicodeSK (Postgres):
use App\Models\Area;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Subcategory;
use App\Models\TicketType;
use App\Models\Ticket;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\TicketAttachment;
use App\Models\TicketEvent;
use App\Services\TicketCodeService;
use App\Services\SlaResolver;
use App\Services\AuthorizationService;
use App\Models\SystemSetting;

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
    public ?int $priorityId = null;

    /** Content */
    public string $title = '';
    public string $description = '';

    /** Combos */
    public array $areas = [];
    public array $ticketTypes = [];
    public array $categories = [];
    public array $subcategories = [];
    public array $priorities = [];

    /** Preview */
    public ?string $slaPreview = null;

    /** Attachments */
    public array $attachmentUploads = [];
    public array $pendingAttachments = [];
    public array $attachmentPreview = [];

    protected ?array $policiesCache = null;

    public function mount(): void
    {
        if (!app(AuthorizationService::class)->canCreateTicket()) {
            abort(403, 'Você não tem permissão para abrir novos tickets.');
        }

        $this->loadAreas();
        $this->loadPriorities();
        $this->broadcastState();
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

    protected function loadPriorities(): void
    {
        $collection = Priority::query()
            ->where('active', true)
            ->orderByDesc('weight')
            ->orderBy('name')
            ->get(['id','name','slug','color','is_default']);

        $this->priorities = $collection->map(function ($priority) {
            return [
                'id' => $priority->id,
                'name' => $priority->name,
                'slug' => $priority->slug,
                'color' => $priority->color,
                'is_default' => $priority->is_default,
            ];
        })->toArray();

        if (!$this->priorityId && $collection->isNotEmpty()) {
            $default = $collection->firstWhere('is_default', true) ?? $collection->first();
            $this->priorityId = $default?->id;
        }
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
        $this->broadcastState();
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
        $this->broadcastState();
    }

    public function updatedTicketTypeId(): void
    {
        $this->computeSlaPreview();
        $this->broadcastState();
    }

    public function updatedPriorityId(): void
    {
        $this->computeSlaPreview();
        $this->broadcastState();
    }

    /* ---------- Steps ---------- */

    public function next(): void
    {
        $this->validateStep($this->step);

        $this->step = min(4, $this->step + 1);
        $this->broadcastState();
    }

    public function prev(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->broadcastState();
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
                'priorityId' => ['required', Rule::exists('priorities', 'id')->where('active', true)],
            ], [], [
                'categoryId' => 'categoria',
                'subcategoryId' => 'subcategoria',
                'priorityId' => 'prioridade',
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

    protected function resolveSla(?CarbonInterface $baseline = null): ?array
    {
        if (!$this->priorityId) {
            return null;
        }

        $baseline = $baseline ?? now();

        $resolver = app(SlaResolver::class);

        $minutes = $resolver->resolveMinutes(
            $this->priorityId,
            $this->areaId,
            $this->ticketTypeId,
            $this->categoryId,
            $this->subcategoryId
        );

        $calendar = $this->areaId
            ? Area::query()->with(['workCalendar.holidays'])->find($this->areaId)?->workCalendar
            : null;

        $dueAt = $resolver->resolveDueDate(
            $this->priorityId,
            $this->areaId,
            $this->ticketTypeId,
            $this->categoryId,
            $this->subcategoryId,
            $baseline,
            $calendar
        );

        return [
            'minutes' => $minutes,
            'due_at' => $dueAt,
        ];
    }

    protected function computeSlaPreview(): void
    {
        if (!$this->priorityId || !$this->areaId || !$this->ticketTypeId) {
            $this->slaPreview = null;
            return;
        }

        $result = $this->resolveSla();

        if (!$result) {
            $this->slaPreview = null;
            return;
        }

        $minutes = $result['minutes'] ?? null;
        $dueAt = $result['due_at'] ?? null;

        if ($minutes === null || !$dueAt) {
            $this->slaPreview = null;
            return;
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;
        $durationLabel = $rest ? "{$hours}h {$rest}m" : "{$hours}h";

        $this->slaPreview = sprintf('%s • vence %s', $durationLabel, $dueAt->translatedFormat('d/m H:i'));
    }

    /* ---------- Submit ---------- */

    public function submit(): void
    {
        $this->validateStep(1);
        $this->validateStep(2);
        $this->validateStep(3);

        $user = Auth::user(); // SicodeUser (MariaDB)
        $requesterSicodeId = $user->id; // uuid

        $slaResult = $this->resolveSla();
        $slaMinutes = $slaResult['minutes'] ?? 0;
        $dueAt = $slaResult['due_at'] ?? now()->addMinutes($slaMinutes);

        $selectedPriority = $this->priorityId ? Priority::find($this->priorityId) : null;
        $prioritySlug = $selectedPriority?->slug;

        $pendingUploads = $this->pendingAttachments;

        $ticket = null;

        DB::connection('pgsql')->transaction(function () use (&$ticket, $requesterSicodeId, $dueAt, $selectedPriority, $prioritySlug) {
            $workflow = $this->resolveWorkflow();
            $workflowId = $workflow?->id;

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
                'priority_id'         => $selectedPriority?->id,
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
                    'priority_id' => $selectedPriority?->id,
                    'priority_slug' => $prioritySlug,
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

        $this->dispatch('ticket-form-cleared');

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
        $policies = $this->globalPolicies();
        $maxPerTicket = (int) data_get($policies, 'attachments.max_per_ticket', 10);
        $maxSizeMb = (int) data_get($policies, 'attachments.max_size_mb', 25);
        $maxBytes = $maxSizeMb * 1024 * 1024;
        $allowed = collect(data_get($policies, 'attachments.allowed_extensions', []))
            ->filter()
            ->map(fn ($ext) => strtolower($ext))
            ->values();
        $blocked = collect(data_get($policies, 'attachments.blocked_extensions', []))
            ->filter()
            ->map(fn ($ext) => strtolower($ext))
            ->values();

        foreach ($this->attachmentUploads as $upload) {
            if (!$upload) {
                continue;
            }

            if (count($this->pendingAttachments) >= $maxPerTicket) {
                $this->dispatch('sweet-alert', [
                    'type' => 'warning',
                    'title' => 'Limite de anexos atingido',
                    'text' => 'Remova um arquivo antes de adicionar outro.',
                    'toast' => true,
                ]);
                break;
            }

            if ($upload->getSize() > $maxBytes) {
                $this->dispatch('sweet-alert', [
                    'type' => 'error',
                    'title' => 'Arquivo muito grande',
                    'text' => "Cada anexo pode ter até {$maxSizeMb} MB.",
                    'toast' => true,
                ]);
                continue;
            }

            $extension = strtolower($upload->getClientOriginalExtension() ?: '');

            if ($blocked->contains($extension)) {
                $this->dispatch('sweet-alert', [
                    'type' => 'error',
                    'title' => 'Extensão bloqueada',
                    'text' => "Arquivos .{$extension} não são permitidos.",
                    'toast' => true,
                ]);
                continue;
            }

            if ($allowed->isNotEmpty() && !$allowed->contains($extension)) {
                $this->dispatch('sweet-alert', [
                    'type' => 'error',
                    'title' => 'Formato não permitido',
                    'text' => 'O arquivo não está na lista de formatos aceitos.',
                    'toast' => true,
                ]);
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
        $policies = $this->globalPolicies();
        $maxPerTicket = (int) data_get($policies, 'attachments.max_per_ticket', 10);
        $maxSizeMb = (int) data_get($policies, 'attachments.max_size_mb', 25);
        $maxBytes = $maxSizeMb * 1024 * 1024;
        $allowed = collect(data_get($policies, 'attachments.allowed_extensions', []))->map(fn ($ext) => strtolower($ext))->filter();
        $blocked = collect(data_get($policies, 'attachments.blocked_extensions', []))->map(fn ($ext) => strtolower($ext))->filter();

        if (count($this->pendingAttachments) > $maxPerTicket) {
            $this->setErrorBag(new \Illuminate\Support\MessageBag([
                'attachments' => ["Máximo de {$maxPerTicket} anexos por ticket."],
            ]));
            throw \Illuminate\Validation\ValidationException::withMessages([
                'attachments' => "Máximo de {$maxPerTicket} anexos por ticket.",
            ]);
        }

        foreach ($this->pendingAttachments as $upload) {
            if (!$upload) {
                continue;
            }

            if ($upload->getSize() > $maxBytes) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'attachments' => "Cada anexo pode ter até {$maxSizeMb} MB.",
                ]);
            }

            $extension = strtolower($upload->getClientOriginalExtension() ?: '');

            if ($blocked->contains($extension)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'attachments' => "Arquivos .{$extension} não são permitidos.",
                ]);
            }

            if ($allowed->isNotEmpty() && !$allowed->contains($extension)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'attachments' => 'Um ou mais anexos estão em formato não permitido.',
                ]);
            }
        }
    }

    private function persistAttachments(Ticket $ticket, array $uploads, string $actorId): void
    {
        $disk = 'public';
        $directory = "tickets/{$ticket->code}";
        $existingCount = TicketAttachment::where('ticket_id', $ticket->id)->count();

        $policies = $this->globalPolicies();
        $maxPerTicket = (int) data_get($policies, 'attachments.max_per_ticket', 10);

        if ($existingCount + count($uploads) > $maxPerTicket) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'attachments' => 'Quantidade de anexos excede a política configurada.',
            ]);
        }

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

    #[On('restore-ticket-draft')]
    public function restoreDraft(array $state): void
    {
        $this->step = max(1, min(4, (int) ($state['step'] ?? $this->step)));

        $this->areaId = $state['area_id'] ?? null;
        $this->ticketTypeId = $state['ticket_type_id'] ?? null;
        $this->categoryId = $state['category_id'] ?? null;
        $this->subcategoryId = $state['subcategory_id'] ?? null;
        $this->priorityId = $state['priority_id'] ?? $this->priorityId;
        $this->title = $state['title'] ?? $this->title;
        $this->description = $state['description'] ?? $this->description;

        // Recarrega combos com base na área selecionada
        if ($this->areaId) {
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
        } else {
            $this->ticketTypes = [];
            $this->categories = [];
        }

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

        // Garante que IDs ainda existam nas coleções carregadas
        if ($this->ticketTypes && !collect($this->ticketTypes)->firstWhere('id', $this->ticketTypeId)) {
            $this->ticketTypeId = null;
        }

        if ($this->categories && !collect($this->categories)->firstWhere('id', $this->categoryId)) {
            $this->categoryId = null;
        }

        if ($this->subcategories && !collect($this->subcategories)->firstWhere('id', $this->subcategoryId)) {
            $this->subcategoryId = null;
        }

        if ($this->priorities && !collect($this->priorities)->firstWhere('id', $this->priorityId)) {
            $default = collect($this->priorities)->firstWhere('is_default', true) ?? collect($this->priorities)->first();
            $this->priorityId = $default['id'] ?? null;
        }

        $this->computeSlaPreview();
        $this->broadcastState();
    }

    private function formState(): array
    {
        return [
            'step' => $this->step,
            'area_id' => $this->areaId,
            'ticket_type_id' => $this->ticketTypeId,
            'category_id' => $this->categoryId,
            'subcategory_id' => $this->subcategoryId,
            'priority_id' => $this->priorityId,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    private function broadcastState(): void
    {
        $this->dispatch('ticket-form-state', $this->formState());
    }

    public function updated($property, $value): void
    {
        if (in_array($property, ['title', 'description'])) {
            $this->broadcastState();
        }
    }

    private function resolveWorkflow(): ?Workflow
    {
        if (!$this->areaId) {
            return null;
        }

        return Workflow::query()
            ->where('area_id', $this->areaId)
            ->where('active', true)
            ->when($this->ticketTypeId, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('ticket_type_id')
                        ->orWhere('ticket_type_id', $this->ticketTypeId);
                });
            })
            ->when($this->categoryId, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('category_id')
                        ->orWhere('category_id', $this->categoryId);
                });
            })
            ->orderByRaw('CASE WHEN ticket_type_id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN category_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('name')
            ->first();
    }

    private function globalPolicies(): array
    {
        if ($this->policiesCache !== null) {
            return $this->policiesCache;
        }

        $defaults = [
            'attachments' => [
                'max_size_mb' => 25,
                'max_per_ticket' => 10,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'],
                'blocked_extensions' => ['exe', 'bat'],
            ],
            'permissions' => [
                'ticket_creation_roles' => ['requester', 'manager'],
                'comment_roles' => ['requester', 'agent', 'manager'],
            ],
            'notifications' => [
                'sla_breach_email' => true,
                'sla_breach_minutes_before' => 30,
            ],
        ];

        $setting = SystemSetting::query()->where('key', 'global_policies')->first();
        $value = $setting?->value ?? [];

        return $this->policiesCache = array_replace_recursive($defaults, $value);
    }
}
