<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Auditoria</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Auditoria do sistema</h1>
                    <p class="text-xs text-zinc-400">Trilha de ações críticas realizadas pelos usuários.</p>
                </div>
                <div class="relative">
                    <input type="search" placeholder="Buscar por tipo, ticket ou usuário" wire:model.debounce.600ms="search"
                        class="w-64 rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-xs text-zinc-100" />
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] shadow-lg">
            <table class="min-w-full text-sm">
                <thead class="border-b border-[#2b3649] bg-[#0f172a] text-zinc-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Quando</th>
                        <th class="px-4 py-2 text-left">Evento</th>
                        <th class="px-4 py-2 text-left">Ticket</th>
                        <th class="px-4 py-2 text-left">Usuário</th>
                        <th class="px-4 py-2 text-left">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr class="border-b border-[#2b3649]">
                            <td class="px-4 py-3 text-xs text-zinc-400">{{ $event->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-zinc-100">{{ \Illuminate\Support\Str::headline($event->type) }}</td>
                            <td class="px-4 py-3 text-zinc-300">{{ $event->ticket?->code ?? '—' }}</td>
                            <td class="px-4 py-3 text-zinc-300">{{ $event->actor?->name ?? 'Sistema' }}</td>
                            <td class="px-4 py-3 text-xs text-zinc-400">
                                {{ json_encode($event->payload_json ?? []) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-400">
                                Nenhum registro encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="border-t border-[#2b3649] px-4 py-3">
                {{ $events->links() }}
            </div>
        </section>
    </main>
</div>
