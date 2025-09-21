<!doctype html>
<html lang="pt-BR" class="h-full w-full dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'sicodeSK') }}</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="max-h-screen w-full m-0 bg-[var(--ui-bg)] text-[var(--ui-text)] antialiased">

    {{-- MAIN ocupa todo o espaço disponível --}}
    <main class="app-container app-main flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- FOOTER sempre visível no final da tela --}}
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
