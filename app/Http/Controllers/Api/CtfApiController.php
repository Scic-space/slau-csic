<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Services\CtfScoreboardService;
use App\Services\CtfService;
use Illuminate\Http\Request;

class CtfApiController extends Controller
{
    public function __construct(
        protected CtfService $ctfService,
        protected CtfScoreboardService $scoreboardService,
    ) {}

    public function competitions()
    {
        $competitions = CtfCompetition::public()
            ->published()
            ->currentlyActive()
            ->withCount('challenges')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'description' => $c->description,
                'start_date' => $c->start_date?->toISOString(),
                'end_date' => $c->end_date?->toISOString(),
                'challenges_count' => $c->challenges_count,
            ]);

        return response()->json(['data' => $competitions]);
    }

    public function competitionShow(CtfCompetition $competition)
    {
        if (! $competition->isActive()) {
            return response()->json(['error' => 'Competition not available'], 404);
        }

        $competition->load(['activeChallenges.category']);

        return response()->json([
            'data' => [
                'id' => $competition->id,
                'title' => $competition->title,
                'description' => $competition->description,
                'start_date' => $competition->start_date?->toISOString(),
                'end_date' => $competition->end_date?->toISOString(),
                'challenges' => $competition->activeChallenges->map(fn ($ch) => [
                    'id' => $ch->id,
                    'title' => $ch->title,
                    'slug' => $ch->slug,
                    'description' => $ch->description,
                    'points' => $ch->points,
                    'difficulty' => $ch->difficulty,
                    'category' => $ch->category?->name,
                ]),
            ],
        ]);
    }

    public function submitFlag(Request $request, CtfChallenge $challenge)
    {
        $validated = $request->validate([
            'flag' => ['required', 'string', 'max:500', 'regex:'.CtfChallenge::FLAG_PATTERN],
        ]);

        if (! $challenge->is_active) {
            return response()->json(['error' => 'Challenge is not active'], 400);
        }

        $competition = $challenge->competition;
        if ($competition && ! $competition->isActive()) {
            return response()->json(['error' => 'Competition is not currently active'], 403);
        }

        $result = $this->ctfService->submitFlag(
            $challenge,
            $request->user(),
            $validated['flag'],
            $request->ip(),
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'correct' => true,
                'points' => $result['points'],
                'message' => 'Correct flag! Points awarded.',
            ]);
        }

        if ($result['error'] === 'already_solved') {
            return response()->json(['error' => 'Already solved this challenge'], 409);
        }

        return response()->json([
            'success' => false,
            'correct' => false,
            'message' => 'Incorrect flag. Try again.',
        ]);
    }

    public function scoreboard(CtfCompetition $competition)
    {
        $scoreboard = $this->scoreboardService->getScoreboard($competition, 100);

        return response()->json(['data' => $scoreboard]);
    }
}
