<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationStat extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_count',
        'total_sessions_attended',
        'current_streak',
        'longest_streak',
        'bonus_points',
        'score',
        'rank',
        'rank_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'rank_changed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public const RANK_THRESHOLDS = [
        'bronze' => 0,
        'silver' => 200,
        'gold' => 500,
        'platinum' => 1000,
    ];

    public function getTotalPointsAttribute(): int
    {
        return $this->bonus_points;
    }

    public function getCurrentRankAttribute(): string
    {
        $points = $this->total_points;

        if ($points >= self::RANK_THRESHOLDS['platinum']) {
            return 'platinum';
        }
        if ($points >= self::RANK_THRESHOLDS['gold']) {
            return 'gold';
        }
        if ($points >= self::RANK_THRESHOLDS['silver']) {
            return 'silver';
        }

        return 'bronze';
    }
}
