<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="hover:underline" wire:navigate>Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.overview') }}" class="hover:underline" wire:navigate>Administração</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Governança de perfis</span>
    @endsection

    <header class="py-4">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Governança de perfis</h1>
                    <p class="text-xs text-zinc-400">Controle quem pode atuar como solicitante, agente ou gestor.</p>
                </div>
                <button type="button" wire:click="clearSelection"
                    class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Limpar seleção
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] shadow-lg">
            <div class="grid gap-6 lg:grid-cols-[340px,1fr]">
                <aside class="border-b border-[#2b3649] bg-[#121a2a] p-6 lg:border-b-0 lg:border-r">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Buscar usuário</h2>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Nome ou e-mail</label>
                            <input type="text" wire:model.live.debounce.350ms="search"
                                placeholder="Digite para pesquisar"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-sm text-zinc-100 focus:border-edp-iceblue-100 focus:outline-none">
                            @error('search')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            @foreach ($searchResults as $result)
                                <button type="button" wire:click="selectUser('{{ $result['id'] }}')"
                                    class="w-full rounded-lg border border-[#2b3649] bg-[#0f172a] px-3 py-2 text-left text-sm text-zinc-300 transition hover:border-edp-iceblue-100/60 hover:bg-[#1a2436]">
                                    <span class="font-medium text-zinc-100">{{ $result['name'] }}</span>
                                    <span class="block text-xs text-zinc-500">{{ $result['email'] }}</span>
                                    @if ($result['superadm'])
                                        <span class="mt-1 inline-flex items-center gap-1 rounded bg-amber-500/10 px-2 py-0.5 text-[10px] uppercase tracking-wide text-amber-200">Super administrador</span>
                                    @endif
                                </button>
                            @endforeach

                            @if ($searchResults === [] && trim($search) !== '')
                                <p class="rounded-lg border border-dashed border-[#2b3649] bg-[#101929] px-3 py-6 text-center text-xs text-zinc-500">
                                    Nenhum usuário encontrado.
                                </p>
                            @endif
                        </div>
                    </div>
                </aside>

                <div class="p-6">
                    @if ($selectedUser)
                        <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-5 shadow-inner">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-semibold text-zinc-100">{{ $selectedUser['name'] }}</h2>
                                    <p class="text-xs text-zinc-400">{{ $selectedUser['email'] }}</p>
                                </div>
                                <div class="text-right text-[11px] uppercase tracking-wide text-zinc-500">
                                    ID SICODE: <span class="font-mono text-zinc-300">{{ $selectedUser['sicode_id'] }}</span>
                                    @if ($selectedUser['superadm'])
                                        <div class="mt-1 inline-flex items-center gap-1 rounded bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-200">
                                            Super administrador
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <form wire:submit.prevent="saveRoles" class="mt-5 space-y-4">
                                <fieldset class="space-y-3">
                                    <legend class="text-xs uppercase tracking-wide text-zinc-400">Papéis atribuídos</legend>
                                    @foreach ($roleLabels as $slug => $label)
                                        <div class="flex items-center justify-between rounded-lg border border-[#1f2937] bg-[#101a2c] px-4 py-3">
                                            <div>
                                                <p class="text-sm font-medium text-zinc-100">{{ $label }}</p>
                                                <p class="text-xs text-zinc-500">
                                                    @switch($slug)
                                                        @case(App\Services\AuthorizationService::ROLE_REQUESTER)
                                                            Pode abrir tickets e acompanhar suas solicitações.
                                                            @break
                                                        @case(App\Services\AuthorizationService::ROLE_AGENT)
                                                            Atua nos tickets atribuídos como executor.
                                                            @break
                                                        @case(App\Services\AuthorizationService::ROLE_AREA_MANAGER)
                                                            Visualiza e gerencia tickets das áreas sob sua gestão.
                                                            @break
                                                        @case(App\Services\AuthorizationService::ROLE_GLOBAL_MANAGER)
                                                            Possui visão consolidada de todas as áreas.
                                                            @break
                                                        @case(App\Services\AuthorizationService::ROLE_ADMIN)
                                                            Acesso completo às configurações do sistema.
                                                            @break
                                                    @endswitch
                                                </p>
                                            </div>
                                            <button type="button" wire:click="toggleRole('{{ $slug }}')"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                                                @class([
                                                    'bg-emerald-400/60' => $formRoles[$slug] ?? false,
                                                    'bg-zinc-600/40' => !($formRoles[$slug] ?? false),
                                                    'cursor-not-allowed opacity-60' => $slug === App\Services\AuthorizationService::ROLE_REQUESTER || ($slug === App\Services\AuthorizationService::ROLE_ADMIN && ($selectedUser['superadm'] ?? false)),
                                                ])
                                                @if($slug === App\Services\AuthorizationService::ROLE_REQUESTER || ($slug === App\Services\AuthorizationService::ROLE_ADMIN && ($selectedUser['superadm'] ?? false))) disabled @endif>
                                                <span class="inline-block h-5 w-5 transform rounded-full bg-[#0f172a] shadow transition"
                                                    @class([
                                                        'translate-x-5' => $formRoles[$slug] ?? false,
                                                        'translate-x-1' => !($formRoles[$slug] ?? false),
                                                    ])></span>
                                            </button>
                                        </div>
                                    @endforeach
                                </fieldset>

                                @error('selectedUser')
                                    <p class="text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                                @error('roles')
                                    <p class="text-xs text-rose-400">{{ $message }}</p>
                                @enderror

                                <div class="flex items-center justify-end gap-3">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg border border-edp-iceblue-100 px-4 py-2 text-xs font-semibold text-edp-iceblue-100 transition hover:bg-edp-iceblue-100/10">
                                        Salvar perfis
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="flex h-full items-center justify-center rounded-lg border border-dashed border-[#2b3649] bg-[#101929] p-12 text-center text-sm text-zinc-500">
                            Busque um usuário no painel ao lado para gerenciar os perfis atribuídos.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
</div>
