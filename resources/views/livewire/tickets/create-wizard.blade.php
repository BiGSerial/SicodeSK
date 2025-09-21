<div class=" text-zinc-100">


    @section('breadcrumb')
        <a href="{{ route('tickets.index') }}" class="hover:underline">Tickets</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">Novo Ticket</span>
    @endsection

    <div class="py-3">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ url()->previous() }}" class="mr-3 text-zinc-100 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <span class="text-zinc-400 text-sm">Tickets</span>
            </div>
            <div>
                <a href="{{ route('tickets.create') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600">
                    + Novo Ticket
                </a>
            </div>
        </div>
    </div>
    <main class="mx-auto px-4 py-8">
        <!-- Steps header -->
        <ol class="mb-6 flex items-center gap-3 text-sm">
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 1 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">1</span>
                <span class="{{ $step >= 1 ? 'text-zinc-100' : 'text-zinc-400' }}">Area e Tipo</span>
            </li>
            <span class="w-8 h-px bg-[#2b3649]"></span>
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 2 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">2</span>
                <span class="{{ $step >= 2 ? 'text-zinc-100' : 'text-zinc-400' }}">Classificação</span>
            </li>
            <span class="w-8 h-px bg-[#2b3649]"></span>
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 3 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">3</span>
                <span class="{{ $step >= 3 ? 'text-zinc-100' : 'text-zinc-400' }}">Detalhes</span>
            </li>
            <span class="w-8 h-px bg-[#2b3649]"></span>
            <li class="flex items-center gap-2">
                <span
                    class="size-6 grid place-items-center rounded-full border {{ $step >= 4 ? 'bg-edp-iceblue-100 text-[#0b1220] border-edp-iceblue-100' : 'border-[#2b3649] text-zinc-400' }}">4</span>
                <span class="{{ $step >= 4 ? 'text-zinc-100' : 'text-zinc-400' }}">Revisão</span>
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
                                <option value="">Selecione uma área…</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a['id'] }}">{{ $a['name'] }}</option>
                                @endforeach
                            </select>
                            @error('areaId')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Tipo de Ticket</label>
                            <select wire:model.live="ticketTypeId"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                {{ !$areaId ? 'disabled' : '' }}>
                                <option value="">Selecione um tipo…</option>
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
                                <label class="block text-sm text-zinc-300 mb-1">Categoria</label>
                                <select wire:model.live="categoryId"
                                    class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                                    <option value="">(opcional)</option>
                                    @foreach ($categories as $c)
                                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('categoryId')
                                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm text-zinc-300 mb-1">Subcategoria</label>
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
                            <label class="block text-sm text-zinc-300 mb-1">Prioridade</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach ($priorities as $priorityOption)
                                    @php
                                        $active = (int) $priorityId === (int) $priorityOption['id'];
                                    @endphp
                                    <button type="button" wire:click="$set('priorityId', {{ $priorityOption['id'] }})"
                                        class="rounded-lg border px-3 py-2 text-sm transition {{ $priorityId == $priorityOption['id'] ? 'border-edp-iceblue-100 bg-edp-iceblue-100/20 text-edp-iceblue-100 shadow' : 'border-[#334155] bg-[#0f172a] text-zinc-300 hover:border-edp-iceblue-100/60 hover:text-edp-iceblue-100' }}">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block h-2.5 w-2.5 rounded-full"
                                                style="background-color: {{ $priorityOption['color'] ?? ($priorityId == $priorityOption['id'] ? '#38bdf8' : '#22d3ee') }}"></span>
                                            {{ $priorityOption['name'] }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            @error('priorityId')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- STEP 3 --}}
                @if ($step === 3)
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Título</label>
                            <input type="text" wire:model.defer="title"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                placeholder="Resumo curto (ex: 'Implementar nova funcionalidade SICODE')" />
                            @error('title')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-zinc-300 mb-1">Descrição</label>
                            <textarea wire:model.defer="description" rows="6"
                                class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                                placeholder="Contexto, critérios de aceitação, links, anexos (cole aqui)"></textarea>
                            @error('description')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-data="{
                            isDropping: false,
                            handleDrop(event) {
                                this.isDropping = false;
                                const files = event.dataTransfer.files;
                                if (files && files.length) {
                                    this.$refs.fileInput.files = files;
                                    this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            }
                        }" class="space-y-3">
                            <label class="block text-sm text-zinc-300">Anexos</label>
                            <div @dragover.prevent="isDropping = true" @dragleave.prevent="isDropping = false"
                                @drop.prevent="handleDrop($event)" @click="$refs.fileInput.click()"
                                :class="isDropping ? 'border-edp-iceblue-100 bg-[#162036]' :
                                    'border-dashed border-[#334155] bg-[#0f172a]'"
                                class="relative flex flex-col items-center justify-center w-full rounded-lg border-2 py-8 cursor-pointer transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-500"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 3a1 1 0 011 1v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L9 11.586V4a1 1 0 011-1zm-4 12a1 1 0 100 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="mt-2 text-sm text-zinc-300 text-center">
                                    Solte arquivos aqui ou <span class="underline">clique para escolher</span>
                                </p>
                                <p class="text-xs text-zinc-500">Formatos comuns até 10 MB por arquivo</p>
                                <input type="file" multiple x-ref="fileInput" wire:model="attachmentUploads"
                                    class="hidden" />
                            </div>

                            @if ($errors->has('attachments.*'))
                                <p class="text-sm text-red-400">{{ $errors->first('attachments.*') }}</p>
                            @endif

                            @if ($attachmentPreview)
                                <ul class="space-y-2">
                                    @foreach ($attachmentPreview as $key => $file)
                                        <li
                                            class="flex items-center justify-between rounded-lg border border-[#2b3649] bg-[#0f172a] px-3 py-2 text-sm">
                                            <div>
                                                <p class="text-zinc-100">{{ $file['name'] }}</p>
                                                <p class="text-xs text-zinc-500">{{ $file['size'] }}</p>
                                            </div>
                                            <button type="button"
                                                wire:click="requestAttachmentRemoval('{{ $key }}')"
                                                class="text-xs text-rose-300 hover:text-rose-200">Remover</button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- STEP 4 (Review) --}}
                @if ($step === 4)
                    <div class="space-y-4">
                        <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                            <h3 class="font-semibold mb-2">Resumo</h3>
                            <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-zinc-400">Área</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($areas)->firstWhere('id', $this->areaId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Tipo do Ticket</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($ticketTypes)->firstWhere('id', $this->ticketTypeId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Categoria</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($categories)->firstWhere('id', $this->categoryId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Subcategoria</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($subcategories)->firstWhere('id', $this->subcategoryId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">Prioridade</dt>
                                    <dd class="text-zinc-100">
                                        {{ optional(collect($priorities)->firstWhere('id', $priorityId))['name'] ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-400">SLA (previsão)</dt>
                                    <dd class="text-zinc-100">{{ $slaPreview ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                            <h3 class="font-semibold mb-2">Detalhes</h3>
                            <p class="text-sm"><span class="text-zinc-400">Título:</span> {{ $title ?: '-' }}</p>
                            <p class="text-sm mt-1"><span class="text-zinc-400">Descrição:</span></p>
                            <div class="mt-1 text-sm text-zinc-200 whitespace-pre-line">{{ $description ?: '-' }}
                            </div>
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-zinc-300 mb-2">Anexos</p>
                                @if ($attachmentPreview)
                                    <ul class="space-y-2 text-sm">
                                        @foreach ($attachmentPreview as $file)
                                            <li
                                                class="flex items-center justify-between rounded border border-[#2b3649] bg-[#121a2a] px-3 py-2">
                                                <span class="text-zinc-100">{{ $file['name'] }}</span>
                                                <span class="text-xs text-zinc-500">{{ $file['size'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-zinc-500">Nenhum anexo selecionado.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Nav buttons --}}
                <div class="mt-6 flex items-center justify-between">
                    <button type="button" wire:click="prev"
                        class="rounded-lg px-4 py-2 text-sm border border-[#2b3649] bg-[#0f172a] hover:bg-[#121a2a]"
                        @disabled($step === 1)>
                        Voltar
                    </button>

                    @if ($step < 4)
                        <button type="button" wire:click="next"
                            class="rounded-lg px-4 py-2.5 text-white font-medium bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600">
                            Proximo
                        </button>
                    @else
                        <button type="button" wire:click="requestSubmit"
                            class="rounded-lg px-4 py-2.5 text-white font-medium bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400"
                            wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove wire:target="submit">Criar Ticket</span>
                            <span wire:loading wire:target="submit">Enviando...</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Right: SLA & tips -->
            <aside class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-5">
                <h3 class="font-semibold">SLA & Orientações</h3>
                <p class="text-sm text-zinc-400 mt-1">
                    Escolha a área e tipo; a classificação define o encaminhamento e SLA.
                </p>

                <div class="mt-4 rounded-lg border border-[#2b3649] bg-[#0f172a] p-4">
                    <div class="text-sm text-zinc-400">SLA (estimado)</div>
                    <div class="mt-1 text-2xl font-semibold">
                        {{ $slaPreview ? $slaPreview : '—' }}
                    </div>
                    <div class="mt-2 text-xs text-zinc-500">
                        Baseado na prioridade{{ $areaId ? ' e área' : '' }}{{ $ticketTypeId ? ' / tipo' : '' }}.
                    </div>
                </div>

                <ul class="mt-4 text-xs text-zinc-400 list-disc pl-5 space-y-1">
                    <li>Forneça um título claro e critérios de aceitação.</li>
                    <li>Anexe links/telas ou cole o conteúdo na descrição.</li>
                    <li>Gestores podem redefinir prioridades e atribuir posteriormente.</li>
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
