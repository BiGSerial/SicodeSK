<!doctype html>
<html lang="pt-BR" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SicodeDesk') }}</title>

    {{-- Fonte opcional --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    @yield('styles')

    {{-- Scripts --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="h-full bg-marineblue-100 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100 antialiased">
    <main>
        {{-- @yield('content') --}}
        {{ $slot ?? '' }}
    </main>

    @livewireScripts
    @stack('modals')
    @stack('scripts')
</body>

</html>
