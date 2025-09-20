<!doctype html>
<html lang="pt-BR" class="h-full dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SicodeSK') }}</title>

    {{-- Fonte opcional --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    @yield('styles')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="h-full bg-[var(--ui-bg)] text-[var(--ui-text)] antialiased">
    {{-- Topbar fixa do app (usa slot $header opcional) --}}
    <header class="topbar">
        <div class="app-container app-header flex items-center justify-between">
            {{-- Esquerda: logo + título padrão; pode ser sobrescrito pelo @section('header') --}}
            @hasSection('header')
                @yield('header')
            @else
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="EDP" class="h-7">
                    <span class="text-edp-verde-100 text-lg font-semibold tracking-wide">sicodeSK</span>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Espaço para ações globais (login/logout, etc.) --}}
                    @yield('actions')
                </div>
            @endif
        </div>
    </header>

    {{-- Conteúdo principal (largura estável) --}}
    <main class="app-container app-main">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- Footer simples e estável --}}
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
