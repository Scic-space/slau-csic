<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElectionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $elections = Election::live()->with([
            'candidates',
            'votes' => fn ($q) => $q->where('user_id', $request->user()->id),
        ])
            ->withCount('votes')
            ->latest('starts_at')
            ->get()
            ->map(fn ($election) => [
                'id' => $election->id,
                'title' => $election->title,
                'position' => $election->position,
                'description' => $election->description,
                'status' => $election->status,
                'is_open' => $election->isOpen(),
                'results_visible' => $election->results_visible,
                'starts_at' => $election->starts_at,
                'ends_at' => $election->ends_at,
                'total_votes' => $election->votes_count,
                'user_vote' => $election->votes->first()?->election_candidate_id,
                'candidates' => $election->candidates->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'manifesto' => $c->manifesto,
                    'agenda' => $c->agenda,
                    'photo_url' => $c->photo ? asset('storage/'.$c->photo) : null,
                    'votes_count' => $c->votes->count(),
                    'sort_order' => $c->sort_order,
                ]),
            ]);

        return response()->json(['data' => $elections]);
    }

    public function show(Request $request, Election $election): JsonResponse
    {
        abort_if($election->is_test_ballot && ! $request->user()->hasAnyRole(['admin', 'super-admin']), 404);

        $election->load([
            'candidates',
            'votes' => fn ($q) => $q->where('user_id', $request->user()->id),
        ]);

        return response()->json(['data' => [
            'id' => $election->id,
            'title' => $election->title,
            'position' => $election->position,
            'description' => $election->description,
            'status' => $election->status,
            'is_open' => $election->isOpen(),
            'results_visible' => $election->results_visible,
            'starts_at' => $election->starts_at,
            'ends_at' => $election->ends_at,
            'total_votes' => $election->votes()->count(),
            'user_vote' => $election->votes->first()?->election_candidate_id,
            'candidates' => $election->candidates->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'manifesto' => $c->manifesto,
                'agenda' => $c->agenda,
                'photo_url' => $c->photo ? asset('storage/'.$c->photo) : null,
                'votes_count' => $c->votes->count(),
                'sort_order' => $c->sort_order,
            ]),
        ]]);
    }

    public function castVote(Request $request, Election $election): JsonResponse
    {
        abort_unless($request->user()->canVoteIn($election), 403);

        if (! $election->allowsVoteChanges() && $request->user()->hasVotedIn($election)) {
            abort(403, 'Vote changes are not allowed for this election.');
        }

        $validated = $request->validate([
            'candidate_id' => ['required', 'exists:election_candidates,id'],
        ]);

        $candidate = $election->candidates()->findOrFail($validated['candidate_id']);

        $receiptCode = ElectionVote::generateReceiptCode();

        ElectionVote::updateOrCreate(
            ['election_id' => $election->id, 'user_id' => $request->user()->id],
            ['election_candidate_id' => $candidate->id, 'receipt_code' => $receiptCode],
        );

        activity()
            ->performedOn($election)
            ->causedBy($request->user())
            ->withProperties([
                'candidate_id' => $candidate->id,
                'candidate_name' => $candidate->name,
                'election_title' => $election->title,
            ])
            ->log('vote_cast');

        $request->user()->notify(new \App\Notifications\VoteReceiptNotification($election, $receiptCode));

        return response()->json(['message' => 'Your vote has been recorded.', 'receipt_code' => $receiptCode]);
    }
}
