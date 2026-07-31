<?php

namespace App\Http\Controllers;

use App\Models\CtfCategory;
use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\Testimonial;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PublicCtfController extends Controller
{
    public function __invoke(): Response
    {
        $competitions = CtfCompetition::query()
            ->published()
            ->public()
            ->withCount(['challenges', 'teams'])
            ->with(['challenges.category'])
            ->orderByDesc('start_date')
            ->take(6)
            ->get()
            ->map(fn (CtfCompetition $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'description' => str($c->description)->limit(220),
                'start_date' => $c->start_date->toIso8601String(),
                'end_date' => $c->end_date?->toIso8601String(),
                'max_score' => $c->max_score,
                'allow_teams' => $c->allow_teams,
                'max_team_size' => $c->max_team_size,
                'challenges_count' => $c->challenges_count,
                'teams_count' => $c->teams_count,
                'is_active' => $c->isActive(),
                'categories' => $c->challenges
                    ->pluck('category')
                    ->filter()
                    ->unique('id')
                    ->map(fn ($cat) => ['name' => $cat->name, 'color' => $cat->color])
                    ->values()
                    ->all(),
                'difficulty_range' => $c->challenges
                    ->pluck('difficulty')
                    ->filter()
                    ->unique()
                    ->sort(fn (string $a, string $b) => array_search(strtolower($a), ['easy', 'medium', 'hard', 'insane', 'master']) <=> array_search(strtolower($b), ['easy', 'medium', 'hard', 'insane', 'master']))
                    ->values()
                    ->all(),
            ]);

        $totalCompetitions = CtfCompetition::published()->public()->count();
        $totalChallenges = CtfCompetition::published()->public()->withCount('challenges')->get()->sum('challenges_count');
        $totalSolves = CtfSubmission::correct()->count();
        $totalParticipants = CtfSubmission::correct()->distinct('user_id')->count('user_id');

        $categories = CtfCategory::ordered()
            ->withCount('challenges')
            ->get()
            ->map(fn (CtfCategory $cat) => [
                'name' => $cat->name,
                'slug' => $cat->slug,
                'color' => $cat->color,
                'icon' => $cat->icon,
                'challenges_count' => $cat->challenges_count,
            ]);

        $topPlayers = CtfSubmission::query()
            ->correct()
            ->selectRaw('user_id, SUM(points_awarded) as total_points, COUNT(DISTINCT ctf_challenge_id) as solved')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->take(5)
            ->get()
            ->map(function ($entry) {
                $user = User::query()->find($entry->user_id);

                if (! $user) {
                    return null;
                }

                return [
                    'name' => $user->name,
                    'rank' => $user->rank,
                    'total_points' => $entry->total_points,
                    'solved' => $entry->solved,
                ];
            })
            ->filter()
            ->values();

        $testimonials = Testimonial::approved()
            ->featured()
            ->with('user')
            ->orderByDesc('sort_order')
            ->take(3)
            ->get()
            ->map(fn (Testimonial $t) => [
                'quote' => $t->quote,
                'name' => $t->user->name,
                'role' => $t->user->course_year ?? 'SLAU-CSIC Member',
                'rank' => $t->user->rank,
            ]);

        if ($testimonials->count() < 3) {
            $remaining = 3 - $testimonials->count();
            $fallback = Testimonial::approved()
                ->with('user')
                ->orderByDesc('created_at')
                ->take($remaining)
                ->get()
                ->map(fn (Testimonial $t) => [
                    'quote' => $t->quote,
                    'name' => $t->user->name,
                    'role' => $t->user->course_year ?? 'SLAU-CSIC Member',
                    'rank' => $t->user->rank,
                ]);
            $testimonials = $testimonials->concat($fallback);
        }

        $pastSeasons = CtfCompetition::query()
            ->published()
            ->public()
            ->where('end_date', '<', now())
            ->withCount(['challenges', 'teams'])
            ->with(['challenges.submissions' => function ($q) {
                $q->where('is_correct', true);
            }])
            ->orderByDesc('end_date')
            ->take(3)
            ->get()
            ->map(function (CtfCompetition $c) {
                $solves = $c->challenges->sum(fn ($ch) => $ch->submissions->count());
                $uniqueSolvers = $c->challenges->flatMap(fn ($ch) => $ch->submissions->pluck('user_id'))->unique()->count();

                return [
                    'title' => $c->title,
                    'end_date' => $c->end_date->toIso8601String(),
                    'challenges_count' => $c->challenges_count,
                    'teams_count' => $c->teams_count,
                    'total_solves' => $solves,
                    'unique_solvers' => $uniqueSolvers,
                ];
            });

        return Inertia::render('public/CtfLanding', [
            'competitions' => $competitions,
            'categories' => $categories,
            'topPlayers' => $topPlayers,
            'testimonials' => $testimonials,
            'pastSeasons' => $pastSeasons,
            'stats' => [
                'total_competitions' => $totalCompetitions,
                'total_challenges' => $totalChallenges,
                'total_solves' => $totalSolves,
                'total_participants' => $totalParticipants,
            ],
        ]);
    }
}
