<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'users'; // tabela local do Sicodesk

    protected $fillable = [
        'sicode_id',
        'preferences', // JSON para configs locais
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public function sicodeUser()
    {
        return $this->belongsTo(SicodeUser::class, 'sicode_id');
    }
}
