<div class="space-y-6 text-sm text-zinc-100">
    <form wire:submit.prevent="save" class="space-y-6">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
            <h3 class="text-base font-semibold">Anexos</h3>
            <p class="mt-1 text-xs text-zinc-400">Controle tamanho e tipos de arquivo aceitos no sistema.</p>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Tamanho máximo (MB)</label>
                    <input type="number" min="1" max="2048" wire:model.defer="form.attachments.max_size_mb"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                    @error('form.attachments.max_size_mb')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Máx. anexos por ticket</label>
                    <input type="number" min="1" max="50" wire:model.defer="form.attachments.max_per_ticket"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100">
                    @error('form.attachments.max_per_ticket')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Extensões permitidas</label>
                    <textarea rows="3" wire:model.defer="form.attachments.allowed_extensions"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                        placeholder="Ex.: pdf, jpg, png"></textarea>
                    <p class="mt-1 text-[11px] text-zinc-500">Informe uma lista separada por vírgulas. Deixe vazio para permitir qualquer formato.</p>
                    @error('form.attachments.allowed_extensions')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Extensões bloqueadas</label>
                    <textarea rows="3" wire:model.defer="form.attachments.blocked_extensions"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                        placeholder="Ex.: exe, bat"></textarea>
                    <p class="mt-1 text-[11px] text-zinc-500">Bloqueia extensões mesmo que estejam na lista de permitidos.</p>
                    @error('form.attachments.blocked_extensions')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
            <h3 class="text-base font-semibold">Permissões</h3>
            <p class="mt-1 text-xs text-zinc-400">Defina quem pode abrir tickets e participar das conversas.</p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Quem pode criar tickets</h4>
                    <div class="mt-3 space-y-2 text-xs text-zinc-300">
                        @foreach ($roleOptions as $value => $label)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.defer="form.permissions.ticket_creation_roles" value="{{ $value }}"
                                    class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('form.permissions.ticket_creation_roles')
                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Quem pode comentar</h4>
                    <div class="mt-3 space-y-2 text-xs text-zinc-300">
                        @foreach ($roleOptions as $value => $label)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.defer="form.permissions.comment_roles" value="{{ $value }}"
                                    class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('form.permissions.comment_roles')
                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6">
            <h3 class="text-base font-semibold">Notificações</h3>
            <p class="mt-1 text-xs text-zinc-400">Ajuste como o sistema alerta equipes sobre riscos de SLA.</p>

            <div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-zinc-300">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="form.notifications.sla_breach_email"
                        class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                    Enviar email quando o SLA estiver prestes a estourar
                </label>
                <div>
                    <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Antecedência (minutos)</label>
                    <input type="number" min="1" max="600" wire:model.defer="form.notifications.sla_breach_minutes_before"
                        class="w-full rounded-lg border border-[#334155] bg-[#0f172a] px-3 py-2 text-zinc-100"
                        placeholder="30">
                    @error('form.notifications.sla_breach_minutes_before')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-2">
            <button type="submit"
                class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-2 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                Salvar configurações
            </button>
        </div>
    </form>
</div>
