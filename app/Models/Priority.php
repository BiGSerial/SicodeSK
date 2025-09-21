<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'weight',
        'color',
        'is_default',
        'active',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'active' => 'boolean',
        'metadata' => 'array',
    ];
}
