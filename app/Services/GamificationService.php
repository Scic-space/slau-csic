<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Core gamification service handling points, badges, and rank upgrades.
 */
class GamificationService
{
    /**
     * Award points to a user, log transaction, check badges, check rank upgrade.
     */
    public function awardPoints(
        User $user,
        int $points,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): PointTransaction {
        return DB::transaction(function () use ($user, $points, $reason, $referenceType, $referenceId) {
            // Create the point transaction
            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            // Check for new badges and rank upgrade
            $this->checkBadges($user);

            // Check if rank should be upgraded
            $this->checkRankUpgrade($user);

            return $transaction;
        });
    }

    /**
     * Deduct points from a user (negative points transaction).
     */
    public function deductPoints(User $user, int $points, string $reason): PointTransaction
    {
        return $this->awardPoints(
            $user,
            -abs($points),
            $reason
        );
    }

    /**
     * Check all badges for a user, unlock any newly earned.
     *
     * @return Collection<int, Badge>
     */
    public function checkBadges(User $user): Collection
    {
        $earnedBadges = collect();

        // Get badges not yet earned by this user
        $availableBadges = Badge::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        foreach ($availableBadges as $badge) {
            if ($badge->checkCriteria($user)) {
                // Award the badge
                $this->awardBadge($user, $badge);
                $earnedBadges->push($badge);

                // If badge has points bonus, award them too
                if ($badge->points_bonus > 0) {
                    $this->awardPoints(
                        $user,
                        $badge->points_bonus,
                        "Badge bonus: {$badge->name}",
                        Badge::class,
                        $badge->id
                    );
                }
            }
        }

        return $earnedBadges;
    }

    /**
     * Check if user should upgrade rank.
     */
    public function checkRankUpgrade(User $user): ?string
    {
        $currentRank = $user->rank;
        $targetRank = $user->current_rank;

        if ($currentRank !== $targetRank) {
            $user->update([
                'rank' => $targetRank,
                'rank_changed_at' => now(),
            ]);

            return $targetRank;
        }

        return null;
    }

    /**
     * Award a badge manually to a user (for 'custom' criteria type).
     */
    public function awardBadge(User $user, Badge $badge): UserBadge
    {
        $existing = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
        ]);
    }

    /**
     * Revoke a badge from a user.
     */
    public function revokeBadge(User $user, Badge $badge): bool
    {
        return (bool) UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->delete();
    }
}
