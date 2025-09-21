<div class="text-zinc-100" x-data="{ tab: 'priorities' }">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Parametrizações</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-lg font-semibold">Parametrizações do sistema</h1>
            <p class="text-xs text-zinc-400">Gerencie prioridades, tipos, categorias e demais regras de negócio.</p>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-1 text-xs">
            <nav class="flex flex-wrap gap-1">
                <button type="button" @click="tab = 'priorities'"
                    :class="tab === 'priorities' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Prioridades</button>
                <button type="button" @click="tab = 'types'"
                    :class="tab === 'types' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Tipos & categorias</button>
                <button type="button" @click="tab = 'sla'"
                    :class="tab === 'sla' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">SLAs & tolerâncias</button>
                <button type="button" @click="tab = 'workflows'"
                    :class="tab === 'workflows' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Workflows</button>
                <button type="button" @click="tab = 'policies'"
                    :class="tab === 'policies' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Políticas globais</button>
            </nav>
        </div>

        <section x-show="tab === 'priorities'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
            <livewire:admin.priorities-manager />
        </section>

        <section x-show="tab === 'types'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">Tipos & categorias</h2>
                <p class="text-xs text-zinc-400">Estruture o catálogo de serviços suportados.</p>
            </header>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Tipos de ticket</h3>
                    <ul class="mt-3 space-y-2 text-xs text-zinc-300">
                        <li>Incidente</li>
                        <li>Requisição de serviço</li>
                        <li>Mudança planejada</li>
                    </ul>
                </div>
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Categorias & Subcategorias</h3>
                    <p class="mt-2 text-xs text-zinc-400">Monte árvores por área, facilitando roteamento e métricas.</p>
                    <button class="mt-3 rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                        Adicionar categoria
                    </button>
                </div>
            </div>
        </section>

        <section x-show="tab === 'sla'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">SLAs & tolerâncias</h2>
                <p class="text-xs text-zinc-400">Configure metas de atendimento por combinação de atributos.</p>
            </header>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Regras ativas</h3>
                    <ul class="mt-3 space-y-2 text-xs text-zinc-300">
                        <li>Incidente crítico • 4h • Pausa suspende SLA</li>
                        <li>Requisição padrão • 16h • Pausa não suspende</li>
                    </ul>
                </div>
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Criar nova regra</h3>
                    <p class="mt-2 text-xs text-zinc-400">Selecione prioridade/tipo/categoria e defina alvo em horas.</p>
                    <button class="mt-3 rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                        Nova regra
                    </button>
                </div>
            </div>
        </section>

        <section x-show="tab === 'workflows'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">Workflows</h2>
                <p class="text-xs text-zinc-400">Desenhe o fluxo de aprovação e etapas do ticket.</p>
            </header>
            <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                <p class="text-xs text-zinc-400">Arraste e solte estágios, defina responsáveis e gatilhos automáticos.</p>
                <button class="mt-3 rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Criar workflow
                </button>
            </div>
        </section>

        <section x-show="tab === 'policies'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">Políticas globais</h2>
                <p class="text-xs text-zinc-400">Defina regras padrão de comentários, anexos e janelas de exclusão.</p>
            </header>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Comentários</h3>
                    <ul class="mt-3 space-y-2 text-xs text-zinc-300">
                        <li>Exclusão de comentário requisitando resposta: até 30 min sem resposta.</li>
                        <li>Conversão de visibilidade: permitido enquanto ticket estiver aberto.</li>
                    </ul>
                </div>
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Anexos</h3>
                    <ul class="mt-3 space-y-2 text-xs text-zinc-300">
                        <li>Tamanho máximo: 25 MB</li>
                        <li>Extensões bloqueadas: .exe, .bat</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
</div>
