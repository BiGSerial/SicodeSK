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
class Index extends Component
{
    use WithPagination;
    use WildcardFormatter;

    // Filtros
    public ?int $areaId = null;
    public ?string $status = null;     // open|in_progress|paused|resolved|closed
    public ?string $priority = null;   // low|medium|high|urgent
    public string $search = '';
    public bool $onlyLate = false;

    public int $perPage = 20;

    protected $queryString = [
        'areaId'    => ['except' => null],
        'status'    => ['except' => null],
        'priority'  => ['except' => null],
        'search'    => ['except' => ''],
        'onlyLate'  => ['except' => false],
        'page'      => ['except' => 1],
        'perPage'   => ['except' => 20],
    ];

    public function updated($prop): void
    {


        // sempre que mudar filtro, volta para página 1
        if (in_array($prop, ['areaId','status','priority','search','onlyLate','perPage'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset([
            'areaId','status','priority','search','onlyLate','perPage'
        ]);
        $this->perPage = 20;
    }

    public function getRows()
    {
        $userId = Auth::id();

        $q = Ticket::query()
            ->where('requester_sicode_id', $userId)
            ->with(['area:id,name', 'type:id,name']) // ajuste de relações no Model (area, type)
            ->when($this->areaId, fn ($qq) => $qq->where('area_id', $this->areaId))
            ->when($this->status, fn ($qq) => $qq->where('status', $this->status))
            ->when($this->priority, fn ($qq) => $qq->where('priority', $this->priority))
            ->when($this->onlyLate, fn ($qq) => $qq->where('is_late', true))
            ->when($wildcard = $this->formatWildcard($this->search, false), function (Builder $query) use ($wildcard) {

                $query->where(function (Builder $sub) use ($wildcard) {
                    $sub->where('code', $wildcard->type, $wildcard->term)
                        ->orWhere('title', $wildcard->type, $wildcard->term)
                        ->orWhere('description', $wildcard->type, $wildcard->term);
                });
            })
            ->latest('updated_at');

        return $q->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.tickets.index', [
            'areas'   => Area::orderBy('name')->get(['id','name']),
            'tickets' => $this->getRows(),
            'now'     => Carbon::now(),
        ]);
    }
}
