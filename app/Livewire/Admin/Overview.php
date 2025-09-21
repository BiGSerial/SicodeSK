<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Overview extends Component
{
    use ChecksAdminAccess;

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function render()
    {
        $now = Carbon::now();
        $baseQuery = Ticket::query();

        $metrics = [
            [
                'label' => 'Tickets abertos',
                'value' => (clone $baseQuery)->whereIn('status', ['open', 'in_progress', 'paused'])->count(),
                'muted' => 'Visão global de filas ativas',
            ],
            [
                'label' => 'Violações de SLA em 24h',
                'value' => (clone $baseQuery)
                    ->where('is_late', true)
                    ->where('updated_at', '>=', $now->copy()->subDay())
                    ->count(),
                'muted' => 'Registros com atraso nas últimas 24h',
                'accent' => 'text-rose-300',
            ],
            [
                'label' => 'Conclusões no mês',
                'value' => (clone $baseQuery)
                    ->where('status', 'closed')
                    ->whereMonth('updated_at', $now->month)
                    ->whereYear('updated_at', $now->year)
                    ->count(),
                'muted' => 'Fechados no mês corrente',
            ],
        ];

        return view('livewire.admin.overview', [
            'metrics' => $metrics,
            'now' => $now,
        ]);
    }
}
