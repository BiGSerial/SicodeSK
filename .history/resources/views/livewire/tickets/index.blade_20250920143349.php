<div class="min-h-screen bg-[#0b1220] text-zinc-100">
    {{-- Header --}}
    <header class="border-b border-[#2b3649] bg-[#0f172a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="EDP" class="h-7">
                <span class="text-edp-verde-100 text-xl font-semibold tracking-wide">sicodeSK</span>
                <span class="text-zinc-400 text-sm">/ Tickets</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tickets.create') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600">
                    + New ticket
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-[280px,1fr] gap-6">
            {{-- Sidebar Filtros --}}
            <aside class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-4">
                <h3 class="font-semibold mb-3">Filters</h3>

                <div class="space-y-4 text-sm">
                    {{-- Área --}}
                    <div>
                        <label class="block text-zinc-300 mb-1">Area</label>
                        <select wire:model.live="areaId"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            <option value="">All areas</option>
                            @foreach ($areas as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-zinc-300 mb-1">Status</label>
                        <select wire:model.live="status"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            <option value="">Any</option>
                            @foreach (['open', 'in_progress', 'paused', 'resolved', 'closed'] as $st)
                                <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="block text-zinc-300 mb-1">Priority</label>
                        <select wire:model.live="priority"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            <option value="">Any</option>
                            @foreach (['low', 'medium', 'high', 'urgent'] as $p)
                                <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Toggles --}}
                    <div class="flex items-center gap-2">
                        <input id="mine" type="checkbox" wire:model.live="onlyMine"
                            class="size-4 rounded border-[#334155] bg-[#0f172a]">
                        <label for="mine" class="select-none">My tickets</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="late" type="checkbox" wire:model.live="onlyLate"
                            class="size-4 rounded border-[#334155] bg-[#0f172a]">
                        <label for="late" class="select-none">Late only</label>
                    </div>

                    {{-- Search --}}
                    <div>
                        <label class="block text-zinc-300 mb-1">Search</label>
                        <input type="text" placeholder="Title or description..." wire:model.debounce.400ms="search"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100" />
                    </div>

                    {{-- Per page --}}
                    <div>
                        <label class="block text-zinc-300 mb-1">Per page</label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                            @foreach ([10, 20, 30, 50] as $n)
                                <option value="{{ $n }}">{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2">
                        <button wire:click="clearFilters"
                            class="w-full rounded-lg px-3 py-2 text-sm border border-[#2b3649] bg-[#0f172a] hover:bg-[#121a2a]">
                            Clear filters
                        </button>
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
                                    <th class="px-4 py-2 text-left">ID</th>
                                    <th class="px-4 py-2 text-left">Title</th>
                                    <th class="px-4 py-2 text-left">Area / Type</th>
                                    <th class="px-4 py-2 text-left">Priority</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">SLA</th>
                                    <th class="px-4 py-2 text-left">Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tickets as $t)
                                    @php
                                        $late = $t->is_late;
                                        $due = $t->sla_due_at
                                            ? \Illuminate\Support\Carbon::parse($t->sla_due_at)
                                            : null;
                                        $slaLabel = $due
                                            ? ($late
                                                ? 'Late by ' .
                                                    $due->diffForHumans(now(), [
                                                        'parts' => 2,
                                                        'short' => true,
                                                        'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                                    ])
                                                : $due->diffForHumans(now(), ['parts' => 2, 'short' => true]))
                                            : '—';
                                    @endphp
                                    <tr class="border-b border-[#2b3649] hover:bg-[#1f2940]">
                                        <td class="px-4 py-2 whitespace-nowrap text-zinc-300">#{{ $t->id }}</td>
                                        <td class="px-4 py-2">
                                            <div class="font-medium text-zinc-100">{{ $t->title }}</div>
                                            <div class="text-xs text-zinc-400 line-clamp-1">{{ $t->description }}</div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="text-zinc-100">{{ $t->area->name ?? '—' }}</div>
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
                                                    fault => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
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
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs
                                                {{ $late ? 'bg-rose-700/30 text-rose-300 border-rose-700/60' : 'bg-emerald-700/20 text-emerald-300 border-emerald-700/40' }}">
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
</div>
