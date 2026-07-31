<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Badge model representing achievement badges that users can earn.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $icon
 * @property BadgeCriteriaType $criteria_type
 * @property int $criteria_value
 * @property int $points_bonus
 * @property BadgeRarity $rarity
 */
class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'criteria_type',
        'criteria_value',
        'points_bonus',
        'rarity',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'criteria_value' => 'integer',
            'points_bonus' => 'integer',
            'criteria_type' => BadgeCriteriaType::class,
            'rarity' => BadgeRarity::class,
        ];
    }

    /**
     * Get the users who have earned this badge.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * Check if a user meets the criteria for this badge.
     */
    public function checkCriteria(User $user): bool
    {
        return match ($this->criteria_type) {
            BadgeCriteriaType::EventsAttended => $this->checkEventsAttended($user),
            BadgeCriteriaType::CtfCompleted => $this->checkCtfCompleted($user),
            BadgeCriteriaType::TotalPoints => $this->checkTotalPoints($user),
            BadgeCriteriaType::TeachingSessions => $this->checkTeachingSessions($user),
            BadgeCriteriaType::StreakDays => $this->checkStreakDays($user),
            BadgeCriteriaType::CtfScore => $this->checkCtfScore($user),
            BadgeCriteriaType::Custom => false,
            default => false,
        };
    }

    /**
     * Auto-generate slug from name if not set.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Badge $badge) {
            if (empty($badge->slug)) {
                $badge->slug = Str::slug($badge->name);
            }
        });
    }

    /**
     * Get all available criteria types.
     *
     * @return array<string, string>
     */
    public static function getCriteriaTypes(): array
    {
        return array_column(BadgeCriteriaType::cases(), 'value');
    }

    // ============================================
    // CRITERIA CHECK METHODS
    // ============================================

    private function checkEventsAttended(User $user): bool
    {
        $count = $user->eventRegistrations()
            ->where('status', 'attended')
            ->count();

        return $count >= $this->criteria_value;
    }

    private function checkCtfCompleted(User $user): bool
    {
        $count = $user->ctfSubmissions()
            ->where('is_correct', true)
            ->distinct('ctf_challenge_id')
            ->count('ctf_challenge_id');

        return $count >= $this->criteria_value;
    }

    private function checkTotalPoints(User $user): bool
    {
        return $user->total_points >= $this->criteria_value;
    }

    private function checkTeachingSessions(User $user): bool
    {
        return $user->getTeachingSessionsAttended() >= $this->criteria_value;
    }

    private function checkStreakDays(User $user): bool
    {
        return ($user->current_streak ?? 0) >= $this->criteria_value;
    }

    private function checkCtfScore(User $user): bool
    {
        $totalScore = $user->ctfSubmissions()
            ->where('is_correct', true)
            ->sum('points_awarded');

        return $totalScore >= $this->criteria_value;
    }
}
