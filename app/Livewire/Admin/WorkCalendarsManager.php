<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ChecksAdminAccess;
use App\Models\Area;
use App\Models\WorkCalendar;
use App\Models\WorkCalendarHoliday;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Livewire\Component;

class WorkCalendarsManager extends Component
{
    use ChecksAdminAccess;

    public ?int $selectedCalendarId = null;
    public bool $showForm = false;

    public array $calendarForm = [
        'name' => '',
        'workweek' => [],
    ];

    public array $holidayForm = [
        'holiday_date' => '',
        'label' => '',
    ];

    public ?WorkCalendar $editing = null;

    protected $listeners = [
        'admin-tab-changed' => 'handleTabChanged',
    ];

    public function mount(): void
    {
        $this->ensureAdminAccess();
        $this->calendarForm['workweek'] = $this->defaultWorkweek();
    }

    public function render()
    {
        $calendars = WorkCalendar::query()
            ->withCount('holidays')
            ->orderBy('name')
            ->get();

        if ($calendars->isNotEmpty() && !$calendars->pluck('id')->contains($this->selectedCalendarId)) {
            $this->selectedCalendarId = $calendars->first()->id;
        }

        $selected = $this->selectedCalendarId
            ? WorkCalendar::with(['holidays', 'areas:id,name,work_calendar_id'])->find($this->selectedCalendarId)
            : null;

        $areas = Area::query()
            ->with('workCalendar')
            ->orderBy('name')
            ->get(['id', 'name', 'work_calendar_id']);

        return view('livewire.admin.work-calendars-manager', [
            'calendars' => $calendars,
            'selectedCalendar' => $selected,
            'areas' => $areas,
            'weekdays' => $this->weekdays(),
        ]);
    }

    public function handleTabChanged($payload = null): void
    {
        $tab = is_array($payload) ? ($payload['tab'] ?? null) : $payload;

        if ($tab !== 'calendars') {
            return;
        }

        $this->dispatch('$refresh');
    }

    public function selectCalendar(int $calendarId): void
    {
        $this->selectedCalendarId = $calendarId;
        $this->showForm = false;
        $this->editing = null;
        $this->resetErrorBag();
    }

    public function openCreate(): void
    {
        $this->editing = null;
        $this->calendarForm = [
            'name' => '',
            'workweek' => $this->defaultWorkweek(),
        ];
        $this->showForm = true;
        $this->resetValidation();
    }

    public function openEdit(int $calendarId): void
    {
        $calendar = WorkCalendar::findOrFail($calendarId);
        $this->editing = $calendar;
        $this->calendarForm = [
            'name' => $calendar->name,
            'workweek' => $this->mergeWorkweek($calendar->workweek ?? []),
        ];
        $this->showForm = true;
        $this->resetValidation();
    }

    public function saveCalendar(): void
    {
        $this->validate([
            'calendarForm.name' => ['required', 'string', 'max:80', Rule::unique('work_calendars', 'name')->ignore($this->editing?->id)],
        ], [], [
            'calendarForm.name' => 'nome',
        ]);

        $payload = [
            'name' => trim($this->calendarForm['name']),
            'workweek' => $this->sanitizeWorkweek($this->calendarForm['workweek']),
        ];

        if (!$payload['workweek']) {
            $this->addError('calendarForm.workweek', 'Defina ao menos um período ativo na semana.');
            return;
        }

        $calendar = $this->editing ?? new WorkCalendar();
        $calendar->fill($payload);
        $calendar->save();

        $this->selectedCalendarId = $calendar->id;
        $this->editing = null;
        $this->showForm = false;
        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Calendário salvo',
            'toast' => true,
        ]);
        $this->dispatch('catalog-data-updated');
    }

    public function deleteCalendar(int $calendarId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover calendário de trabalho?',
            'text' => 'Áreas associadas ficarão sem calendário até nova definição.',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'confirmDeleteCalendar',
            'payload' => ['calendar_id' => $calendarId],
            'componentId' => $this->getId(),
        ]);
    }

    public function confirmDeleteCalendar($payload = null): void
    {
        $calendarId = is_array($payload) ? ($payload['calendar_id'] ?? null) : $payload;
        if (!$calendarId) {
            return;
        }

        $calendar = WorkCalendar::with('areas')->find($calendarId);

        if (!$calendar) {
            return;
        }

        foreach ($calendar->areas as $area) {
            $area->work_calendar_id = null;
            $area->save();
        }

        $calendar->delete();

        if ($this->selectedCalendarId === $calendarId) {
            $this->selectedCalendarId = WorkCalendar::value('id');
        }

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Calendário removido',
            'toast' => true,
        ]);
        $this->dispatch('catalog-data-updated');
    }

    public function addHoliday(): void
    {
        if (!$this->selectedCalendarId) {
            return;
        }

        $this->validate([
            'holidayForm.holiday_date' => ['required', 'date', Rule::unique('work_calendar_holidays', 'holiday_date')->where('work_calendar_id', $this->selectedCalendarId)],
            'holidayForm.label' => ['nullable', 'string', 'max:120'],
        ], [], [
            'holidayForm.holiday_date' => 'data',
            'holidayForm.label' => 'descrição',
        ]);

        WorkCalendarHoliday::create([
            'work_calendar_id' => $this->selectedCalendarId,
            'holiday_date' => $this->holidayForm['holiday_date'],
            'label' => $this->holidayForm['label'] ?: null,
        ]);

        $this->holidayForm = ['holiday_date' => '', 'label' => ''];

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Feriado adicionado',
            'toast' => true,
        ]);
        $this->dispatch('catalog-data-updated');
    }

    public function deleteHoliday(int $holidayId): void
    {
        $this->dispatch('sweet-confirm', [
            'title' => 'Remover feriado?',
            'icon' => 'warning',
            'confirmButtonText' => 'Remover',
            'cancelButtonText' => 'Cancelar',
            'callback' => 'confirmDeleteHoliday',
            'payload' => ['holiday_id' => $holidayId],
            'componentId' => $this->getId(),
        ]);
    }

    public function confirmDeleteHoliday($payload = null): void
    {
        $holidayId = is_array($payload) ? ($payload['holiday_id'] ?? null) : $payload;
        if (!$holidayId) {
            return;
        }

        WorkCalendarHoliday::whereKey($holidayId)->delete();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Feriado removido',
            'toast' => true,
        ]);
        $this->dispatch('catalog-data-updated');
    }

    public function updateAreaCalendar(int $areaId, $calendarId = null): void
    {
        $area = Area::findOrFail($areaId);
        $area->work_calendar_id = $calendarId ? (int) $calendarId : null;
        $area->save();

        $this->dispatch('sweet-alert', [
            'type' => 'success',
            'title' => 'Área atualizada',
            'toast' => true,
        ]);
        $this->dispatch('catalog-data-updated');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->editing = null;
    }

    private function defaultWorkweek(): array
    {
        $default = [];
        foreach ($this->weekdays() as $key => $label) {
            $default[$key] = [
                'enabled' => in_array($key, ['mon', 'tue', 'wed', 'thu', 'fri'], true),
                'start' => '08:00',
                'end' => '17:00',
            ];
        }

        return $default;
    }

    private function mergeWorkweek(array $existing): array
    {
        $merged = $this->defaultWorkweek();

        foreach ($existing as $key => $value) {
            if (!isset($merged[$key])) {
                continue;
            }

            $merged[$key]['enabled'] = true;
            $merged[$key]['start'] = $value['start'] ?? $merged[$key]['start'];
            $merged[$key]['end'] = $value['end'] ?? $merged[$key]['end'];
        }

        return $merged;
    }

    private function sanitizeWorkweek(array $workweek): array
    {
        $clean = [];

        foreach ($this->weekdays() as $key => $label) {
            $day = $workweek[$key] ?? null;

            if (!is_array($day) || empty($day['enabled'])) {
                continue;
            }

            $start = $day['start'] ?? null;
            $end = $day['end'] ?? null;

            if (!$this->validTime($start) || !$this->validTime($end)) {
                continue;
            }

            if ($start >= $end) {
                continue;
            }

            $clean[$key] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        return $clean;
    }

    private function validTime(?string $time): bool
    {
        return is_string($time) && preg_match('/^([01]?\d|2[0-3]):[0-5]\d$/', $time);
    }

    private function weekdays(): array
    {
        return [
            'mon' => 'Segunda',
            'tue' => 'Terça',
            'wed' => 'Quarta',
            'thu' => 'Quinta',
            'fri' => 'Sexta',
            'sat' => 'Sábado',
            'sun' => 'Domingo',
        ];
    }
}
