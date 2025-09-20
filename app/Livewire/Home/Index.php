<?php

namespace App\Livewire\Home;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        // KPIs de exemplo — depois você busca do banco
        $kpis = [
            ['label' => 'Abertos',        'value' => 12, 'muted' => 'Todos os status',               'accent' => null],
            ['label' => '% no prazo',     'value' => '92%', 'muted' => 'Últimos 7 dias',            'accent' => 'text-edp-verde-100'],
            ['label' => 'SLA médio',      'value' => '6h',  'muted' => 'Tempo de atendimento',      'accent' => null],
            ['label' => 'Backlog',        'value' => 4,    'muted' => 'Com atraso',                 'accent' => 'text-edp-warning'],
        ];

        return view('livewire.home.index', compact('kpis'));
    }
}
