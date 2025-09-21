<?php

namespace App\Livewire\Tickets;

use App\Models\Area;
use App\Models\Priority;
use App\Models\Ticket;
use App\Support\Concerns\WildcardFormatter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Admin extends Component
{
    use WithPagination;
    use WildcardFormatter;

    public ?int $areaId = null;
    public ?string $status = null;
    public ?int $priorityId = null;
    public string $search = '';
    public bool $onlyLate = false;
    public string $mode = 'gestao'; // gestao|executor
    public int $perPage = 20;

    protected $queryString = [
        'mode' => ['except' => 'gestao'],
        'areaId' => ['except' => null],
        'status' => ['except' => null],
        'priorityId' => ['except' => null],
        'search' => ['except' => ''],
        'onlyLate' => ['except' => false],
        'page' => ['except' => 1],
        'perPage' => ['except' => 20],
    ];

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthorizationException();
        }

        $requestedMode = in_array($this->mode, ['gestao', 'executor'], true) ? $this->mode : null;

        if ($this->isSuperadm()) {
            $this->mode = $requestedMode ?? 'gestao';
            return;
        }

        $userId = (string) $user->id;

        $manages = Ticket::query()->where('manager_sicode_id', $userId)->exists();
        $executes = Ticket::query()->where('executor_sicode_id', $userId)->exists();

        if (!$manages && !$executes) {
            throw new AuthorizationException();
        }

        if ($requestedMode && ($requestedMode === 'gestao' ? $manages : $executes)) {
            $this->mode = $requestedMode;
            return;
        }

        $this->mode = $manages ? 'gestao' : 'executor';
    }

    public function updating($prop): void
    {
        if (in_array($prop, ['mode', 'areaId', 'status', 'priorityId', 'search', 'onlyLate', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function setMode(string $mode): void
    {
        if (!in_array($mode, ['gestao', 'executor'], true)) {
            return;
        }

        if ($this->mode === $mode) {
            return;
        }

        $this->mode = $mode;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'areaId',
            'status',
            'priorityId',
            'search',
            'onlyLate',
            'perPage',
        ]);

        $this->perPage = 20;
        $this->resetPage();
    }

    public function updatedPriorityId($value): void
    {
        $this->priorityId = $value !== '' ? (int) $value : null;
    }

    public function render()
    {
        return view('livewire.tickets.admin', [
            'areas' => $this->areas(),
            'tickets' => $this->tickets(),
            'metrics' => $this->metrics(),
            'priorities' => $this->priorities(),
            'now' => Carbon::now(),
        ]);
    }

    private function areas()
    {
        return Area::orderBy('name')->get(['id', 'name']);
    }

    private function tickets()
    {
        return $this->baseQuery()->paginate($this->perPage);
    }

    private function metrics(): array
    {
        $base = $this->baseQuery();

        $total = (clone $base)->count();
        $late = (clone $base)->where('is_late', true)->count();
        $inProgress = (clone $base)->whereIn('status', ['open', 'in_progress', 'paused'])->count();

        return [
            [
                'label' => 'Total no filtro',
                'value' => $total,
                'muted' => 'Chamados visíveis nesta visão',
            ],
            [
                'label' => 'Em andamento',
                'value' => $inProgress,
                'muted' => 'Aberto, em progresso ou pausado',
            ],
            [
                'label' => 'Em atraso',
                'value' => $late,
                'muted' => 'SLA vencido',
                'accent' => $late > 0 ? 'text-rose-300' : 'text-emerald-300',
            ],
        ];
    }

    private function baseQuery(): Builder
    {
        $user = Auth::user();
        $userId = $user?->id;

        $query = Ticket::query()
            ->with([
                'area:id,name,sigla',
                'type:id,name',
                'requester:id,name',
                'executor:id,name',
                'priority:id,name,slug,color',
            ])
            ->when(!$this->isSuperadm(), function (Builder $builder) use ($userId) {
                if ($this->mode === 'gestao') {
                    $builder->where('manager_sicode_id', $userId);
                } else {
                    $builder->where('executor_sicode_id', $userId);
                }
            })
            ->when($this->areaId, fn ($builder) => $builder->where('area_id', $this->areaId))
            ->when($this->status, fn ($builder) => $builder->where('status', $this->status))
            ->when($this->priorityId, fn ($builder) => $builder->where('priority_id', $this->priorityId))
            ->when($this->onlyLate, fn ($builder) => $builder->where('is_late', true))
            ->when($wildcard = $this->formatWildcard($this->search, false), function (Builder $builder) use ($wildcard) {
                $builder->where(function (Builder $sub) use ($wildcard) {
                    $term = $wildcard->term;
                    if ($wildcard->type === 'LIKE' && !str_contains($term, '%')) {
                        $term = "%{$term}%";
                    }

                    $sub->where('code', $wildcard->type, $term)
                        ->orWhere('title', $wildcard->type, $term)
                        ->orWhere('description', $wildcard->type, $term);
                });
            })
            ->latest('updated_at');

        return $query;
    }

    private function isSuperadm(): bool
    {
        $user = Auth::user();

        return (bool) ($user->superadm ?? false);
    }

    private function priorities()
    {
        return Priority::query()
            ->orderByDesc('weight')
            ->orderBy('name')
            ->get(['id','name','slug','color']);
    }
}
