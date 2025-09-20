<!doctype html>
<html lang="pt-BR" class="h-full dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'sicodeSK') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="h-full bg-[var(--ui-bg)] text-[var(--ui-text)] antialiased">
    {{-- HEADER GLOBAL --}}
    <header class="topbar">
        <div class="app-container app-header flex items-center justify-between">
            @hasSection('header')
                @yield('header')
            @else
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="EDP" class="h-7 ml-">
                    <span class="text-edp-verde-100 text-lg font-semibold ml-0">sicodeSK</span>
                </div>
                <div class="flex items-center gap-3">
                    @yield('actions')
                </div>
            @endif
        </div>
    </header>

    {{-- MAIN --}}
    <main class="app-container app-main">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- FOOTER GLOBAL --}}
    <footer class="app-footer">
        <div class="app-container text-xs text-zinc-500">
            © {{ date('Y') }} SicodeSK • CIP • EDP
        </div>
    </footer>

    @livewireScripts
    @stack('modals')
    @stack('scripts')
</body>

</html>
