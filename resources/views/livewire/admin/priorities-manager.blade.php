<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-zinc-100">Prioridades cadastradas</h3>
            <p class="text-xs text-zinc-400">Utilizadas para ordenar filas e calcular SLAs.</p>
        </div>

        <button wire:click="openCreate"
            class="inline-flex items-center gap-2 rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
            Nova prioridade
        </button>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#2b3649]">
        <table class="min-w-full text-sm">
            <thead class="border-b border-[#2b3649] bg-[#0f172a] text-zinc-400">
                <tr>
                    <th class="px-4 py-2 text-left">Nome</th>
                    <th class="px-4 py-2 text-left">Peso</th>
                    <th class="px-4 py-2 text-left">Cor</th>
                    <th class="px-4 py-2 text-left">Padrão</th>
                    <th class="px-4 py-2 text-left">Ativa</th>
                    <th class="px-4 py-2 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3649]">
                @forelse ($priorities as $priority)
                    <tr class="bg-[#1b2535] hover:bg-[#1f2940]">
                        <td class="px-4 py-2 text-zinc-100">
                            <div class="font-medium">{{ $priority->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $priority->slug }}</div>
                        </td>
                        <td class="px-4 py-2 text-zinc-300">{{ $priority->weight }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex items-center gap-2 text-xs text-zinc-200">
                                <span class="inline-block h-4 w-4 rounded-full" style="background-color: {{ $priority->color }}"></span>
                                {{ $priority->color }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-zinc-300">
                            @if ($priority->is_default)
                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-2 py-0.5 text-xs text-emerald-200">Sim</span>
                            @else
                                <button wire:click="markDefault({{ $priority->id }})"
                                    class="text-xs text-edp-iceblue-100 hover:underline">Definir</button>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-zinc-300">
                            <button wire:click="toggleActive({{ $priority->id }})"
                                class="text-xs {{ $priority->active ? 'text-emerald-200' : 'text-zinc-500 hover:text-zinc-300' }}">
                                {{ $priority->active ? 'Ativa' : 'Inativa' }}
                            </button>
                        </td>
                        <td class="px-4 py-2 text-right text-xs text-zinc-300">
                            <div class="inline-flex items-center gap-3">
                                <button wire:click="openEdit({{ $priority->id }})" class="text-edp-iceblue-100 hover:underline">
                                    Editar
                                </button>
                                <button wire:click="confirmDelete({{ $priority->id }})" class="text-rose-300 hover:underline">
                                    Remover
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-400">
                            Nenhuma prioridade cadastrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <div class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
            <form wire:submit.prevent="save" class="space-y-4 text-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Nome</label>
                        <input type="text" wire:model.live="form.name"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @error('form.name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Slug</label>
                        <input type="text" wire:model.live="form.slug"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @error('form.slug')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Peso</label>
                        <input type="number" wire:model.live="form.weight" min="0" max="255"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @error('form.weight')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Cor</label>
                        <div class="flex items-center gap-3">
                            <input type="color" wire:model.live="form.color"
                                class="h-10 w-16 rounded border border-[#334155] bg-[#0f172a] p-1">
                            <input type="text" wire:model.live="form.color"
                                class="flex-1 rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        </div>
                        @error('form.color')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-wrap gap-6 text-xs">
                    <label class="inline-flex items-center gap-2 text-zinc-300">
                        <input type="checkbox" wire:model.live="form.is_default"
                            class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                        Definir como padrão
                    </label>
                    <label class="inline-flex items-center gap-2 text-zinc-300">
                        <input type="checkbox" wire:model.live="form.active"
                            class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                        Ativa para uso
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="cancel"
                        class="rounded-lg border border-[#2b3649] px-3 py-2 text-xs text-zinc-300 hover:bg-[#121a2a]">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-2 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                        Salvar prioridade
                    </button>
                </div>
            </form>
        </div>
    @endif

</div>
