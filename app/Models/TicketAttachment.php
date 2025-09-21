<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SicodeUser;

class TicketAttachment extends Model
{
    protected $table = 'ticket_attachments';

    protected $fillable = [
        'ticket_id',
        'uploader_sicode_id',
        'filename',
        'disk',
        'path',
        'mime',
        'size_bytes',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(SicodeUser::class, 'uploader_sicode_id', 'id');
    }
}
