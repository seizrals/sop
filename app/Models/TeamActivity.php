<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class TeamActivity extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'year',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sopDocuments(): HasMany
    {
        return $this->hasMany(SopDocument::class);
    }
}
