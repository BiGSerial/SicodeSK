<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Area;
use App\Models\SicodeUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Organization extends Component
{
    use ChecksAdminAccess;

    public ?int $selectedArea = null;
    public bool $showAreaForm = false;

    public array $areaForm = [
        'name' => '',
        'sigla' => '',
        'active' => true,
        'manager_sicode_id' => null,
    ];

    public string $managerSearch = '';
    public array $managerResults = [];

    public string $executorSearch = '';
    public array $executorResults = [];

    public function mount(): void
    {
        $this->ensureAdminAccess();
    }

    public function render()
    {
        $areas = Area::query()
            ->with(['manager:id,name,email'])
            ->orderBy('name')
            ->get(['id', 'name', 'sigla', 'active', 'manager_sicode_id']);

        $areas = $areas->map(function ($area) {
            $executorIds = DB::table('area_user')
                ->where('area_id', $area->id)
                ->pluck('sicode_id');

            $area->executors_list = $executorIds->isEmpty()
                ? collect()
                : SicodeUser::query()
                    ->whereIn('id', $executorIds)
                    ->orderBy('name')
                    ->get(['id', 'name', 'email'])
                    ->map(function ($user) use ($area) {
                        $pivot = DB::table('area_user')
                            ->where('area_id', $area->id)
                            ->where('sicode_id', $user->id)
                            ->first();

                        $user->pivot = (object) [
                            'role_in_area' => $pivot->role_in_area ?? 'member',
                        ];

                        return $user;
                    });

            return $area;
        });

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

    public function toggleAreaForm(): void
    {
        $this->resetAreaForm();
        $this->showAreaForm = !$this->showAreaForm;
    }

    public function startCreateArea(): void
    {
        $this->showAreaForm = true;
        $this->resetAreaForm();
        $this->selectedArea = null;
    }

    public function selectArea(int $areaId): void
    {
        $this->selectedArea = $areaId;
        $this->executorResults = [];
        $this->executorSearch = '';
    }

    public function saveArea(): void
    {
        $data = $this->validate([
            'areaForm.name' => ['required', 'string', 'max:120', Rule::unique('areas', 'name')->ignore($this->selectedArea)],
            'areaForm.sigla' => ['required', 'string', 'max:10', Rule::unique('areas', 'sigla')->ignore($this->selectedArea)],
            'areaForm.manager_sicode_id' => ['nullable', 'uuid'],
        ], [], [
            'areaForm.name' => 'nome',
            'areaForm.sigla' => 'sigla',
            'areaForm.manager_sicode_id' => 'gestor',
        ]);

        $payload = $data['areaForm'];
        $payload['sigla'] = strtoupper($payload['sigla']);

        $area = Area::updateOrCreate(
            ['id' => $this->selectedArea],
            $payload
        );

        $this->selectedArea = $area->id;
        $this->showAreaForm = false;
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Área salva com sucesso.',
            'toast' => true,
        ]);
    }

    public function toggleAreaActive(int $areaId): void
    {
        $area = Area::findOrFail($areaId);
        $area->active = ! $area->active;
        $area->save();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $area->active ? 'Área ativada' : 'Área desativada',
            'toast' => true,
        ]);
    }

    public function searchManagers(): void
    {
        $this->managerResults = $this->searchSicodeUsers($this->managerSearch);
    }

    public function assignManager(string $sicodeId): void
    {
        $this->areaForm['manager_sicode_id'] = $sicodeId;
        $user = SicodeUser::find($sicodeId);
        $this->managerSearch = $user?->name ?? '';
        $this->managerResults = [];
    }

    public function clearManager(): void
    {
        $this->areaForm['manager_sicode_id'] = null;
        $this->managerSearch = '';
        $this->managerResults = [];
    }

    public function searchExecutors(): void
    {
        $this->executorResults = $this->searchSicodeUsers($this->executorSearch, $this->selectedArea);
    }

    public function addExecutor(string $sicodeId): void
    {
        if (!$this->selectedArea) {
            return;
        }

        DB::table('area_user')->updateOrInsert(
            ['area_id' => $this->selectedArea, 'sicode_id' => $sicodeId],
            ['role_in_area' => 'member', 'updated_at' => now(), 'created_at' => now()]
        );

        $this->executorResults = [];
        $this->executorSearch = '';

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Executor adicionado à área.',
            'toast' => true,
        ]);
    }

    public function removeExecutor(string $sicodeId): void
    {
        if (!$this->selectedArea) {
            return;
        }

        DB::table('area_user')
            ->where('area_id', $this->selectedArea)
            ->where('sicode_id', $sicodeId)
            ->delete();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Executor removido da área.',
            'toast' => true,
        ]);
    }

    private function resetAreaForm(): void
    {
        $this->areaForm = [
            'name' => '',
            'sigla' => '',
            'active' => true,
            'manager_sicode_id' => null,
        ];
        $this->managerSearch = '';
        $this->managerResults = [];
    }

    private function searchSicodeUsers(string $term, ?int $areaId = null): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 3) {
            return [];
        }

        return SicodeUser::query()
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->toArray();
    }
}
