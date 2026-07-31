<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * PointTransaction model representing a points ledger entry.
 *
 * @property int $id
 * @property int $user_id
 * @property int $points
 * @property string $reason
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property Carbon $created_at
 */
class PointTransaction extends Model
{
    use HasFactory;

    protected $table = 'point_transactions';

    protected $fillable = [
        'user_id',
        'points',
        'reason',
        'reference_type',
        'reference_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user associated with this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the polymorphic reference (if any).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: filter transactions for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope: filter transactions since a specific date.
     */
    public function scopeSince($query, Carbon $date)
    {
        return $query->where('created_at', '>=', $date);
    }

    /**
     * Check if this is a positive (earned) transaction.
     */
    public function isPositive(): bool
    {
        return $this->points > 0;
    }

    /**
     * Check if this is a negative (deducted) transaction.
     */
    public function isNegative(): bool
    {
        return $this->points < 0;
    }
}
