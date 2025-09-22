<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkCalendarHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_calendar_id',
        'holiday_date',
        'label',
    ];

    protected $casts = [
        'holiday_date' => 'date',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(WorkCalendar::class, 'work_calendar_id');
    }
}

