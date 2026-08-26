<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'code',
        'name',
        'display_name',
        'leader_name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TeamActivity::class);
    }

    public function sops(): HasMany
    {
        return $this->hasMany(SopDocument::class);
    }
}
