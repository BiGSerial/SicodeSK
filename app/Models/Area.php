<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['name', 'sigla', 'active'];

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
}
