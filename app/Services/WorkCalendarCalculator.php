<?php

namespace App\Services;

use App\Models\WorkCalendar;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class WorkCalendarCalculator
{
    public function addBusinessMinutes(CarbonInterface $start, int $minutes, ?WorkCalendar $calendar = null): CarbonInterface
    {
        $minutes = max(0, $minutes);

        if ($minutes === 0) {
            return $start->copy();
        }

        if (!$calendar) {
            return $start->copy()->addMinutes($minutes);
        }

        $calendar->loadMissing('holidays');

        $current = CarbonImmutable::instance($start);
        $remaining = $minutes;

        if (!$this->hasWorkingHours($calendar)) {
            return $current->addMinutes($remaining);
        }

        while ($remaining > 0) {
            if (!$this->isWorkingDay($current, $calendar)) {
                $current = $this->nextWorkingStart($current, $calendar);
                continue;
            }

            [$dayStart, $dayEnd] = $this->dayBounds($current, $calendar);

            if ($current->lt($dayStart)) {
                $current = $dayStart;
            }

            if ($current->gte($dayEnd)) {
                $current = $this->nextWorkingStart($current->addDay()->startOfDay(), $calendar);
                continue;
            }

            $available = $current->diffInMinutes($dayEnd);

            if ($remaining <= $available) {
                return $current->addMinutes($remaining);
            }

            $remaining -= $available;
            $current = $this->nextWorkingStart($current->addDay()->startOfDay(), $calendar);
        }

        return $current;
    }

    private function hasWorkingHours(WorkCalendar $calendar): bool
    {
        $workweek = $calendar->workweek ?? [];

        if (!is_array($workweek)) {
            return false;
        }

        foreach ($workweek as $schedule) {
            if (is_array($schedule) && !empty($schedule['start']) && !empty($schedule['end'])) {
                return true;
            }
        }

        return false;
    }

    private function isWorkingDay(CarbonInterface $date, WorkCalendar $calendar): bool
    {
        if ($calendar->isHoliday($date)) {
            return false;
        }

        return $calendar->getDailySchedule($date->format('l')) !== null;
    }

    private function nextWorkingStart(CarbonInterface $date, WorkCalendar $calendar): CarbonImmutable
    {
        $probe = CarbonImmutable::instance($date)->startOfDay();

        for ($i = 0; $i < 14; $i++) {
            $schedule = $calendar->getDailySchedule($probe->format('l'));

            if ($schedule && !$calendar->isHoliday($probe)) {
                return $probe->setTimeFromTimeString($schedule['start']);
            }

            $probe = $probe->addDay();
        }

        return CarbonImmutable::instance($date);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function dayBounds(CarbonInterface $date, WorkCalendar $calendar): array
    {
        $schedule = $calendar->getDailySchedule($date->format('l'));

        if (!$schedule) {
            $start = CarbonImmutable::instance($date)->startOfDay();
            return [$start, $start];
        }

        $start = CarbonImmutable::instance($date)->setTimeFromTimeString($schedule['start']);
        $end = CarbonImmutable::instance($date)->setTimeFromTimeString($schedule['end']);

        if ($end->lte($start)) {
            $end = $start->addDay();
        }

        return [$start, $end];
    }
}

