<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Area;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Organization extends Component
{
    use ChecksAdminAccess;

    public ?int $selectedArea = null;

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function render()
    {
        $areas = Area::query()
            ->with(['manager:id,name,email'])
            ->orderBy('name')
            ->get(['id', 'name', 'manager_sicode_id']);

        return view('livewire.admin.organization', [
            'areas' => $areas,
            'teams' => $this->teamsForSelectedArea($areas),
        ]);
    }

    private function teamsForSelectedArea(Collection $areas): Collection
    {
        if (!$this->selectedArea) {
            return collect();
        }

        $area = $areas->firstWhere('id', $this->selectedArea);

        if (!$area) {
            return collect();
        }

        return collect([]);
    }
}
