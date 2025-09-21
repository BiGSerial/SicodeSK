<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">SLAs</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Gestão de SLAs</h1>
                    <p class="text-xs text-zinc-400">Configure metas, suspensões e tolerâncias por cenário.</p>
                </div>
                <button class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Nova regra de SLA
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] shadow-lg">
            <table class="min-w-full text-sm">
                <thead class="border-b border-[#2b3649] bg-[#0f172a] text-zinc-400">
                    <tr>
                        <th class="px-4 py-2 text-left">Nome</th>
                        <th class="px-4 py-2 text-left">Meta</th>
                        <th class="px-4 py-2 text-left">Critério</th>
                        <th class="px-4 py-2 text-left">Tolerância</th>
                        <th class="px-4 py-2 text-left">Pausa</th>
                        <th class="px-4 py-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slas as $sla)
                        <tr class="border-b border-[#2b3649]">
                            <td class="px-4 py-3 text-zinc-100">{{ $sla->name }}</td>
                            <td class="px-4 py-3 text-zinc-300">{{ $sla->target_hours }}h</td>
                            <td class="px-4 py-3 text-xs text-zinc-400">{{ json_encode($sla->criteria ?? []) }}</td>
                            <td class="px-4 py-3 text-zinc-300">{{ $sla->tolerance_minutes ?? 0 }} min</td>
                            <td class="px-4 py-3 text-zinc-300">{{ data_get($sla->criteria, 'pause_suspends', false) ? 'Suspende' : 'Não suspende' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button class="text-xs text-edp-iceblue-100 hover:underline">Editar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-400">
                                Nenhuma regra cadastrada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <h2 class="text-base font-semibold">Regras de cálculo</h2>
            <ul class="mt-3 space-y-2 text-xs text-zinc-400">
                <li>SLA é selecionado a partir da combinação (área, tipo, categoria, prioridade).</li>
                <li>Quando um ticket é reclassificado, o SLA é recalculado conforme nova combinação.</li>
                <li>Pausas podem suspender a contagem se a regra definir <em>pause_suspends</em>.</li>
                <li>Histórico das mudanças fica registrado para auditoria.</li>
            </ul>
        </section>
    </main>
</div>
