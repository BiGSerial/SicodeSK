<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Slas extends Component
{
    use ChecksAdminAccess;

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function render()
    {
        return view('livewire.admin.slas');
    }
}
