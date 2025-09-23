<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'workweek',
    ];

    protected $casts = [
        'workweek' => 'array',
    ];

    public function holidays(): HasMany
    {
        return $this->hasMany(WorkCalendarHoliday::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function getDailySchedule(string $weekday): ?array
    {
        $normalized = $this->normalizeWeekdayKey($weekday);
        $workweek = $this->workweek ?? [];

        if (!$normalized || !is_array($workweek) || empty($workweek[$normalized])) {
            return null;
        }

        $schedule = $workweek[$normalized];

        if (!is_array($schedule)) {
            return null;
        }

        $start = $schedule['start'] ?? null;
        $end = $schedule['end'] ?? null;

        if (!$start || !$end) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    private function normalizeWeekdayKey(string $weekday): ?string
    {
        $map = [
            'monday' => 'mon',
            'mon' => 'mon',
            'tuesday' => 'tue',
            'tue' => 'tue',
            'wednesday' => 'wed',
            'wed' => 'wed',
            'thursday' => 'thu',
            'thu' => 'thu',
            'friday' => 'fri',
            'fri' => 'fri',
            'saturday' => 'sat',
            'sat' => 'sat',
            'sunday' => 'sun',
            'sun' => 'sun',
        ];

        $normalized = strtolower($weekday);

        return $map[$normalized] ?? null;
    }

    public function isHoliday(\DateTimeInterface $date): bool
    {
        $formatted = $date->format('Y-m-d');

        if ($this->relationLoaded('holidays')) {
            return $this->holidays
                ->contains(fn (WorkCalendarHoliday $holiday) => $holiday->holiday_date === $formatted);
        }

        return $this->holidays()
            ->whereDate('holiday_date', $formatted)
            ->exists();
    }
}
