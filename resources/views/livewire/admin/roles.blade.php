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
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <h1 class="text-lg font-semibold">Governança de perfis</h1>
                    <p class="text-xs text-zinc-400">Siga os dois passos: (1) localize o usuário e (2) ative os perfis que ele deve usar no sistema.</p>
                </div>
                <div class="grid gap-2 text-[11px] text-zinc-400 md:grid-cols-3">
                    <span class="inline-flex items-center gap-2 rounded-lg border border-[#2b3649] bg-[#101a2c] px-3 py-1.5">
                        <span class="text-edp-iceblue-100">1</span>
                        Buscar o usuário
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-lg border border-[#2b3649] bg-[#101a2c] px-3 py-1.5">
                        <span class="text-edp-iceblue-100">2</span>
                        Escolher os perfis
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-lg border border-[#2b3649] bg-[#101a2c] px-3 py-1.5">
                        <span class="text-edp-iceblue-100">3</span>
                        Salvar e pronto
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <section class="grid gap-6 lg:grid-cols-[360px,1fr]">
            <aside class="space-y-5">
                <div class="rounded-xl border border-[#2b3649] bg-[#121a2a] p-5">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Passo 1 · Localize o usuário</h2>
                    <p class="mt-1 text-[11px] text-zinc-500">Digite nome ou e-mail, depois escolha o resultado para carregar os perfis atuais.</p>

                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Buscar</label>
                            <input type="text" wire:model.live.debounce.350ms="search"
                                placeholder="Ex: Maria, Gestor..."
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
                                    Nenhum usuário encontrado com esse termo.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-[#2b3649] bg-[#121a2a] p-5">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Perfis do sistema</h3>
                    <p class="mt-1 text-[11px] text-zinc-500">Veja para que serve cada papel e quantas pessoas já o utilizam.</p>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($roleGuides as $slug => $guide)
                            @php
                                $count = $roleStats[$slug] ?? 0;
                            @endphp
                            <li class="rounded-lg border border-[#2b3649] bg-[#101a2c] px-3 py-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium text-zinc-100">{{ $guide['icon'] ?? '👤' }} {{ $guide['name'] }}</p>
                                        <p class="text-[11px] text-zinc-500">{{ $guide['summary'] }}</p>
                                    </div>
                                    <span class="text-[11px] text-zinc-400">{{ $count }} {{ \Illuminate\Support\Str::plural('usuário', $count) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            <div class="space-y-5">
                @if ($selectedUser)
                    @php
                        $activeRoles = collect($formRoles)
                            ->filter(fn ($enabled) => $enabled)
                            ->keys()
                            ->map(fn ($slug) => $roleGuides[$slug]['name'] ?? $slug);
                    @endphp

                    <div class="rounded-xl border border-[#2b3649] bg-[#0f172a] p-6 shadow-inner">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-zinc-100">{{ $selectedUser['name'] }}</h2>
                                <p class="text-xs text-zinc-400">{{ $selectedUser['email'] }}</p>
                                <p class="mt-2 text-[11px] text-zinc-500">Perfis ativos: {{ $activeRoles->isEmpty() ? 'Apenas solicitante' : $activeRoles->implode(', ') }}</p>
                            </div>
                            <div class="text-right text-[11px] uppercase tracking-wide text-zinc-500">
                                ID SICODE: <span class="font-mono text-zinc-300">{{ $selectedUser['sicode_id'] }}</span>
                                @if ($selectedUser['superadm'])
                                    <div class="mt-1 inline-flex items-center gap-1 rounded bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-200">
                                        Super administrador
                                    </div>
                                @endif
                                <div class="mt-3 space-x-2">
                                    <button type="button" wire:click="clearSelection"
                                        class="rounded border border-[#2b3649] px-3 py-1.5 text-[11px] text-zinc-300 hover:bg-[#1a2436]">
                                        Trocar usuário
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="saveRoles" class="space-y-4">
                        <div class="rounded-xl border border-[#2b3649] bg-[#121a2a] p-6">
                            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-zinc-100">Passo 2 · Ative ou desative perfis</h3>
                                    <p class="text-[11px] text-zinc-500">Use os cartões abaixo para definir exatamente o que este usuário pode fazer dentro da plataforma.</p>
                                </div>
                                <span class="rounded-full border border-edp-iceblue-100 px-3 py-1.5 text-[11px] uppercase tracking-wide text-edp-iceblue-100">
                                    Clique para alternar cada perfil
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($roleGuides as $slug => $guide)
                                    @php
                                        $enabled = $formRoles[$slug] ?? false;
                                        $locked = ($guide['locked'] ?? false) || ($slug === App\Services\AuthorizationService::ROLE_ADMIN && ($selectedUser['superadm'] ?? false));
                                        $count = $roleStats[$slug] ?? 0;
                                        $statusLabel = $enabled ? 'Ativo' : 'Inativo';
                                        $lockedReason = null;

                                        if ($guide['locked'] ?? false) {
                                            $lockedReason = 'Perfil obrigatório para todos os usuários.';
                                        }

                                        if ($slug === App\Services\AuthorizationService::ROLE_ADMIN && ($selectedUser['superadm'] ?? false)) {
                                            $lockedReason = 'Super administradores têm acesso de administrador por padrão.';
                                        }
                                    @endphp

                                    <div
                                        @class([
                                            'rounded-xl border px-4 py-4 transition h-full flex flex-col gap-3',
                                            'border-edp-iceblue-100 bg-edp-iceblue-100/10' => $enabled,
                                            'border-[#2b3649] bg-[#101a2c]' => ! $enabled,
                                        ])>
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="text-lg">{{ $guide['icon'] ?? '👤' }}</span>
                                                <h4 class="text-sm font-semibold text-zinc-100">{{ $guide['name'] }}</h4>
                                                <p class="mt-1 text-[11px] text-zinc-400">{{ $guide['summary'] }}</p>
                                            </div>
                                            <span class="text-[11px] text-zinc-400">{{ $count }} {{ \Illuminate\Support\Str::plural('usuário', $count) }}</span>
                                        </div>

                                        <span class="inline-flex w-fit items-center gap-2 rounded-full border border-[#2b3649] px-3 py-1 text-[11px] text-zinc-400">
                                            {{ $guide['badge'] ?? 'Perfil do sistema' }}
                                        </span>

                                        @if ($lockedReason)
                                            <p class="text-[11px] text-emerald-300">{{ $lockedReason }}</p>
                                        @endif

                                        <div class="mt-auto flex items-center justify-between">
                                            <span class="text-xs font-medium {{ $enabled ? 'text-emerald-300' : 'text-zinc-500' }}">{{ $statusLabel }}</span>
                                            <button type="button"
                                                @if (! $locked) wire:click="toggleRole('{{ $slug }}')" @endif
                                                @class([
                                                    'relative inline-flex h-7 w-14 items-center rounded-full border border-[#2b3649] transition focus:outline-none',
                                                    'bg-emerald-400/70' => $enabled,
                                                    'bg-zinc-700/60' => ! $enabled,
                                                    'cursor-not-allowed opacity-60' => $locked,
                                                ])
                                                @if($locked) disabled @endif>
                                                <span class="inline-block h-6 w-6 transform rounded-full bg-[#0f172a] shadow transition"
                                                    @class([
                                                        'translate-x-7' => $enabled,
                                                        'translate-x-1' => ! $enabled,
                                                    ])></span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @error('selectedUser')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                        @error('roles')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror

                        <div class="flex flex-col gap-3 rounded-xl border border-[#2b3649] bg-[#121a2a] px-5 py-4 text-sm text-zinc-300 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2 text-xs text-zinc-400">
                                <span class="text-edp-iceblue-100">Dica:</span>
                                Papéis extras podem exigir configurar o usuário na <a href="{{ route('admin.organization') }}" class="text-edp-iceblue-100 hover:underline" wire:navigate>estrutura organizacional</a>.
                            </div>
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg border border-edp-iceblue-100 px-4 py-2 text-xs font-semibold text-edp-iceblue-100 transition hover:bg-edp-iceblue-100/10">
                                Salvar perfis
                            </button>
                        </div>
                    </form>
                @else
                    <div class="flex h-full min-h-[320px] items-center justify-center rounded-xl border border-dashed border-[#2b3649] bg-[#101929] p-12 text-center text-sm text-zinc-500">
                        Busque um usuário ao lado para começar a gerenciar os perfis.
                    </div>
                @endif
            </div>
        </section>
    </main>
</div>
