@php
    $calendarIndex = collect($calendars ?? [])->keyBy('id');
@endphp

<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[300px,1fr]">
        <aside class="rounded-xl border border-[#2b3649] bg-[#0f172a] p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-100">Áreas cadastradas</h3>
                    <p class="text-xs text-zinc-400">Selecione para gerenciar tipos e categorias.</p>
                </div>
                <button wire:click="openAreaCreate"
                    class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Nova área
                </button>
            </div>

            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($areas as $area)
                    <li class="rounded-lg border {{ $selectedAreaId === $area->id ? 'border-edp-iceblue-100 bg-edp-iceblue-100/10' : 'border-[#2b3649] bg-[#101a2c] hover:border-edp-iceblue-100/50' }}">
                        <div class="flex items-start justify-between gap-2 px-3 py-2">
                            <button type="button" wire:click="selectArea({{ $area->id }})"
                                class="flex-1 text-left">
                                <div class="flex items-center gap-2 text-zinc-100">
                                    <span class="font-medium">{{ $area->name }}</span>
                                    <span class="rounded bg-[#1f2d3f] px-1.5 py-0.5 text-[11px] uppercase text-zinc-300">{{ $area->sigla }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-zinc-400">{{ $area->active ? 'Ativa' : 'Inativa' }}</p>
                            </button>
                            <div class="flex items-center gap-2">
                                <button wire:click="toggleAreaActive({{ $area->id }})"
                                    class="text-[11px] {{ $area->active ? 'text-emerald-200' : 'text-zinc-500 hover:text-zinc-300' }}">
                                    {{ $area->active ? 'Ativa' : 'Inativa' }}
                                </button>
                                <button wire:click="openAreaEdit({{ $area->id }})"
                                    class="text-[11px] text-edp-iceblue-100 hover:underline">Editar</button>
                                <button wire:click="confirmAreaDelete({{ $area->id }})"
                                    class="text-[11px] text-rose-300 hover:underline">Excluir</button>
                            </div>
                            <div class="text-[11px] text-zinc-500">
                                {{ $calendarIndex[$area->work_calendar_id]['name'] ?? 'Sem calendário' }}
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="rounded-lg border border-[#2b3649] bg-[#101a2c] px-3 py-4 text-center text-xs text-zinc-400">
                        Nenhuma área cadastrada ainda.
                    </li>
                @endforelse
            </ul>

            @if ($showAreaForm)
                <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm">
                    <form wire:submit.prevent="saveArea" class="space-y-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">
                            {{ $areaEditing ? 'Editar área' : 'Nova área' }}
                        </h4>
                        <div>
                            <label class="mb-1 block text-xs text-zinc-400">Nome</label>
                            <input type="text" wire:model.live="areaForm.name"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            @error('areaForm.name')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-zinc-400">Sigla</label>
                            <input type="text" wire:model.lazy="areaForm.sigla" maxlength="10"
                                class="w-full uppercase rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            @error('areaForm.sigla')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-zinc-400">Calendário de trabalho</label>
                            <select wire:model.defer="areaForm.work_calendar_id"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                <option value="">Sem calendário</option>
                                @foreach ($calendars as $calendar)
                                    <option value="{{ $calendar['id'] }}">{{ $calendar['name'] }}</option>
                                @endforeach
                            </select>
                            @error('areaForm.work_calendar_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs text-zinc-300">
                            <input type="checkbox" wire:model.live="areaForm.active"
                                class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                            Área ativa
                        </label>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" wire:click="cancelArea"
                                class="rounded-lg border border-[#2b3649] px-3 py-1.5 text-xs text-zinc-300 hover:bg-[#1a2434]">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-1.5 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </aside>

        <div class="space-y-6">
            <section class="rounded-xl border border-[#2b3649] bg-[#0f172a] p-6">
                <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-100">Tipos de ticket</h3>
                        <p class="text-xs text-zinc-400">Organize os tipos permitidos para a área selecionada.</p>
                    </div>
                    <button wire:click="openTypeCreate"
                        class="self-start rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                        Novo tipo
                    </button>
                </header>

                @error('type.area')
                    <p class="mt-3 rounded border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                        {{ $message }}
                    </p>
                @enderror

                <div class="mt-4 overflow-hidden rounded-lg border border-[#2b3649]">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-[#2b3649] bg-[#132033] text-xs uppercase tracking-wide text-zinc-400">
                            <tr>
                                <th class="px-4 py-2 text-left">Nome</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#2b3649]">
                            @forelse ($types as $type)
                                <tr @class([
                                    'bg-[#1a2537]',
                                    'ring-1 ring-edp-iceblue-100/60 bg-[#1f2d3f]' => $selectedTypeId === $type->id,
                                ]) wire:key="type-{{ $type->id }}">
                                    <td class="px-4 py-3 text-zinc-100">
                                        <button type="button" wire:click="selectType({{ $type->id }})"
                                            class="flex w-full items-center justify-between text-left">
                                            <span class="font-medium {{ $selectedTypeId === $type->id ? 'text-edp-iceblue-100' : 'text-zinc-100' }}">
                                                {{ $type->name }}
                                            </span>
                                            @if ($selectedTypeId === $type->id)
                                                <span class="text-[11px] uppercase text-edp-iceblue-100">Selecionado</span>
                                            @endif
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-zinc-200">
                                        <button wire:click="toggleTypeActive({{ $type->id }})"
                                            class="text-xs {{ $type->active ? 'text-emerald-200' : 'text-zinc-500 hover:text-zinc-300' }}">
                                            {{ $type->active ? 'Ativo' : 'Inativo' }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-right text-xs text-zinc-300">
                                        <div class="inline-flex items-center gap-3">
                                            <button wire:click="openTypeEdit({{ $type->id }})" class="text-edp-iceblue-100 hover:underline">Editar</button>
                                            <button wire:click="confirmTypeDelete({{ $type->id }})" class="text-rose-300 hover:underline">Excluir</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-4 text-center text-xs text-zinc-400">
                                        Nenhum tipo cadastrado para esta área.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($showTypeForm)
                    <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm">
                        <form wire:submit.prevent="saveType" class="space-y-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">
                                {{ $typeEditing ? 'Editar tipo' : 'Novo tipo' }}
                            </h4>
                            <div>
                                <label class="mb-1 block text-xs text-zinc-400">Nome</label>
                                <input type="text" wire:model.live="typeForm.name"
                                    class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                @error('typeForm.name')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs text-zinc-300">
                                <input type="checkbox" wire:model.live="typeForm.active"
                                    class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                Tipo ativo
                            </label>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" wire:click="cancelType"
                                    class="rounded-lg border border-[#2b3649] px-3 py-1.5 text-xs text-zinc-300 hover:bg-[#1a2434]">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-1.5 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                                    Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            </section>

            <section class="rounded-xl border border-[#2b3649] bg-[#0f172a] p-6">
                <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-100">Categorias & Subcategorias</h3>
                        <p class="text-xs text-zinc-400">Estruture o catálogo de atendimento para a área.</p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="openCategoryCreate"
                            @class([
                                'rounded-lg border px-3 py-1.5 text-xs',
                                'border-edp-iceblue-100 text-edp-iceblue-100 hover:bg-edp-iceblue-100/10' => $selectedTypeId,
                                'border-[#2b3649] text-zinc-500 opacity-50 cursor-not-allowed' => !$selectedTypeId,
                            ])>
                            Nova categoria
                        </button>
                        <button wire:click="openSubcategoryCreate"
                            @class([
                                'rounded-lg border px-3 py-1.5 text-xs',
                                'border-[#2b3649] text-zinc-200 hover:bg-[#1a2434]' => $selectedCategoryId,
                                'border-[#2b3649] text-zinc-500 opacity-50 cursor-not-allowed' => !$selectedCategoryId,
                            ])>
                            Nova subcategoria
                        </button>
                    </div>
                </header>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-lg border border-[#2b3649]">
                        <div class="border-b border-[#2b3649] bg-[#132033] px-4 py-2 text-xs uppercase tracking-wide text-zinc-400">
                            Categorias
                        </div>
                        <ul class="divide-y divide-[#2b3649] text-sm">
                            @forelse ($categories as $category)
                                <li class="bg-[#1a2537]">
                                    <div class="flex items-start justify-between gap-2 px-4 py-3">
                                        <button type="button" wire:click="selectCategory({{ $category->id }})"
                                            class="flex-1 text-left {{ $selectedCategoryId === $category->id ? 'text-edp-iceblue-100' : 'text-zinc-100' }}">
                                            <div class="font-medium">{{ $category->name }}</div>
                                            <div class="text-xs text-zinc-400">{{ $category->active ? 'Ativa' : 'Inativa' }}</div>
                                        </button>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="toggleCategoryActive({{ $category->id }})"
                                                class="text-[11px] {{ $category->active ? 'text-emerald-200' : 'text-zinc-500 hover:text-zinc-300' }}">
                                                {{ $category->active ? 'Ativa' : 'Inativa' }}
                                            </button>
                                            <button wire:click="openCategoryEdit({{ $category->id }})"
                                                class="text-[11px] text-edp-iceblue-100 hover:underline">Editar</button>
                                            <button wire:click="confirmCategoryDelete({{ $category->id }})"
                                                class="text-[11px] text-rose-300 hover:underline">Excluir</button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="px-4 py-4 text-center text-xs text-zinc-400">
                                    {{ $selectedTypeId ? 'Nenhuma categoria cadastrada.' : 'Selecione um tipo de ticket para visualizar categorias.' }}
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="rounded-lg border border-[#2b3649]">
                        <div class="border-b border-[#2b3649] bg-[#132033] px-4 py-2 text-xs uppercase tracking-wide text-zinc-400">
                            Subcategorias
                        </div>
                        <ul class="divide-y divide-[#2b3649] text-sm">
                            @forelse ($subcategories as $subcategory)
                                <li class="bg-[#1a2537]">
                                    <div class="flex items-start justify-between gap-2 px-4 py-3">
                                        <div class="text-zinc-100">
                                            <div class="font-medium">{{ $subcategory->name }}</div>
                                            <div class="text-xs text-zinc-400">{{ $subcategory->active ? 'Ativa' : 'Inativa' }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="toggleSubcategoryActive({{ $subcategory->id }})"
                                                class="text-[11px] {{ $subcategory->active ? 'text-emerald-200' : 'text-zinc-500 hover:text-zinc-300' }}">
                                                {{ $subcategory->active ? 'Ativa' : 'Inativa' }}
                                            </button>
                                            <button wire:click="openSubcategoryEdit({{ $subcategory->id }})"
                                                class="text-[11px] text-edp-iceblue-100 hover:underline">Editar</button>
                                            <button wire:click="confirmSubcategoryDelete({{ $subcategory->id }})"
                                                class="text-[11px] text-rose-300 hover:underline">Excluir</button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="px-4 py-4 text-center text-xs text-zinc-400">
                                    {{ $selectedCategoryId ? 'Nenhuma subcategoria para esta categoria.' : 'Selecione uma categoria para visualizar subcategorias.' }}
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                @error('category.type')
                    <p class="mt-3 rounded border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                        {{ $message }}
                    </p>
                @enderror

                @error('subcategory.category')
                    <p class="mt-3 rounded border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                        {{ $message }}
                    </p>
                @enderror

                @if ($showCategoryForm)
                    <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm">
                        <form wire:submit.prevent="saveCategory" class="space-y-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">
                                {{ $categoryEditing ? 'Editar categoria' : 'Nova categoria' }}
                            </h4>
                            @php
                                $currentType = $types->firstWhere('id', $categoryForm['ticket_type_id'] ?? $selectedTypeId);
                            @endphp
                            <p class="text-[11px] uppercase tracking-wide text-zinc-500">
                                Tipo selecionado: <span class="text-zinc-300">{{ $currentType->name ?? 'Não definido' }}</span>
                            </p>
                            <div>
                                <label class="mb-1 block text-xs text-zinc-400">Nome</label>
                                <input type="text" wire:model.live="categoryForm.name"
                                    class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                @error('categoryForm.name')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs text-zinc-300">
                                <input type="checkbox" wire:model.live="categoryForm.active"
                                    class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                Categoria ativa
                            </label>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" wire:click="cancelCategory"
                                    class="rounded-lg border border-[#2b3649] px-3 py-1.5 text-xs text-zinc-300 hover:bg-[#1a2434]">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-1.5 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                                    Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                @if ($showSubcategoryForm)
                    <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm">
                        <form wire:submit.prevent="saveSubcategory" class="space-y-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">
                                {{ $subcategoryEditing ? 'Editar subcategoria' : 'Nova subcategoria' }}
                            </h4>
                            <div>
                                <label class="mb-1 block text-xs text-zinc-400">Nome</label>
                                <input type="text" wire:model.live="subcategoryForm.name"
                                    class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                @error('subcategoryForm.name')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs text-zinc-300">
                                <input type="checkbox" wire:model.live="subcategoryForm.active"
                                    class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                Subcategoria ativa
                            </label>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" wire:click="cancelSubcategory"
                                    class="rounded-lg border border-[#2b3649] px-3 py-1.5 text-xs text-zinc-300 hover:bg-[#1a2434]">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-1.5 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                                    Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
