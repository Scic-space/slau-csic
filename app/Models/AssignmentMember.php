<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_role_id',
        'user_id',
        'is_lead',
        'is_backup',
        'confidence_score',
        'reasoning',
        'conflict_flags',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_lead' => 'boolean',
            'is_backup' => 'boolean',
            'confidence_score' => 'decimal:2',
            'conflict_flags' => 'array',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(AssignmentRole::class, 'assignment_role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
