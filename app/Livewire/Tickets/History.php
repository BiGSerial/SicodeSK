<?php

namespace App\Livewire\Tickets;

use App\Exports\TicketHistoryExport;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Support\Concerns\WildcardFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class History extends Component
{
    use WithPagination;
    use WildcardFormatter;

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $status = null;
    public ?int $priorityId = null;
    public ?int $ticketTypeId = null;
    public string $search = '';
    public int $perPage = 10;

    protected $queryString = [
        'startDate' => ['except' => null],
        'endDate' => ['except' => null],
        'status' => ['except' => null],
        'priorityId' => ['except' => null],
        'ticketTypeId' => ['except' => null],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'perPage' => ['except' => 10],
    ];

    public function updating($name): void
    {
        if (in_array($name, ['startDate', 'endDate', 'status', 'priorityId', 'ticketTypeId', 'search', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'startDate',
            'endDate',
            'status',
            'priorityId',
            'ticketTypeId',
            'search',
            'perPage',
        ]);
        $this->perPage = 10;
        $this->resetPage();
    }

    public function updatedPriorityId($value): void
    {
        $this->priorityId = $value !== '' ? (int) $value : null;
    }

    public function render()
    {
        return view('livewire.tickets.history', [
            'ticketTypes' => $this->ticketTypes(),
            'tickets' => $this->tickets(),
            'priorities' => $this->priorities(),
        ]);
    }

    private function ticketTypes()
    {
        return TicketType::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function tickets()
    {
        return $this->baseQuery()
            ->paginate($this->perPage);
    }

    private function priorities()
    {
        return \App\Models\Priority::query()
            ->orderByDesc('weight')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function export(string $format = 'xlsx')
    {
        $format = strtolower($format);
        $writerType = $format === 'csv' ? ExcelWriter::CSV : ExcelWriter::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = 'historico-tickets-' . now()->format('Ymd_His') . '.' . $extension;

        $data = $this->baseQuery()->get();

        return Excel::download(
            new TicketHistoryExport($data),
            $fileName,
            $writerType
        );
    }

    private function baseQuery(): Builder
    {
        $userId = Auth::id();

        return Ticket::query()
            ->with(['type:id,name', 'area:id,name,sigla', 'priority:id,name,slug,color'])
            ->where('requester_sicode_id', $userId)
            ->when($this->startDate, function (Builder $query) {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $query->whereDate('created_at', '>=', $start);
            })
            ->when($this->endDate, function (Builder $query) {
                $end = Carbon::parse($this->endDate)->endOfDay();
                $query->whereDate('created_at', '<=', $end);
            })
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->priorityId, fn (Builder $query) => $query->where('priority_id', $this->priorityId))
            ->when($this->ticketTypeId, fn (Builder $query) => $query->where('ticket_type_id', $this->ticketTypeId))
            ->when($wildcard = $this->formatWildcard($this->search, false), function (Builder $query) use ($wildcard) {

                $query->where(function (Builder $sub) use ($wildcard) {
                    $sub->where('code', $wildcard->type, $wildcard->term)
                        ->orWhere('title', $wildcard->type, $wildcard->term)
                        ->orWhere('description', $wildcard->type, $wildcard->term);
                });
            })
            ->latest('created_at');
    }
}
