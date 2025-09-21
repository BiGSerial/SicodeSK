<div class="rounded-xl border border-[#2b3649] bg-[#0f172a]">
    <div class="px-4 py-3 border-b border-[#2b3649] flex items-center justify-between">
        <h2 class="text-lg font-semibold">Meus tickets recentes</h2>
        <a href="#" class="text-sm text-edp-iceblue-100 hover:underline">Ver todos</a>
    </div>

    <ul class="divide-y divide-[#2b3649]">
        @forelse ($items as $t)
            <li class="px-4 py-3 flex items-center justify-between">
                <div>
                    <p class="font-medium"><span class="text-edp-verde-70">[{{ $t['key'] }}]</span>
                        {{ $t['title'] }}</p>
                    <p class="text-xs text-zinc-400">{{ $t['meta'] }}</p>
                </div>
                <span
                    class="text-xs rounded-full px-2 py-1 {{ $t['badge']['class'] }}">{{ $t['badge']['label'] }}</span>
            </li>
        @empty
            <li class="px-4 py-8 text-zinc-400 text-center">Nenhum ticket por aqui…</li>
        @endforelse
    </ul>
</div>
