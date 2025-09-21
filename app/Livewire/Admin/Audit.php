<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\TicketEvent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Audit extends Component
{
    use ChecksAdminAccess;
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function updating($name): void
    {
        if ($name === 'search') {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = TicketEvent::query()
            ->with(['ticket:id,code', 'actor:id,name,email'])
            ->latest('created_at')
            ->when($this->search !== '', function ($builder) {
                $term = '%' . strtolower($this->search) . '%';
                $builder->where(function ($inner) use ($term) {
                    $inner->whereRaw('LOWER(type) LIKE ?', [$term])
                        ->orWhereHas('ticket', fn ($q) => $q->whereRaw('LOWER(code) LIKE ?', [$term]))
                        ->orWhereHas('actor', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$term]));
                });
            });

        return view('livewire.admin.audit', [
            'events' => $query->paginate(20),
        ]);
    }
}
