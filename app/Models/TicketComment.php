<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SicodeUser;

class TicketComment extends Model
{
    protected $table = 'ticket_comments';

    protected $fillable = [
        'ticket_id',
        'author_sicode_id',
        'body',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(SicodeUser::class, 'author_sicode_id', 'id');
    }
}
