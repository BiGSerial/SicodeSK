<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Administração</span>
    @endsection

    <div class="py-3">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <div>
                <h1 class="text-lg font-semibold">Centro administrativo</h1>
                <p class="text-xs text-zinc-400">Gerencie solicitações como gestor ou acompanhe o trabalho como executor.</p>
            </div>

            <div class="flex items-center gap-2 rounded-lg border border-[#2b3649] bg-[#0f172a] p-1 text-xs">
                <button type="button" wire:click="setMode('gestao')"
                    @class([
                        'rounded-md px-3 py-1 transition',
                        'bg-edp-iceblue-100/20 text-edp-iceblue-100' => $mode === 'gestao',
                        'text-zinc-400 hover:text-zinc-200' => $mode !== 'gestao',
                    ])>
                    Visão gestor
                </button>
                <button type="button" wire:click="setMode('executor')"
                    @class([
                        'rounded-md px-3 py-1 transition',
                        'bg-edp-iceblue-100/20 text-edp-iceblue-100' => $mode === 'executor',
                        'text-zinc-400 hover:text-zinc-200' => $mode !== 'executor',
                    ])>
                    Visão executor
                </button>
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-7xl space-y-6 px-4 pb-10 sm:px-6 lg:px-8">
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($metrics as $metric)
                <article class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-4 shadow-lg">
                    <p class="text-xs uppercase tracking-wide text-zinc-500">{{ $metric['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold {{ $metric['accent'] ?? 'text-zinc-100' }}">{{ $metric['value'] }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ $metric['muted'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 text-sm">
                <div>
                    <label class="mb-1 block text-zinc-300">Área</label>
                    <select wire:model.live="areaId"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        <option value="">Todas</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-zinc-300">Status</label>
                    <select wire:model.live="status"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        <option value="">Qualquer</option>
                        <option value="open">Aberto</option>
                        <option value="in_progress">Em andamento</option>
                        <option value="paused">Pausado</option>
                        <option value="resolved">Resolvido</option>
                        <option value="closed">Fechado</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-zinc-300">Prioridade</label>
                    <select wire:model.live="priorityId"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        <option value="">Qualquer</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-zinc-300">Pesquisar</label>
                    <input type="search" placeholder="Código, título ou descrição" wire:model.live.debounce.600ms="search"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                </div>
                <div>
                    <label class="mb-1 block text-zinc-300">Resultados por página</label>
                    <select wire:model.live="perPage"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                        @foreach ([10, 20, 30, 50] as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <label class="inline-flex items-center gap-2 text-xs text-zinc-300">
                        <input type="checkbox" wire:model.live="onlyLate"
                            class="rounded border-[#334155] bg-[#0f172a] text-rose-400 focus:ring-rose-400">
                        Apenas atrasados
                    </label>
                </div>
                <div class="flex items-end justify-end">
                    <button type="button" wire:click="clearFilters"
                        class="inline-flex items-center gap-2 rounded-lg border border-[#2b3649] bg-[#0f172a] px-4 py-2 text-sm text-zinc-200 hover:bg-[#121a2a]">
                        Limpar filtros
                    </button>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] overflow-hidden shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#2b3649] px-4 py-3">
                <h2 class="font-semibold">{{ $mode === 'gestao' ? 'Tickets sob minha gestão' : 'Tickets atribuídos a mim' }}</h2>
                <span class="text-xs text-zinc-400">{{ $tickets->total() }} registro(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-[#2b3649] bg-[#0f172a] text-zinc-300">
                        <tr>
                            <th class="px-4 py-2 text-left">Ticket</th>
                            <th class="px-4 py-2 text-left">Título</th>
                            <th class="px-4 py-2 text-left">Área / Tipo</th>
                            <th class="px-4 py-2 text-left">Solicitante</th>
                            <th class="px-4 py-2 text-left">Executor</th>
                            <th class="px-4 py-2 text-left">Prioridade</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">SLA</th>
                            <th class="px-4 py-2 text-left">Atualizado</th>
                            <th class="px-4 py-2 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            @php
                                $due = $ticket->sla_due_at;
                                $nowRef = $now;
                                if ($due) {
                                    $isOverdue = $due->isPast();
                                    $minutesToDue = $nowRef->diffInMinutes($due, false);
                                    $diffHuman = $isOverdue
                                        ? $due->diffForHumans($nowRef, [
                                            'parts' => 1,
                                            'short' => true,
                                            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                        ])
                                        : $nowRef->diffForHumans($due, [
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

                                $statusLabel = ucfirst(str_replace('_', ' ', $ticket->status));
                                $statusClass = match ($ticket->status) {
                                    'open' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                                    'in_progress' => 'bg-indigo-700/30 text-indigo-300 border-indigo-700/60',
                                    'paused' => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                    'resolved' => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                                    'closed' => 'bg-zinc-800/60 text-zinc-400 border-zinc-700/60',
                                    default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                };
                            @endphp
                            <tr class="border-b border-[#2b3649] hover:bg-[#1f2940]">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                                            class="font-mono text-zinc-100 hover:underline">
                                            {{ $ticket->code }}
                                        </a>
                                        <span class="text-xs text-zinc-500">#{{ $ticket->id }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="font-medium text-zinc-100 line-clamp-1">{{ $ticket->title }}</div>
                                    <div class="text-xs text-zinc-400 line-clamp-1">{{ $ticket->description }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="text-zinc-100">
                                        {{ $ticket->area->name ?? '—' }}
                                        @if ($ticket->area?->sigla)
                                            <span class="ml-1 text-xs text-zinc-400">({{ $ticket->area->sigla }})</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-zinc-400">{{ $ticket->type->name ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-2 text-zinc-300">
                                    {{ $ticket->requester->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-zinc-300">
                                    {{ $ticket->executor->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2">
                                    @php
                                        $priority = $ticket->priority;
                                        $prioritySlug = $priority?->slug;
                                        $priorityClass = match ($prioritySlug) {
                                            'low' => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                                            'medium' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                                            'high' => 'bg-amber-700/30 text-amber-300 border-amber-700/60',
                                            'urgent' => 'bg-rose-700/30 text-rose-300 border-rose-700/60',
                                            default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $priorityClass }}">
                                        {{ $priority?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs {{ $slaClass }}">
                                        {{ $slaLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-zinc-300 whitespace-nowrap">
                                    {{ $ticket->updated_at?->diffForHumans() }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                                        class="text-xs text-edp-iceblue-100 hover:underline">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-sm text-zinc-400">
                                    Nenhum ticket encontrado para os filtros atuais.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="border-t border-[#2b3649] px-4 py-3">
                    {{ $tickets->onEachSide(1)->links() }}
                </div>
            @endif
        </section>
    </main>

    <x-loading.status target="areaId,status,priorityId,search,onlyLate,perPage,mode" text="Carregando visão administrativa...<br>aguarde um instante." />
</div>
