<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Administração</span>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Visão geral</span>
    @endsection

    <div class="py-4">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Centro de controle</h1>
                    <p class="text-xs text-zinc-400">Resumo executivo com indicadores de saúde do ambiente.</p>
                </div>
                <div class="text-xs text-zinc-500">Atualizado em {{ $now->locale('pt_BR')->translatedFormat('d \d\e F \à\s H:i') }}</div>
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-7xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($metrics as $metric)
                <article class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-5 shadow-lg">
                    <p class="text-xs uppercase tracking-wide text-zinc-500">{{ $metric['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold {{ $metric['accent'] ?? 'text-zinc-100' }}">{{ $metric['value'] }}</p>
                    <p class="mt-1 text-xs text-zinc-400">{{ $metric['muted'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <h2 class="text-base font-semibold">Próximos passos do administrador</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold text-zinc-100">Parametrizações</h3>
                    <p class="mt-2 text-xs text-zinc-400">Prioridades, tipos, categorias, SLAs e workflows.</p>
                    <a href="{{ route('admin.settings') }}" wire:navigate
                        class="mt-3 inline-flex items-center gap-2 text-xs text-edp-iceblue-100 hover:underline">
                        Acessar configurações
                    </a>
                </div>

                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold text-zinc-100">Estrutura organizacional</h3>
                    <p class="mt-2 text-xs text-zinc-400">Monte times, defina gestores e mantenha a área atualizada.</p>
                    <a href="{{ route('admin.organization') }}" wire:navigate
                        class="mt-3 inline-flex items-center gap-2 text-xs text-edp-iceblue-100 hover:underline">
                        Gerenciar organograma
                    </a>
                </div>

                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold text-zinc-100">Auditoria</h3>
                    <p class="mt-2 text-xs text-zinc-400">Acompanhe ações críticas e histórico de movimentação.</p>
                    <a href="{{ route('admin.audit') }}" wire:navigate
                        class="mt-3 inline-flex items-center gap-2 text-xs text-edp-iceblue-100 hover:underline">
                        Ver trilha de auditoria
                    </a>
                </div>

                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold text-zinc-100">Painel gestor</h3>
                    <p class="mt-2 text-xs text-zinc-400">Acesse a visão operacional de gestão/executor.</p>
                    <a href="{{ route('tickets.admin') }}" wire:navigate
                        class="mt-3 inline-flex items-center gap-2 text-xs text-edp-iceblue-100 hover:underline">
                        Abrir painel administrativo
                    </a>
                </div>
            </div>
        </section>
    </main>
</div>
