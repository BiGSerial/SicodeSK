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
        'sicode_uuid',
        'preferences', // JSON para configs locais
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public function sicodeUser()
    {
        return $this->belongsTo(SicodeUser::class, 'sicode_uuid', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains(fn ($role) => $role->slug === $slug);
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
    }
}
