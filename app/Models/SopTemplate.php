<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SopTemplate extends Model
{
    protected $fillable = [
        'team_id',
        'team_activity_id',
        'source_sop_id',
        'name',
        'template_code',
        'description',
        'template_payload',
    ];

    protected function casts(): array
    {
        return [
            'template_payload' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TeamActivity::class, 'team_activity_id');
    }

    public function sourceSop(): BelongsTo
    {
        return $this->belongsTo(SopDocument::class, 'source_sop_id');
    }
}
