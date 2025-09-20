<?php

namespace App\Livewire\Tickets;

use App\Models\Area;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Filtros
    public ?int $areaId = null;
    public ?string $status = null;     // open|in_progress|paused|resolved|closed
    public ?string $priority = null;   // low|medium|high|urgent
    public string $search = '';
    public bool $onlyMine = false;
    public bool $onlyLate = false;

    public int $perPage = 20;

    protected $queryString = [
        'areaId'    => ['except' => null],
        'status'    => ['except' => null],
        'priority'  => ['except' => null],
        'search'    => ['except' => ''],
        'onlyMine'  => ['except' => false],
        'onlyLate'  => ['except' => false],
        'page'      => ['except' => 1],
        'perPage'   => ['except' => 20],
    ];

    public function updating($prop): void
    {
        // sempre que mudar filtro, volta para página 1
        if (in_array($prop, ['areaId','status','priority','search','onlyMine','onlyLate','perPage'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset([
            'areaId','status','priority','search','onlyMine','onlyLate','perPage'
        ]);
        $this->perPage = 20;
    }

    public function getRows()
    {
        $q = Ticket::query()
            ->with(['area:id,name','type:id,name']) // ajuste de relações no Model (area, type)
            ->when($this->areaId, fn ($qq) => $qq->where('area_id', $this->areaId))
            ->when($this->status, fn ($qq) => $qq->where('status', $this->status))
            ->when($this->priority, fn ($qq) => $qq->where('priority', $this->priority))
            ->when($this->onlyMine, fn ($qq) => $qq->where('executor_sicode_id', Auth::id()))
            ->when($this->onlyLate, fn ($qq) => $qq->where('is_late', true))
            ->when(strlen($this->search) > 0, function ($qq) {
                // Postgres friendly: ILIKE em título/descrição (pode trocar por full-text com GIN depois)
                $term = '%'.str_replace('%', '\%', $this->search).'%';
                $qq->where(function ($w) use ($term) {
                    $w->where('title', 'ILIKE', $term)
                      ->orWhere('description', 'ILIKE', $term);
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
