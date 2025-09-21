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
                    <h1 class="text-lg font-semibold">Workflows e estágios</h1>
                    <p class="text-xs text-zinc-400">Modelo visual dos passos que um ticket percorre.</p>
                </div>
                <button class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Novo workflow
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] shadow-lg">
            <table class="min-w-full text-sm">
                <thead class="border-b border-[#2b3649] bg-[#0f172a] text-zinc-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Workflow</th>
                        <th class="px-4 py-2 text-left">Etapas</th>
                        <th class="px-4 py-2 text-left">Aprovação</th>
                        <th class="px-4 py-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workflows as $workflow)
                        <tr class="border-b border-[#2b3649]">
                            <td class="px-4 py-3 text-zinc-100">{{ $workflow->name }}</td>
                            <td class="px-4 py-3 text-xs text-zinc-400">
                                {{ $workflow->steps->pluck('name')->join(' → ') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-400">
                                {{ $workflow->requires_approval ? 'Exige aprovação' : 'Fluxo direto' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="text-xs text-edp-iceblue-100 hover:underline">Editar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-400">
                                Nenhum workflow cadastrado até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <h2 class="text-base font-semibold">Boas práticas</h2>
            <ul class="mt-3 space-y-2 text-xs text-zinc-400">
                <li>Padronize nomenclatura dos estágios para facilitar leitura.</li>
                <li>Defina claramente quem aprova cada etapa e o tempo máximo de decisão.</li>
                <li>Utilize automações (ex.: notificação, atribuição) para reduzir trabalho manual.</li>
            </ul>
        </section>
    </main>
</div>
