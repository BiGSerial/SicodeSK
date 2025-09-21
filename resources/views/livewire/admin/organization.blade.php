<div class="text-zinc-100" x-data="{ panel: 'areas' }">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Estrutura organizacional</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-lg font-semibold">Estrutura organizacional</h1>
            <p class="text-xs text-zinc-400">Gerencie áreas, gestores, executores e hierarquia.</p>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
                <aside>
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold">Áreas</h2>
                        <button class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                            Nova área
                        </button>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach ($areas as $area)
                            <li>
                                <button wire:click="$set('selectedArea', {{ $area->id }})"
                                    @class([
                                        'w-full rounded-lg border px-3 py-2 text-left transition',
                                        'border-edp-iceblue-100 bg-edp-iceblue-100/10 text-edp-iceblue-100' => $selectedArea === $area->id,
                                        'border-[#2b3649] bg-[#0f172a] text-zinc-200 hover:border-edp-iceblue-100/60' => $selectedArea !== $area->id,
                                    ])>
                                    <span class="font-medium">{{ $area->name }}</span>
                                    <span class="block text-xs text-zinc-400">Gestor: {{ $area->manager->name ?? '—' }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </aside>

                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-6">
                    @if (!$selectedArea)
                        <div class="grid place-items-center py-16 text-sm text-zinc-500">
                            <p>Selecione uma área para visualizar o time e editar os vínculos.</p>
                        </div>
                    @else
                        <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-base font-semibold">Time da área selecionada</h3>
                                <p class="text-xs text-zinc-400">Adicione ou remova executores ativos, defina lideranças.</p>
                            </div>
                            <div class="flex gap-2">
                                <button class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                                    Adicionar executor
                                </button>
                                <button class="rounded-lg border border-[#2b3649] px-3 py-1.5 text-xs text-zinc-200 hover:bg-[#121a2a]">
                                    Exportar CSV
                                </button>
                            </div>
                        </header>

                        <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#121a2a]">
                            <table class="min-w-full text-xs">
                                <thead class="border-b border-[#2b3649] text-zinc-500">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Executor</th>
                                        <th class="px-4 py-2 text-left">Email</th>
                                        <th class="px-4 py-2 text-left">Desde</th>
                                        <th class="px-4 py-2 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-4 py-3 text-zinc-200">Exemplo de Executor</td>
                                        <td class="px-4 py-3 text-zinc-400">executor@empresa.com</td>
                                        <td class="px-4 py-3 text-zinc-400">01/09/2024</td>
                                        <td class="px-4 py-3 text-right">
                                            <button class="text-xs text-edp-iceblue-100 hover:underline">Remover</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <header>
                <h2 class="text-base font-semibold">Gerência geral</h2>
                <p class="text-xs text-zinc-400">Defina responsáveis globais e linhas de substituição.</p>
            </header>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Gerentes gerais</h3>
                    <ul class="mt-3 space-y-2 text-xs text-zinc-300">
                        <li>Maria Fernanda • mar.geral@empresa.com</li>
                        <li>João Ribeiro • joao.geral@empresa.com</li>
                    </ul>
                </div>
                <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <h3 class="text-sm font-semibold">Substitutos / Escuta</h3>
                    <p class="mt-2 text-xs text-zinc-400">Mantenha linha de sucessão para férias/ausências.</p>
                </div>
            </div>
        </section>
    </main>
</div>
