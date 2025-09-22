<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Workflows</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Workflows de atendimento</h1>
                    <p class="text-xs text-zinc-400">Desenhe as etapas que um ticket percorre dentro da área.</p>
                </div>
                <button wire:click="openCreate"
                    class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Novo workflow
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-xl border border-[#2b3649] bg-[#1b2535]">
            <table class="min-w-full text-sm">
                <thead class="border-b border-[#2b3649] bg-[#0f172a] text-xs uppercase tracking-wide text-zinc-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Workflow</th>
                        <th class="px-4 py-2 text-left">Escopo</th>
                        <th class="px-4 py-2 text-left">Etapas</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2b3649]">
                    @forelse ($workflows as $workflow)
                        <tr class="bg-[#1b2535] hover:bg-[#1f2940]">
                            <td class="px-4 py-3 text-zinc-100">{{ $workflow->name }}</td>
                            <td class="px-4 py-3 text-xs text-zinc-300">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm text-zinc-100">{{ $workflow->area->name ?? '—' }}</span>
                                    <span class="text-[11px] text-zinc-500">
                                        Tipo: {{ $workflow->ticketType->name ?? 'Todos' }}
                                        @if ($workflow->category)
                                            • Categoria: {{ $workflow->category->name }}
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-400">
                                @if ($workflow->steps->isNotEmpty())
                                    {{ $workflow->steps->pluck('name')->join(' → ') }}
                                @else
                                    <span class="text-zinc-600">Sem etapas definidas</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-300">
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full {{ $workflow->active ? 'bg-emerald-400' : 'bg-zinc-500' }}"></span>
                                    {{ $workflow->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-zinc-300">
                                <div class="inline-flex items-center gap-3">
                                    <button wire:click="toggleActive({{ $workflow->id }})"
                                        class="text-emerald-200 hover:underline">
                                        {{ $workflow->active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button wire:click="openEdit({{ $workflow->id }})"
                                        class="text-edp-iceblue-100 hover:underline">
                                        Editar
                                    </button>
                                    <button wire:click="confirmDelete({{ $workflow->id }})"
                                        class="text-rose-300 hover:underline">
                                        Remover
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-zinc-400">
                                Nenhum workflow cadastrado até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($showForm)
            <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 text-sm">
                <form wire:submit.prevent="save" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Área</label>
                            <select wire:model.live="workflowForm.area_id"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                <option value="">Selecione</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area['id'] }}">{{ $area['name'] }}</option>
                                @endforeach
                            </select>
                            @error('workflowForm.area_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Nome do workflow</label>
                            <input type="text" wire:model.defer="workflowForm.name"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            @error('workflowForm.name')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Tipo de ticket</label>
                            <select wire:model.live="workflowForm.ticket_type_id"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                @disabled(empty($types))>
                                <option value="">Todos os tipos</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                            @error('workflowForm.ticket_type_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Categoria</label>
                            <select wire:model.live="workflowForm.category_id"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                @disabled(empty($categories))>
                                <option value="">Todas as categorias</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                @endforeach
                            </select>
                            @error('workflowForm.category_id')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center gap-2 pt-5 text-xs text-zinc-300">
                            <input type="checkbox" wire:model.defer="workflowForm.active"
                                class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                            Workflow ativo
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Etapas do fluxo</h3>
                            <button type="button" wire:click="addStep"
                                class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                                Adicionar etapa
                            </button>
                        </div>
                        @error('workflowForm.steps')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror

                        <div class="space-y-3">
                            @foreach ($workflowForm['steps'] as $index => $step)
                                <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] p-4" wire:key="step-{{ $step['key'] }}">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div class="flex items-center gap-3 text-xs text-zinc-400">
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#0f172a] text-xs text-edp-iceblue-100">
                                                {{ $index + 1 }}
                                            </span>
                                            <input type="text" wire:model.defer="workflowForm.steps.{{ $index }}.name"
                                                class="w-full rounded border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                                placeholder="Nome da etapa">
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" wire:click="moveStep('{{ $step['key'] }}', 'up')"
                                                class="rounded border border-[#2b3649] px-2 py-1 text-zinc-400 hover:bg-[#1a2436]"
                                                title="Mover para cima">↑</button>
                                            <button type="button" wire:click="moveStep('{{ $step['key'] }}', 'down')"
                                                class="rounded border border-[#2b3649] px-2 py-1 text-zinc-400 hover:bg-[#1a2436]"
                                                title="Mover para baixo">↓</button>
                                            <button type="button" wire:click="removeStep('{{ $step['key'] }}')"
                                                class="rounded border border-rose-500/60 px-2 py-1 text-rose-300 hover:bg-rose-500/10"
                                                title="Remover etapa">Remover</button>
                                        </div>
                                    </div>

                                    <div class="mt-3 grid gap-3 md:grid-cols-3 text-xs text-zinc-300">
                                        <div>
                                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Regra de atribuição</label>
                                            <select wire:model.defer="workflowForm.steps.{{ $index }}.assign_rule"
                                                class="w-full rounded border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                                @foreach ($assignRules as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">SLA etapa (min)</label>
                                            <input type="number" min="0" wire:model.defer="workflowForm.steps.{{ $index }}.sla_target_minutes"
                                                class="w-full rounded border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                                placeholder="Opcional">
                                        </div>
                                        <div class="flex flex-col justify-end text-[11px] text-zinc-500">
                                            <p>Tempo adicional para controle interno. Deixe vazio para não definir meta específica.</p>
                                        </div>
                                    </div>

                                    @error('workflowForm.steps.'.$index.'.name')
                                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="cancel"
                            class="rounded-lg border border-[#2b3649] px-3 py-2 text-xs text-zinc-300 hover:bg-[#121a2a]">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-2 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                            Salvar workflow
                        </button>
                    </div>
                </form>
            </section>
        @else
            <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
                <h2 class="text-base font-semibold">Como planejar um bom fluxo</h2>
                <ul class="mt-3 space-y-2 text-xs text-zinc-400">
                    <li><strong class="text-zinc-200">Mapeie responsabilidades:</strong> cada etapa deve deixar claro quem atua ou aprova.</li>
                    <li><strong class="text-zinc-200">Use metas realistas:</strong> o SLA por etapa ajuda a identificar gargalos antes do vencimento final.</li>
                    <li><strong class="text-zinc-200">Evite etapas desnecessárias:</strong> mantenha a jornada enxuta e oriente condicionalidades via assign rules.</li>
                </ul>
            </section>
        @endif
    </main>
</div>
