<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SopDocument extends Model
{
    protected $fillable = [
        'root_document_id',
        'parent_document_id',
        'team_id',
        'team_activity_id',
        'created_by_id',
        'updated_by_id',
        'template_id',
        'sop_number',
        'title',
        'year',
        'revision_number',
        'status',
        'creation_date',
        'revision_date',
        'effective_date',
        'approval_position',
        'approval_name',
        'approval_nip',
        'legal_basis',
        'executor_qualifications',
        'related_documents',
        'equipment',
        'warnings',
        'recording',
        'executors',
        'activities',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'revision_number' => 'integer',
            'creation_date' => 'date',
            'revision_date' => 'date',
            'effective_date' => 'date',
            'legal_basis' => 'array',
            'executor_qualifications' => 'array',
            'related_documents' => 'array',
            'equipment' => 'array',
            'warnings' => 'array',
            'recording' => 'array',
            'executors' => 'array',
            'activities' => 'array',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function rootDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_document_id');
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_document_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'root_document_id');
    }
}
