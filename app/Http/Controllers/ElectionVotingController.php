<?php

namespace App\Http\Controllers;

use App\Http\Requests\CastElectionVoteRequest;
use App\Models\Election;
use App\Models\ElectionNomination;
use App\Models\ElectionVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ElectionVotingController extends Controller
{
    protected const APPLICATION_STATUS_FLOW = [
        'submitted' => ['color' => 'gray', 'label' => 'Submitted'],
        'under_review' => ['color' => 'amber', 'label' => 'Under Review'],
        'shortlisted' => ['color' => 'blue', 'label' => 'Shortlisted'],
        'approved' => ['color' => 'emerald', 'label' => 'Approved'],
        'rejected' => ['color' => 'red', 'label' => 'Rejected'],
    ];

    public function verifyForm(): Response
    {
        return Inertia::render('elections/VerifyReceipt');
    }

    public function verifyReceipt(Request $request): Response
    {
        $validated = $request->validate([
            'receipt_code' => ['required', 'string', 'max:64'],
        ]);

        $vote = ElectionVote::findByReceipt($validated['receipt_code']);

        if (! $vote) {
            return Inertia::render('elections/VerifyReceipt', [
                'result' => null,
                'receipt' => $validated['receipt_code'],
                'error' => 'No vote found with this receipt code.',
            ]);
        }

        $vote->load(['election', 'candidate']);

        return Inertia::render('elections/VerifyReceipt', [
            'result' => [
                'election_title' => $vote->election->title,
                'election_position' => $vote->election->position,
                'candidate_name' => $vote->candidate->name,
                'voted_at' => $vote->created_at->format('M j, Y g:i A'),
            ],
            'receipt' => $validated['receipt_code'],
            'error' => null,
        ]);
    }

    public function index(): Response
    {
        $user = auth()->user();

        $elections = Election::live()->with([
            'candidates',
            'votes' => fn ($q) => $q->where('user_id', $user->id),
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
                'total_votes' => $election->votes_count,
                'user_vote' => $election->votes->first()?->election_candidate_id,
                'candidates' => $election->candidates->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'manifesto' => $c->manifesto,
                    'agenda' => $c->agenda,
                    'photo' => $c->photo ? asset('storage/'.$c->photo) : null,
                    'votes_count' => $c->votes_count ?? $c->votes->count(),
                    'sort_order' => $c->sort_order,
                ]),
            ]);

        return Inertia::render('elections/Voting', [
            'elections' => $elections,
        ]);
    }

    public function castVote(CastElectionVoteRequest $request, Election $election): RedirectResponse
    {
        abort_unless($request->user()->canVoteIn($election), 403);

        if (! $election->allowsVoteChanges() && $request->user()->hasVotedIn($election)) {
            abort(403, 'Vote changes are not allowed for this election.');
        }

        $candidate = $election->candidates()->findOrFail($request->integer('candidate_id'));

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

        return back()->with('status', 'Your vote has been recorded.')->with('receipt_code', $receiptCode);
    }

    public function nominationsIndex(): Response
    {
        $user = auth()->user();

        $openElections = Election::live()
            ->whereIn('status', ['draft', 'open'])
            ->withCount('candidates')
            ->latest('starts_at')
            ->get();

        $userNominations = ElectionNomination::where('user_id', $user->id)
            ->with('election')
            ->get()
            ->keyBy('election_id');

        return Inertia::render('elections/Nominations', [
            'elections' => $openElections->map(fn ($election) => [
                'id' => $election->id,
                'title' => $election->title,
                'position' => $election->position,
                'description' => $election->description,
                'status' => $election->status,
                'candidates_count' => $election->candidates_count,
                'applications_starts_at' => $election->applications_starts_at?->toIso8601String(),
                'applications_ends_at' => $election->applications_ends_at?->toIso8601String(),
                'is_accepting_applications' => $election->isAcceptingApplications(),
                'user_nomination' => $userNominations->get($election->id)?->only([
                    'id', 'statement', 'manifesto', 'agenda', 'photo', 'status',
                    'submitted_at', 'reviewed_at', 'admin_notes',
                ]),
            ]),
        ]);
    }

    public function submitNomination(Request $request, Election $election): RedirectResponse
    {
        abort_unless($request->user()->isActiveMember(), 403);
        abort_unless(in_array($election->status, ['draft', 'open']), 403);

        $validated = $request->validate([
            'statement' => ['nullable', 'string', 'max:5000'],
            'manifesto' => ['nullable', 'string', 'max:10000'],
            'agenda' => ['nullable', 'string', 'max:10000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $existingNomination = ElectionNomination::where([
            'election_id' => $election->id,
            'user_id' => $request->user()->id,
        ])->first();

        if ($existingNomination && $existingNomination->canReapply()) {
            $existingNomination->reapply([
                'statement' => $validated['statement'] ?? null,
                'manifesto' => $validated['manifesto'] ?? null,
                'agenda' => $validated['agenda'] ?? null,
            ]);

            $nomination = $existingNomination;
        } else {
            $nomination = ElectionNomination::updateOrCreate(
                ['election_id' => $election->id, 'user_id' => $request->user()->id],
                [
                    'statement' => $validated['statement'] ?? null,
                    'manifesto' => $validated['manifesto'] ?? null,
                    'agenda' => $validated['agenda'] ?? null,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ],
            );
        }

        if ($request->hasFile('photo')) {
            $nomination->update([
                'photo' => $request->file('photo')->store('nomination-photos', 'public'),
            ]);
        }

        if ($request->hasFile('documents')) {
            $paths = [];
            foreach ($request->file('documents') as $doc) {
                $paths[] = $doc->store('nomination-documents', 'public');
            }
            $nomination->update(['documents' => $paths]);
        }

        activity()
            ->performedOn($election)
            ->causedBy($request->user())
            ->log('nomination_submitted');

        return back()->with('status', 'Your application has been submitted for review.');
    }

    public function withdrawNomination(Request $request, Election $election): RedirectResponse
    {
        $nomination = ElectionNomination::where([
            'election_id' => $election->id,
            'user_id' => $request->user()->id,
        ])->firstOrFail();

        abort_unless($nomination->canWithdraw(), 403, 'You cannot withdraw this application.');

        $nomination->withdraw();

        return back()->with('status', 'Your application has been withdrawn.');
    }

    public function myApplications(): Response
    {
        $user = auth()->user();

        $applications = ElectionNomination::where('user_id', $user->id)
            ->with(['election', 'reviewer', 'reviews.user'])
            ->latest('submitted_at')
            ->get()
            ->map(fn ($nom) => [
                'id' => $nom->id,
                'election' => [
                    'id' => $nom->election->id,
                    'title' => $nom->election->title,
                    'position' => $nom->election->position,
                ],
                'statement' => $nom->statement,
                'manifesto' => $nom->manifesto,
                'agenda' => $nom->agenda,
                'photo' => $nom->photo ? asset('storage/'.$nom->photo) : null,
                'documents' => $nom->documents ? collect($nom->documents)->map(fn ($d) => asset('storage/'.$d))->toArray() : [],
                'status' => $nom->status,
                'score_average' => $nom->score_average,
                'admin_notes' => $nom->admin_notes,
                'reviewer_name' => $nom->reviewer?->name,
                'submitted_at' => $nom->submitted_at?->toIso8601String(),
                'reviewed_at' => $nom->reviewed_at?->toIso8601String(),
                'interview_scheduled_at' => $nom->interview_scheduled_at?->toIso8601String(),
                'interview_location' => $nom->interview_location,
                'interview_notes' => $nom->interview_notes,
                'can_withdraw' => $nom->canWithdraw(),
                'can_reapply' => $nom->canReapply(),
                'status_flow' => static::APPLICATION_STATUS_FLOW,
                'reviews' => $nom->reviews->map(fn ($r) => [
                    'id' => $r->id,
                    'from_status' => $r->from_status,
                    'to_status' => $r->to_status,
                    'notes' => $r->notes,
                    'reviewer_name' => $r->user?->name,
                    'created_at' => $r->created_at->toIso8601String(),
                ]),
            ]);

        return Inertia::render('elections/MyApplications', [
            'applications' => $applications,
        ]);
    }

    public function results(): Response
    {
        $eligibleCount = \App\Models\User::activeMembers()->count();

        $elections = Election::live()->with(['candidates.votes'])
            ->withCount('votes')
            ->where(fn ($q) => $q->where('results_visible', true)->orWhere('status', 'closed'))
            ->latest('ends_at')
            ->get()
            ->map(function ($election) use ($eligibleCount) {
                $maxVotes = $election->candidates->max(fn ($c) => $c->votes->count());
                $winner = $election->candidates->first(fn ($c) => $c->votes->count() === $maxVotes && $maxVotes > 0);

                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'position' => $election->position,
                    'status' => $election->status,
                    'results_visible' => $election->results_visible,
                    'total_votes' => $election->votes_count,
                    'eligible_voters' => $eligibleCount,
                    'turnout' => $eligibleCount > 0
                        ? round(($election->votes_count / $eligibleCount) * 100, 1)
                        : 0,
                    'starts_at' => $election->starts_at,
                    'ends_at' => $election->ends_at,
                    'winner' => $winner ? [
                        'id' => $winner->id,
                        'name' => $winner->name,
                        'votes_count' => $winner->votes->count(),
                    ] : null,
                    'candidates' => $election->candidates->map(fn ($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'votes_count' => $c->votes->count(),
                        'percentage' => $election->votes_count > 0
                            ? round(($c->votes->count() / $election->votes_count) * 100, 1)
                            : 0,
                        'is_winner' => $winner && $c->id === $winner->id,
                    ]),
                ];
            });

        return Inertia::render('elections/Results', [
            'elections' => $elections,
        ]);
    }
}
