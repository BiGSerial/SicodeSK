<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'area_id',
        'ticket_type_id',
        'creator_sicode_id',
        'executor_sicode_id',
        'is_late',
        'due_date',
    ];


    public function area()
    {
        return $this->belongsTo(\Area::class);
    }
    public function type()
    {
        return $this->belongsTo(\TicketType::class, 'ticket_type_id');
    }
}
