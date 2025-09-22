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
            $statusLabel = $this->statusLabel($t->status);

            return [
                'ticket_id' => $t->id,
                'key'   => $t->code,
                'title' => $t->title,
                'meta'  => $statusLabel . ($priority ? ' • Prioridade: ' . $priority : '') .
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

        $label = $this->statusLabel($status);

        return match ($status) {
            'open' => [
                'label' => $label,
                'class' => 'bg-sky-900/30 border border-sky-700 text-sky-200',
            ],
            'in_progress' => [
                'label' => $label,
                'class' => 'bg-indigo-900/30 border border-indigo-700 text-indigo-200',
            ],
            'paused' => [
                'label' => $label,
                'class' => 'bg-zinc-800/30 border border-zinc-700 text-zinc-300',
            ],
            'resolved' => [
                'label' => $label,
                'class' => 'bg-emerald-900/30 border border-emerald-800 text-emerald-200',
            ],
            'closed' => [
                'label' => $label,
                'class' => 'bg-zinc-900/30 border border-zinc-700 text-zinc-300',
            ],
            default => [
                'label' => $label,
                'class' => 'bg-zinc-700/40 border border-zinc-600 text-zinc-200',
            ],
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'open' => 'Aberto',
            'in_progress' => 'Em andamento',
            'paused' => 'Pausado',
            'resolved' => 'Resolvido',
            'closed' => 'Fechado',
            default => ucfirst($status),
        };
    }

    public function render()
    {
        return view('livewire.tickets.recent');
    }
}
