<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class);
    }
    public function type()
    {
        return $this->belongsTo(\App\Models\TicketType::class, 'ticket_type_id');
    }
}
