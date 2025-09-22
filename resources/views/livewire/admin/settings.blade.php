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
                <button type="button" @click="tab = 'priorities'; Livewire.dispatch('admin-tab-changed', { tab: 'priorities' })"
                    :class="tab === 'priorities' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Prioridades</button>
                <button type="button" @click="tab = 'types'; Livewire.dispatch('admin-tab-changed', { tab: 'types' })"
                    :class="tab === 'types' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Tipos & categorias</button>
                <button type="button" @click="tab = 'sla'; Livewire.dispatch('admin-tab-changed', { tab: 'sla' })"
                    :class="tab === 'sla' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">SLAs & tolerâncias</button>
                <button type="button" @click="tab = 'calendars'; Livewire.dispatch('admin-tab-changed', { tab: 'calendars' })"
                    :class="tab === 'calendars' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Calendários</button>
                <button type="button" @click="tab = 'policies'; Livewire.dispatch('admin-tab-changed', { tab: 'policies' })"
                    :class="tab === 'policies' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400 hover:text-zinc-200'"
                    class="rounded-md px-3 py-1">Políticas globais</button>
            </nav>
        </div>

        <section x-show="tab === 'priorities'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
            <livewire:admin.priorities-manager />
        </section>

        <section x-show="tab === 'types'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">Tipos, categorias & áreas</h2>
                <p class="text-xs text-zinc-400">Mantenha o catálogo de serviços alinhado à estrutura organizacional.</p>
            </header>

            <div class="mt-4">
                <livewire:admin.catalog-manager />
            </div>
        </section>

        <section x-show="tab === 'sla'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">SLAs & tolerâncias</h2>
                <p class="text-xs text-zinc-400">Configure metas de atendimento, tolerâncias e pausas por escopo.</p>
            </header>

            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <livewire:admin.sla-manager />
                </div>
                <aside class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4 text-xs text-zinc-300">
                    <h3 class="text-sm font-semibold text-zinc-100">Boas práticas</h3>
                    <ul class="mt-3 space-y-2">
                        <li>Use uma regra ampla (área/tipo vazio) para garantir fallback.</li>
                        <li>Crie combinações específicas apenas quando houver exceções.</li>
                        <li>Documente o motivo em <em>observações</em> para auditoria.</li>
                        <li>Ao alterar prioridades, revise as regras que dependem delas.</li>
                    </ul>
                </aside>
            </div>
        </section>

        <section x-show="tab === 'calendars'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">Calendários & feriados</h2>
                <p class="text-xs text-zinc-400">Ajuste jornadas úteis e feriados que impactam o cálculo de SLA.</p>
            </header>

            <div class="mt-4">
                <livewire:admin.work-calendars-manager />
            </div>
        </section>

        <section x-show="tab === 'policies'" x-transition class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6" x-cloak>
            <header>
                <h2 class="text-base font-semibold">Políticas globais</h2>
                <p class="text-xs text-zinc-400">Defina regras de anexos, permissões de comentário e alertas sistêmicos.</p>
            </header>

            <div class="mt-4">
                <livewire:admin.global-policies />
            </div>
        </section>
    </main>
</div>
