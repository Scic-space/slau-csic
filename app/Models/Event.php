<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    protected $fillable = [
        'title',
        'description',
        'type',
        'start_date',
        'end_date',
        'location',
        'virtual_link',
        'banner_image',
        'gallery',
        'max_participants',
        'registration_required',
        'waitlist_enabled',
        'is_public',
        'visibility',
        'registration_deadline',
        'registration_type',
        'status',
        'organizer_id',
        'instructor_id',
        'requirements',
        'learning_objectives',
        'skill_level',
        'registration_fee',
        'no_show_fine_amount',
        'external_link',
        'is_recurring',
        'parent_event_id',
        'cancelled_at',
        'rsvp_deadline',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'registration_deadline' => 'datetime',
            'rsvp_deadline' => 'datetime',
            'gallery' => 'array',
            'registration_required' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'is_public' => 'boolean',
            'is_recurring' => 'boolean',
            'registration_fee' => 'decimal:2',
            'no_show_fine_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(EventAttendance::class, 'event_id');
    }

    public function prerequisites(): HasMany
    {
        return $this->hasMany(EventPrerequisite::class);
    }

    public function ctfCompetition(): HasOne
    {
        return $this->hasOne(CtfCompetition::class);
    }

    public function hasMemberAttended(User $member): bool
    {
        return $this->attendanceRecords()
            ->where('member_id', $member->id)
            ->whereIn('status', ['present', 'excused'])
            ->exists();
    }

    public function getAttendanceRateAttribute(): float
    {
        $total = $this->registrations()->where('status', 'registered')->count();

        if ($total === 0) {
            return 0;
        }

        $attended = $this->attendanceRecords()
            ->where('status', 'present')
            ->count();

        return round(($attended / $total) * 100, 1);
    }

    public function canMemberRegister(User $member): array
    {
        $errors = [];

        $allowedCount = $this->allowedMembers()->count();

        if ($allowedCount > 0 && ! $this->allowedMembers()->where('user_id', $member->id)->exists()) {
            $errors[] = 'You are not in the allowed members list for this event.';
        }

        if ($this->status === 'cancelled') {
            $errors[] = 'This event has been cancelled.';
        }

        if ($this->start_date && $this->start_date->isPast()) {
            $errors[] = 'This event has already started.';
        }

        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            $errors[] = 'The registration deadline has passed.';
        }

        if ($this->is_full && ! $this->waitlist_enabled) {
            $errors[] = 'This event is full.';
        }

        foreach ($this->prerequisites as $prerequisite) {
            if ($prerequisite->prerequisite_event_id) {
                $hasCompleted = EventAttendance::where('event_id', $prerequisite->prerequisite_event_id)
                    ->where('member_id', $member->id)
                    ->where('status', 'present')
                    ->exists();

                if (! $hasCompleted) {
                    $prereqEvent = Event::find($prerequisite->prerequisite_event_id);
                    $errors[] = "Prerequisite not met: You must attend '{$prereqEvent?->title}' first.";
                }
            }

            if ($prerequisite->required_badge_id) {
                $hasBadge = $member->earnedBadges()
                    ->where('badge_id', $prerequisite->required_badge_id)
                    ->exists();

                if (! $hasBadge) {
                    $badge = \App\Models\Badge::find($prerequisite->required_badge_id);
                    $errors[] = "Prerequisite not met: You need the '{$badge?->name}' badge.";
                }
            }

            if ($prerequisite->required_skill_level) {
                $levels = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
                $memberLevel = $levels[$this->skill_level ?? 'beginner'] ?? 1;
                $requiredLevel = $levels[$prerequisite->required_skill_level] ?? 1;

                if ($memberLevel < $requiredLevel) {
                    $errors[] = "This event requires at least '{$prerequisite->required_skill_level}' skill level.";
                }
            }
        }

        return [
            'can_register' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(EventFeedback::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_instructors')
            ->withPivot('role', 'guest_details')
            ->withTimestamps();
    }

    public function allowedMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_allowed_members');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(EventResource::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(EventCategory::class, 'event_category_event');
    }

    public function recurrence(): HasMany
    {
        return $this->hasMany(EventRecurrence::class);
    }

    public function getRecurrenceAttribute()
    {
        return $this->recurrence()->first();
    }

    public function agendaItems(): HasMany
    {
        return $this->hasMany(EventAgendaItem::class)->orderBy('sort_order');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_favorites')->withTimestamps();
    }

    /**
     * Check if this event is a recurring event series master
     */
    public function isRecurring(): bool
    {
        return (bool) $this->is_recurring;
    }

    /**
     * Check if this event is an occurrence of a recurring series
     */
    public function isOccurrence(): bool
    {
        return $this->parent_event_id !== null;
    }

    /**
     * Get the master event for this occurrence
     */
    public function masterEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    /**
     * Get all occurrences of this recurring event
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    /**
     * Check if this is the master event of a series
     */
    public function isMasterEvent(): bool
    {
        return $this->is_recurring && ! $this->parent_event_id;
    }

    /**
     * Get all future occurrences (including the master if future)
     */
    public function getFutureOccurrences()
    {
        $query = Event::where(function ($q) {
            $q->where('parent_event_id', $this->id)
                ->orWhere('id', $this->id);
        })
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc');

        return $query;
    }

    /**
     * Sync changes to all future occurrences
     */
    public function syncOccurrences(): void
    {
        if (! $this->is_recurring) {
            return;
        }

        $fieldsToSync = [
            'description',
            'location',
            'max_participants',
            'registration_required',
            'is_public',
            'registration_deadline',
            'requirements',
            'registration_fee',
            'external_link',
        ];

        $this->occurrences()
            ->where('start_date', '>', now())
            ->update($this->only($fieldsToSync));
    }

    /**
     * Skip a specific occurrence date
     */
    public function skipOccurrence(Event $occurrence): void
    {
        $occurrence->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function getWaitlistPositionForUser(User $user): ?int
    {
        $position = EventRegistration::where('event_id', $this->id)
            ->where('status', 'waitlist')
            ->whereNotNull('waitlisted_at')
            ->orderBy('waitlisted_at', 'asc')
            ->pluck('user_id')
            ->search(function ($id) use ($user) {
                return (int) $id === $user->id;
            });

        return $position !== false ? $position + 1 : null;
    }

    public function getRegisteredCountAttribute(): int
    {
        return $this->registrations()->where('status', 'registered')->count();
    }

    public function getWaitlistedCountAttribute(): int
    {
        return $this->registrations()->where('status', 'waitlist')->count();
    }

    public function getAttendedCountAttribute(): int
    {
        return $this->registrations()->whereNotNull('attended_at')->count();
    }

    public function getAverageRatingAttribute(): ?float
    {
        $feedbacks = $this->feedback()->whereNotNull('rating')->get();

        return $feedbacks->isEmpty() ? null : round($feedbacks->avg('rating'), 1);
    }

    public function getFeedbackCountAttribute(): int
    {
        return $this->feedback()->count();
    }

    public function getRatingDistributionAttribute(): array
    {
        $feedbacks = $this->feedback()->whereNotNull('rating')->get();
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        foreach ($feedbacks as $feedback) {
            $distribution[$feedback->rating]++;
        }

        return $distribution;
    }

    public function getIsFullAttribute(): bool
    {
        return $this->max_participants && $this->registered_count >= $this->max_participants;
    }

    public function getRemainingSpotsAttribute(): int
    {
        if (! $this->max_participants) {
            return 999;
        }

        return max(0, $this->max_participants - $this->registered_count);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
