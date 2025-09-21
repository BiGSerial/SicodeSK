<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Tickets</span>
    @endsection

    {{-- Header --}}
    <div class="py-3">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="mr-3 text-zinc-100 hover:text-white" wire:navigate>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <span class="text-zinc-400 text-sm">Tickets</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tickets.create') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600">
                    + Novo Ticket
                </a>
                <a href="{{ route('tickets.history') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium border border-[#2b3649] bg-[#0f172a] hover:bg-[#121a2a]"
                    wire:navigate>
                    Histórico
                </a>
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-[280px,1fr] gap-6">
            {{-- Sidebar Filtros --}}
            <aside class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-5 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                clip-rule="evenodd" />
                        </svg>
                        Filtros
                    </h3>
                    <button type="button" wire:click="clearFilters"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-[#2b3649] px-3 py-1.5 text-xs font-medium text-zinc-300 hover:text-white hover:border-edp-iceblue-100/60 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Limpar
                    </button>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="relative">
                        <label for="tickets-search" class="sr-only">Pesquisar tickets</label>
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-zinc-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input id="tickets-search" type="text" placeholder="Buscar por código, título ou descrição"
                            wire:model.live.debounce.800ms="search"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] pl-10 pr-3 py-2 text-zinc-100 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            autocomplete="off" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wide text-zinc-400">Área</span>
                            <select wire:model.live="areaId"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todas</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wide text-zinc-400">Status</span>
                            <select wire:model.live="status"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Qualquer</option>
                                <option value="open">Aberto</option>
                                <option value="in_progress">Em andamento</option>
                                <option value="paused">Pausado</option>
                                <option value="resolved">Resolvido</option>
                                <option value="closed">Fechado</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wide text-zinc-400">Prioridade</span>
                            <select wire:model.live="priority"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Qualquer</option>
                                <option value="low">Baixa</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wide text-zinc-400">Itens por página</span>
                            <select wire:model.live="perPage"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                @foreach ([10, 20, 30, 50] as $n)
                                    <option value="{{ $n }}">{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs uppercase tracking-wide text-zinc-400 mb-2 block">Atalhos</span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="$toggle('onlyLate')" @class([
                                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                                'border-rose-400 text-rose-200 bg-rose-500/10 shadow-sm' => $onlyLate,
                                'border-[#2b3649] text-zinc-300 bg-[#0f172a] hover:text-rose-300 hover:border-rose-400/60' => !$onlyLate,
                            ])>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-12a.75.75 0 011.5 0v3.19l2.28 2.28a.75.75 0 11-1.06 1.06l-2.47-2.47A.75.75 0 019.25 9.5V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Apenas atrasados
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Lista --}}
            <section>
                <div class="rounded-xl border border-[#2b3649] bg-[#1b2535] overflow-hidden">
                    <div class="px-4 py-3 border-b border-[#2b3649] flex items-center justify-between">
                        <h3 class="font-semibold">Tickets</h3>
                        <div class="text-xs text-zinc-400">
                            Showing {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} of {{ $tickets->total() }}
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-[#0f172a] text-zinc-300 border-b border-[#2b3649]">
                                <tr>
                                    <th class="px-4 py-2 text-left">Ticket</th>
                                    <th class="px-4 py-2 text-left">Título</th>
                                    <th class="px-4 py-2 text-left">Área / Tipo</th>
                                    <th class="px-4 py-2 text-left">Prioridade</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">SLA</th>
                                    <th class="px-4 py-2 text-left">Atualizado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tickets as $t)
                                    @php
                                    $due = $t->sla_due_at;
                                    $now = now();

                                    if ($due) {
                                        $isOverdue = $due->isPast();
                                        $minutesToDue = $now->diffInMinutes($due, false);

                                        $diffHuman = $isOverdue
                                            ? $due->diffForHumans($now, [
                                                'parts' => 1,
                                                'short' => true,
                                                'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                            ])
                                            : $now->diffForHumans($due, [
                                                'parts' => 1,
                                                'short' => true,
                                                'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                            ]);

                                        if ($isOverdue) {
                                            $slaLabel = 'Venceu há ' . $diffHuman;
                                            $slaClass = 'bg-rose-700/30 text-rose-300 border-rose-700/60';
                                        } elseif ($minutesToDue <= 120) {
                                            $slaLabel = 'Vence em ' . $diffHuman;
                                            $slaClass = 'bg-amber-500/20 text-amber-200 border-amber-500/40';
                                        } else {
                                            $slaLabel = 'Vence em ' . $diffHuman;
                                            $slaClass = 'bg-emerald-700/20 text-emerald-300 border-emerald-700/40';
                                        }
                                    } else {
                                        $slaLabel = 'Sem SLA';
                                        $slaClass = 'bg-zinc-700/30 text-zinc-300 border-zinc-700/60';
                                    }
                                    @endphp
                                    <tr class="border-b border-[#2b3649] hover:bg-[#1f2940]">
                                        {{-- CÓDIGO + ID pequeno --}}
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('tickets.show', $t) }}" wire:navigate
                                                    class="font-mono text-zinc-100 hover:underline">
                                                    {{ $t->code ?? '—' }}
                                                </a>
                                                <span class="text-xs text-zinc-500">#{{ $t->id }}</span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-2">
                                            <div class="font-medium text-zinc-100 line-clamp-1">{{ $t->title }}
                                            </div>
                                            <div class="text-xs text-zinc-400 line-clamp-1">{{ $t->description }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-2">
                                            <div class="text-zinc-100">
                                                {{ $t->area->name ?? '—' }}
                                                @if ($t->area?->sigla)
                                                    <span
                                                        class="ml-1 text-xs text-zinc-400">({{ $t->area->sigla }})</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-zinc-400">{{ $t->type->name ?? '—' }}</div>
                                        </td>

                                        <td class="px-4 py-2">
                                            @php
                                                $prio = $t->priority;
                                                $pClass = match ($prio) {
                                                    'low' => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                                                    'medium' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                                                    'high' => 'bg-amber-700/30 text-amber-300 border-amber-700/60',
                                                    'urgent' => 'bg-rose-700/30 text-rose-300 border-rose-700/60',
                                                    default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $pClass }}">
                                                {{ ucfirst($prio) }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2">
                                            @php
                                                $st = $t->status;
                                                $sClass = match ($st) {
                                                    'open' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                                                    'in_progress'
                                                        => 'bg-indigo-700/30 text-indigo-300 border-indigo-700/60',
                                                    'paused' => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                                    'resolved'
                                                        => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                                                    'closed' => 'bg-zinc-800/60 text-zinc-400 border-zinc-700/60',
                                                    default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $sClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $slaClass ?? '' }}">
                                                {{ $slaLabel }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 text-zinc-300 whitespace-nowrap">
                                            {{ \Illuminate\Support\Carbon::parse($t->updated_at)->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-zinc-400">
                                            No tickets found with current filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($tickets->hasPages())
                        <div class="px-4 py-3 border-t border-[#2b3649]">
                            {{ $tickets->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>

    <x-loading.status text="Carregando tickets...<br>aguarde um instante." />
</div>
