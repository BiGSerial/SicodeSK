<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target_hours',
        'criteria',
        'tolerance_minutes',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];
}
