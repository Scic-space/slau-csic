<?php

namespace App\Services;

use App\Models\CtfChallenge;
use App\Models\CtfChallengeSolve;
use App\Models\CtfCompetition;
use App\Models\CtfHint;
use App\Models\CtfHintPurchase;
use App\Models\CtfSubmission;
use App\Models\CtfTeam;
use App\Models\User;
use App\Notifications\ChallengeSolvedNotification;
use App\Notifications\TeamMemberJoinedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CtfService
{
    public function __construct(
        protected GamificationService $gamificationService,
        protected CtfScoreboardService $scoreboardService,
    ) {}

    public function submitFlag(
        CtfChallenge $challenge,
        User $user,
        string $submittedFlag,
        ?string $ipAddress = null,
        ?CtfTeam $team = null
    ): array {
        $competition = $challenge->competition;

        // All critical checks inside a transaction with row locking to prevent race conditions
        return DB::transaction(function () use ($challenge, $competition, $user, $submittedFlag, $ipAddress, $team) {
            // Lock the challenge row to serialize concurrent submissions
            $challenge = CtfChallenge::where('id', $challenge->id)->lockForUpdate()->firstOrFail();
            $competition = $challenge->competition;

            // 0. Check competition is active
            if ($competition && ! $competition->isActive()) {
                return [
                    'success' => false,
                    'error' => 'competition_inactive',
                    'message' => 'This competition is not currently active',
                ];
            }

            // 1. Check if already solved (inside transaction, after lock)
            if ($challenge->isSolvedBy($user)) {
                return ['success' => false, 'error' => 'already_solved', 'message' => 'You have already solved this challenge'];
            }

            // 2. Check dependencies
            if (! $challenge->areDependenciesMet($user)) {
                $dep = $challenge->dependsOn;

                return [
                    'success' => false,
                    'error' => 'dependency_not_met',
                    'message' => "You must solve '{$dep->title}' first before this challenge is available",
                ];
            }

            // 3. Check max_attempts
            if ($challenge->max_attempts > 0) {
                $attemptCount = CtfSubmission::forChallenge($challenge)
                    ->byUser($user)
                    ->count();

                if ($attemptCount >= $challenge->max_attempts) {
                    return [
                        'success' => false,
                        'error' => 'max_attempts_reached',
                        'message' => "You have reached the maximum of {$challenge->max_attempts} attempts for this challenge",
                    ];
                }
            }

            // 4. Verify the flag
            if (! $challenge->verifyFlag($submittedFlag)) {
                CtfSubmission::create([
                    'ctf_challenge_id' => $challenge->id,
                    'user_id' => $user->id,
                    'ctf_team_id' => $team?->id,
                    'submitted_flag' => $submittedFlag,
                    'is_correct' => false,
                    'points_awarded' => 0,
                    'attempt_number' => $this->getAttemptNumber($challenge, $user),
                    'ip_address' => $ipAddress,
                    'submitted_at' => now(),
                ]);

                return ['success' => false, 'error' => 'incorrect', 'message' => 'Incorrect flag'];
            }

            // 5. Calculate points (dynamic scoring)
            $pointsAwarded = $challenge->getDynamicPoints();

            // 6. Record solve
            $solveOrder = DB::table('ctf_challenge_solves')
                ->where('ctf_challenge_id', $challenge->id)
                ->lockForUpdate()
                ->max('solve_order') + 1;

            CtfSubmission::create([
                'ctf_challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'ctf_team_id' => $team?->id,
                'submitted_flag' => $submittedFlag,
                'is_correct' => true,
                'points_awarded' => $pointsAwarded,
                'attempt_number' => $this->getAttemptNumber($challenge, $user),
                'ip_address' => $ipAddress,
                'submitted_at' => now(),
            ]);

            CtfChallengeSolve::create([
                'ctf_challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'ctf_team_id' => $team?->id,
                'solve_order' => $solveOrder,
                'points_awarded' => $pointsAwarded,
                'solved_at' => now(),
            ]);

            $challenge->increment('solve_count');

            $this->gamificationService->awardPoints(
                $user,
                $pointsAwarded,
                "CTF Challenge solved: {$challenge->title}",
                CtfChallenge::class,
                $challenge->id
            );

            $this->checkCtfBadges($user, $competition);

            try {
                $user->notify(new ChallengeSolvedNotification($challenge, $pointsAwarded));
            } catch (\Exception $e) {
                Log::error('Failed to send challenge solved notification: '.$e->getMessage());
            }

            $this->scoreboardService->invalidateCache($competition);

            return [
                'success' => true,
                'points' => $pointsAwarded,
                'solve_order' => $solveOrder,
                'total_points' => $user->total_points,
            ];
        });
    }

    public function purchaseHint(CtfChallenge $challenge, User $user): array
    {
        if ($challenge->isSolvedBy($user)) {
            return ['success' => false, 'error' => 'already_solved', 'message' => 'Challenge already solved, hint not needed'];
        }

        $hints = $challenge->hints()->get();

        if ($hints->isEmpty()) {
            return ['success' => false, 'error' => 'no_hint', 'message' => 'No hint available for this challenge'];
        }

        // Find the first unpurchased hint tier
        $purchasedTiers = $challenge->hintPurchases()
            ->where('user_id', $user->id)
            ->pluck('hint_tier')
            ->toArray();

        $nextHint = $hints->first(fn (CtfHint $hint) => ! in_array($hint->tier, $purchasedTiers));

        if (! $nextHint) {
            return ['success' => false, 'error' => 'all_purchased', 'message' => 'All hint tiers already purchased'];
        }

        $cost = $nextHint->cost;

        if ($cost > 0 && $user->total_points < $cost) {
            return [
                'success' => false,
                'error' => 'insufficient_points',
                'message' => "You need {$cost} points to purchase this hint. You have {$user->total_points}.",
            ];
        }

        return DB::transaction(function () use ($challenge, $user, $cost, $nextHint) {
            if ($cost > 0) {
                $this->gamificationService->deductPoints(
                    $user,
                    $cost,
                    "Hint purchase (tier {$nextHint->tier}): {$challenge->title}"
                );
            }

            CtfHintPurchase::create([
                'ctf_challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'hint_tier' => $nextHint->tier,
                'points_spent' => $cost,
                'purchased_at' => now(),
            ]);

            return [
                'success' => true,
                'hint' => $nextHint->content,
                'tier' => $nextHint->tier,
                'points_spent' => $cost,
            ];
        });
    }

    public function joinTeam(CtfTeam $team, User $user): array
    {
        $competition = $team->competition;

        if ($team->isMember($user)) {
            return ['success' => false, 'error' => 'already_member', 'message' => 'You are already a member of this team'];
        }

        $currentTeam = $this->getUserTeam($competition, $user);
        if ($currentTeam) {
            return ['success' => false, 'error' => 'in_team', 'message' => 'You are already in a team for this competition. Leave first.'];
        }

        if (! $team->is_open) {
            return ['success' => false, 'error' => 'team_closed', 'message' => 'This team is not accepting new members'];
        }

        $memberCount = $team->members()->count();
        if ($memberCount >= $competition->max_team_size) {
            return ['success' => false, 'error' => 'team_full', 'message' => 'This team is full'];
        }

        $team->members()->create([
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        activity()
            ->performedOn($team)
            ->causedBy($user)
            ->withProperties(['competition_id' => $competition->id])
            ->log("{$user->name} joined team {$team->name}");

        // Notify existing team members
        try {
            $team->members()
                ->where('user_id', '!=', $user->id)
                ->with('user')
                ->get()
                ->each(fn ($member) => $member->user->notify(new TeamMemberJoinedNotification($team, $user)));
        } catch (\Exception $e) {
            Log::error('Failed to send team join notification: '.$e->getMessage());
        }

        return ['success' => true, 'team' => $team];
    }

    public function leaveTeam(CtfCompetition $competition, User $user): array
    {
        $team = $this->getUserTeam($competition, $user);
        if (! $team) {
            return ['success' => false, 'error' => 'not_in_team', 'message' => 'You are not in a team'];
        }

        if ($team->isCaptain($user)) {
            return ['success' => false, 'error' => 'is_captain', 'message' => 'Transfer captaincy or disband the team first'];
        }

        $team->members()->where('user_id', $user->id)->delete();

        activity()
            ->performedOn($team)
            ->causedBy($user)
            ->withProperties(['competition_id' => $competition->id])
            ->log("{$user->name} left team {$team->name}");

        return ['success' => true];
    }

    public function disbandTeam(CtfCompetition $competition, User $user): array
    {
        $team = $this->getUserTeam($competition, $user);
        if (! $team) {
            return ['success' => false, 'error' => 'not_in_team', 'message' => 'You are not in a team'];
        }

        if (! $team->isCaptain($user)) {
            return ['success' => false, 'error' => 'not_captain', 'message' => 'Only the team captain can disband the team'];
        }

        $teamName = $team->name;

        DB::transaction(function () use ($team) {
            $team->members()->delete();
            $team->delete();
        });

        activity()
            ->causedBy($user)
            ->withProperties(['competition_id' => $competition->id])
            ->log("{$user->name} disbanded team {$teamName}");

        return ['success' => true];
    }

    public function transferCaptaincy(CtfTeam $team, User $currentUser, User $newCaptain): array
    {
        if (! $team->isCaptain($currentUser)) {
            return ['success' => false, 'error' => 'not_captain', 'message' => 'Only the team captain can transfer captaincy'];
        }

        if (! $team->isMember($newCaptain)) {
            return ['success' => false, 'error' => 'not_member', 'message' => 'The user is not a member of this team'];
        }

        if ($team->isCaptain($newCaptain)) {
            return ['success' => false, 'error' => 'already_captain', 'message' => 'This user is already the captain'];
        }

        DB::transaction(function () use ($team, $currentUser, $newCaptain) {
            $team->members()->where('user_id', $currentUser->id)->update(['role' => 'member']);
            $team->members()->where('user_id', $newCaptain->id)->update(['role' => 'captain']);
            $team->update(['captain_id' => $newCaptain->id]);
        });

        activity()
            ->performedOn($team)
            ->causedBy($currentUser)
            ->withProperties(['new_captain_id' => $newCaptain->id])
            ->log("{$currentUser->name} transferred captaincy to {$newCaptain->name}");

        return ['success' => true, 'team' => $team->fresh()];
    }

    public function createTeam(CtfCompetition $competition, User $user, string $name, ?string $description = null): CtfTeam
    {
        $existing = $this->getUserTeam($competition, $user);
        if ($existing) {
            throw new \RuntimeException('You are already in a team for this competition');
        }

        $team = CtfTeam::create([
            'ctf_competition_id' => $competition->id,
            'name' => $name,
            'description' => $description,
            'captain_id' => $user->id,
            'is_open' => true,
        ]);

        $team->members()->create([
            'user_id' => $user->id,
            'role' => 'captain',
        ]);

        activity()
            ->performedOn($team)
            ->causedBy($user)
            ->withProperties(['competition_id' => $competition->id])
            ->log("{$user->name} created team {$name}");

        return $team;
    }

    public function updateTeam(CtfTeam $team, array $data): CtfTeam
    {
        $team->update($data);

        activity()
            ->performedOn($team)
            ->causedBy(auth()->user())
            ->withProperties(['team_id' => $team->id])
            ->log("Team settings updated: {$team->name}");

        return $team->fresh();
    }

    public function getUserTeam(CtfCompetition $competition, User $user): ?CtfTeam
    {
        return CtfTeam::forCompetition($competition)
            ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->first();
    }

    public function getUserSolves(CtfCompetition $competition, User $user): Collection
    {
        $challengeIds = $competition->challenges()->pluck('id');

        return CtfSubmission::correct()
            ->whereIn('ctf_challenge_id', $challengeIds)
            ->byUser($user)
            ->get();
    }

    public function getAttemptNumber(CtfChallenge $challenge, User $user): int
    {
        return CtfSubmission::forChallenge($challenge)
            ->byUser($user)
            ->count() + 1;
    }

    protected function checkCtfBadges(User $user, CtfCompetition $competition): Collection
    {
        return $this->gamificationService->checkBadges($user);
    }

    public function createChallenge(array $data): CtfChallenge
    {
        if (! isset($data['flag']) || ! preg_match(CtfChallenge::FLAG_PATTERN, $data['flag'])) {
            throw new \InvalidArgumentException('Flag must match format: SLAU_CSIC{text}');
        }

        $flag = $data['flag'];
        unset($data['flag'], $data['flag_hash']);

        $challenge = new CtfChallenge;
        $challenge->fill($data);
        $challenge->flag = $flag;
        $challenge->save();

        return $challenge;
    }

    public function updateChallenge(CtfChallenge $challenge, array $data): CtfChallenge
    {
        if (isset($data['flag'])) {
            if (! preg_match(CtfChallenge::FLAG_PATTERN, $data['flag'])) {
                throw new \InvalidArgumentException('Flag must match format: SLAU_CSIC{text}');
            }

            $challenge->flag = $data['flag'];
            unset($data['flag']);
        }

        $challenge->update($data);

        return $challenge;
    }
}
