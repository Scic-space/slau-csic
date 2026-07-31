<?php

namespace App\Services;

use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Models\CtfTeam;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CtfScoreboardService
{
    protected int $cacheTtl = 60;

    protected function getGeneration(CtfCompetition $competition): int
    {
        return Cache::remember("ctf.scoreboard.generation.{$competition->id}", 86400, fn () => 1);
    }

    public function getScoreboard(CtfCompetition $competition, int $limit = 100): Collection
    {
        if ($competition->allow_teams) {
            return $this->getTeamScoreboard($competition, $limit);
        }

        $generation = $this->getGeneration($competition);
        $cacheKey = "ctf.scoreboard.{$competition->id}.{$limit}.{$generation}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($competition, $limit) {
            return $this->computeIndividualScoreboard($competition, $limit);
        });
    }

    public function getTeamScoreboard(CtfCompetition $competition, int $limit = 100): Collection
    {
        $generation = $this->getGeneration($competition);
        $cacheKey = "ctf.team_scoreboard.{$competition->id}.{$limit}.{$generation}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($competition, $limit) {
            return $this->computeTeamScoreboard($competition, $limit);
        });
    }

    public function getUserRank(CtfCompetition $competition, User $user): ?int
    {
        if ($competition->allow_teams) {
            $team = CtfTeam::forCompetition($competition)
                ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
                ->first();

            if (! $team) {
                return null;
            }

            $scoreboard = $this->getTeamScoreboard($competition);
            $entry = $scoreboard->firstWhere('team_id', $team->id);

            return $entry ? $entry['rank'] : null;
        }

        $scoreboard = $this->getScoreboard($competition);
        $entry = $scoreboard->firstWhere('user_id', $user->id);

        return $entry ? $entry['rank'] : null;
    }

    public function invalidateCache(CtfCompetition $competition): void
    {
        Cache::increment("ctf.scoreboard.generation.{$competition->id}");
    }

    protected function computeIndividualScoreboard(CtfCompetition $competition, int $limit): Collection
    {
        $challengeIds = $competition->challenges()->pluck('id');

        $users = User::query()
            ->whereHas('ctfSubmissions', function ($query) use ($challengeIds) {
                $query->whereIn('ctf_challenge_id', $challengeIds)
                    ->where('is_correct', true);
            })
            ->withSum(['ctfSubmissions' => function ($query) use ($challengeIds) {
                $query->whereIn('ctf_challenge_id', $challengeIds)
                    ->where('is_correct', true);
            }], 'points_awarded')
            ->orderBy('ctf_submissions_sum_points_awarded', 'desc')
            ->limit($limit)
            ->get()
            ->map(function (User $user, $index) use ($challengeIds) {
                $solvesCount = $user->ctfSubmissions()
                    ->whereIn('ctf_challenge_id', $challengeIds)
                    ->where('is_correct', true)
                    ->count();

                return [
                    'rank' => $index + 1,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'total_score' => (int) ($user->ctf_submissions_sum_points_awarded ?? 0),
                    'solves_count' => $solvesCount,
                ];
            });

        return $users;
    }

    protected function computeTeamScoreboard(CtfCompetition $competition, int $limit): Collection
    {
        $teams = CtfTeam::forCompetition($competition)
            ->withCount('members')
            ->get()
            ->map(function (CtfTeam $team) {
                $score = $team->getTotalScore();
                $solves = $team->getSolveCount();

                return [
                    'team_id' => $team->id,
                    'name' => $team->name,
                    'captain_id' => $team->captain_id,
                    'member_count' => $team->members_count,
                    'total_score' => $score,
                    'solves_count' => $solves,
                ];
            })
            ->sortByDesc('total_score')
            ->values()
            ->map(fn ($entry, $index) => array_merge($entry, ['rank' => $index + 1]));

        return $teams;
    }

    public function getChallengeSolveDistribution(CtfChallenge $challenge): Collection
    {
        return $challenge->solves()
            ->orderBy('solve_order')
            ->get()
            ->map(fn ($solve) => [
                'solve_order' => $solve->solve_order,
                'user_id' => $solve->user_id,
                'user_name' => $solve->user?->name,
                'points_awarded' => $solve->points_awarded,
                'solved_at' => $solve->solved_at,
            ]);
    }
}
