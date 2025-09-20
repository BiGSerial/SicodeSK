<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class SicodeUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mysql_sicode';
    protected $table = 'users';
    protected $primaryKey = 'id';   // chave primária no SICODE
    public $incrementing = false;     // não é auto incremento
    protected $keyType = 'string';    // uuid é string

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

     public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
