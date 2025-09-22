<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['name', 'sigla', 'active', 'work_calendar_id', 'manager_sicode_id'];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Padroniza sigla em maiúsculas
    protected static function booted(): void
    {
        static::saving(function (Area $area) {
            if (!empty($area->sigla)) {
                $area->sigla = strtoupper(trim($area->sigla));
            }
        });
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    /** Relacionamentos */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }

    public function workCalendar(): BelongsTo
    {
        return $this->belongsTo(WorkCalendar::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(SicodeUser::class, 'manager_sicode_id', 'id');
    }

    public function executors(): BelongsToMany
    {
        return $this->belongsToMany(SicodeUser::class, 'area_user', 'area_id', 'sicode_id')
            ->withPivot('role_in_area')
            ->withTimestamps();
    }
}
