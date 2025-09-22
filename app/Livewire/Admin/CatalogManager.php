<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Area;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\TicketType;
use App\Models\WorkCalendar;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CatalogManager extends Component
{
    use ChecksAdminAccess;

    public ?int $selectedAreaId = null;
    public ?int $selectedTypeId = null;
    public ?int $selectedCategoryId = null;

    /** Area form state */
    public bool $showAreaForm = false;
    public array $areaForm = [
        'name' => '',
        'sigla' => '',
        'active' => true,
        'work_calendar_id' => null,
    ];
    public ?Area $areaEditing = null;

    public array $calendars = [];

    /** Ticket type form state */
    public bool $showTypeForm = false;
    public array $typeForm = [
        'area_id' => null,
        'name' => '',
        'active' => true,
    ];
    public ?TicketType $typeEditing = null;

    /** Category form state */
    public bool $showCategoryForm = false;
    public array $categoryForm = [
        'ticket_type_id' => null,
        'name' => '',
        'active' => true,
    ];
    public ?Category $categoryEditing = null;

    /** Subcategory form state */
    public bool $showSubcategoryForm = false;
    public array $subcategoryForm = [
        'category_id' => null,
        'name' => '',
        'active' => true,
    ];
    public ?Subcategory $subcategoryEditing = null;

    protected array $areaRules = [
        'areaForm.name' => 'required|string|max:120',
        'areaForm.sigla' => 'required|string|max:10',
        'areaForm.active' => 'boolean',
        'areaForm.work_calendar_id' => 'nullable|exists:work_calendars,id',
    ];

    protected array $typeRules = [
        'typeForm.area_id' => 'required|exists:areas,id',
        'typeForm.name' => 'required|string|max:120',
        'typeForm.active' => 'boolean',
    ];

    protected array $categoryRules = [
        'categoryForm.ticket_type_id' => 'required|exists:ticket_types,id',
        'categoryForm.name' => 'required|string|max:120',
        'categoryForm.active' => 'boolean',
    ];

    protected array $subcategoryRules = [
        'subcategoryForm.category_id' => 'required|exists:categories,id',
        'subcategoryForm.name' => 'required|string|max:120',
        'subcategoryForm.active' => 'boolean',
    ];

    public function mount(): void
    {
        $this->ensureAdminAccess();

        $this->selectedAreaId = Area::query()->orderBy('name')->value('id');
        $this->syncNestedSelections();
    }

    public function render()
    {
        $areas = Area::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sigla', 'active']);

        if (!$areas->pluck('id')->contains($this->selectedAreaId)) {
            $this->selectedAreaId = $areas->first()?->id;
            $this->syncNestedSelections();
        }

        $types = collect();
        $categories = collect();
        $subcategories = collect();

        if ($this->selectedAreaId) {
            $types = TicketType::query()
                ->where('area_id', $this->selectedAreaId)
                ->orderBy('name')
                ->get(['id', 'name', 'active']);

            $validTypeIds = $types->pluck('id');

            if ($validTypeIds->isNotEmpty()) {
                if (!$validTypeIds->contains($this->selectedTypeId)) {
                    $this->selectedTypeId = $validTypeIds->first();
                }
            } else {
                $this->selectedTypeId = null;
            }
        } else {
            $this->selectedTypeId = null;
        }

        if ($this->selectedTypeId) {
            $categories = Category::query()
                ->where('ticket_type_id', $this->selectedTypeId)
                ->orderBy('name')
                ->get(['id', 'name', 'active']);
        } else {
            $this->selectedCategoryId = null;
        }

        $validCategoryIds = $categories->pluck('id');

        if ($validCategoryIds->isNotEmpty()) {
            if (!$validCategoryIds->contains($this->selectedCategoryId)) {
                $this->selectedCategoryId = $validCategoryIds->first();
            }
        } else {
            $this->selectedCategoryId = null;
        }

        if ($this->selectedCategoryId) {
            $subcategories = Subcategory::query()
                ->where('category_id', $this->selectedCategoryId)
                ->orderBy('name')
                ->get(['id', 'name', 'active']);
        }

        $this->syncNestedSelections();

        $this->calendars = WorkCalendar::query()->orderBy('name')->get(['id', 'name'])->toArray();

        return view('livewire.admin.catalog-manager', [
            'areas' => $areas,
            'types' => $types,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'calendars' => $this->calendars,
        ]);
    }

    public function selectArea(int $areaId): void
    {
        if ($areaId === $this->selectedAreaId) {
            return;
        }

        $this->selectedAreaId = $areaId;
        $this->selectedTypeId = TicketType::query()
            ->where('area_id', $areaId)
            ->orderBy('name')
            ->value('id');
        $this->selectedCategoryId = null;
        $this->resetTypeForm();
        $this->resetCategoryForm();
        $this->resetSubcategoryForm();
        $this->resetValidation();
    }

    public function selectType(int $typeId): void
    {
        if ($typeId === $this->selectedTypeId) {
            return;
        }

        $type = TicketType::query()
            ->where('area_id', $this->selectedAreaId)
            ->findOrFail($typeId);

        $this->selectedTypeId = $type->getKey();
        $this->selectedCategoryId = null;
        $this->resetCategoryForm();
        $this->resetSubcategoryForm();
        $this->resetValidation();
    }

    public function selectCategory(int $categoryId): void
    {
        $category = Category::query()
            ->where('ticket_type_id', $this->selectedTypeId)
            ->findOrFail($categoryId);

        $this->selectedCategoryId = $category->getKey();
        $this->resetSubcategoryForm();
        $this->resetValidation();
    }

    /* === Areas === */

    public function openAreaCreate(): void
    {
        $this->areaEditing = null;
        $this->areaForm = [
            'name' => '',
            'sigla' => '',
            'active' => true,
            'work_calendar_id' => null,
        ];
        $this->showAreaForm = true;
        $this->resetValidation();
    }

    public function openAreaEdit(int $areaId): void
    {
        $area = Area::findOrFail($areaId);
        $this->areaEditing = $area;
        $this->areaForm = Arr::only($area->toArray(), ['name', 'sigla', 'active', 'work_calendar_id']);
        $this->showAreaForm = true;
        $this->resetValidation();
    }

    public function saveArea(): void
    {
        $data = $this->validate($this->areaRules, [], [
            'areaForm.name' => 'nome',
            'areaForm.sigla' => 'sigla',
        ]);

        $payload = $data['areaForm'];
        $payload['name'] = trim($payload['name']);
        $payload['sigla'] = strtoupper(trim($payload['sigla']));
        $payload['work_calendar_id'] = $payload['work_calendar_id'] ?: null;
        $this->areaForm['sigla'] = $payload['sigla'];
        $this->areaForm['name'] = $payload['name'];

        $uniqueRule = Rule::unique('areas', 'sigla');
        if ($this->areaEditing) {
            $uniqueRule = $uniqueRule->ignore($this->areaEditing->getKey());
        }

        $uniqueNameRule = Rule::unique('areas', 'name');
        if ($this->areaEditing) {
            $uniqueNameRule = $uniqueNameRule->ignore($this->areaEditing->getKey());
        }

        $this->validate([
            'areaForm.sigla' => [$uniqueRule],
            'areaForm.name' => [$uniqueNameRule],
        ], [], [
            'areaForm.sigla' => 'sigla',
            'areaForm.name' => 'nome',
        ]);

        $area = $this->areaEditing ?? new Area();
        $area->fill($payload);
        $area->save();

        $this->selectedAreaId = $area->getKey();
        $this->showAreaForm = false;
        $this->areaEditing = null;

        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Área salva',
            'toast' => true,
        ]);
    }

    public function cancelArea(): void
    {
        $this->showAreaForm = false;
        $this->areaEditing = null;
        $this->resetValidation();
    }

    public function confirmAreaDelete(int $areaId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover área?',
            'text' => 'É preciso remover ou transferir dependências antes da exclusão.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'deleteArea',
            'payload' => ['area_id' => $areaId],
            'componentId' => $this->getId(),
        ]);
    }

    public function deleteArea($payload = null): void
    {
        $areaId = is_array($payload) ? ($payload['area_id'] ?? null) : $payload;
        if (!$areaId) {
            return;
        }

        $area = Area::withCount(['ticketTypes', 'categories', 'workflows', 'tickets'])->find($areaId);

        if (!$area) {
            return;
        }

        if ($area->ticket_types_count || $area->categories_count || $area->workflows_count || $area->tickets_count) {
            $this->dispatch('sweet-alert', [
                'type' => 'error',
                'title' => 'Área não pode ser removida',
                'text' => 'Remova ou transfira os itens vinculados antes de excluir.',
            ]);
            return;
        }

        $area->delete();

        if ($this->selectedAreaId === $area->getKey()) {
            $this->selectedAreaId = Area::query()->orderBy('name')->value('id');
            $this->selectedTypeId = $this->selectedAreaId
                ? TicketType::query()->where('area_id', $this->selectedAreaId)->orderBy('name')->value('id')
                : null;
            $this->selectedCategoryId = null;
            $this->resetTypeForm();
            $this->resetCategoryForm();
            $this->resetSubcategoryForm();
        }

        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Área removida',
            'toast' => true,
        ]);
    }

    public function toggleAreaActive(int $areaId): void
    {
        $area = Area::findOrFail($areaId);
        $area->active = ! $area->active;
        $area->save();
        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $area->active ? 'Área ativada' : 'Área desativada',
            'toast' => true,
        ]);
    }

    /* === Ticket types === */

    public function openTypeCreate(): void
    {
        if (!$this->selectedAreaId) {
            $this->addError('type.area', 'Selecione uma área antes de cadastrar tipos.');
            $this->dispatch('sweet-alert', [
                'type' => 'warning',
                'title' => 'Selecione uma área',
                'text' => 'Escolha uma área para vincular o tipo de ticket.',
                'toast' => true,
            ]);
            return;
        }

        $this->typeEditing = null;
        $this->typeForm = [
            'area_id' => $this->selectedAreaId,
            'name' => '',
            'active' => true,
        ];
        $this->showTypeForm = true;
        $this->resetValidation();
    }

    public function openTypeEdit(int $typeId): void
    {
        $type = TicketType::findOrFail($typeId);
        $this->typeEditing = $type;
        $this->typeForm = Arr::only($type->toArray(), ['area_id', 'name', 'active']);
        $this->showTypeForm = true;
        $this->resetValidation();
    }

    public function saveType(): void
    {
        if (!$this->typeForm['area_id']) {
            $this->typeForm['area_id'] = $this->selectedAreaId;
        }

        $data = $this->validate($this->typeRules, [], [
            'typeForm.area_id' => 'área',
            'typeForm.name' => 'nome',
        ]);

        $payload = $data['typeForm'];
        $payload['name'] = trim($payload['name']);
        $this->typeForm['name'] = $payload['name'];

        $uniqueRule = Rule::unique('ticket_types', 'name')->where('area_id', $payload['area_id']);
        if ($this->typeEditing) {
            $uniqueRule = $uniqueRule->ignore($this->typeEditing->getKey());
        }

        $this->validate([
            'typeForm.name' => [$uniqueRule],
        ], [], [
            'typeForm.name' => 'nome',
        ]);

        $type = $this->typeEditing ?? new TicketType();
        $type->fill($payload);
        $type->save();

        $this->selectedAreaId = $type->area_id;
        $this->showTypeForm = false;
        $this->typeEditing = null;

        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Tipo de ticket salvo',
            'toast' => true,
        ]);
    }

    public function cancelType(): void
    {
        $this->showTypeForm = false;
        $this->typeEditing = null;
        $this->resetValidation();
    }

    public function confirmTypeDelete(int $typeId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover tipo de ticket?',
            'text' => 'Tickets existentes manterão o tipo salvo.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'deleteType',
            'payload' => ['type_id' => $typeId],
            'componentId' => $this->getId(),
        ]);
    }

    public function deleteType($payload = null): void
    {
        $typeId = is_array($payload) ? ($payload['type_id'] ?? null) : $payload;
        if (!$typeId) {
            return;
        }

        $type = TicketType::find($typeId);
        if ($type) {
            $type->delete();
        }

        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Tipo de ticket removido',
            'toast' => true,
        ]);
    }

    public function toggleTypeActive(int $typeId): void
    {
        $type = TicketType::findOrFail($typeId);
        $type->active = ! $type->active;
        $type->save();
        $this->notifyCatalogChanged();
    }

    /* === Categories === */

    public function openCategoryCreate(): void
    {
        if (!$this->selectedTypeId) {
            $this->addError('category.type', 'Selecione um tipo de ticket para cadastrar categorias.');
            $this->dispatch('sweet-alert', [
                'type' => 'warning',
                'title' => 'Selecione um tipo',
                'text' => 'Escolha um tipo de ticket antes de criar a categoria.',
                'toast' => true,
            ]);
            return;
        }

        $this->categoryEditing = null;
        $this->categoryForm = [
            'ticket_type_id' => $this->selectedTypeId,
            'name' => '',
            'active' => true,
        ];
        $this->showCategoryForm = true;
        $this->resetValidation();
    }

    public function openCategoryEdit(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $this->categoryEditing = $category;
        $this->selectedAreaId = $category->area_id;
        $this->selectedTypeId = $category->ticket_type_id;
        $this->selectedCategoryId = $category->getKey();
        $this->categoryForm = Arr::only($category->toArray(), ['ticket_type_id', 'name', 'active']);
        $this->showCategoryForm = true;
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        if (!$this->categoryForm['ticket_type_id']) {
            $this->categoryForm['ticket_type_id'] = $this->selectedTypeId;
        }

        $data = $this->validate($this->categoryRules, [], [
            'categoryForm.ticket_type_id' => 'tipo de ticket',
            'categoryForm.name' => 'nome',
        ]);

        $payload = $data['categoryForm'];
        $payload['name'] = trim($payload['name']);
        $this->categoryForm['name'] = $payload['name'];

        $type = TicketType::findOrFail($payload['ticket_type_id']);
        $payload['area_id'] = $type->area_id;

        $uniqueRule = Rule::unique('categories', 'name')->where('ticket_type_id', $payload['ticket_type_id']);
        if ($this->categoryEditing) {
            $uniqueRule = $uniqueRule->ignore($this->categoryEditing->getKey());
        }

        $this->validate([
            'categoryForm.name' => [$uniqueRule],
        ], [], [
            'categoryForm.name' => 'nome',
        ]);

        $category = $this->categoryEditing ?? new Category();
        $category->fill($payload);
        $category->save();

        $this->selectedAreaId = $category->area_id;
        $this->selectedTypeId = $category->ticket_type_id;
        $this->selectedCategoryId = $category->getKey();
        $this->showCategoryForm = false;
        $this->categoryEditing = null;

        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Categoria salva',
            'toast' => true,
        ]);
    }

    public function cancelCategory(): void
    {
        $this->showCategoryForm = false;
        $this->categoryEditing = null;
        $this->resetValidation();
    }

    public function confirmCategoryDelete(int $categoryId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover categoria?',
            'text' => 'Remova as subcategorias associadas antes de excluir.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'deleteCategory',
            'payload' => ['category_id' => $categoryId],
            'componentId' => $this->getId(),
        ]);
    }

    public function deleteCategory($payload = null): void
    {
        $categoryId = is_array($payload) ? ($payload['category_id'] ?? null) : $payload;
        if (!$categoryId) {
            return;
        }

        $category = Category::withCount('subcategories')->find($categoryId);

        if (!$category) {
            return;
        }

        if ($category->subcategories_count) {
            $this->dispatch('sweet-alert', [
                'type' => 'error',
                'title' => 'Categoria possui subcategorias',
                'text' => 'Remova as subcategorias vinculadas antes de excluir.',
            ]);
            return;
        }

        $category->delete();

        if ($this->selectedCategoryId === $category->getKey()) {
            $this->selectedCategoryId = null;
        }

        $this->resetSubcategoryForm();
        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Categoria removida',
            'toast' => true,
        ]);
    }

    public function toggleCategoryActive(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $category->active = ! $category->active;
        $category->save();
        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $category->active ? 'Categoria ativada' : 'Categoria desativada',
            'toast' => true,
        ]);
    }

    /* === Subcategories === */

    public function openSubcategoryCreate(): void
    {
        if (!$this->selectedCategoryId) {
            $this->addError('subcategory.category', 'Selecione uma categoria para cadastrar subcategorias.');
            $this->dispatch('sweet-alert', [
                'type' => 'warning',
                'title' => 'Selecione uma categoria',
                'text' => 'Escolha uma categoria para vincular a subcategoria.',
                'toast' => true,
            ]);
            return;
        }

        $this->subcategoryEditing = null;
        $this->subcategoryForm = [
            'category_id' => $this->selectedCategoryId,
            'name' => '',
            'active' => true,
        ];
        $this->showSubcategoryForm = true;
        $this->resetValidation();
    }

    public function openSubcategoryEdit(int $subcategoryId): void
    {
        $subcategory = Subcategory::findOrFail($subcategoryId);
        $this->subcategoryEditing = $subcategory;
        $this->selectedCategoryId = $subcategory->category_id;
        $this->subcategoryForm = Arr::only($subcategory->toArray(), ['category_id', 'name', 'active']);
        $this->showSubcategoryForm = true;
        $this->resetValidation();
    }

    public function saveSubcategory(): void
    {
        if (!$this->subcategoryForm['category_id']) {
            $this->subcategoryForm['category_id'] = $this->selectedCategoryId;
        }

        $data = $this->validate($this->subcategoryRules, [], [
            'subcategoryForm.category_id' => 'categoria',
            'subcategoryForm.name' => 'nome',
        ]);

        $payload = $data['subcategoryForm'];
        $payload['name'] = trim($payload['name']);
        $this->subcategoryForm['name'] = $payload['name'];

        $uniqueRule = Rule::unique('subcategories', 'name')->where('category_id', $payload['category_id']);
        if ($this->subcategoryEditing) {
            $uniqueRule = $uniqueRule->ignore($this->subcategoryEditing->getKey());
        }

        $this->validate([
            'subcategoryForm.name' => [$uniqueRule],
        ], [], [
            'subcategoryForm.name' => 'nome',
        ]);

        $subcategory = $this->subcategoryEditing ?? new Subcategory();
        $subcategory->fill($payload);
        $subcategory->save();

        $this->selectedCategoryId = $subcategory->category_id;
        $this->showSubcategoryForm = false;
        $this->subcategoryEditing = null;

        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Subcategoria salva',
            'toast' => true,
        ]);
    }

    public function cancelSubcategory(): void
    {
        $this->showSubcategoryForm = false;
        $this->subcategoryEditing = null;
        $this->resetValidation();
    }

    public function confirmSubcategoryDelete(int $subcategoryId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover subcategoria?',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'deleteSubcategory',
            'payload' => ['subcategory_id' => $subcategoryId],
            'componentId' => $this->getId(),
        ]);
    }

    public function deleteSubcategory($payload = null): void
    {
        $subcategoryId = is_array($payload) ? ($payload['subcategory_id'] ?? null) : $payload;
        if (!$subcategoryId) {
            return;
        }

        $subcategory = Subcategory::find($subcategoryId);
        if ($subcategory) {
            $subcategory->delete();
        }

        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Subcategoria removida',
            'toast' => true,
        ]);
    }

    public function toggleSubcategoryActive(int $subcategoryId): void
    {
        $subcategory = Subcategory::findOrFail($subcategoryId);
        $subcategory->active = ! $subcategory->active;
        $subcategory->save();
        $this->notifyCatalogChanged();
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => $subcategory->active ? 'Subcategoria ativada' : 'Subcategoria desativada',
            'toast' => true,
        ]);
    }

    private function resetTypeForm(): void
    {
        $this->typeEditing = null;
        $this->showTypeForm = false;
        $this->typeForm = [
            'area_id' => $this->selectedAreaId,
            'name' => '',
            'active' => true,
        ];
    }

    private function resetCategoryForm(): void
    {
        $this->categoryEditing = null;
        $this->showCategoryForm = false;
        $this->categoryForm = [
            'ticket_type_id' => $this->selectedTypeId,
            'name' => '',
            'active' => true,
        ];
    }

    private function resetSubcategoryForm(): void
    {
        $this->subcategoryEditing = null;
        $this->showSubcategoryForm = false;
        $this->subcategoryForm = [
            'category_id' => $this->selectedCategoryId,
            'name' => '',
            'active' => true,
        ];
    }

    private function syncNestedSelections(): void
    {
        $this->typeForm['area_id'] = $this->selectedAreaId;
        $this->categoryForm['ticket_type_id'] = $this->selectedTypeId;
        $this->subcategoryForm['category_id'] = $this->selectedCategoryId;
    }

    private function notifyCatalogChanged(): void
    {
        $this->dispatch('catalog-data-updated');
    }
}
