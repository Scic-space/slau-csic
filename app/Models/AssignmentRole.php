<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'role_template_id',
        'name',
        'seats_required',
        'seats_filled',
        'required_skills',
        'is_lead_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'is_lead_required' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function roleTemplate(): BelongsTo
    {
        return $this->belongsTo(RoleTemplate::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(AssignmentMember::class);
    }

    public function approvedMembers(): HasMany
    {
        return $this->hasMany(AssignmentMember::class)->where('status', 'approved');
    }

    public function getSkillsRemainingAttribute(): int
    {
        return $this->seats_required - $this->seats_filled;
    }
}
