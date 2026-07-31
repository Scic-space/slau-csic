<?php

namespace App\Http\Controllers;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PublicLeaderboardController extends Controller
{
    public function __invoke(): Response
    {
        $period = request()->query('period', 'all-time');
        $period = in_array($period, ['all-time', 'week', 'month', 'semester']) ? $period : 'all-time';

        return Inertia::render('public/Leaderboard', [
            'leaders' => $this->getLeaders($period),
            'currentUserRank' => $this->getCurrentUserRank($period),
            'totalMembers' => User::activeMembers()->count(),
            'rankThresholds' => User::RANK_THRESHOLDS,
            'period' => $period,
        ]);
    }

    private function getLeaders(string $period): array
    {
        $periodQuery = $this->applyPeriodFilter(PointTransaction::query(), $period);

        $pointsByUser = $periodQuery
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->havingRaw('SUM(points) > 0')
            ->orderByDesc('total_points')
            ->limit(50)
            ->pluck('total_points', 'user_id');

        if ($pointsByUser->isEmpty()) {
            return [];
        }

        $users = User::activeMembers()
            ->whereIn('id', $pointsByUser->keys())
            ->with('earnedBadges')
            ->withCount('earnedBadges')
            ->get()
            ->keyBy('id');

        return $pointsByUser->map(function ($points, $userId) use ($users) {
            $user = $users->get($userId);

            if (! $user) {
                return null;
            }

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'headline' => $user->headline,
                'profile_photo_url' => $user->profile_photo_url,
                'total_points' => (int) $points,
                'rank_tier' => $user->current_rank,
                'badges' => $user->earnedBadges->take(3)->map(fn ($b) => [
                    'name' => $b->name,
                    'icon' => $b->icon,
                ]),
                'badge_count' => $user->earned_badges_count,
                'events_attended' => $user->gamificationStats?->attendance_count ?? 0,
                'streak' => $user->gamificationStats?->current_streak ?? 0,
            ];
        })
            ->filter()
            ->values()
            ->map(fn (array $entry, int $i) => array_merge($entry, [
                'rank' => $i + 1,
            ]))
            ->toArray();
    }

    private function getCurrentUserRank(string $period): ?array
    {
        if (! auth()->check()) {
            return null;
        }

        /** @var User $user */
        $user = auth()->user();

        $periodQuery = $this->applyPeriodFilter(PointTransaction::query(), $period);

        $userPoints = (int) $periodQuery
            ->where('user_id', $user->id)
            ->sum('points');

        if ($userPoints <= 0) {
            return null;
        }

        $position = $this->applyPeriodFilter(PointTransaction::query(), $period)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->havingRaw('SUM(points) > ?', [$userPoints])
            ->count() + 1;

        $nextRankThreshold = $this->getNextRankThreshold($user->current_rank);
        $pointsToNext = $nextRankThreshold !== null
            ? max(0, $nextRankThreshold - $userPoints)
            : null;

        return [
            'rank' => $position,
            'points' => $userPoints,
            'rank_tier' => $user->current_rank,
            'points_to_next' => $pointsToNext,
            'next_rank' => $nextRankThreshold !== null
                ? array_search($nextRankThreshold, User::RANK_THRESHOLDS)
                : null,
        ];
    }

    private function applyPeriodFilter($query, string $period)
    {
        return match ($period) {
            'week' => $query->where('created_at', '>=', Carbon::now()->startOfWeek()),
            'month' => $query->where('created_at', '>=', Carbon::now()->startOfMonth()),
            'semester' => $query->where('created_at', '>=', $this->getSemesterStart()),
            default => $query,
        };
    }

    private function getSemesterStart(): Carbon
    {
        $now = Carbon::now();

        if ($now->month >= 9) {
            return Carbon::create($now->year, 9, 1)->startOfDay();
        }

        if ($now->month >= 2) {
            return Carbon::create($now->year, 2, 1)->startOfDay();
        }

        return Carbon::create($now->year - 1, 9, 1)->startOfDay();
    }

    private function getNextRankThreshold(?string $currentRank): ?int
    {
        $thresholds = User::RANK_THRESHOLDS;

        return match ($currentRank) {
            'bronze' => $thresholds['silver'],
            'silver' => $thresholds['gold'],
            'gold' => $thresholds['platinum'],
            default => null,
        };
    }
}
