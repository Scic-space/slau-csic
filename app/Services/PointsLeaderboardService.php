<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Leaderboard service for gamification points-based rankings.
 * Uses caching for performance.
 */
class PointsLeaderboardService
{
    protected int $cacheTtl = 300; // 5 minutes

    /**
     * Get top N users by total points, cached.
     */
    public function getTopUsers(int $limit = 100): Collection
    {
        $cacheKey = "gamification.leaderboard.top{$limit}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($limit) {
            return $this->computeLeaderboard($limit);
        });
    }

    /**
     * Get a specific user's rank position (1-indexed).
     */
    public function getUserRank(User $user): ?int
    {
        $cacheKey = "gamification.leaderboard.rank.{$user->id}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user) {
            $totalPoints = $user->total_points;

            if ($totalPoints <= 0) {
                return null;
            }

            return User::whereHas('pointTransactions', function ($query) {
                $query->where('points', '>', 0);
            })
                ->where(function ($query) use ($user) {
                    $query->whereExists(function ($subQuery) use ($user) {
                        $subQuery->selectRaw('SUM(points) as total')
                            ->from('point_transactions')
                            ->whereColumn('user_id', 'users.id')
                            ->groupBy('user_id')
                            ->havingRaw('SUM(points) > ?', [$user->total_points]);
                    });
                })
                ->count() + 1;
        });
    }

    /**
     * Get leaderboard for a specific time period.
     */
    public function getTopUsersSince(\Carbon\Carbon $since, int $limit = 100): Collection
    {
        $cacheKey = "gamification.leaderboard.since.{$since->timestamp}.{$limit}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($since, $limit) {
            return $this->computeLeaderboardSince($since, $limit);
        });
    }

    /**
     * Invalidate cached leaderboard data.
     */
    public function invalidateCache(): void
    {
        Cache::forget('gamification.leaderboard.top100');
        Cache::forget('gamification.leaderboard.top10');

        // Invalidate individual user ranks
        $userIds = User::pluck('id');
        foreach ($userIds as $userId) {
            Cache::forget("gamification.leaderboard.rank.{$userId}");
        }
    }

    /**
     * Compute the leaderboard from scratch.
     */
    protected function computeLeaderboard(int $limit): Collection
    {
        $users = User::whereHas('pointTransactions', function ($query) {
            $query->where('points', '>', 0);
        })
            ->get()
            ->map(function (User $user) {
                return [
                    'rank' => 0, // Will be set after sorting
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'student_id' => $user->student_id,
                    'avatar_url' => $user->avatar_url,
                    'total_points' => $user->total_points,
                    'rank_level' => $user->rank,
                ];
            })
            ->sortByDesc('total_points')
            ->take($limit)
            ->values()
            ->map(function ($entry, $index) {
                $entry['rank'] = $index + 1;

                return $entry;
            });

        return $users;
    }

    /**
     * Compute the leaderboard since a specific date.
     */
    protected function computeLeaderboardSince(\Carbon\Carbon $since, int $limit): Collection
    {
        $users = User::whereHas('pointTransactions', function ($query) use ($since) {
            $query->where('points', '>', 0)
                ->where('created_at', '>=', $since);
        })
            ->get()
            ->map(function (User $user) use ($since) {
                $points = $user->pointTransactions()
                    ->where('points', '>', 0)
                    ->where('created_at', '>=', $since)
                    ->sum('points');

                return [
                    'rank' => 0,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'student_id' => $user->student_id,
                    'avatar_url' => $user->avatar_url,
                    'total_points' => $points,
                    'rank_level' => $user->rank,
                ];
            })
            ->sortByDesc('total_points')
            ->take($limit)
            ->values()
            ->map(function ($entry, $index) {
                $entry['rank'] = $index + 1;

                return $entry;
            });

        return $users;
    }
}
