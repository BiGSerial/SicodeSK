@section('header')
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center">
            <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="EDP" class="h-7">
            <span class="text-edp-verde-100 text-xl font-semibold tracking-wide ml-2">sicodeSK</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden sm:block text-sm text-zinc-300">Olá, {{ auth()->user()->name }}</span>
            <button wire:click="logout"
                class="rounded-lg px-3 py-1.5 text-sm font-medium text-white bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600">
                Sair
            </button>
        </div>
    </div>
@endsection

<div class="min-h-screen bg-[#0b1220] text-zinc-100">
    {{-- Conteúdo (já está dentro do <main class="app-container app-main"> do layout) --}}
    <section class="surface p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold">
                    Bem-vindo ao <span class="text-edp-iceblue-100">SicodeSK</span>
                </h1>
                <p class="mt-1 text-zinc-400">
                    Abra, acompanhe e conclua tickets da área CIP com agilidade e rastreabilidade.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tickets.create') }}" class="btn-brand" wire:navigate>+ Novo ticket</a>
                <a href="{{ route('tickets.index') }}"
                    class="rounded-lg px-4 py-2.5 text-sm font-medium border border-[#2b3649] bg-[#0f172a] hover:bg-[#121a2a]"
                    wire:navigate>
                    Ver meus tickets
                </a>
            </div>
        </div>
    </section>

    {{-- KPIs --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($kpis as $kpi)
            <div class="surface p-4">
                <div class="text-sm text-zinc-400">{{ $kpi['label'] }}</div>
                <div class="mt-2 text-3xl font-semibold {{ $kpi['accent'] }}">{{ $kpi['value'] }}</div>
                <div class="mt-1 text-xs text-zinc-500">{{ $kpi['muted'] }}</div>
            </div>
        @endforeach
    </section>

    {{-- Tickets Recentes (Livewire) --}}
    <section class="mt-8">
        <livewire:tickets.recent />
    </section>
</div>
