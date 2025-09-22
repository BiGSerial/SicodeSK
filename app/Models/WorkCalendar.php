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
        $normalized = strtolower($weekday);
        $workweek = $this->workweek ?? [];

        if (!is_array($workweek) || empty($workweek[$normalized])) {
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
