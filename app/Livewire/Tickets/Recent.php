<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Recent extends Component
{
    public array $items = [];

    public function mount(): void
    {
        $user = Auth::user();
        $sicodeId = $user?->id; // UUID do usuário (requester)

        // Buscar últimos 5 tickets do usuário logado
        $tickets = Ticket::query()
            ->where('requester_sicode_id', $sicodeId)
            ->with(['priority:id,name,slug'])
            ->latest('created_at')
            ->take(5)
            ->get();

        // Mapear para o mesmo formato que sua view espera
        $this->items = $tickets->map(function ($t) {
            $priority = $t->priority?->name;
            return [
                'key'   => $t->code,
                'title' => $t->title,
                'meta'  => ucfirst($t->status) . ($priority ? ' • Prioridade: ' . $priority : '') .
                          ($t->sla_due_at ? ' • SLA: ' . $t->sla_due_at->diffForHumans() : ''),
                'badge' => $this->badgeFor($t->status, $t->is_late),
            ];
        })->toArray();
    }

    protected function badgeFor(string $status, bool $isLate): array
    {
        if ($isLate) {
            return [
                'label' => 'Atrasado',
                'class' => 'bg-red-900/40 border border-red-800 text-red-200',
            ];
        }

        return match ($status) {
            'open', 'in_progress' => [
                'label' => 'Em andamento',
                'class' => 'bg-edp-marineblue-70/40 border border-[#2b3649]',
            ],
            'resolved', 'closed' => [
                'label' => 'Concluído',
                'class' => 'bg-emerald-900/30 border border-emerald-800 text-emerald-200',
            ],
            default => [
                'label' => ucfirst($status),
                'class' => 'bg-zinc-700/40 border border-zinc-600 text-zinc-200',
            ],
        };
    }

    public function render()
    {
        return view('livewire.tickets.recent');
    }
}
