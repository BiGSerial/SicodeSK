<?php

namespace App\Livewire\Tickets;

use App\Models\Area;
use App\Models\Ticket;
use App\Support\Concerns\WildcardFormatter;
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
    public ?string $priority = null;
    public string $search = '';
    public bool $onlyLate = false;
    public string $mode = 'gestao'; // gestao|executor
    public int $perPage = 20;

    protected $queryString = [
        'mode' => ['except' => 'gestao'],
        'areaId' => ['except' => null],
        'status' => ['except' => null],
        'priority' => ['except' => null],
        'search' => ['except' => ''],
        'onlyLate' => ['except' => false],
        'page' => ['except' => 1],
        'perPage' => ['except' => 20],
    ];

    public function updating($prop): void
    {
        if (in_array($prop, ['mode', 'areaId', 'status', 'priority', 'search', 'onlyLate', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function setMode(string $mode): void
    {
        if (!in_array($mode, ['gestao', 'executor'], true)) {
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
            'priority',
            'search',
            'onlyLate',
            'perPage',
        ]);

        $this->perPage = 20;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.tickets.admin', [
            'areas' => $this->areas(),
            'tickets' => $this->tickets(),
            'metrics' => $this->metrics(),
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
        $userId = Auth::id();

        $query = Ticket::query()
            ->with([
                'area:id,name,sigla',
                'type:id,name',
                'requester:id,name',
                'executor:id,name',
            ])
            ->when($this->mode === 'gestao', function (Builder $builder) use ($userId) {
                $builder->where('manager_sicode_id', $userId);
            }, function (Builder $builder) use ($userId) {
                $builder->where('executor_sicode_id', $userId);
            })
            ->when($this->areaId, fn ($builder) => $builder->where('area_id', $this->areaId))
            ->when($this->status, fn ($builder) => $builder->where('status', $this->status))
            ->when($this->priority, fn ($builder) => $builder->where('priority', $this->priority))
            ->when($this->onlyLate, fn ($builder) => $builder->where('is_late', true))
            ->when($wildcard = $this->formatWildcard($this->search, false), function (Builder $builder) use ($wildcard) {
                $builder->where(function (Builder $sub) use ($wildcard) {
                    $sub->where('code', $wildcard->type, $wildcard->term)
                        ->orWhere('title', $wildcard->type, $wildcard->term)
                        ->orWhere('description', $wildcard->type, $wildcard->term);
                });
            })
            ->latest('updated_at');

        return $query;
    }
}
