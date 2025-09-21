@php
    $statusOptions = [
        '' => 'Qualquer',
        'open' => 'Aberto',
        'in_progress' => 'Em andamento',
        'paused' => 'Pausado',
        'resolved' => 'Resolvido',
        'closed' => 'Fechado',
    ];

    $priorityOptions = [
        '' => 'Qualquer',
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ];
@endphp

<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('tickets.index') }}" class="hover:underline" wire:navigate>Tickets</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Histórico</span>
    @endsection

    <div class="py-3">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold">Histórico de tickets</h1>
                <p class="text-xs text-zinc-400">Consulte tickets criados por você filtrando por período, tipo e status.
                </p>
            </div>
            <a href="{{ route('tickets.index') }}"
                class="rounded-lg px-3 py-2 text-sm font-medium border border-[#2b3649] bg-[#0f172a] hover:bg-[#121a2a]"
                wire:navigate>
                Voltar para Tickets
            </a>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-10 space-y-6">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-5 shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <label class="block text-zinc-300 mb-1">Data inicial</label>
                    <input type="date" wire:model.live="startDate"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                </div>
                <div>
                    <label class="block text-zinc-300 mb-1">Data final</label>
                    <input type="date" wire:model.live="endDate"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                </div>
                <div>
                    <label class="block text-zinc-300 mb-1">Status</label>
                    <select wire:model.live="status"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-zinc-300 mb-1">Prioridade</label>
                    <select wire:model.live="priority"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @foreach ($priorityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-zinc-300 mb-1">Tipo</label>
                    <select wire:model.live="ticketTypeId"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        <option value="">Qualquer</option>
                        @foreach ($ticketTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-zinc-300 mb-1">Buscar</label>
                    <input type="search" placeholder="Código, título ou descrição"
                        wire:model.live.debounce.800ms="search"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                </div>
                <div>
                    <label class="block text-zinc-300 mb-1">Resultados por página</label>
                    <select wire:model.live="perPage"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @foreach ([10, 20, 30, 50] as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end justify-end">
                    <button type="button" wire:click="clearFilters"
                        class="inline-flex items-center gap-2 rounded-lg border border-[#2b3649] bg-[#0f172a] px-4 py-2 text-sm text-zinc-200 hover:bg-[#121a2a]">
                        Limpar filtros
                    </button>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] overflow-hidden">
            <div class="px-4 py-3 border-b border-[#2b3649] flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold">Resultados</h2>
                <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-400">
                    <span>{{ $tickets->total() }} registro(s)</span>
                    <span class="hidden sm:inline text-zinc-600">•</span>
                    <div class="flex gap-2 text-[length:inherit]">
                        <button type="button" wire:click="export('xlsx')" wire:loading.attr="disabled"
                            wire:target="export"
                            class="inline-flex items-center gap-1 rounded-lg border border-edp-iceblue-100/60 bg-[#0f172a] px-3 py-1.5 font-medium text-edp-iceblue-100 hover:border-edp-iceblue-100 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-edp-iceblue-100 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f172a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v7.5a.5.5 0 01-.757.429L13 9.333 9.757 10.93a.5.5 0 01-.514 0L6 9.333l-3.243 1.596A.5.5 0 012 10.5V3zm11 7.333l2 1V17a1 1 0 01-1 1H5a1 1 0 01-1-1v-5.667l2-1 3 1.5 3-1.5 2 1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Excel
                        </button>
                        <button type="button" wire:click="export('csv')" wire:loading.attr="disabled"
                            wire:target="export"
                            class="inline-flex items-center gap-1 rounded-lg border border-[#2b3649] bg-[#0f172a] px-3 py-1.5 font-medium text-zinc-200 hover:border-edp-iceblue-100/60 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-edp-iceblue-100 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f172a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M3 4a2 2 0 012-2h10a2 2 0 012 2v1H3V4zm0 3h14v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm4.5 2a.5.5 0 00-.5.5v5a.5.5 0 00.5.5H8a2 2 0 000-4h-.5V9.5a.5.5 0 00-.5-.5zm4 0a.5.5 0 00-.5.5V15a.5.5 0 001 0v-1.5h.5a1.5 1.5 0 000-3H11.5V9.5a.5.5 0 00-.5-.5z" />
                                CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#0f172a] text-zinc-300 border-b border-[#2b3649]">
                        <tr>
                            <th class="px-4 py-2 text-left">Ticket</th>
                            <th class="px-4 py-2 text-left">Título</th>
                            <th class="px-4 py-2 text-left">Tipo</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Prioridade</th>
                            <th class="px-4 py-2 text-left">Criado em</th>
                            <th class="px-4 py-2 text-left">Atualizado em</th>
                            <th class="px-4 py-2 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($tickets->count())
                            <div wire:transition>
                                @foreach ($tickets as $ticket)
                                    @php
                                        $status = $ticket->status ?? 'unknown';
                                        $statusLabel = ucfirst(str_replace('_', ' ', $status));
                                        $statusClass = match ($status) {
                                            'open' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                                            'in_progress' => 'bg-indigo-700/30 text-indigo-300 border-indigo-700/60',
                                            'paused' => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                            'resolved' => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                                            'closed' => 'bg-zinc-800/60 text-zinc-400 border-zinc-700/60',
                                            default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                        };

                                        $priority = $ticket->priority ?? null;
                                        $priorityLabel = $priority ? ucfirst($priority) : '—';
                                        $priorityClass = match ($priority) {
                                            'low' => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                                            'medium' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                                            'high' => 'bg-amber-700/30 text-amber-300 border-amber-700/60',
                                            'urgent' => 'bg-rose-700/30 text-rose-300 border-rose-700/60',
                                            default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                        };
                                    @endphp
                                    <tr class="border-b border-[#2b3649] hover:bg-[#1f2940]">
                                        <td class="px-4 py-2 whitespace-nowrap font-mono text-zinc-100">
                                            {{ $ticket->code }}
                                        </td>
                                        <td class="px-4 py-2 text-zinc-100">{{ $ticket->title }}</td>
                                        <td class="px-4 py-2 text-zinc-300">{{ $ticket->type->name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-zinc-300">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-zinc-300">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $priorityClass }}">
                                                {{ $priorityLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-zinc-400">
                                            {{ $ticket->created_at?->locale('pt_BR')->translatedFormat('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-2 text-zinc-400">
                                            {{ $ticket->updated_at?->locale('pt_BR')->translatedFormat('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                                                class="text-xs text-edp-iceblue-100 hover:underline">Detalhes</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </div>
                        @else
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-zinc-400 text-sm">
                                    Nenhum ticket encontrado com os filtros atuais.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="px-4 py-3 border-t border-[#2b3649]">
                    {{ $tickets->onEachSide(1)->links() }}
                </div>
            @endif
        </section>
    </main>

    <x-loading.status text="Carregando histórico...<br>aguarde um instante." />
</div>
