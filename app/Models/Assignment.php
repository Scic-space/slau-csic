<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target_type',
        'target_id',
        'deadline',
        'priority',
        'status',
        'confidence_score',
        'fairness_score',
        'policy_weights',
        'context_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'policy_weights' => 'array',
            'confidence_score' => 'decimal:2',
            'fairness_score' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(AssignmentRole::class);
    }

    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(AssignmentMember::class, AssignmentRole::class);
    }

    public function scopeForEvent($query, Event $event)
    {
        return $query->where('target_type', 'event')->where('target_id', $event->id);
    }

    public function scopeForProject($query, $project)
    {
        return $query->where('target_type', 'project')->where('target_id', $project->id);
    }

    public function getTotalSeatsNeededAttribute(): int
    {
        return $this->roles->sum('seats_required');
    }

    public function getTotalSeatsFilledAttribute(): int
    {
        return $this->roles->sum('seats_filled');
    }
}
