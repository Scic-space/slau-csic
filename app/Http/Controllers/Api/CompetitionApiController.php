<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CompetitionApiResource;
use App\Models\Competition;
use App\Models\CompetitionParticipants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompetitionApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Competition::withCount('participants')->orderBy('start_date', 'desc');

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'upcoming' => $query->upcoming(),
                'ongoing' => $query->ongoing(),
                'past' => $query->past(),
                default => null,
            };
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        $competitions = $query->paginate($perPage);

        return CompetitionApiResource::collection($competitions);
    }

    public function show(Competition $competition): CompetitionApiResource
    {
        $competition->loadCount('participants')->load('members');

        return new CompetitionApiResource($competition);
    }

    public function join(Request $request, Competition $competition): JsonResponse
    {
        $user = $request->user();

        if ($competition->participants()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You are already a participant in this competition.',
            ], 409);
        }

        $validated = $request->validate([
            'team_name' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:leader,member',
        ]);

        CompetitionParticipants::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'team_name' => $validated['team_name'] ?? null,
            'role' => $validated['role'] ?? 'member',
        ]);

        $competition->loadCount('participants');

        return response()->json([
            'message' => 'Successfully joined the competition.',
            'data' => new CompetitionApiResource($competition),
        ], 201);
    }

    public function leave(Request $request, Competition $competition): JsonResponse
    {
        $user = $request->user();

        $deleted = $competition->participants()
            ->where('user_id', $user->id)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'You are not a participant in this competition.',
            ], 404);
        }

        $competition->loadCount('participants');

        return response()->json([
            'message' => 'Successfully left the competition.',
            'data' => new CompetitionApiResource($competition),
        ]);
    }
}
