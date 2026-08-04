<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Collection;

class LeaderboardService
{
    public function recalculate(User $user): void
    {
        $attendanceRate = $this->calculateAttendanceRate($user);
        $consistencyScore = min($user->current_streak * 10, 100);
        $bonusPoints = $user->bonus_points ?? 0;

        $score = ($attendanceRate * 0.7) + ($consistencyScore * 0.2) + ($bonusPoints * 0.1);

        $user->update([
            'score' => round($score, 2),
        ]);
    }

    public function recalculateAll(): void
    {
        $users = User::activeMembers()->get();

        foreach ($users as $user) {
            $this->recalculate($user);
        }
    }

    public function getRanked(string $filter = 'all'): Collection
    {
        $query = User::activeMembers()
            ->where('score', '>', 0)
            ->orderByDesc('score')
            ->orderByDesc('current_streak')
            ->orderByDesc('total_sessions_attended');

        $query = $this->applyFilter($query, $filter);

        return $query->get()->map(function ($user, $index) {
            return [
                'rank' => $index + 1,
                'user_id' => $user->id,
                'name' => $user->name,
                'registration_number' => $user->registration_number,
                'avatar_url' => $user->avatar_url,
                'total_sessions_attended' => $user->total_sessions_attended,
                'current_streak' => $user->current_streak,
                'longest_streak' => $user->longest_streak,
                'bonus_points' => $user->bonus_points,
                'score' => $user->score,
                'attendance_rate' => $user->getTeachingSessionAttendanceRate(),
                'badge' => $this->getBadge($user->getTeachingSessionAttendanceRate()),
            ];
        });
    }

    public function getTopUsers(int $limit = 10, string $filter = 'all'): Collection
    {
        return $this->getRanked($filter)->take($limit);
    }

    public function getUserRank(User $user): int
    {
        return User::activeMembers()
            ->where('score', '>', $user->score)
            ->count() + 1;
    }

    public function getStatistics(): array
    {
        $totalSessions = Meeting::teachingSessions()->completedTeachingSessions()->count();
        $activeMembers = User::activeMembers()->count();

        $averageScore = User::activeMembers()->avg('score') ?? 0;
        $totalBonusPoints = User::activeMembers()->sum('bonus_points');

        $topStreak = User::activeMembers()->max('current_streak') ?? 0;
        $longestStreak = User::activeMembers()->max('longest_streak') ?? 0;

        return [
            'total_sessions' => $totalSessions,
            'active_members' => $activeMembers,
            'average_score' => round($averageScore, 2),
            'total_bonus_points' => $totalBonusPoints,
            'top_streak' => $topStreak,
            'longest_streak' => $longestStreak,
        ];
    }

    private function calculateAttendanceRate(User $user): float
    {
        $totalSessions = Meeting::teachingSessions()
            ->completedTeachingSessions()
            ->count();

        if ($totalSessions === 0) {
            return 0;
        }

        $attendedSessions = $user->attendance()
            ->whereHas('meeting', function ($query) {
                $query->teachingSessions()
                    ->completedTeachingSessions();
            })
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($attendedSessions / $totalSessions) * 100, 2);
    }

    private function applyFilter($query, string $filter)
    {
        return match ($filter) {
            'this_month' => $query->whereHas('attendance', function ($q) {
                $q->whereMonth('checked_in_at', now()->month)
                    ->whereYear('checked_in_at', now()->year);
            }),
            'this_semester' => $query->whereHas('attendance', function ($q) {
                $semesterStart = now()->month >= 9
                    ? now()->setMonth(9)->startOfMonth()
                    : now()->setMonth(2)->startOfMonth();
                $q->where('checked_in_at', '>=', $semesterStart);
            }),
            default => $query,
        };
    }

    private function getBadge(float $attendanceRate): ?string
    {
        if ($attendanceRate >= 90) {
            return 'gold';
        }

        if ($attendanceRate >= 75) {
            return 'silver';
        }

        if ($attendanceRate >= 50) {
            return 'bronze';
        }

        return null;
    }
}
