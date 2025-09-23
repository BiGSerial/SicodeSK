<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Area;
use App\Models\Category;
use App\Models\SicodeUser;
use App\Models\Subcategory;
use App\Models\TicketType;
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

    /** @var array<string,string> */
    public array $areaRoleOptions = [
        'member' => 'Executor',
        'triage' => 'Triagem',
        'approver' => 'Aprovador',
        'viewer' => 'Observador',
    ];

    public ?int $scopeTicketTypeId = null;
    public ?int $scopeCategoryId = null;
    public ?int $scopeSubcategoryId = null;

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

        $areaIds = $areas->pluck('id');

        $pivotRows = $areaIds->isEmpty()
            ? collect()
            : DB::table('area_user')
                ->whereIn('area_id', $areaIds)
                ->select('id', 'area_id', 'sicode_id', 'role_in_area', 'created_at')
                ->orderBy('created_at')
                ->get();

        $scopeRows = $areaIds->isEmpty()
            ? collect()
            : DB::table('area_user_scopes')
                ->whereIn('area_id', $areaIds)
                ->select('id', 'area_id', 'sicode_id', 'ticket_type_id', 'category_id', 'subcategory_id')
                ->get();

        $userIds = $pivotRows->pluck('sicode_id')->filter()->unique();

        $users = $userIds->isEmpty()
            ? collect()
            : SicodeUser::query()
                ->whereIn('id', $userIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->keyBy('id');

        $ticketTypeList = $areaIds->isEmpty()
            ? collect()
            : TicketType::query()
                ->whereIn('area_id', $areaIds)
                ->orderBy('name')
                ->get(['id', 'area_id', 'name']);

        $categoryList = $areaIds->isEmpty()
            ? collect()
            : Category::query()
                ->whereIn('area_id', $areaIds)
                ->orderBy('name')
                ->get(['id', 'area_id', 'ticket_type_id', 'name']);

        $subcategoryList = $categoryList->isEmpty()
            ? collect()
            : Subcategory::query()
                ->whereIn('category_id', $categoryList->pluck('id'))
                ->orderBy('name')
                ->get(['id', 'category_id', 'name']);

        $ticketTypesByArea = $ticketTypeList->groupBy('area_id');
        $categoriesByArea = $categoryList->groupBy('area_id');
        $subcategoriesByCategory = $subcategoryList->groupBy('category_id');

        $typeNameMap = $ticketTypeList->pluck('name', 'id')->all();
        $categoryNameMap = $categoryList->pluck('name', 'id')->all();
        $subcategoryNameMap = $subcategoryList->pluck('name', 'id')->all();

        $categoryToType = $categoryList->mapWithKeys(fn ($category) => [$category->id => $category->ticket_type_id])->all();
        $subcategoryToCategory = $subcategoryList->mapWithKeys(fn ($subcategory) => [$subcategory->id => $subcategory->category_id])->all();

        $pivotByArea = $pivotRows->groupBy('area_id');
        $scopesByArea = $scopeRows->groupBy('area_id');

        $self = $this;

        $areas = $areas->map(function ($area) use ($pivotByArea, $users, $scopesByArea, $ticketTypesByArea, $categoriesByArea, $subcategoriesByCategory, $typeNameMap, $categoryNameMap, $subcategoryNameMap, $categoryToType, $subcategoryToCategory, $self) {
            $areaPivots = $pivotByArea->get($area->id, collect());
            $areaScopes = $scopesByArea->get($area->id, collect())->groupBy('sicode_id');

            $area->setAttribute('ticket_types', $ticketTypesByArea->get($area->id, collect())
                ->map(fn ($type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                ])->values()->all());

            $area->setAttribute('categories', $categoriesByArea->get($area->id, collect())
                ->map(function ($category) use ($subcategoriesByCategory) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'ticket_type_id' => $category->ticket_type_id,
                        'subcategories' => $subcategoriesByCategory->get($category->id, collect())
                            ->map(fn ($sub) => [
                                'id' => $sub->id,
                                'name' => $sub->name,
                            ])->values()->all(),
                    ];
                })->values()->all());

            $executors = $areaPivots->map(function ($pivot) use ($users, $areaScopes, $typeNameMap, $categoryNameMap, $subcategoryNameMap, $categoryToType, $subcategoryToCategory, $self) {
                $user = $users->get($pivot->sicode_id);

                if (!$user) {
                    return null;
                }

                $scopes = $areaScopes->get($pivot->sicode_id, collect())
                    ->map(function ($scope) use ($typeNameMap, $categoryNameMap, $subcategoryNameMap, $categoryToType, $subcategoryToCategory, $self) {
                        $ticketTypeId = $scope->ticket_type_id ? (int) $scope->ticket_type_id : null;
                        $categoryId = $scope->category_id ? (int) $scope->category_id : null;
                        $subcategoryId = $scope->subcategory_id ? (int) $scope->subcategory_id : null;

                        [$normalizedType, $normalizedCategory, $normalizedSubcategory] = $self->normalizeScopeValues(
                            $ticketTypeId,
                            $categoryId,
                            $subcategoryId,
                            $categoryToType,
                            $subcategoryToCategory
                        );

                        return [
                            'id' => $scope->id,
                            'ticket_type_id' => $normalizedType,
                            'category_id' => $normalizedCategory,
                            'subcategory_id' => $normalizedSubcategory,
                            'label' => $self->formatScopeLabel($normalizedType, $normalizedCategory, $normalizedSubcategory, $typeNameMap, $categoryNameMap, $subcategoryNameMap),
                            'key' => $self->makeScopeKey($normalizedType, $normalizedCategory, $normalizedSubcategory),
                        ];
                    })->values()->all();

                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $pivot->role_in_area ?? 'member',
                    'scopes' => $scopes,
                    'scope_keys' => array_map(fn ($scope) => $scope['key'], $scopes),
                ];
            })->filter()->values();

            $area->setAttribute('executors_list', $executors);

            return $area;
        });

        [$normalizedType, $normalizedCategory, $normalizedSubcategory] = $this->normalizeScopeValues(
            $this->scopeTicketTypeId ? (int) $this->scopeTicketTypeId : null,
            $this->scopeCategoryId ? (int) $this->scopeCategoryId : null,
            $this->scopeSubcategoryId ? (int) $this->scopeSubcategoryId : null,
            $categoryToType,
            $subcategoryToCategory
        );

        $scopeKey = $this->makeScopeKey($normalizedType, $normalizedCategory, $normalizedSubcategory);

        return view('livewire.admin.organization', [
            'areas' => $areas,
            'scopeContext' => [
                'key' => $scopeKey,
                'ticket_type_id' => $normalizedType,
                'category_id' => $normalizedCategory,
                'subcategory_id' => $normalizedSubcategory,
            ],
        ]);
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
        $this->scopeTicketTypeId = null;
        $this->scopeCategoryId = null;
        $this->scopeSubcategoryId = null;
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

        DB::table('area_user_scopes')
            ->where('area_id', $this->selectedArea)
            ->where('sicode_id', $sicodeId)
            ->delete();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Executor removido da área.',
            'toast' => true,
        ]);
    }

    public function setExecutorRole(string $sicodeId, string $role): void
    {
        if (!$this->selectedArea) {
            return;
        }

        if (!array_key_exists($role, $this->areaRoleOptions)) {
            return;
        }

        $updated = DB::table('area_user')
            ->where('area_id', $this->selectedArea)
            ->where('sicode_id', $sicodeId)
            ->update([
                'role_in_area' => $role,
                'updated_at' => now(),
            ]);

        if ($updated) {
            $this->dispatch('sweet-alert', [
                'type' => 'success',
                'title' => 'Função atualizada.',
                'toast' => true,
            ]);
        }
    }

    public function updatedScopeTicketTypeId($value): void
    {
        $this->scopeTicketTypeId = $value !== '' ? (int) $value : null;
        $this->scopeCategoryId = null;
        $this->scopeSubcategoryId = null;
    }

    public function updatedScopeCategoryId($value): void
    {
        if (!$this->selectedArea) {
            $this->scopeCategoryId = null;
            $this->scopeSubcategoryId = null;
            return;
        }

        $categoryId = $value !== '' ? (int) $value : null;

        if (!$categoryId) {
            $this->scopeCategoryId = null;
            $this->scopeSubcategoryId = null;
            return;
        }

        $category = Category::find($categoryId);

        if (!$category || $category->area_id !== $this->selectedArea) {
            $this->scopeCategoryId = null;
            $this->scopeSubcategoryId = null;
            return;
        }

        $this->scopeCategoryId = $categoryId;
        $this->scopeSubcategoryId = null;

        if ($category->ticket_type_id) {
            $this->scopeTicketTypeId = $category->ticket_type_id;
        }
    }

    public function updatedScopeSubcategoryId($value): void
    {
        if (!$this->selectedArea) {
            $this->scopeSubcategoryId = null;
            return;
        }

        $subcategoryId = $value !== '' ? (int) $value : null;

        if (!$subcategoryId) {
            $this->scopeSubcategoryId = null;
            return;
        }

        $subcategory = Subcategory::find($subcategoryId);

        if (!$subcategory) {
            $this->scopeSubcategoryId = null;
            return;
        }

        $category = Category::find($subcategory->category_id);

        if (!$category || $category->area_id !== $this->selectedArea) {
            $this->scopeSubcategoryId = null;
            return;
        }

        $this->scopeSubcategoryId = $subcategoryId;
        $this->scopeCategoryId = $category->id;

        if ($category->ticket_type_id) {
            $this->scopeTicketTypeId = $category->ticket_type_id;
        }
    }

    public function clearScopeSelection(): void
    {
        $this->scopeTicketTypeId = null;
        $this->scopeCategoryId = null;
        $this->scopeSubcategoryId = null;
    }

    public function toggleScopeAssignment(string $sicodeId): void
    {
        if (!$this->selectedArea) {
            return;
        }

        $exists = DB::table('area_user')
            ->where('area_id', $this->selectedArea)
            ->where('sicode_id', $sicodeId)
            ->exists();

        if (!$exists) {
            $this->dispatch('sweet-alert', [
                'type' => 'error',
                'title' => 'Vincule o executor à área antes de atribuir escopos.',
                'toast' => true,
            ]);
            return;
        }

        [$ticketTypeId, $categoryId, $subcategoryId] = $this->normalizedScopeSelection();

        $key = [
            'area_id' => $this->selectedArea,
            'sicode_id' => $sicodeId,
            'ticket_type_id' => $ticketTypeId,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
        ];

        $existing = DB::table('area_user_scopes')->where($key)->first();

        if ($existing) {
            DB::table('area_user_scopes')->where('id', $existing->id)->delete();

            $this->dispatch('sweet-alert', [
                'type' => 'info',
                'title' => 'Escopo removido do executor.',
                'toast' => true,
            ]);

            return;
        }

        DB::table('area_user_scopes')->insert(array_merge($key, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Escopo atribuído ao executor.',
            'toast' => true,
        ]);
    }

    public function removeScope(int $scopeId): void
    {
        if (!$this->selectedArea) {
            return;
        }

        $scope = DB::table('area_user_scopes')->where('id', $scopeId)->first();

        if (!$scope || (int) $scope->area_id !== $this->selectedArea) {
            return;
        }

        DB::table('area_user_scopes')->where('id', $scopeId)->delete();

        $this->dispatch('sweet-alert', [
            'type' => 'info',
            'title' => 'Escopo removido.',
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

    private function normalizedScopeSelection(): array
    {
        if (!$this->selectedArea) {
            return [null, null, null];
        }

        $ticketTypeId = $this->scopeTicketTypeId ? (int) $this->scopeTicketTypeId : null;
        $categoryId = $this->scopeCategoryId ? (int) $this->scopeCategoryId : null;
        $subcategoryId = $this->scopeSubcategoryId ? (int) $this->scopeSubcategoryId : null;

        if ($subcategoryId) {
            $subcategory = Subcategory::find($subcategoryId);

            if (!$subcategory) {
                $subcategoryId = null;
            } else {
                $categoryId = $subcategory->category_id;
            }
        }

        if ($categoryId) {
            $category = Category::find($categoryId);

            if (!$category || $category->area_id !== $this->selectedArea) {
                $categoryId = null;
                $subcategoryId = null;
            } else {
                $ticketTypeId = $category->ticket_type_id ?: $ticketTypeId;
            }
        }

        if ($ticketTypeId) {
            $type = TicketType::find($ticketTypeId);

            if (!$type || $type->area_id !== $this->selectedArea) {
                $ticketTypeId = null;
            }
        }

        return [$ticketTypeId, $categoryId, $subcategoryId];
    }

    private function normalizeScopeValues(?int $ticketTypeId, ?int $categoryId, ?int $subcategoryId, array $categoryToType, array $subcategoryToCategory): array
    {
        if ($subcategoryId && isset($subcategoryToCategory[$subcategoryId])) {
            $categoryId = $subcategoryToCategory[$subcategoryId];
        }

        if ($categoryId && isset($categoryToType[$categoryId])) {
            $ticketTypeId = $categoryToType[$categoryId];
        }

        return [$ticketTypeId, $categoryId, $subcategoryId];
    }

    private function makeScopeKey(?int $ticketTypeId, ?int $categoryId, ?int $subcategoryId): string
    {
        return sprintf('type:%s|cat:%s|sub:%s', $ticketTypeId ?? '0', $categoryId ?? '0', $subcategoryId ?? '0');
    }

    private function formatScopeLabel(
        ?int $ticketTypeId,
        ?int $categoryId,
        ?int $subcategoryId,
        array $typeNames,
        array $categoryNames,
        array $subcategoryNames
    ): string {
        if ($subcategoryId && isset($subcategoryNames[$subcategoryId])) {
            $typeLabel = $ticketTypeId && isset($typeNames[$ticketTypeId]) ? $typeNames[$ticketTypeId] : 'Tipo';
            $categoryLabel = $categoryId && isset($categoryNames[$categoryId]) ? $categoryNames[$categoryId] : 'Categoria';
            $subcategoryLabel = $subcategoryNames[$subcategoryId];

            return sprintf('%s • %s › %s', $typeLabel, $categoryLabel, $subcategoryLabel);
        }

        if ($categoryId && isset($categoryNames[$categoryId])) {
            $categoryLabel = $categoryNames[$categoryId];

            if ($ticketTypeId && isset($typeNames[$ticketTypeId])) {
                return sprintf('%s • %s', $typeNames[$ticketTypeId], $categoryLabel);
            }

            return $categoryLabel;
        }

        if ($ticketTypeId && isset($typeNames[$ticketTypeId])) {
            return $typeNames[$ticketTypeId];
        }

        return 'Todos os tipos da área';
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
