<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-zinc-100">Regras configuradas</h3>
            <p class="text-xs text-zinc-400">Combine filtros para ajustar metas e tolerâncias por cenário.</p>
        </div>

        <button wire:click="openCreate"
            class="inline-flex items-center gap-2 rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
            Nova regra de SLA
        </button>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#2b3649]">
        <table class="min-w-full text-sm">
            <thead class="border-b border-[#2b3649] bg-[#0f172a] text-xs uppercase tracking-wide text-zinc-400">
                <tr>
                    <th class="px-4 py-2 text-left">Prioridade</th>
                    <th class="px-4 py-2 text-left">Escopo</th>
                    <th class="px-4 py-2 text-left">Incremento</th>
                    <th class="px-4 py-2 text-left">Tolerância</th>
                    <th class="px-4 py-2 text-left">Pausa</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3649]">
                @forelse ($rules as $rule)
                    @php
                        $incrementHours = intdiv($rule->increment_minutes, 60);
                        $incrementRest = $rule->increment_minutes % 60;
                        $incrementLabel = $incrementHours ? sprintf('%dh %02dm', $incrementHours, $incrementRest) : sprintf('%d min', $rule->increment_minutes);

                        if ($incrementHours && ! $incrementRest) {
                            $incrementLabel = sprintf('%dh', $incrementHours);
                        }

                        $toleranceHours = intdiv($rule->tolerance_minutes, 60);
                        $toleranceRest = $rule->tolerance_minutes % 60;
                        $toleranceLabel = $toleranceHours ? sprintf('%dh %02dm', $toleranceHours, $toleranceRest) : sprintf('%d min', $rule->tolerance_minutes);

                        if ($toleranceHours && ! $toleranceRest) {
                            $toleranceLabel = sprintf('%dh', $toleranceHours);
                        }
                    @endphp
                    <tr class="bg-[#1b2535] hover:bg-[#1f2940]" wire:key="rule-{{ $rule->id }}">
                        <td class="px-4 py-3 text-zinc-100">
                            <div class="font-medium">{{ $rule->priority?->name ?? '—' }}</div>
                            <div class="text-xs text-zinc-500">{{ $rule->priority?->slug }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-400">
                            <div class="flex flex-wrap gap-1">
                                <span class="rounded-md bg-[#0f172a] px-2 py-0.5 text-zinc-200">Área: {{ $rule->area?->name ?? 'Qualquer' }}</span>
                                <span class="rounded-md bg-[#0f172a] px-2 py-0.5 text-zinc-200">Tipo: {{ $rule->type?->name ?? 'Qualquer' }}</span>
                                <span class="rounded-md bg-[#0f172a] px-2 py-0.5 text-zinc-200">Categoria: {{ $rule->category?->name ?? 'Qualquer' }}</span>
                                <span class="rounded-md bg-[#0f172a] px-2 py-0.5 text-zinc-200">Subcategoria: {{ $rule->subcategory?->name ?? 'Qualquer' }}</span>
                            </div>
                            @if ($rule->notes)
                                <p class="mt-2 rounded-md border border-[#2b3649] bg-[#121a2a] px-2 py-1 text-[11px] text-zinc-300">{{ $rule->notes }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-100">{{ $incrementLabel }}</td>
                        <td class="px-4 py-3 text-zinc-100">{{ $toleranceLabel }}</td>
                        <td class="px-4 py-3 text-zinc-100">
                            {{ $rule->pause_suspends ? 'Suspende' : 'Contagem segue' }}
                        </td>
                        <td class="px-4 py-3 text-zinc-100">
                            <button wire:click="toggleActive({{ $rule->id }})"
                                class="text-xs {{ $rule->active ? 'text-emerald-200' : 'text-zinc-500 hover:text-zinc-300' }}">
                                {{ $rule->active ? 'Ativa' : 'Inativa' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-zinc-300">
                            <div class="inline-flex items-center gap-3">
                                <button wire:click="openEdit({{ $rule->id }})" class="text-edp-iceblue-100 hover:underline">
                                    Editar
                                </button>
                                <button wire:click="confirmDelete({{ $rule->id }})" class="text-rose-300 hover:underline">
                                    Remover
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-zinc-400">
                            Nenhuma regra configurada ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <div class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
            <form wire:submit.prevent="save" class="space-y-5 text-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Prioridade</label>
                        <select wire:model.live="form.priority_id"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            <option value="">Selecione</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority['id'] }}">{{ $priority['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.priority_id')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Área</label>
                        <select wire:model.live="form.area_id"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            <option value="">Qualquer área</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area['id'] }}">{{ $area['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.area_id')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Tipo de ticket</label>
                        <select wire:model.live="form.ticket_type_id"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                            @disabled(empty($types))>
                            <option value="">Qualquer tipo</option>
                            @foreach ($types as $type)
                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.ticket_type_id')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Categoria</label>
                        <select wire:model.live="form.category_id"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                            @disabled(empty($categories))>
                            <option value="">Qualquer categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.category_id')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Subcategoria</label>
                        <select wire:model.live="form.subcategory_id"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                            @disabled(empty($subcategories))>
                            <option value="">Qualquer subcategoria</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory['id'] }}">{{ $subcategory['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.subcategory_id')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Incremento (min)</label>
                        <input type="number" min="0" wire:model.live="form.increment_minutes"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @error('form.increment_minutes')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Tolerância (min)</label>
                        <input type="number" min="0" wire:model.live="form.tolerance_minutes"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @error('form.tolerance_minutes')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Observações (opcional)</label>
                    <textarea wire:model.live="form.notes" rows="3"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"></textarea>
                    @error('form.notes')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-6 text-xs">
                    <label class="inline-flex items-center gap-2 text-zinc-300">
                        <input type="checkbox" wire:model.live="form.pause_suspends"
                            class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                        Pausa suspende SLA
                    </label>
                    <label class="inline-flex items-center gap-2 text-zinc-300">
                        <input type="checkbox" wire:model.live="form.active"
                            class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                        Regra ativa
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="cancel"
                        class="rounded-lg border border-[#2b3649] px-3 py-2 text-xs text-zinc-300 hover:bg-[#121a2a]">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-2 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                        Salvar regra
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
