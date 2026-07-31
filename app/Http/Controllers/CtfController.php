<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\JoinTeamRequest;
use App\Http\Requests\SubmitFlagRequest;
use App\Http\Requests\UpdateTeamSettingsRequest;
use App\Models\CtfChallenge;
use App\Models\CtfChallengeFile;
use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\CtfTeam;
use App\Models\CtfWriteup;
use App\Models\User;
use App\Services\CtfScoreboardService;
use App\Services\CtfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CtfController extends Controller
{
    public function __construct(
        protected CtfService $ctfService,
        protected CtfScoreboardService $scoreboardService,
    ) {}

    public function index(): View
    {
        $competitions = CtfCompetition::published()
            ->public()
            ->currentlyActive()
            ->withCount(['challenges as total_challenges' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('start_date')
            ->get();

        $competitionStats = $competitions->map(function (CtfCompetition $competition) {
            $challengeIds = $competition->challenges()->pluck('id');
            $solved = CtfSubmission::where('user_id', auth()->id())
                ->whereIn('ctf_challenge_id', $challengeIds)
                ->where('is_correct', true)
                ->count();
            $points = CtfSubmission::where('user_id', auth()->id())
                ->whereIn('ctf_challenge_id', $challengeIds)
                ->where('is_correct', true)
                ->sum('points_awarded');

            return [
                'competition' => $competition,
                'total' => $competition->total_challenges,
                'solved' => $solved,
                'points' => $points,
            ];
        });

        $totalChallenges = $competitions->sum('total_challenges');
        $solvedCount = $competitionStats->sum('solved');
        $userTotalPoints = $competitionStats->sum('points');

        return view('pages.ctf.index', [
            'title' => 'CTF Arena',
            'competitionStats' => $competitionStats,
            'totalChallenges' => $totalChallenges,
            'solvedCount' => $solvedCount,
            'userTotalPoints' => $userTotalPoints,
            'competitionCount' => $competitions->count(),
        ]);
    }

    public function dashboard(): View
    {
        $user = auth()->user();

        $competitions = CtfCompetition::published()
            ->public()
            ->withCount(['challenges as total_challenges' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('start_date')
            ->get();

        $competitionData = $competitions->map(function (CtfCompetition $competition) use ($user) {
            $challengeIds = $competition->challenges()->pluck('id');

            $correctSubmissions = CtfSubmission::where('user_id', $user->id)
                ->whereIn('ctf_challenge_id', $challengeIds)
                ->where('is_correct', true)
                ->with('challenge')
                ->get();

            $incorrectCount = CtfSubmission::where('user_id', $user->id)
                ->whereIn('ctf_challenge_id', $challengeIds)
                ->where('is_correct', false)
                ->count();

            $totalPoints = $correctSubmissions->sum('points_awarded');

            $unsolvedChallenges = $competition->challenges()
                ->where('is_active', true)
                ->whereNotIn('id', $correctSubmissions->pluck('ctf_challenge_id'))
                ->with('category')
                ->orderBy('sort_order')
                ->get();

            return [
                'competition' => $competition,
                'total_challenges' => $competition->total_challenges,
                'solved_count' => $correctSubmissions->count(),
                'total_points' => $totalPoints,
                'incorrect_count' => $incorrectCount,
                'recent_solves' => $correctSubmissions->sortByDesc('created_at')->take(5),
                'unsolved_challenges' => $unsolvedChallenges,
            ];
        });

        $globalStats = [
            'total_solves' => $competitionData->sum('solved_count'),
            'total_points' => $competitionData->sum('total_points'),
            'total_incorrect' => $competitionData->sum('incorrect_count'),
            'competitions_participated' => $competitionData->filter(fn ($d) => $d['solved_count'] > 0)->count(),
        ];

        $recentActivity = CtfSubmission::where('user_id', $user->id)
            ->with('challenge.competition', 'challenge.category')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('pages.ctf.dashboard', [
            'title' => 'My CTF Dashboard',
            'competitionData' => $competitionData,
            'globalStats' => $globalStats,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function show(Request $request, CtfCompetition $competition): View
    {
        $isPreview = $request->query('preview') === '1';

        if ($isPreview) {
            abort_unless(auth()->user()?->isAdmin(), 403);
            abort_unless($competition->status === 'published', 403);
        } else {
            abort_unless($competition->status === 'published' || auth()->user()?->isAdmin(), 403);
        }

        $competition->load([
            'challenges' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('points'),
            'challenges.category',
            'challenges.files',
            'challenges.hints',
            'challenges.dependsOn',
            'challenges.hintPurchases' => fn ($q) => $q->where('user_id', auth()->id()),
            'challenges.solves',
        ]);

        $challenges = $competition->challenges;

        $purchasedHintTiers = $challenges->mapWithKeys(fn ($c) => [
            $c->id => $c->hintPurchases->pluck('hint_tier')->toArray(),
        ])->toArray();

        $grouped = $challenges->groupBy(fn ($c) => $c->category?->name ?? 'Uncategorized');

        $userSolved = $challenges->filter(fn ($c) => $c->solves->contains('user_id', auth()->id()))
            ->pluck('id')
            ->toArray();

        $userAttempts = CtfSubmission::where('user_id', auth()->id())
            ->whereIn('ctf_challenge_id', $challenges->pluck('id'))
            ->selectRaw('ctf_challenge_id, COUNT(*) as attempt_count')
            ->groupBy('ctf_challenge_id')
            ->pluck('attempt_count', 'ctf_challenge_id')
            ->toArray();

        $firstBloods = collect();
        $firstBloodSubmissions = $challenges->map(function ($challenge) {
            $firstSolve = $challenge->solves->sortBy('id')->first();

            if ($firstSolve) {
                return [
                    'user_name' => $firstSolve->user?->name ?? 'Unknown',
                    'user_id' => $firstSolve->user_id,
                ];
            }

            return null;
        })->filter()->toArray();

        $solveDistribution = $challenges->mapWithKeys(fn ($challenge) => [
            $challenge->id => $challenge->solves
                ->sortBy('solve_order')
                ->values()
                ->map(fn ($s) => [
                    'order' => $s->solve_order,
                    'points' => $s->points_awarded,
                    'at' => $s->solved_at?->timestamp,
                ]),
        ])->toArray();

        $categories = $challenges->pluck('category.name')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $scoreboard = $this->scoreboardService->getScoreboard($competition, 10);

        $userTeam = null;
        $teams = null;
        if ($competition->allow_teams) {
            $userTeam = $this->ctfService->getUserTeam($competition, auth()->user());
            $teams = CtfTeam::forCompetition($competition)
                ->withCount('members')
                ->with('members.user')
                ->orderBy('id')
                ->get();
        }

        return view('pages.ctf.competition', [
            'title' => $competition->title,
            'competition' => $competition,
            'challengesByCategory' => $grouped,
            'userSolved' => $userSolved,
            'userAttempts' => $userAttempts,
            'purchasedHintTiers' => $purchasedHintTiers,
            'categories' => $categories,
            'scoreboard' => $scoreboard,
            'userTeam' => $userTeam,
            'teams' => $teams,
            'isPreview' => $isPreview,
            'firstBloods' => $firstBloods,
            'solveDistribution' => $solveDistribution,
        ]);
    }

    public function submit(SubmitFlagRequest $request, CtfCompetition $competition, CtfChallenge $challenge): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()
                ->back()
                ->with('error', 'This competition is not currently active');
        }

        $team = $competition->allow_teams
            ? $this->ctfService->getUserTeam($competition, auth()->user())
            : null;

        $result = $this->ctfService->submitFlag(
            $challenge,
            auth()->user(),
            $request->input('flag'),
            $request->ip(),
            $team,
        );

        if ($result['success']) {
            return redirect()
                ->back()
                ->with('status', "Correct! +{$result['points']} points. Total: {$result['total_points']}");
        }

        return redirect()
            ->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    public function purchaseHint(Request $request, CtfCompetition $competition, CtfChallenge $challenge): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()->back()->with('error', 'This competition is not currently active');
        }

        $result = $this->ctfService->purchaseHint($challenge, auth()->user());

        if ($result['success']) {
            return redirect()
                ->back()
                ->with('status', "Hint revealed! ({$result['points_spent']} points spent)");
        }

        return redirect()
            ->back()
            ->with('error', $result['message']);
    }

    public function createTeam(CreateTeamRequest $request, CtfCompetition $competition): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()->back()->with('error', 'This competition is not currently active');
        }

        try {
            $team = $this->ctfService->createTeam(
                $competition,
                auth()->user(),
                $request->input('name'),
                $request->input('description'),
            );

            return redirect()
                ->back()
                ->with('status', "Team '{$team->name}' created!");
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function joinTeam(JoinTeamRequest $request, CtfCompetition $competition): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()->back()->with('error', 'This competition is not currently active');
        }

        if ($request->filled('invite_code')) {
            $team = CtfTeam::forCompetition($competition)
                ->where('invite_code', $request->input('invite_code'))
                ->first();

            if (! $team) {
                return redirect()->back()->with('error', 'Invalid invite code');
            }
        } else {
            $team = CtfTeam::forCompetition($competition)
                ->where('id', $request->input('team_id'))
                ->first();

            if (! $team) {
                return redirect()->back()->with('error', 'Team not found');
            }
        }

        $result = $this->ctfService->joinTeam($team, auth()->user());

        if ($result['success']) {
            return redirect()->back()->with('status', "Joined team '{$team->name}'!");
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function leaveTeam(Request $request, CtfCompetition $competition): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()->back()->with('error', 'This competition is not currently active');
        }

        $result = $this->ctfService->leaveTeam($competition, auth()->user());

        if ($result['success']) {
            return redirect()->back()->with('status', 'Left the team');
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function disbandTeam(Request $request, CtfCompetition $competition): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()->back()->with('error', 'This competition is not currently active');
        }

        $result = $this->ctfService->disbandTeam($competition, auth()->user());

        if ($result['success']) {
            return redirect()->back()->with('status', 'Team disbanded');
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function transferCaptaincy(Request $request, CtfCompetition $competition): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()->back()->with('error', 'This competition is not currently active');
        }

        $request->validate(['user_id' => 'required|exists:users,id']);

        $team = $this->ctfService->getUserTeam($competition, auth()->user());
        if (! $team) {
            return redirect()->back()->with('error', 'You are not in a team');
        }

        $newCaptain = User::findOrFail($request->input('user_id'));

        $result = $this->ctfService->transferCaptaincy($team, auth()->user(), $newCaptain);

        if ($result['success']) {
            return redirect()->back()->with('status', "Captaincy transferred to {$newCaptain->name}");
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function updateTeamSettings(UpdateTeamSettingsRequest $request, CtfCompetition $competition): RedirectResponse
    {
        if (! $competition->isActive()) {
            return redirect()->back()->with('error', 'This competition is not currently active');
        }

        $team = $this->ctfService->getUserTeam($competition, auth()->user());
        if (! $team) {
            return redirect()->back()->with('error', 'You are not in a team');
        }

        if (! $team->isCaptain(auth()->user())) {
            return redirect()->back()->with('error', 'Only the team captain can change team settings');
        }

        $team = $this->ctfService->updateTeam($team, [
            'name' => $request->input('name'),
            'is_open' => $request->boolean('is_open'),
        ]);

        return redirect()->back()->with('status', "Team settings updated for '{$team->name}'");
    }

    public function exportScoreboard(CtfCompetition $competition): \Illuminate\Http\Response
    {
        abort_unless(auth()->check(), 403);

        $viewIndividual = ! $competition->allow_teams;

        $scoreboard = $viewIndividual
            ? $this->scoreboardService->getScoreboard($competition, 1000)
            : $this->scoreboardService->getTeamScoreboard($competition, 1000);

        $csv = fopen('php://memory', 'r+');

        if ($viewIndividual) {
            fputcsv($csv, ['Rank', 'Name', 'Score', 'Solves']);
            foreach ($scoreboard as $entry) {
                fputcsv($csv, [
                    $entry['rank'],
                    $this->sanitizeCsvField($entry['name']),
                    $entry['total_score'],
                    $entry['solves_count'],
                ]);
            }
        } else {
            fputcsv($csv, ['Rank', 'Team Name', 'Score', 'Solves', 'Members']);
            foreach ($scoreboard as $entry) {
                fputcsv($csv, [
                    $entry['rank'],
                    $this->sanitizeCsvField($entry['name']),
                    $entry['total_score'],
                    $entry['solves_count'],
                    $entry['member_count'],
                ]);
            }
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        $filename = "scoreboard-{$competition->slug}-".now()->format('Y-m-d').'.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function sanitizeCsvField(string $value): string
    {
        if (in_array($value[0] ?? '', ['=', '+', '-', '@', "\t", "\r"])) {
            return "'".$value;
        }

        return $value;
    }

    public function scoreboard(Request $request, CtfCompetition $competition): View
    {
        $viewIndividual = $request->get('view') === 'individual' || ! $competition->allow_teams;

        $scoreboard = $viewIndividual
            ? $this->scoreboardService->getScoreboard($competition, 100)
            : $this->scoreboardService->getTeamScoreboard($competition, 100);

        $userRank = auth()->check()
            ? $this->scoreboardService->getUserRank($competition, auth()->user())
            : null;

        return view('pages.ctf.scoreboard', [
            'title' => "{$competition->title} — Scoreboard",
            'competition' => $competition,
            'scoreboard' => $scoreboard,
            'userRank' => $userRank,
        ]);
    }

    public function writeups(CtfCompetition $competition, CtfChallenge $challenge): View
    {
        abort_unless($challenge->isSolvedBy(auth()->user()), 403);

        $writeups = CtfWriteup::where('ctf_challenge_id', $challenge->id)
            ->where('status', 'approved')
            ->with('user')
            ->get();

        return view('pages.ctf.writeups', [
            'title' => "Writeups: {$challenge->title}",
            'competition' => $competition,
            'challenge' => $challenge,
            'writeups' => $writeups,
        ]);
    }

    public function writeup(CtfCompetition $competition, CtfChallenge $challenge): View
    {
        abort_unless($challenge->isSolvedBy(auth()->user()), 403);

        $existingWriteup = CtfWriteup::where('ctf_challenge_id', $challenge->id)
            ->where('user_id', auth()->id())
            ->first();

        return view('pages.ctf.writeup', [
            'title' => "Writeup: {$challenge->title}",
            'competition' => $competition,
            'challenge' => $challenge,
            'existingWriteup' => $existingWriteup,
        ]);
    }

    public function downloadFile(CtfChallengeFile $file): StreamedResponse
    {
        $challenge = $file->challenge;
        $competition = $challenge->competition;

        abort_unless($competition->status === 'published' || auth()->user()?->isAdmin(), 403);

        abort_unless(Storage::exists($file->stored_path), 404);

        return Storage::download($file->stored_path, $file->original_name);
    }

    public function submitWriteup(Request $request, CtfCompetition $competition, CtfChallenge $challenge): RedirectResponse
    {
        abort_unless($challenge->isSolvedBy(auth()->user()), 403);

        $request->validate(['content' => 'required|string|min:100|max:50000']);

        CtfWriteup::updateOrCreate(
            ['ctf_challenge_id' => $challenge->id, 'user_id' => auth()->id()],
            ['content' => strip_tags($request->input('content')), 'status' => 'pending']
        );

        return redirect()
            ->route('ctf.competition', $competition)
            ->with('status', 'Writeup submitted for review.');
    }
}
