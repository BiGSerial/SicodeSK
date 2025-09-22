<?php

namespace App\Services;

use App\Models\Priority;
use App\Models\SlaRule;
use App\Models\WorkCalendar;
use Illuminate\Support\Facades\Cache;
use Carbon\CarbonInterface;

class SlaResolver
{
    public function resolveMinutes(int $priorityId, ?int $areaId, ?int $ticketTypeId, ?int $categoryId, ?int $subcategoryId): int
    {
        $priority = Priority::find($priorityId);

        $baseMinutes = (int) data_get($priority?->metadata, 'base_minutes', $this->defaultBaseMinutes($priority?->slug));

        $rules = Cache::tags('sla_rules')->remember(
            "priority:{$priorityId}:rules",
            now()->addMinutes(30),
            fn () => SlaRule::query()
                ->where('active', true)
                ->where('priority_id', $priorityId)
                ->get()
        )
            ->filter(function (SlaRule $rule) use ($areaId, $ticketTypeId, $categoryId, $subcategoryId) {
                return (! $rule->area_id || $rule->area_id === $areaId)
                    && (! $rule->ticket_type_id || $rule->ticket_type_id === $ticketTypeId)
                    && (! $rule->category_id || $rule->category_id === $categoryId)
                    && (! $rule->subcategory_id || $rule->subcategory_id === $subcategoryId);
            });

        $increment = $rules->sum('increment_minutes');

        return max(0, $baseMinutes + $increment);
    }

    public function resolveDueDate(
        int $priorityId,
        ?int $areaId,
        ?int $ticketTypeId,
        ?int $categoryId,
        ?int $subcategoryId,
        CarbonInterface $start,
        ?WorkCalendar $calendar = null
    ): CarbonInterface {
        $minutes = $this->resolveMinutes($priorityId, $areaId, $ticketTypeId, $categoryId, $subcategoryId);

        return app(WorkCalendarCalculator::class)
            ->addBusinessMinutes($start, $minutes, $calendar);
    }

    private function defaultBaseMinutes(?string $prioritySlug): int
    {
        return match ($prioritySlug) {
            'urgent' => 4 * 60,
            'high' => 8 * 60,
            'medium' => 16 * 60,
            'low' => 24 * 60,
            default => 16 * 60,
        };
    }
}
