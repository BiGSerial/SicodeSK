<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SicodeUser;

class TicketEvent extends Model
{
    protected $table = 'ticket_events';

    protected $fillable = [
        'ticket_id',
        'actor_sicode_id',
        'type',
        'payload_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(SicodeUser::class, 'actor_sicode_id', 'id');
    }
}
