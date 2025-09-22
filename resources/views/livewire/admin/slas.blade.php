<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">SLAs</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Gestão de SLAs</h1>
                    <p class="text-xs text-zinc-400">Defina metas de atendimento por prioridade, área e catálogo de serviço.</p>
                </div>
                <div class="rounded-xl border border-[#2b3649] bg-[#101a2c] px-4 py-2 text-xs text-zinc-300">
                    <span class="font-semibold text-edp-iceblue-100">Dica:</span>
                    combine regras gerais com regras específicas para cenários críticos.
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <livewire:admin.sla-manager />
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-[#2b3649] bg-[#101829] p-5">
                <h2 class="text-sm font-semibold text-zinc-100">Como o SLA é escolhido?</h2>
                <p class="mt-2 text-xs text-zinc-400">
                    O sistema avalia da regra mais específica para a mais ampla, somando incrementos válidos
                    para chegar no tempo alvo final. Regras inativas são ignoradas automaticamente.
                </p>
            </div>
            <div class="rounded-xl border border-[#2b3649] bg-[#101829] p-5">
                <h2 class="text-sm font-semibold text-zinc-100">Reclassificação automática</h2>
                <p class="mt-2 text-xs text-zinc-400">
                    Ao alterar área, tipo ou categoria do ticket, o cálculo é refeito usando as combinações
                    vigentes. A tolerância é aplicada somente no monitoramento de prazos.</p>
            </div>
            <div class="rounded-xl border border-[#2b3649] bg-[#101829] p-5">
                <h2 class="text-sm font-semibold text-zinc-100">Pausas e contagem</h2>
                <p class="mt-2 text-xs text-zinc-400">
                    Ative <em>pausa suspende</em> para congelar o relógio em situações tratadas pelos workflows
                    (ex.: aguardando cliente). Caso contrário, o tempo segue correndo.</p>
            </div>
        </section>
    </main>
</div>
