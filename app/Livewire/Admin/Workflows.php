<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Workflow;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Workflows extends Component
{
    use ChecksAdminAccess;

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function render()
    {
        return view('livewire.admin.workflows', [
            'workflows' => Workflow::query()->orderBy('name')->with('steps')->get(),
        ]);
    }
}
