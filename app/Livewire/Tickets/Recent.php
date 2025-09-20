<?php

namespace App\Livewire\Tickets;

use Livewire\Component;

class Recent extends Component
{
    public array $items = [];

    public function mount(): void
    {
        // TODO: depois trocar por consulta no Postgres (sicodesk)
        $this->items = [
            ['key' => 'CIP-234', 'title' => 'Acesso ao repositório',          'meta' => 'Aberto • Prioridade: Média • SLA: 24h', 'badge' => ['label' => 'Em andamento', 'class' => 'bg-edp-marineblue-70/40 border border-[#2b3649]']],
            ['key' => 'CIP-233', 'title' => 'Erro no painel de indicadores',  'meta' => 'Aberto • Prioridade: Alta • SLA: 8h',   'badge' => ['label' => 'Atrasado',     'class' => 'bg-red-900/40 border border-red-800 text-red-200']],
            ['key' => 'CIP-232', 'title' => 'Solicitação de criação de usuário','meta' => 'Fechado • Prioridade: Baixa',        'badge' => ['label' => 'Concluído',    'class' => 'bg-emerald-900/30 border border-emerald-800 text-emerald-200']],
        ];
    }

    public function render()
    {
        return view('livewire.tickets.recent');
    }
}
