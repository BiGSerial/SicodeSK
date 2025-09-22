<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'priority_id',
        'area_id',
        'ticket_type_id',
        'category_id',
        'subcategory_id',
        'increment_minutes',
        'tolerance_minutes',
        'pause_suspends',
        'active',
        'notes',
    ];

    protected $casts = [
        'increment_minutes' => 'integer',
        'tolerance_minutes' => 'integer',
        'pause_suspends' => 'boolean',
        'active' => 'boolean',
    ];

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }
}
