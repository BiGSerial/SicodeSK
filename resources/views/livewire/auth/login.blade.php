<div class="min-h-screen flex items-center justify-center px-4 bg-[#0b1220]">
    <div class="w-full max-w-md bg-[#1b2535] rounded-xl shadow-2xl p-8 border border-[#2b3649]">
        <div class="text-center mb-6">
            <div class="mx-auto h-14 w-14 rounded-lg grid place-items-center font-bold text-white"
                style="background: linear-gradient(135deg, #0CD3F8, #263CC8);">
                SD
            </div>
            <div class="mt-5 flex items-center justify-center">
                <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="" style="height: 30px;" class="mr-0">
                <span class="text-edp-verde-100 text-2xl ml-0">sicodeSK</span>
            </div>
            <p class="mt-1 text-sm text-gray-400">Entre com suas credenciais do SICODE</p>
        </div>

        @if (session('status'))
            <div class="mb-4 text-sm text-emerald-400 bg-emerald-900/40 border border-emerald-700 rounded p-3">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit.prevent="login" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-300">E-mail</label>
                <input type="email" wire:model.defer="email" required autofocus autocomplete="email"
                    class="mt-1 block w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="voce@sicode.com.br" />
                @error('email')
                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300">Senha</label>
                <input type="password" wire:model.defer="password" required autocomplete="current-password"
                    class="mt-1 block w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="••••••••" />
                @error('password')
                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" wire:model="remember"
                        class="rounded border-gray-600 text-sky-500 bg-[#0f172a]">
                    Lembrar-me
                </label>
            </div>

            <button type="submit"
                class="w-full rounded-lg px-4 py-2.5 text-white font-medium
                           bg-gradient-to-r from-sky-600 to-blue-700
                           hover:from-sky-500 hover:to-blue-600
                           focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 focus:ring-offset-[#1b2535]"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Entrar</span>
                <span wire:loading>Entrando…</span>
            </button>
        </form>
    </div>
</div>
