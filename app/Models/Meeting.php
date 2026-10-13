<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Meeting extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'scheduled_at',
        'started_at',
        'ended_at',
        'location',
        'meeting_code',
        'meeting_link',
        'code_expires_minutes',
        'attendance_open',
        'duration_minutes',
        'expected_attendees',
        'missed_fine_amount',
        'created_by',
        'agenda',
        'minutes',
        'late_threshold_minutes',
        'is_recurring',
        'parent_meeting_id',
        'cancelled_at',
        'cancellation_reason',
        'minutes_status',
        'training_id',
        'audience',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'attendance_open' => 'boolean',
            'is_recurring' => 'boolean',
            'cancelled_at' => 'datetime',
            'missed_fine_amount' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'type', 'scheduled_at', 'location', 'status', 'attendance_open', 'minutes_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meeting) {
            if (! $meeting->meeting_code) {
                $meeting->meeting_code = self::generateUniqueMeetingCode();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(MeetingInterest::class);
    }

    public function isInterested(User $user): bool
    {
        return $this->interests()->where('user_id', $user->id)->exists();
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'attendance')
            ->withPivot('checked_in_at', 'check_in_method')
            ->withTimestamps();
    }

    public function allowedAttendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_allowed_attendees');
    }

    public function agendaItems(): HasMany
    {
        return $this->hasMany(MeetingAgendaItem::class)->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MeetingAttachment::class);
    }

    public function recurrence(): HasMany
    {
        return $this->hasMany(MeetingRecurrence::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(MeetingFeedback::class);
    }

    public function masterMeeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'parent_meeting_id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Meeting::class, 'parent_meeting_id');
    }

    public function canUserAttend(User $user): bool
    {
        if (! $this->allowedAttendees()->exists()) {
            return true;
        }

        return $this->allowedAttendees()->where('user_id', $user->id)->exists();
    }

    public function scopeUpcoming($query)
    {
        return $query->whereNull('cancelled_at')->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at', 'asc');
    }

    public function scopePast($query)
    {
        return $query->whereNull('cancelled_at')->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereNull('cancelled_at')->whereDate('scheduled_at', today());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_at');
    }

    public function scopeNotCancelled($query)
    {
        return $query->whereNull('cancelled_at');
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeOccurrences($query)
    {
        return $query->whereNotNull('parent_meeting_id');
    }

    public function scopeTeachingSessions($query)
    {
        return $query->where('type', 'teaching_session');
    }

    public function scopeCompletedTeachingSessions($query)
    {
        return $query->where('type', 'teaching_session')
            ->whereNotNull('ended_at');
    }

    public function scopeActiveTeachingSessions($query)
    {
        return $query->where('type', 'teaching_session')
            ->where('attendance_open', true);
    }

    public static function generateUniqueMeetingCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('meeting_code', $code)->exists());

        return $code;
    }

    public function getQrCodeUrl(): string
    {
        return route('attendance.verify', ['code' => $this->meeting_code]);
    }

    /**
     * Duplicate the class for another day, carrying content but no history.
     *
     * Copies details, agenda, attachments, and audience (rebuilding the
     * restricted check-in list). Starts fresh: new code, closed
     * attendance, no attendance rows, feedback, fines, minutes, or
     * recurrence.
     */
    public function replicateForDate(\Carbon\Carbon $newDate, ?User $by = null): Meeting
    {
        return DB::transaction(function () use ($newDate, $by) {
            $clone = $this->replicate([
                'meeting_code',
                'attendance_open',
                'started_at',
                'ended_at',
                'cancelled_at',
                'cancellation_reason',
                'minutes',
                'minutes_status',
                'parent_meeting_id',
                'is_recurring',
                'created_by',
                'deleted_at',
            ]);

            $clone->scheduled_at = $newDate;
            $clone->created_by = $by?->id ?? $this->created_by;
            $clone->attendance_open = false;
            $clone->is_recurring = false;
            $clone->minutes_status = 'draft';
            $clone->save();

            foreach ($this->agendaItems as $item) {
                $itemClone = $item->replicate();
                $itemClone->meeting_id = $clone->id;
                $itemClone->save();
            }

            foreach ($this->attachments as $attachment) {
                $fileClone = $attachment->replicate();
                $fileClone->meeting_id = $clone->id;
                $fileClone->save();
            }

            $clone->syncAudienceAllowList();

            return $clone->fresh();
        });
    }

    public function openAttendance(): void
    {
        $this->update([
            'attendance_open' => true,
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function closeAttendance(): void
    {
        $this->update([
            'attendance_open' => false,
            'ended_at' => $this->ended_at ?? now(),
        ]);
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'attendance_open' => false,
        ]);
    }

    public function reschedule(\Carbon\Carbon $newDateTime): void
    {
        $this->update([
            'scheduled_at' => $newDateTime,
            'started_at' => null,
            'ended_at' => null,
            'attendance_open' => false,
        ]);
    }

    public function finalizeMinutes(): void
    {
        $this->update(['minutes_status' => 'finalized']);
    }

    public function publishMinutes(): void
    {
        $this->update(['minutes_status' => 'published']);
    }

    public function getAttendanceCount(): int
    {
        return $this->attendance()->count();
    }

    public function getAttendedCount(): int
    {
        return $this->attendance()->whereIn('status', ['present', 'late'])->count();
    }

    public function getAudienceLabelAttribute(): ?string
    {
        return match ($this->audience) {
            'year_1' => 'Year 1 only',
            'year_2_plus' => 'Year 2 and above',
            default => null,
        };
    }

    /**
     * Active member IDs in the meeting's target audience.
     * Empty when the meeting is open to everyone.
     *
     * @return list<int>
     */
    public function audienceMemberIds(): array
    {
        if (! in_array($this->audience, ['year_1', 'year_2_plus'], true)) {
            return [];
        }

        return User::activeMembers()
            ->when(
                $this->audience === 'year_1',
                fn ($query) => $query->whereHas('memberProfile', fn ($profile) => $profile->where('year_of_study', 1)),
                fn ($query) => $query->whereHas('memberProfile', fn ($profile) => $profile->where('year_of_study', '>', 1)),
            )
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Rebuild the restricted check-in list from the target audience.
     * Meetings open to everyone carry no restriction.
     */
    public function syncAudienceAllowList(): void
    {
        $ids = $this->audienceMemberIds();

        if ($ids === []) {
            $this->allowedAttendees()->detach();

            return;
        }

        $this->allowedAttendees()->sync($ids);
    }

    public function getAttendanceRate(): float
    {
        if (! $this->expected_attendees) {
            return 0;
        }

        return round(($this->getAttendanceCount() / $this->expected_attendees) * 100, 2);
    }

    public function isAttendanceOpen(): bool
    {
        return $this->attendance_open;
    }

    public function hasStarted(): bool
    {
        return $this->started_at !== null;
    }

    public function hasEnded(): bool
    {
        return $this->ended_at !== null;
    }

    public function isUpcoming(): bool
    {
        return $this->scheduled_at > now();
    }

    public function isPast(): bool
    {
        return $this->scheduled_at <= now();
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function isMasterMeeting(): bool
    {
        return $this->is_recurring && ! $this->parent_meeting_id;
    }

    public function isOccurrence(): bool
    {
        return $this->parent_meeting_id !== null;
    }

    public function getStatusAttribute(): string
    {
        if ($this->isCancelled()) {
            return 'cancelled';
        }

        if ($this->hasEnded()) {
            return 'completed';
        }

        if ($this->attendance_open) {
            return 'ongoing';
        }

        if ($this->isUpcoming()) {
            return 'scheduled';
        }

        return 'past';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'gray',
            'ongoing' => 'success',
            'scheduled' => 'info',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function hasUserAttended(User $user): bool
    {
        return $this->attendance()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function recordAttendance(User $user, string $method = 'manual', array $additionalData = []): Attendance
    {
        return $this->attendance()->create(array_merge([
            'user_id' => $user->id,
            'checked_in_at' => now(),
            'check_in_method' => $method,
        ], $additionalData));
    }

    public function isTeachingSession(): bool
    {
        return $this->type === 'teaching_session';
    }

    public function getLateThresholdMinutes(): int
    {
        return $this->late_threshold_minutes ?? 15;
    }

    public function getStartTime(): ?\Carbon\Carbon
    {
        return $this->scheduled_at;
    }

    public function getEndTime(): ?\Carbon\Carbon
    {
        if ($this->ended_at) {
            return $this->ended_at;
        }

        if ($this->scheduled_at && $this->duration_minutes) {
            return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
        }

        return null;
    }

    public function getPresentCount(): int
    {
        return $this->attendance()->where('status', 'present')->count();
    }

    public function getLateCount(): int
    {
        return $this->attendance()->where('status', 'late')->count();
    }

    public function getAbsentCount(): int
    {
        return $this->attendance()
            ->where('status', 'absent')
            ->orWhereNull('status')
            ->count();
    }

    public function hasMeetingLink(): bool
    {
        return ! empty($this->meeting_link);
    }

    public function isJoinable(): bool
    {
        if ($this->hasEnded() || $this->isCancelled()) {
            return false;
        }

        if ($this->attendance_open) {
            return true;
        }

        if ($this->scheduled_at && $this->scheduled_at->lte(now()->addMinutes(15))) {
            return true;
        }

        return false;
    }

    public function isEligibleForCheckIn(): bool
    {
        if (! $this->isTeachingSession()) {
            return false;
        }

        if (! $this->attendance_open) {
            return false;
        }

        if ($this->hasEnded() || $this->isCancelled()) {
            return false;
        }

        return true;
    }

    public function getFutureOccurrences()
    {
        $query = Meeting::where(function ($q) {
            $q->where('parent_meeting_id', $this->id)
                ->orWhere('id', $this->id);
        })
            ->whereNull('cancelled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc');

        return $query;
    }

    public function syncOccurrences(): void
    {
        if (! $this->is_recurring) {
            return;
        }

        $fieldsToSync = [
            'description',
            'location',
            'meeting_link',
            'duration_minutes',
            'expected_attendees',
            'late_threshold_minutes',
        ];

        $this->occurrences()
            ->whereNull('cancelled_at')
            ->where('scheduled_at', '>', now())
            ->update($this->only($fieldsToSync));
    }

    public function skipOccurrence(Meeting $occurrence): void
    {
        $occurrence->cancel('Skipped occurrence');
    }
}
