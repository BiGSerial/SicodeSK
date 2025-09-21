<!doctype html>
<html lang="pt-BR" class="h-full w-full dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'sicodeSK') }}</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('css')
</head>

<body class="min-h-screen m-0 w-full bg-[var(--ui-bg)] text-[var(--ui-text)] antialiased">
    {{-- HEADER GLOBAL: full width de fundo; conteúdo centralizado --}}
    <header class="topbar w-full">
        <div class="app-container app-header py-3">
            @php
                $user = auth()->user();
                $displayName = $user?->name ?? 'Usuário';
                // Calcula iniciais (primeiro e último nomes, se houver)
                $parts = preg_split('/\s+/', trim((string) $displayName), -1, PREG_SPLIT_NO_EMPTY);
                $initials = '';
                if ($parts && count($parts) > 0) {
                    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
                    if (count($parts) > 1) {
                        $initials .= mb_strtoupper(mb_substr($parts[count($parts) - 1], 0, 1));
                    }
                }

                $navItems = [
                    [
                        'label'   => 'Dashboard',
                        'route'   => 'dashboard',
                        'pattern' => 'dashboard',
                        'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 9h1v7a1 1 0 001 1h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a1 1 0 001-1V9h1a1 1 0 00.707-1.707l-7-7z" /></svg>',
                    ],
                    [
                        'label'   => 'Tickets',
                        'route'   => 'tickets.index',
                        'pattern' => 'tickets.*',
                        'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2h2V5h10v2h2V5a2 2 0 00-2-2H5z" /><path d="M3 9v4a4 4 0 004 4h6a4 4 0 004-4V9H3z" /></svg>',
                    ],
                ];

                if ($user) {
                    $hasAdminView = (bool) ($user->superadm ?? false);

                    if (!$hasAdminView) {
                        $hasAdminView = \App\Models\Ticket::query()
                            ->where(fn ($q) => $q
                                ->where('manager_sicode_id', $user->id)
                                ->orWhere('executor_sicode_id', $user->id))
                            ->exists();
                    }

                    if ($hasAdminView) {
                        $adminChildren = [
                            [
                                'label' => 'Visão geral',
                                'route' => 'admin.overview',
                                'pattern' => 'admin.overview',
                            ],
                            [
                                'label' => 'Parametrizações',
                                'route' => 'admin.settings',
                                'pattern' => 'admin.settings',
                            ],
                            [
                                'label' => 'Estrutura organizacional',
                                'route' => 'admin.organization',
                                'pattern' => 'admin.organization',
                            ],
                            [
                                'label' => 'SLAs',
                                'route' => 'admin.slas',
                                'pattern' => 'admin.slas',
                            ],
                            [
                                'label' => 'Workflows',
                                'route' => 'admin.workflows',
                                'pattern' => 'admin.workflows',
                            ],
                            [
                                'label' => 'Auditoria',
                                'route' => 'admin.audit',
                                'pattern' => 'admin.audit',
                            ],
                            [
                                'label' => 'Painel gestor',
                                'route' => 'tickets.admin',
                                'pattern' => 'tickets.admin',
                            ],
                        ];

                        $navItems[] = [
                            'label'    => 'Administração',
                            'route'    => 'admin.overview',
                            'pattern'  => ['admin.*', 'tickets.admin'],
                            'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M11 17a1 1 0 11-2 0v-1.268a2 2 0 01.895-1.664l2.379-1.586A2 2 0 0013 11.586V9a3 3 0 10-6 0v2.586a2 2 0 00.726 1.482l2.379 1.586A2 2 0 0111 15.732V17z" /><path d="M7 5a3 3 0 116 0v.764A9.005 9.005 0 0117 14h-2a7 7 0 00-14 0H1a9.005 9.005 0 016-8.236V5z" /></svg>',
                            'children' => $adminChildren,
                        ];
                    }
                }

                $navItems = array_filter($navItems, fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']));
            @endphp

            @hasSection('header')
                @yield('header')
            @else
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-6">
                        <a href="{{ route('dashboard') }}" class="flex items-center" wire:navigate>
                            <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="EDP" class="h-7">
                            <span class="text-edp-verde-100 text-lg font-semibold">sicodeSK</span>
                        </a>

                        @if (!empty($navItems))
                            <nav aria-label="Navegação principal"
                                class="overflow-x-auto lg:overflow-visible scrollbar-thin scrollbar-thumb-[#293445] scrollbar-track-transparent">
                                <ul class="flex items-center gap-1 md:gap-2">
                                    @foreach ($navItems as $item)
                                        @php
                                            $patterns = (array) ($item['pattern'] ?? []);
                                            $isActive = !empty($patterns) ? request()->routeIs($patterns) : false;
                                            $baseClasses = 'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-edp-iceblue-100 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f172a]';
                                            $activeClasses = $item['highlight'] ?? false
                                                ? 'bg-gradient-to-r from-sky-600 to-blue-700 text-white shadow'
                                                : 'bg-[#1a2436] text-white shadow-inner';
                                            $inactiveClasses = $item['highlight'] ?? false
                                                ? 'text-white bg-gradient-to-r from-sky-600/80 to-blue-700/80 hover:from-sky-500 hover:to-blue-600'
                                                : 'text-zinc-300 hover:text-white hover:bg-[#1a2436]/80';
                                            $classes = $baseClasses . ' ' . ($isActive ? $activeClasses : $inactiveClasses);
                                        @endphp
                                        <li class="relative" x-data="{ open: false }">
                                            @if (!empty($item['children']))
                                                <button type="button" @click="open = !open"
                                                    @keydown.escape.window="open = false"
                                                    class="{{ $classes }}"
                                                    x-bind:class="open ? 'bg-[#1a2436] text-white shadow-inner' : ''">
                                                    {!! $item['icon'] !!}
                                                    <span>{{ $item['label'] }}</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M5.23 7.21a.75.75 0 011.06.02L10 10.939l3.71-3.71a.75.75 0 111.06 1.062l-4.24 4.24a.75.75 0 01-1.06 0l-4.24-4.24a.75.75 0 01.02-1.06z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>

                                                <div x-show="open" x-transition x-cloak
                                                    @click.away="open = false"
                                                    class="absolute right-0 z-50 mt-2 w-56 rounded-lg border border-[#2b3649] bg-[#0f172a] p-2 shadow-xl"
                                                    style="min-width: 14rem">
                                                    <ul class="space-y-1 text-sm">
                                                        @foreach ($item['children'] as $child)
                                                            <li>
                                                                @php
                                                                    $childPatterns = (array) ($child['pattern'] ?? []);
                                                                    $childActive = !empty($childPatterns) ? request()->routeIs($childPatterns) : false;
                                                                @endphp
                                                                <a href="{{ route($child['route']) }}" wire:navigate
                                                                    class="flex items-center justify-between rounded-md px-3 py-2 text-zinc-300 hover:bg-[#1a2436] hover:text-white"
                                                                    @if ($childActive) aria-current="page" @endif>
                                                                    <span>{{ $child['label'] }}</span>
                                                                    @if ($childActive)
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.414l-7.004 7.005a1 1 0 01-1.414 0L3.296 8.72a1 1 0 111.414-1.414L9 11.596l6.296-6.305a1 1 0 011.408-.001z" clip-rule="evenodd" />
                                                                        </svg>
                                                                    @endif
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @else
                                                <a href="{{ route($item['route']) }}" wire:navigate class="{{ $classes }}"
                                                    @if ($isActive) aria-current="page" @endif>
                                                    {!! $item['icon'] !!}
                                                    <span>{{ $item['label'] }}</span>
                                                </a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        @endif
                    </div>

                    <div class="flex items-center">
                        @if ($user)
                            <span class="hidden sm:block text-sm text-zinc-300 mr-2">Olá, {{ $displayName }}</span>

                            {{-- Avatar de iniciais (placeholder para futuro avatar real) --}}
                            <button type="button" class="relative inline-flex items-center gap-2">
                                <span class="sr-only">Conta</span>
                                <span
                                    class="grid place-items-center h-9 w-9 rounded-full ring-1 ring-[#2b3649] bg-[#0f172a] text-edp-iceblue-100 font-semibold mr-2"
                                    title="{{ $displayName }}" aria-label="Perfil de {{ $displayName }}">
                                    {{ $initials ?: 'US' }}
                                </span>
                            </button>

                            {{-- Logout (POST) --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    class="rounded-lg px-3 py-1.5 text-sm font-medium text-white bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 focus:ring-offset-[#0f172a]">
                                    Sair
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-brand" wire:navigate>Entrar</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @hasSection('breadcrumb')
            <div class="app-container py-2 border-t border-zinc-700/50">
                <div class="flex items-center text-sm text-zinc-400">
                    @yield('breadcrumb')
                </div>
            </div>
        @endif
    </header>

    {{-- MAIN (container sem cor) --}}
    <main class="app-container app-main">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- FOOTER GLOBAL: também full width de fundo, conteúdo centralizado --}}
    <footer class="w-full">
        <div class="app-container text-xs text-zinc-500 py-8">
            © {{ date('Y') }} SicodeSK • CIP • EDP
        </div>
    </footer>

    @livewireScripts
    @stack('modals')
    @stack('scripts')

    @if (session('status'))
        <script>
            window.addEventListener('load', () => {
                window.dispatchEvent(new CustomEvent('sweet-alert', {
                    detail: {
                        type: 'success',
                        title: 'Tudo certo!',
                        text: @json(session('status')),
                        toast: true,
                    }
                }));
            });
        </script>
    @endif
</body>

</html>
