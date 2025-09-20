<!doctype html>
<html lang="pt-BR" class="h-full dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'sicodeSK') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen m-0 w-full bg-[var(--ui-bg)] text-[var(--ui-text)] antialiased">
    {{-- HEADER GLOBAL: full width de fundo; conteúdo centralizado --}}
    <header class="topbar w-full">
        <div class="app-container app-header flex items-center justify-between">
            @hasSection('header')
                @yield('header')
            @else
                <div class="flex items-center">
                    <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="EDP" class="h-7 mr-0">
                    <span class="text-edp-verde-100 text-lg font-semibold ml-0">sicodeSK</span>
                </div>
                <div class="flex items-center gap-3">
                    @yield('actions')
                </div>
            @endif
        </div>
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
</body>

</html>
