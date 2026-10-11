<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Badge;
use App\Models\BadgeCriteriaType;
use App\Models\ClubResource;
use App\Models\Event;
use App\Models\ExamAttempt;
use App\Models\GamificationStat;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rebuilds a member's points ledger, counters, streaks, badges, and rank
 * from the source-of-truth records (attendance, event check-ins, exam
 * attempts, track completions) using the current fair points rules.
 *
 * Positive transactions issued under previous rules are removed and
 * regenerated; negative transactions (hint purchases, deductions),
 * CTF awards (time-sensitive dynamic scoring), and unrecognized positive
 * transactions are preserved and reported.
 */
class GamificationRebuildService
{
    public function __construct(
        protected GamificationService $gamification,
    ) {}

    /**
     * Rebuild one user's gamification data.
     *
     * @return array<string, mixed> Report of what changed (or would change in dry-run mode).
     */
    public function rebuild(User $user, bool $dryRun = false): array
    {
        return DB::transaction(function () use ($user, $dryRun) {
            $before = $user->total_points;

            $removableIds = $this->removableTransactions($user)->pluck('id')->all();

            $awards = $this->plannedAwards($user, $removableIds);

            $keptPoints = (int) $user->pointTransactions()
                ->where('points', '>', 0)
                ->whereNotIn('id', $removableIds)
                ->sum('points');

            $unknownReasons = $user->pointTransactions()
                ->where('points', '>', 0)
                ->whereNotIn('id', $removableIds)
                ->distinct()
                ->pluck('reason')
                ->values()
                ->all();

            $addedPoints = array_sum(array_column($awards, 'points'));
            $counters = $this->computedCounters($user);

            $newBadges = $this->newlyEarnedBadges($user, $keptPoints + $addedPoints);
            $newBadgeBonus = (int) $newBadges->sum('points_bonus');

            $report = [
                'user_id' => $user->id,
                'name' => $user->name,
                'before' => $before,
                'removed_count' => count($removableIds),
                'removed_points' => (int) $user->pointTransactions()->whereIn('id', $removableIds)->sum('points'),
                'added_count' => count($awards),
                'added_points' => $addedPoints,
                'kept_points' => $keptPoints,
                'new_badges' => $newBadges->pluck('name')->all(),
                'new_badge_bonus' => $newBadgeBonus,
                'after' => $keptPoints + $addedPoints + $newBadgeBonus,
                'unknown_reasons' => $unknownReasons,
                'streak' => $counters['current_streak'],
            ];

            if (! $dryRun) {
                $user->pointTransactions()->whereIn('id', $removableIds)->delete();

                foreach ($awards as $award) {
                    $this->gamification->awardPoints(
                        $user,
                        $award['points'],
                        $award['reason'],
                        $award['reference_type'],
                        $award['reference_id'],
                    );
                }

                $this->syncCounters($user, $counters);
                $this->gamification->checkBadges($user);
                $this->gamification->checkRankUpgrade($user);

                app(PointsLeaderboardService::class)->invalidateCache();
            }

            return $report;
        });
    }

    /**
     * IDs of positive transactions governed by the points rules.
     */
    protected function removableTransactions(User $user)
    {
        return $user->pointTransactions()
            ->where('points', '>', 0)
            ->where(function ($query) {
                $query->whereIn('reference_type', [
                    Meeting::class,
                    Event::class,
                    Badge::class,
                ])->orWhere('reason', 'like', 'Registered for event:%');
            });
    }

    /**
     * Every award the current rules grant, de-duplicated.
     *
     * Includes re-grants of badge bonuses for already-earned badges whose
     * bonus transaction is being removed.
     *
     * @return list<array{points: int, reason: string, reference_type: string, reference_id: int}>
     */
    protected function plannedAwards(User $user, array $removableIds): array
    {
        $awards = [];

        $attendance = $user->attendance()
            ->whereHas('meeting')
            ->with('meeting')
            ->get();

        foreach ($attendance as $record) {
            if (! in_array($record->status, ['present', 'late'], true)) {
                continue;
            }

            $meeting = $record->meeting;

            if (! $meeting || $meeting->cancelled_at) {
                continue;
            }

            $awards[] = [
                'points' => $record->status === 'late'
                    ? GamificationService::POINTS_MEETING_LATE
                    : GamificationService::POINTS_MEETING_PRESENT,
                'reason' => "Checked in: {$meeting->title}",
                'reference_type' => Meeting::class,
                'reference_id' => $meeting->id,
            ];

            if (
                $record->checked_in_at
                && $meeting->scheduled_at
                && $record->checked_in_at->lt($meeting->scheduled_at)
            ) {
                $awards[] = [
                    'points' => GamificationService::POINTS_EARLY_CHECKIN,
                    'reason' => "Early check-in bonus: {$meeting->title}",
                    'reference_type' => Meeting::class,
                    'reference_id' => $meeting->id,
                ];
            }
        }

        $registrations = $user->eventRegistrations()
            ->whereNotNull('attended_at')
            ->with('event')
            ->get();

        foreach ($registrations as $registration) {
            if (! $registration->event) {
                continue;
            }

            $awards[] = [
                'points' => GamificationService::POINTS_EVENT_ATTENDANCE,
                'reason' => "Attended event: {$registration->event->title}",
                'reference_type' => Event::class,
                'reference_id' => $registration->event->id,
            ];
        }

        $attempts = ExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->whereNotNull('submitted_at')
            ->with('exam.questions')
            ->get();

        foreach ($attempts as $attempt) {
            $totalMarks = $attempt->exam->questions->sum(fn ($question) => $question->effective_marks);
            $percentage = $totalMarks > 0 ? (($attempt->total_score / $totalMarks) * 100) : 0;

            $awards[] = [
                'points' => $percentage >= 80
                    ? GamificationService::POINTS_EXAM_DISTINCTION
                    : GamificationService::POINTS_EXAM_PASSED,
                'reason' => "Passed exam: {$attempt->exam->title}",
                'reference_type' => ExamAttempt::class,
                'reference_id' => $attempt->id,
            ];
        }

        $tracks = $user->clubResourceProgress()
            ->where('status', 'completed')
            ->with('resource')
            ->get();

        foreach ($tracks as $progress) {
            if (! $progress->resource) {
                continue;
            }

            $awards[] = [
                'points' => GamificationService::POINTS_TRACK_COMPLETED,
                'reason' => "Completed learning track: {$progress->resource->title}",
                'reference_type' => ClubResource::class,
                'reference_id' => $progress->resource->id,
            ];
        }

        foreach ($user->earnedBadges as $badge) {
            $hasBonus = $user->pointTransactions()
                ->where('points', '>', 0)
                ->where('reference_type', Badge::class)
                ->where('reference_id', $badge->id)
                ->whereNotIn('id', $removableIds)
                ->exists();

            if (! $hasBonus && $badge->points_bonus > 0) {
                $awards[] = [
                    'points' => $badge->points_bonus,
                    'reason' => "Badge bonus: {$badge->name}",
                    'reference_type' => Badge::class,
                    'reference_id' => $badge->id,
                ];
            }
        }

        $seen = [];

        return array_values(array_filter($awards, function (array $award) use (&$seen) {
            $key = $award['reference_type'].'|'.$award['reference_id'].'|'.$award['reason'];

            if (isset($seen[$key])) {
                return false;
            }

            $seen[$key] = true;

            return true;
        }));
    }

    /**
     * Counters derived from attendance history in chronological order.
     *
     * @return array<string, int>
     */
    protected function computedCounters(User $user): array
    {
        $records = $user->attendance()
            ->whereHas('meeting')
            ->with('meeting')
            ->get()
            ->sortBy(fn (Attendance $record) => [
                $record->meeting->scheduled_at?->timestamp ?? $record->created_at->timestamp,
                $record->id,
            ])
            ->values();

        $attended = 0;
        $early = 0;
        $streak = 0;
        $longest = 0;

        foreach ($records as $record) {
            if (in_array($record->status, ['present', 'late'], true)) {
                $attended++;
                $streak++;
                $longest = max($longest, $streak);

                $meeting = $record->meeting;

                if (
                    $meeting && $record->checked_in_at && $meeting->scheduled_at
                    && $record->checked_in_at->lt($meeting->scheduled_at)
                ) {
                    $early++;
                }
            } else {
                $streak = 0;
            }
        }

        return [
            'attendance_count' => $attended,
            'total_sessions_attended' => $attended,
            'bonus_points' => $early * GamificationService::POINTS_EARLY_CHECKIN,
            'current_streak' => $streak,
            'longest_streak' => max($longest, $user->longest_streak ?? 0),
        ];
    }

    protected function syncCounters(User $user, array $counters): void
    {
        $user->update([
            'attendance_count' => $counters['attendance_count'],
            'total_sessions_attended' => $counters['total_sessions_attended'],
            'bonus_points' => $counters['bonus_points'],
            'current_streak' => $counters['current_streak'],
            'longest_streak' => $counters['longest_streak'],
        ]);

        GamificationStat::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'attendance_count' => $counters['attendance_count'],
                'total_sessions_attended' => $counters['total_sessions_attended'],
                'current_streak' => $counters['current_streak'],
                'longest_streak' => $counters['longest_streak'],
                'bonus_points' => $counters['bonus_points'],
                'rank' => $user->current_rank,
            ],
        );
    }

    /**
     * Badges the user would newly earn, evaluated against the projected total.
     */
    protected function newlyEarnedBadges(User $user, int $projectedTotal): Collection
    {
        $earned = collect();
        $runningTotal = $projectedTotal;

        do {
            $found = false;

            $available = Badge::whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereNotIn('id', $earned->pluck('id'))->get();

            foreach ($available as $badge) {
                if ($this->badgeCriteriaMet($badge, $user, $runningTotal)) {
                    $earned->push($badge);
                    $runningTotal += $badge->points_bonus;
                    $found = true;
                }
            }
        } while ($found);

        return $earned;
    }

    protected function badgeCriteriaMet(Badge $badge, User $user, int $projectedTotal): bool
    {
        if ($badge->criteria_type === BadgeCriteriaType::TotalPoints) {
            return $projectedTotal >= $badge->criteria_value;
        }

        return $badge->checkCriteria($user);
    }
}
