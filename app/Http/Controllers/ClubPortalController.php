<?php

namespace App\Http\Controllers;

use App\Http\Requests\CastElectionVoteRequest;
use App\Http\Requests\UpdateClubResourceProgressRequest;
use App\Models\ClubResource;
use App\Models\ClubResourceProgress;
use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\Election;
use App\Models\ElectionVote;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Meeting;
use App\Models\User;
use App\Services\HtbProfileSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ClubPortalController extends Controller
{
    public function __construct(protected HtbProfileSyncService $htbProfileSyncService) {}

    public function index(): View
    {
        $user = auth()->user();
        $this->syncHtbIfNeeded();
        $resources = $this->resourcesWithProgress();
        $stats = $user->getMemberStats();
        $portalProgress = $user->clubResourceProgress()->get();
        $upcomingEvents = Event::query()
            ->where('is_public', true)
            ->whereIn('status', ['published', 'ongoing'])
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(4)
            ->get();

        $ongoingTeachingSession = Meeting::teachingSessions()
            ->where('attendance_open', true)
            ->first();

        $hasCheckedIn = false;
        if ($ongoingTeachingSession && auth()->user()) {
            $hasCheckedIn = $ongoingTeachingSession->attendance()
                ->where('user_id', auth()->id())
                ->exists();
        }

        return view('pages.club.portal', [
            'title' => 'Club Portal',
            'resourcesByCategory' => $resources->groupBy('category'),
            'upcomingEvents' => $upcomingEvents,
            'ongoingTeachingSession' => $ongoingTeachingSession,
            'hasCheckedInToTeachingSession' => $hasCheckedIn,
            'metrics' => [
                'active_tracks' => $portalProgress->where('status', 'in_progress')->count(),
                'completed_tracks' => $portalProgress->where('status', 'completed')->count(),
                'average_progress' => (int) round($portalProgress->avg('progress_percentage') ?? 0),
                'club_points' => (int) $portalProgress->sum('score'),
                'events_attended' => $stats['events_attended'],
                'competition_entries' => $stats['competition_entries'],
            ],
        ]);
    }

    public function competitions(): View
    {
        return $this->renderSection(
            'competition',
            'Internal Competitions',
            'Access club challenge briefs, prepare for team drills, and track your competition readiness.'
        );
    }

    public function voting(): View
    {
        $user = auth()->user();
        $elections = Election::live()
            ->with(['candidates.votes', 'votes' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderByDesc('starts_at')
            ->get();

        return view('pages.club.voting', [
            'title' => 'Cabinet Voting',
            'elections' => $elections,
        ]);
    }

    public function ctfArena(): View
    {
        $this->syncHtbIfNeeded();

        $activeCompetitions = CtfCompetition::published()
            ->public()
            ->currentlyActive()
            ->withCount('challenges')
            ->orderBy('start_date')
            ->get();

        $userSolves = CtfSubmission::where('user_id', auth()->id())
            ->where('is_correct', true)
            ->count();

        $userPoints = CtfSubmission::where('user_id', auth()->id())
            ->where('is_correct', true)
            ->sum('points_awarded');

        return view('pages.club.ctf', [
            'title' => 'CTF Arena',
            'resources' => $this->resourcesWithProgress()->where('category', 'ctf')->values(),
            'leaderboard' => $this->ctfLeaderboard(),
            'badges' => $this->memberBadges(),
            'htbData' => auth()->user()->htb_profile_data ?? [],
            'activeCompetitions' => $activeCompetitions,
            'userSolves' => $userSolves,
            'userPoints' => $userPoints,
        ]);
    }

    public function classes(): View
    {
        $classes = Event::query()
            ->whereIn('type', ['workshop', 'bootcamp', 'talk'])
            ->whereIn('status', ['published', 'ongoing'])
            ->orderBy('start_date')
            ->with(['registrations'])
            ->get();

        $userRegistrations = collect();
        if (auth()->check()) {
            $eventIds = $classes->pluck('id');
            $userRegistrations = EventRegistration::whereIn('event_id', $eventIds)
                ->where('user_id', auth()->id())
                ->get()
                ->keyBy('event_id');
        }

        return view('pages.club.classes', [
            'title' => 'Internal Online Classes',
            'resources' => $this->resourcesWithProgress()->where('category', 'class')->values(),
            'classes' => $classes,
            'userRegistrations' => $userRegistrations,
        ]);
    }

    public function showResource(ClubResource $clubResource): View
    {
        $categoryConfig = match ($clubResource->category) {
            'competition' => [
                'back' => route('portal.competitions'),
                'back_label' => 'Back to Competitions',
                'heading' => 'Internal Competitions',
            ],
            'ctf' => [
                'back' => route('portal.ctf'),
                'back_label' => 'Back to CTF Arena',
                'heading' => 'CTF Challenge',
            ],
            default => [
                'back' => route('dashboard'),
                'back_label' => 'Back to Portal',
                'heading' => $clubResource->title,
            ],
        };

        return view('pages.club.resource-show', [
            'title' => $clubResource->title,
            'resource' => $clubResource,
            'categoryConfig' => $categoryConfig,
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

        ElectionVote::query()->updateOrCreate(
            [
                'election_id' => $election->id,
                'user_id' => $request->user()->id,
            ],
            [
                'election_candidate_id' => $candidate->id,
                'receipt_code' => $receiptCode,
            ],
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

    public function updateProgress(UpdateClubResourceProgressRequest $request, ClubResource $clubResource): RedirectResponse
    {
        ClubResourceProgress::query()->updateOrCreate(
            [
                'club_resource_id' => $clubResource->id,
                'user_id' => $request->user()->id,
            ],
            [
                ...$request->validated(),
                'last_activity_at' => now(),
            ],
        );

        return back()->with('status', 'Progress updated successfully.');
    }

    protected function renderSection(string $category, string $heading, string $intro): View
    {
        return view('pages.club.section', [
            'title' => $heading,
            'category' => $category,
            'heading' => $heading,
            'intro' => $intro,
            'resources' => $this->resourcesWithProgress()->where('category', $category)->values(),
        ]);
    }

    protected function resourcesWithProgress(): Collection
    {
        $user = auth()->user();

        return ClubResource::query()
            ->with(['progresses' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (ClubResource $resource) {
                $resource->setRelation('user_progress', $resource->progresses->first());

                return $resource;
            });
    }

    protected function syncHtbIfNeeded(): void
    {
        $user = auth()->user();

        if (! $user->htb_profile_url) {
            return;
        }

        if ($user->htb_last_synced_at && $user->htb_last_synced_at->gt(now()->subHours(6))) {
            return;
        }

        $this->htbProfileSyncService->sync($user);
    }

    protected function ctfLeaderboard(): Collection
    {
        return User::query()
            ->whereHas('clubResourceProgress.resource', fn ($query) => $query->where('category', 'ctf'))
            ->with(['clubResourceProgress.resource'])
            ->get()
            ->map(function (User $user) {
                $ctfProgress = $user->clubResourceProgress->filter(fn ($progress) => $progress->resource?->category === 'ctf');

                return [
                    'name' => $user->name,
                    'photo' => $user->avatar_url,
                    'score' => $ctfProgress->sum('score'),
                    'completed' => $ctfProgress->where('status', 'completed')->count(),
                    'average_progress' => (int) round($ctfProgress->avg('progress_percentage') ?? 0),
                ];
            })
            ->sortByDesc(fn (array $entry) => [$entry['score'], $entry['completed'], $entry['average_progress']])
            ->take(10)
            ->values();
    }

    protected function memberBadges(): array
    {
        $user = auth()->user();
        $ctfProgress = $user->clubResourceProgress->filter(fn ($progress) => $progress->resource?->category === 'ctf');
        $score = $ctfProgress->sum('score');
        $completed = $ctfProgress->where('status', 'completed')->count();
        $htbCompletion = (int) ($user->htb_profile_data['profile_completion'] ?? 0);

        $badges = [];

        if ($completed >= 1) {
            $badges[] = 'Lab Starter';
        }
        if ($completed >= 3) {
            $badges[] = 'Challenge Finisher';
        }
        if ($score >= 200) {
            $badges[] = 'Score Builder';
        }
        if ($htbCompletion >= 40) {
            $badges[] = 'HTB Connected';
        }
        if ($completed >= 5 && $score >= 400) {
            $badges[] = 'Internal Ranked Player';
        }

        return $badges;
    }
}
