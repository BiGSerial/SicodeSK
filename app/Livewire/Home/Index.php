<?php

namespace App\Livewire\Home;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        $requesterId = $this->requesterSicodeId();

        $kpis = $requesterId
            ? $this->buildKpis($requesterId)
            : $this->emptyKpis();

        return view('livewire.home.index', [
            'kpis' => $kpis,
        ]);
    }

    private function requesterSicodeId(): ?string
    {
        return Auth::id();
    }

    private function buildKpis(string $requesterId): array
    {
        $aggregates = Ticket::query()
            ->where('requester_sicode_id', $requesterId)
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'open' then 1 else 0 end) as open_count")
            ->selectRaw("sum(case when status = 'in_progress' then 1 else 0 end) as in_progress_count")
            ->selectRaw("sum(case when status = 'resolved' then 1 else 0 end) as resolved_count")
            ->selectRaw('sum(case when is_late then 1 else 0 end) as late_count')
            ->first();

        if (!$aggregates) {
            return $this->emptyKpis();
        }

        $total = (int) ($aggregates->total ?? 0);
        $open = (int) ($aggregates->open_count ?? 0);
        $inProgress = (int) ($aggregates->in_progress_count ?? 0);
        $resolved = (int) ($aggregates->resolved_count ?? 0);
        $late = (int) ($aggregates->late_count ?? 0);

        $pctNoPrazo = $total > 0
            ? round((($total - $late) / $total) * 100) . '%'
            : '—';

        return [
            ['label' => 'Total', 'value' => $total, 'muted' => 'Meus tickets'],
            ['label' => 'Abertos', 'value' => $open, 'muted' => 'Status = open'],
            ['label' => 'Em andamento', 'value' => $inProgress, 'muted' => 'Status = in_progress'],
            ['label' => '% no prazo', 'value' => $pctNoPrazo, 'muted' => 'Baseado no SLA', 'accent' => 'text-edp-verde-100'],
            ['label' => 'Atrasados', 'value' => $late, 'muted' => 'SLA vencido', 'accent' => 'text-edp-warning'],
            ['label' => 'Resolvidos', 'value' => $resolved, 'muted' => 'Status = resolved'],
        ];
    }

    private function emptyKpis(): array
    {
        return [
            ['label' => 'Total', 'value' => 0, 'muted' => 'Meus tickets'],
            ['label' => 'Abertos', 'value' => 0, 'muted' => 'Status = open'],
            ['label' => 'Em andamento', 'value' => 0, 'muted' => 'Status = in_progress'],
            ['label' => '% no prazo', 'value' => '—', 'muted' => 'Baseado no SLA', 'accent' => 'text-edp-verde-100'],
            ['label' => 'Atrasados', 'value' => 0, 'muted' => 'SLA vencido', 'accent' => 'text-edp-warning'],
            ['label' => 'Resolvidos', 'value' => 0, 'muted' => 'Status = resolved'],
        ];
    }
}
