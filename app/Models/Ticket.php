<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    protected $fillable = [
        'code',
        'area_id',
        'ticket_type_id',
        'category_id',
        'subcategory_id',
        'workflow_id',
        'step_id',
        'priority_id',
        'title',
        'description',
        'status',
        'requester_sicode_id',
        'manager_sicode_id',
        'executor_sicode_id',
        'sla_due_at',
        'is_late',
    ];

    protected $casts = [
        'is_late'   => 'boolean',
        'sla_due_at' => 'datetime',
    ];

    /** Boot para gerar code automático */
    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (!$ticket->code && $ticket->area_id) {
                $period = now()->format('ym'); // Ex.: 2509
                $prefix = match ($ticket->area_id) {
                    1 => 'ITS', // IT Support
                    2 => 'DEV', // Development
                    3 => 'INF', // Infrastructure
                    default => 'GEN',
                };

                // Lock para não ter duplicados em paralelo
                $counter = DB::table('area_ticket_counters')
                    ->where('area_id', $ticket->area_id)
                    ->where('period', $period)
                    ->lockForUpdate()
                    ->first();

                if (!$counter) {
                    DB::table('area_ticket_counters')->insert([
                        'area_id'     => $ticket->area_id,
                        'period'      => $period,
                        'last_number' => 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $counter = (object)['last_number' => 0];
                }

                $next = $counter->last_number + 1;

                DB::table('area_ticket_counters')
                    ->where('area_id', $ticket->area_id)
                    ->where('period', $period)
                    ->update([
                        'last_number' => $next,
                        'updated_at'  => now(),
                    ]);

                $ticket->code = sprintf('%s-%s-%04d', $prefix, $period, $next);
            }
        });
    }

    /** Relacionamentos */
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
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(SicodeUser::class, 'requester_sicode_id', 'id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(SicodeUser::class, 'manager_sicode_id', 'id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(SicodeUser::class, 'executor_sicode_id', 'id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /** Scopes úteis */
    public function scopeSearch($q, ?string $term)
    {
        if (!$term) {
            return $q;
        }
        $term = trim($term);

        return $q->where(
            fn ($w) =>
            $w->where('code', 'ILIKE', "%{$term}%")
              ->orWhere('title', 'ILIKE', "%{$term}%")
        );
    }

    public function scopeOfArea($q, $areaId)
    {
        return $q->where('area_id', $areaId);
    }
    public function scopeOpen($q)
    {
        return $q->where('status', 'open');
    }

    /** Atributo derivado opcional */
    public function getPeriodAammAttribute(): ?string
    {
        return $this->created_at?->format('ym');
    }
}
