<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'status_notes',
        'approved_by',
        'approved_at',
        'joined_at',
        'left_at',
        'approval_notes',
        'suspension_reason',
        'suspended_until',
        'suspended_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'joined_at' => 'date',
            'left_at' => 'date',
            'suspended_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isLeft(): bool
    {
        return $this->status === 'left';
    }

    public function isAlumni(): bool
    {
        return $this->type === 'alumni';
    }

    public function isAssociate(): bool
    {
        return $this->type === 'associate';
    }
}
