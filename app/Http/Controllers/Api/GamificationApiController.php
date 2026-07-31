<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GamificationStat;
use App\Models\PointTransaction;
use Illuminate\Http\Request;

class GamificationApiController extends Controller
{
    public function leaderboard()
    {
        $leaderboard = GamificationStat::orderBy('score', 'desc')
            ->limit(50)
            ->with('user')
            ->get()
            ->map(fn ($stat, $index) => [
                'rank' => $index + 1,
                'user_id' => $stat->user_id,
                'name' => $stat->user->name,
                'score' => $stat->score,
                'rank_title' => $stat->current_rank,
                'attendance_count' => $stat->attendance_count,
            ]);

        return response()->json(['data' => $leaderboard]);
    }

    public function myPoints(Request $request)
    {
        $user = $request->user();

        $stats = GamificationStat::firstOrCreate(
            ['user_id' => $user->id],
            ['bonus_points' => 0, 'score' => 0],
        );

        $recent = PointTransaction::forUser($user)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($tx) => [
                'points' => $tx->points,
                'reason' => $tx->reason,
                'created_at' => $tx->created_at->toISOString(),
            ]);

        return response()->json([
            'data' => [
                'total_points' => $stats->total_points,
                'score' => $stats->score,
                'rank' => $stats->current_rank,
                'rank_title' => $stats->current_rank,
                'attendance_count' => $stats->attendance_count,
                'current_streak' => $stats->current_streak,
                'recent_transactions' => $recent,
            ],
        ]);
    }

    public function badges(Request $request)
    {
        $badges = $request->user()->earnedBadges()
            ->get()
            ->map(fn ($badge) => [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'earned_at' => $badge->pivot->earned_at,
            ]);

        return response()->json(['data' => $badges]);
    }
}
