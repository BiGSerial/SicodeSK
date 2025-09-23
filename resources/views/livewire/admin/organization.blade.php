<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Estrutura organizacional</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Estrutura organizacional</h1>
                    <p class="text-xs text-zinc-400">Cadastre áreas, defina gestores e monte o time de executores.</p>
                </div>
                <button wire:click="startCreateArea"
                    class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Nova área
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
                <aside class="space-y-4">
                    @if ($showAreaForm)
                        <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm">
                            <h2 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Cadastrar área</h2>
                            <form wire:submit.prevent="saveArea" class="mt-3 space-y-3">
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-400">Nome</label>
                                    <input type="text" wire:model.defer="areaForm.name"
                                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                    @error('areaForm.name')
                                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-400">Sigla</label>
                                    <input type="text" wire:model.defer="areaForm.sigla"
                                        class="w-full uppercase rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                    @error('areaForm.sigla')
                                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-400">Gestor (SICODE)</label>
                                    <input type="text" wire:model.defer="managerSearch" wire:keydown.debounce.400ms="searchManagers"
                                        placeholder="Buscar por nome ou email"
                                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                    <div class="mt-2 space-y-1">
                                        @foreach ($managerResults as $manager)
                                            <button type="button" wire:click="assignManager('{{ $manager['id'] }}')"
                                                class="block w-full rounded border border-[#334155] bg-[#101a2c] px-3 py-1 text-left text-xs text-zinc-300 hover:border-edp-iceblue-100/60">
                                                {{ $manager['name'] }}
                                                <span class="block text-[11px] text-zinc-500">{{ $manager['email'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                    @if ($areaForm['manager_sicode_id'])
                                        <div class="mt-1 flex items-center justify-between text-[11px] text-emerald-300">
                                            <span>Gestor selecionado: {{ $managerSearch }}</span>
                                            <button type="button" wire:click="clearManager" class="text-rose-300 hover:underline">Remover</button>
                                        </div>
                                    @endif
                                    @error('areaForm.manager_sicode_id')
                                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <label class="inline-flex items-center gap-2 text-xs text-zinc-300">
                                    <input type="checkbox" wire:model.defer="areaForm.active"
                                        class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                    Área ativa
                                </label>
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="toggleAreaForm"
                                        class="rounded-lg border border-[#2b3649] px-3 py-1.5 text-xs text-zinc-300 hover:bg-[#1a2436]">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                        class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-1.5 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                                        Salvar área
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div>
                        <h2 class="text-base font-semibold">Áreas cadastradas</h2>
                        <p class="text-xs text-zinc-400">Selecione para visualizar detalhes e equipe.</p>
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($areas as $area)
                                <li>
                                    <button wire:click="selectArea({{ $area->id }})"
                                        @class([
                                            'w-full rounded-lg border px-3 py-2 text-left transition',
                                            'border-edp-iceblue-100 bg-edp-iceblue-100/10 text-edp-iceblue-100' => $selectedArea === $area->id,
                                            'border-[#2b3649] bg-[#0f172a] text-zinc-200 hover:border-edp-iceblue-100/60' => $selectedArea !== $area->id,
                                        ])>
                                        <span class="font-medium">{{ $area->name }}</span>
                                        <span class="block text-xs text-zinc-400">Gestor: {{ $area->manager->name ?? '—' }}</span>
                                        <span class="block text-[11px] text-zinc-500">Sigla: {{ $area->sigla }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>

                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-6">
                    @php
                        $currentArea = $selectedArea ? $areas->firstWhere('id', $selectedArea) : null;
                        $scopeKey = $scopeContext['key'] ?? 'type:0|cat:0|sub:0';
                        $scopeSelection = [
                            'ticket_type_id' => $scopeContext['ticket_type_id'] ?? null,
                            'category_id' => $scopeContext['category_id'] ?? null,
                            'subcategory_id' => $scopeContext['subcategory_id'] ?? null,
                        ];
                    @endphp

                    @if (!$currentArea)
                        <div class="grid place-items-center py-16 text-sm text-zinc-500">
                            <p>Selecione uma área para visualizar o time e editar os vínculos.</p>
                        </div>
                    @else
                        <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-base font-semibold">{{ $currentArea->name }}</h3>
                                <p class="text-xs text-zinc-400">Gestor: {{ $currentArea->manager->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-500">Status: {{ $currentArea->active ? 'Ativa' : 'Inativa' }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="toggleAreaActive({{ $currentArea->id }})"
                                    class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                                    {{ $currentArea->active ? 'Desativar área' : 'Ativar área' }}
                                </button>
                            </div>
                        </header>

                        <div class="mt-4">
                            <label class="block text-xs uppercase tracking-wide text-zinc-400">Adicionar executor</label>
                            <input type="text" wire:model.defer="executorSearch" wire:keydown.debounce.400ms="searchExecutors"
                                placeholder="Buscar por nome ou email"
                                class="mt-2 w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            <div class="mt-2 space-y-1">
                                @foreach ($executorResults as $result)
                                    <button type="button" wire:click="addExecutor('{{ $result['id'] }}')"
                                        class="block w-full rounded border border-[#334155] bg-[#101a2c] px-3 py-1 text-left text-xs text-zinc-300 hover:border-edp-iceblue-100/60">
                                        {{ $result['name'] }}
                                        <span class="block text-[11px] text-zinc-500">{{ $result['email'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#121a2a]">
                            <table class="min-w-full text-xs">
                                <thead class="border-b border-[#2b3649] text-zinc-500">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Executor</th>
                                        <th class="px-4 py-2 text-left">Email</th>
                                        <th class="px-4 py-2 text-left">Função</th>
                                        <th class="px-4 py-2 text-left">Escopo</th>
                                        <th class="px-4 py-2 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($currentArea->executors_list ?? [] as $executor)
                                        <tr class="border-b border-[#2b3649]">
                                            <td class="px-4 py-3 text-zinc-200">{{ $executor->name }}</td>
                                            <td class="px-4 py-3 text-zinc-400">{{ $executor->email }}</td>
                                            <td class="px-4 py-3 text-zinc-300">
                                                <select wire:change="setExecutorRole('{{ $executor->id }}', $event.target.value)"
                                                    class="w-36 rounded border border-[#334155] bg-[#0f172a] px-2 py-1.5 text-xs text-zinc-100">
                                                    @foreach ($areaRoleOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected(($executor->role ?? 'member') === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 text-zinc-300">
                                                <div class="flex flex-wrap gap-1">
                                                    @forelse ($executor->scopes ?? [] as $scope)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-[#1a2536] px-2 py-1 text-[11px] text-zinc-200">
                                                            {{ $scope['label'] }}
                                                            <button type="button" wire:click="removeScope({{ $scope['id'] }})"
                                                                class="text-[10px] text-rose-300 hover:text-rose-200">×</button>
                                                        </span>
                                                    @empty
                                                        <span class="text-[11px] text-zinc-500">Sem escopo atribuído</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button wire:click="removeExecutor('{{ $executor->id }}')"
                                                    class="text-xs text-rose-300 hover:underline">Remover</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if (($currentArea->executors_list ?? collect())->isEmpty())
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-xs text-zinc-500">
                                                Nenhum executor vinculado ainda.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 rounded-lg border border-[#2b3649] bg-[#121a2a] p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-zinc-100">Distribuição por especialidade</h4>
                                    <p class="text-[11px] text-zinc-500">Selecione o recorte de atendimento e marque quem deve receber os chamados automaticamente.</p>
                                </div>
                                <button type="button" wire:click="clearScopeSelection"
                                    class="self-start rounded border border-[#2b3649] px-3 py-1.5 text-[11px] text-zinc-300 hover:bg-[#1a2436]">
                                    Limpar filtros
                                </button>
                            </div>

                            <div class="mt-4 grid gap-3 md:grid-cols-3 xl:grid-cols-4">
                                <div>
                                    <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Tipo de ticket</label>
                                    <select wire:model.live="scopeTicketTypeId"
                                        class="w-full rounded border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                        <option value="">Todos os tipos</option>
                                        @foreach ($currentArea->ticket_types as $type)
                                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Categoria</label>
                                    <select wire:model.live="scopeCategoryId"
                                        class="w-full rounded border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                        <option value="">Todas as categorias</option>
                                        @foreach ($currentArea->categories as $category)
                                            @php
                                                $matchesType = !$scopeSelection['ticket_type_id'] || ($category['ticket_type_id'] ?? null) === $scopeSelection['ticket_type_id'];
                                            @endphp
                                            @if ($matchesType)
                                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Subcategoria</label>
                                    <select wire:model.live="scopeSubcategoryId"
                                        class="w-full rounded border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                        <option value="">Todas as subcategorias</option>
                                        @foreach ($currentArea->categories as $category)
                                            @php
                                                $matchesType = !$scopeSelection['ticket_type_id'] || ($category['ticket_type_id'] ?? null) === $scopeSelection['ticket_type_id'];
                                                $matchesCategory = !$scopeSelection['category_id'] || $scopeSelection['category_id'] === $category['id'];
                                            @endphp
                                            @if ($matchesType && $matchesCategory)
                                                @foreach ($category['subcategories'] as $sub)
                                                    <option value="{{ $sub['id'] }}">{{ $sub['name'] }}</option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @php
                                $scopeSummary = 'Todos os tipos da área';
                                $typeLookup = collect($currentArea->ticket_types);
                                $categoryLookup = collect($currentArea->categories);

                                if ($scopeSelection['subcategory_id']) {
                                    $category = $categoryLookup->firstWhere('id', $scopeSelection['category_id']);
                                    $type = $typeLookup->firstWhere('id', $scopeSelection['ticket_type_id']);
                                    $subcategory = $category ? collect($category['subcategories'])->firstWhere('id', $scopeSelection['subcategory_id']) : null;

                                    if ($category && $subcategory) {
                                        $scopeSummary = sprintf('%s • %s › %s', $type['name'] ?? 'Tipo', $category['name'], $subcategory['name']);
                                    }
                                } elseif ($scopeSelection['category_id']) {
                                    $category = $categoryLookup->firstWhere('id', $scopeSelection['category_id']);
                                    $type = $typeLookup->firstWhere('id', $scopeSelection['ticket_type_id']);

                                    if ($category) {
                                        $scopeSummary = $type ? sprintf('%s • %s', $type['name'], $category['name']) : $category['name'];
                                    }
                                } elseif ($scopeSelection['ticket_type_id']) {
                                    $type = $typeLookup->firstWhere('id', $scopeSelection['ticket_type_id']);
                                    if ($type) {
                                        $scopeSummary = $type['name'];
                                    }
                                }

                                $assignedCount = collect($currentArea->executors_list ?? [])->filter(function ($executor) use ($scopeKey) {
                                    return in_array($scopeKey, $executor->scope_keys ?? []);
                                })->count();
                            @endphp

                            <p class="mt-4 text-xs text-zinc-400">
                                Escopo atual: <span class="text-zinc-200">{{ $scopeSummary }}</span>
                            </p>

                            <div class="mt-4">
                                @if (($currentArea->executors_list ?? collect())->isEmpty())
                                    <p class="rounded border border-dashed border-[#2b3649] bg-[#101a2c] px-4 py-6 text-center text-xs text-zinc-500">
                                        Cadastre executores na área para definir especialidades.
                                    </p>
                                @else
                                    <div class="grid gap-2 md:grid-cols-2">
                                        @foreach ($currentArea->executors_list ?? [] as $executor)
                                            @php
                                                $assigned = in_array($scopeKey, $executor->scope_keys ?? []);
                                            @endphp
                                            <button type="button" wire:click="toggleScopeAssignment('{{ $executor->id }}')"
                                                class="flex items-center justify-between rounded-lg border px-3 py-2 text-left transition"
                                                @class([
                                                    'border-edp-iceblue-100 bg-edp-iceblue-100/10 text-edp-iceblue-100' => $assigned,
                                                    'border-[#2b3649] bg-[#101a2c] text-zinc-200 hover:border-edp-iceblue-100/60' => ! $assigned,
                                                ])>
                                                <span>
                                                    <span class="block text-sm font-medium">{{ $executor->name }}</span>
                                                    <span class="block text-[11px] text-zinc-500">{{ $executor->email }}</span>
                                                </span>
                                                <span class="text-[11px] text-zinc-400">
                                                    {{ $assigned ? 'Designado' : 'Adicionar' }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>

                                    @if ($assignedCount === 0)
                                        <p class="mt-3 text-[11px] text-amber-300">
                                            Nenhum executor ainda foi atribuído a este recorte.
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
</div>
