<div class="min-h-screen bg-[#0b1220] text-zinc-100">
    <header class="border-b border-[#2b3649] bg-[#0f172a]">
        <div class="mx-auto max-w-5xl px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/EDP-Logo-white.svg') }}" alt="EDP" class="h-7">
                <span class="text-edp-verde-100 text-xl font-semibold tracking-wide">sicodeSK</span>
            </div>
            <div class="text-sm text-zinc-300">New ticket</div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        <!-- Steps header -->
        <ol class="mb-6 flex items-center gap-3 text-sm">
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 1 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">1</span>
                <span class="{{ $step >= 1 ? 'text-zinc-100' : 'text-zinc-400' }}">Area & Type</span>
            </li>
            <span class="w-8 h-px bg-[#2b3649]"></span>
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 2 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">2</span>
                <span class="{{ $step >= 2 ? 'text-zinc-100' : 'text-zinc-400' }}">Classification</span>
            </li>
            <span class="w-8 h-px bg-[#2b3649]"></span>
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 3 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">3</span>
                <span class="{{ $step >= 3 ? 'text-zinc-100' : 'text-zinc-400' }}">Details</span>
            </li>
            <span class="w-8 h-px bg-[#2b3649]"></span>
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 4 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">4</span>
                <span class="{{ $step >= 4 ? 'text-zinc-100' : 'text-zinc-400' }}">Review</span>
            </li>
        </ol>

        <div class="grid md:grid-cols-[1fr_320px] gap-6">
            <!-- Left: form -->
            <div class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
                {{-- STEP 1 --}}
                @if ($step === 1)
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Area</label>
                            <select wire:model.live="areaId"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                <option value="">Select an area…</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a['id'] }}">{{ $a['name'] }}</option>
                                @endforeach
                            </select>
                            @error('areaId')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Ticket type</label>
                            <select wire:model.live="ticketTypeId"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                {{ !$areaId ? 'disabled' : '' }}>
                                <option value="">Select a type…</option>
                                @foreach ($ticketTypes as $t)
                                    <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                                @endforeach
                            </select>
                            @error('ticketTypeId')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- STEP 2 --}}
                @if ($step === 2)
                    <div class="space-y-5">
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm text-zinc-300 mb-1">Category</label>
                                <select wire:model.live="categoryId"
                                    class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                    <option value="">(optional)</option>
                                    @foreach ($categories as $c)
                                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('categoryId')
                                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm text-zinc-300 mb-1">Subcategory</label>
                                <select wire:model.live="subcategoryId"
                                    class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                    {{ !$categoryId ? 'disabled' : '' }}>
                                    <option value="">(optional)</option>
                                    @foreach ($subcategories as $s)
                                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('subcategoryId')
                                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Priority</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach (['low', 'medium', 'high', 'urgent'] as $p)
                                    <button type="button" wire:click="$set('priority','{{ $p }}')"
                                        class="rounded-lg border px-3 py-2 text-sm {{ $priority === $p ? 'border-edp-iceblue-100 bg-[#0f172a]' : 'border-[#334155] bg-[#0f172a] opacity-80 hover:opacity-100' }}">
                                        {{ ucfirst($p) }}
                                    </button>
                                @endforeach
                            </div>
                            @error('priority')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- STEP 3 --}}
                @if ($step === 3)
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Title</label>
                            <input type="text" wire:model.defer="title"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                placeholder="Short summary (e.g. 'Implement new SICODE feature')" />
                            @error('title')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Description</label>
                            <textarea wire:model.defer="description" rows="6"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                placeholder="Context, acceptance criteria, links, attachments (paste)"></textarea>
                            @error('description')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- STEP 4 (Review) --}}
                @if ($step === 4)
                    <div class="space-y-4">
                        <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                            <h3 class="font-semibold mb-2">Summary</h3>
                            <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-zinc-400">Area</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($areas)->firstWhere('id', $this->areaId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Ticket type</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($ticketTypes)->firstWhere('id', $this->ticketTypeId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Category</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($categories)->firstWhere('id', $this->categoryId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Subcategory</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($subcategories)->firstWhere('id', $this->subcategoryId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Priority</dt>
                                    <dd class="text-zinc-100 capitalize">{{ $priority }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">SLA (preview)</dt>
                                    <dd class="text-zinc-100">{{ $slaPreview ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                            <h3 class="font-semibold mb-2">Details</h3>
                            <p class="text-sm"><span class="text-zinc-400">Title:</span> {{ $title ?: '-' }}</p>
                            <p class="text-sm mt-1"><span class="text-zinc-400">Description:</span></p>
                            <div class="mt-1 text-sm text-zinc-200 whitespace-pre-line">{{ $description ?: '-' }}</div>
                        </div>
                    </div>
                @endif

                {{-- Nav buttons --}}
                <div class="mt-6 flex items-center justify-between">
                    <button type="button" wire:click="prev"
                        class="rounded-lg px-4 py-2 text-sm border border-[#2b3649] bg-[#0f172a] hover:bg-[#121a2a]"
                        @disabled($step === 1)>
                        Back
                    </button>

                    @if ($step < 4)
                        <button type="button" wire:click="next"
                            class="rounded-lg px-4 py-2.5 text-white font-medium bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600">
                            Next
                        </button>
                    @else
                        <button type="button" wire:click="submit"
                            class="rounded-lg px-4 py-2.5 text-white font-medium bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400">
                            Create ticket
                        </button>
                    @endif
                </div>
            </div>

            <!-- Right: SLA & tips -->
            <aside class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-5">
                <h3 class="font-semibold">SLA & Guidance</h3>
                <p class="text-sm text-zinc-400 mt-1">
                    Choose the area and type; classification tunes routing and SLA.
                </p>

                <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <div class="text-sm text-zinc-400">SLA (estimated)</div>
                    <div class="mt-1 text-2xl font-semibold">
                        {{ $slaPreview ? $slaPreview : '—' }}
                    </div>
                    <div class="mt-2 text-xs text-zinc-500">
                        Based on priority{{ $areaId ? ' and area' : '' }}{{ $ticketTypeId ? ' / type' : '' }}.
                    </div>
                </div>

                <ul class="mt-4 text-xs text-zinc-400 list-disc pl-5 space-y-1">
                    <li>Provide a clear title and acceptance criteria.</li>
                    <li>Attach links/screens or paste content into description.</li>
                    <li>Managers can re-prioritize and assign later.</li>
                </ul>
            </aside>
        </div>

        @if (session('status'))
            <div class="mt-6 text-sm text-emerald-400 bg-emerald-900/40 border border-emerald-700 rounded p-3">
                {{ session('status') }}
            </div>
        @endif
    </main>
</div>
