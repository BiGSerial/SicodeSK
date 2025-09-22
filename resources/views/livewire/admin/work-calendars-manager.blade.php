<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[300px,1fr]">
        <aside class="rounded-xl border border-[#2b3649] bg-[#0f172a] p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-100">Calendários de trabalho</h3>
                    <p class="text-xs text-zinc-400">Defina jornadas e feriados que impactam SLAs.</p>
                </div>
                <button wire:click="openCreate"
                    class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                    Novo
                </button>
            </div>

            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($calendars as $calendar)
                    <li>
                        <button type="button" wire:click="selectCalendar({{ $calendar->id }})"
                            @class([
                                'w-full rounded-lg border px-3 py-2 text-left transition',
                                'border-edp-iceblue-100 bg-edp-iceblue-100/10 text-edp-iceblue-100' => $selectedCalendar?->id === $calendar->id,
                                'border-[#2b3649] bg-[#101a2c] text-zinc-200 hover:border-edp-iceblue-100/60' => $selectedCalendar?->id !== $calendar->id,
                            ])>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium">{{ $calendar->name }}</span>
                                <span class="text-[11px] text-zinc-500">{{ $calendar->holidays_count }} feriados</span>
                            </div>
                        </button>
                    </li>
                @empty
                    <li class="rounded-lg border border-[#2b3649] bg-[#101a2c] px-3 py-4 text-center text-xs text-zinc-400">
                        Nenhum calendário cadastrado ainda.
                    </li>
                @endforelse
            </ul>
        </aside>

        <section class="space-y-6 rounded-xl border border-[#2b3649] bg-[#0f172a] p-6">
            <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-100">
                        {{ $editing ? 'Editar calendário' : ($selectedCalendar?->name ?? 'Novo calendário') }}
                    </h2>
                    <p class="text-xs text-zinc-400">Configure horários úteis e feriados considerados nos SLAs.</p>
                </div>

                @if ($selectedCalendar && !$showForm)
                    <div class="flex items-center gap-2">
                        <button wire:click="openEdit({{ $selectedCalendar->id }})"
                            class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10">
                            Editar
                        </button>
                        <button wire:click="deleteCalendar({{ $selectedCalendar->id }})"
                            class="rounded-lg border border-rose-500/50 px-3 py-1.5 text-xs text-rose-200 hover:bg-rose-500/10">
                            Remover
                        </button>
                    </div>
                @endif
            </header>

            @if ($showForm)
                <form wire:submit.prevent="saveCalendar" class="space-y-5 text-sm">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-wide text-zinc-400">Nome</label>
                        <input type="text" wire:model.defer="calendarForm.name"
                            class="w-full rounded-lg border border-[#334155] bg-[#101a2c] px-3 py-2 text-zinc-100">
                        @error('calendarForm.name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Jornada semanal</h3>
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($weekdays as $key => $label)
                                <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] p-3">
                                    <div class="flex items-center justify-between text-xs text-zinc-300">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" wire:model.live="calendarForm.workweek.{{ $key }}.enabled"
                                                class="rounded border-[#334155] bg-[#0f172a] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                            {{ $label }}
                                        </label>
                                        <span class="text-[10px] text-zinc-500">{{ $key }}</span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                                        <div>
                                            <label class="mb-1 block text-[10px] uppercase tracking-wide text-zinc-500">Início</label>
                                            <input type="time" wire:model.live="calendarForm.workweek.{{ $key }}.start"
                                                class="w-full rounded border border-[#334155] bg-[#0f172a] px-2 py-1 text-zinc-100"
                                                @disabled(empty($calendarForm['workweek'][$key]['enabled']))>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] uppercase tracking-wide text-zinc-500">Fim</label>
                                            <input type="time" wire:model.live="calendarForm.workweek.{{ $key }}.end"
                                                class="w-full rounded border border-[#334155] bg-[#0f172a] px-2 py-1 text-zinc-100"
                                                @disabled(empty($calendarForm['workweek'][$key]['enabled']))>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('calendarForm.workweek')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="cancel"
                            class="rounded-lg border border-[#2b3649] px-3 py-2 text-xs text-zinc-300 hover:bg-[#121a2a]">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="rounded-lg border border-edp-iceblue-100 bg-edp-iceblue-100/10 px-3 py-2 text-xs font-medium text-edp-iceblue-100 hover:bg-edp-iceblue-100/20">
                            Salvar calendário
                        </button>
                    </div>
                </form>
            @else
                @if ($selectedCalendar)
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm text-zinc-200">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Jornada</h3>
                            <ul class="mt-3 space-y-1 text-xs">
                                @foreach ($weekdays as $key => $label)
                                    @php
                                        $schedule = $selectedCalendar->workweek[$key] ?? null;
                                    @endphp
                                    <li class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ $label }}</span>
                                        @if ($schedule)
                                            <span>{{ $schedule['start'] }} – {{ $schedule['end'] }}</span>
                                        @else
                                            <span class="text-zinc-600">Folga</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm text-zinc-200">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Áreas vinculadas</h3>
                            <ul class="mt-3 space-y-1 text-xs text-zinc-300">
                                @php
                                    $linkedAreas = $selectedCalendar->areas->pluck('name')->sort();
                                @endphp
                                @forelse ($linkedAreas as $name)
                                    <li>{{ $name }}</li>
                                @empty
                                    <li class="text-zinc-500">Nenhuma área associada.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-zinc-400">Selecione ou crie um calendário para começar.</p>
                @endif
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Adicionar feriado</h3>
                    <form wire:submit.prevent="addHoliday" class="mt-3 space-y-3">
                        <div>
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Data</label>
                            <input type="date" wire:model.defer="holidayForm.holiday_date"
                                class="w-full rounded border border-[#334155] bg-[#0f172a] px-2 py-1.5 text-zinc-100"
                                @disabled(!$selectedCalendar)>
                            @error('holidayForm.holiday_date')
                                <p class="mt-1 text-[11px] text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">Descrição</label>
                            <input type="text" wire:model.defer="holidayForm.label"
                                class="w-full rounded border border-[#334155] bg-[#0f172a] px-2 py-1.5 text-zinc-100"
                                placeholder="Opcional"
                                @disabled(!$selectedCalendar)>
                            @error('holidayForm.label')
                                <p class="mt-1 text-[11px] text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="rounded-lg border border-edp-iceblue-100 px-3 py-1.5 text-xs text-edp-iceblue-100 hover:bg-edp-iceblue-100/10"
                            @disabled(!$selectedCalendar)>
                            Adicionar feriado
                        </button>
                    </form>

                    @if ($selectedCalendar)
                        <ul class="mt-4 space-y-2 text-xs text-zinc-300">
                            @forelse ($selectedCalendar->holidays as $holiday)
                                <li class="flex items-center justify-between rounded border border-[#2b3649] bg-[#101a2c] px-3 py-2">
                                    <span>
                                        {{ $holiday->holiday_date->format('d/m/Y') }}
                                        @if ($holiday->label)
                                            • {{ $holiday->label }}
                                        @endif
                                    </span>
                                    <button wire:click="deleteHoliday({{ $holiday->id }})"
                                        class="text-[11px] text-rose-300 hover:underline">
                                        Remover
                                    </button>
                                </li>
                            @empty
                                <li class="rounded border border-[#2b3649] bg-[#101a2c] px-3 py-2 text-center text-xs text-zinc-500">
                                    Nenhum feriado cadastrado.
                                </li>
                            @endforelse
                        </ul>
                    @endif
                </div>

                <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] p-4 text-sm">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Vínculo com áreas</h3>
                    <p class="text-[11px] text-zinc-500">Selecione o calendário para cada área para que os SLAs respeitem a jornada configurada.</p>
                    <div class="mt-3 space-y-2">
                        @foreach ($areas as $area)
                            <div class="flex items-center justify-between gap-3 rounded border border-[#2b3649] bg-[#101a2c] px-3 py-2 text-xs text-zinc-300">
                                <span>{{ $area->name }}</span>
                                <select wire:change="updateAreaCalendar({{ $area->id }}, $event.target.value)"
                                    class="rounded border border-[#334155] bg-[#0f172a] px-2 py-1 text-zinc-100">
                                    <option value="">Sem calendário</option>
                                    @foreach ($calendars as $calendar)
                                        <option value="{{ $calendar->id }}" @selected($area->work_calendar_id === $calendar->id)>
                                            {{ $calendar->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
