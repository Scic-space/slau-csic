<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Election extends Model
{
    /** @use HasFactory<\Database\Factories\ElectionFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'position',
        'description',
        'status',
        'starts_at',
        'ends_at',
        'results_visible',
        'allow_vote_changes',
        'is_test_ballot',
        'results_publish_at',
        'applications_starts_at',
        'applications_ends_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($election) {
            if (empty($election->slug)) {
                $election->slug = Str::slug($election->title);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'results_visible' => 'boolean',
            'allow_vote_changes' => 'boolean',
            'is_test_ballot' => 'boolean',
            'results_publish_at' => 'datetime',
            'applications_starts_at' => 'datetime',
            'applications_ends_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'position', 'status', 'starts_at', 'ends_at', 'results_visible', 'is_test_ballot', 'results_publish_at', 'applications_starts_at', 'applications_ends_at'])
            ->logOnlyDirty();
    }

    public function actions(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class)->orderBy('sort_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class);
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(ElectionNomination::class);
    }

    public function voterEligibility(): HasMany
    {
        return $this->hasMany(ElectionVoterEligibility::class);
    }

    public function eligibleVoters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'election_voter_eligibility')
            ->wherePivot('is_eligible', true)
            ->withPivot('reason')
            ->withTimestamps();
    }

    public function ineligibleVoters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'election_voter_eligibility')
            ->wherePivot('is_eligible', false)
            ->withPivot('reason')
            ->withTimestamps();
    }

    public function hasEligibilityOverrideFor(User $user): bool
    {
        return $this->voterEligibility()->where('user_id', $user->id)->exists();
    }

    public function isExplicitlyEligible(User $user): bool
    {
        $override = $this->voterEligibility()->where('user_id', $user->id)->first();

        if (! $override) {
            return true;
        }

        return $override->is_eligible;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open'
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->ends_at || $this->ends_at->isFuture());
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed'
            || ($this->ends_at && $this->ends_at->isPast());
    }

    public function allowsVoteChanges(): bool
    {
        return $this->allow_vote_changes;
    }

    public function getTurnoutAttribute(): string
    {
        $eligible = User::activeMembers()->count();

        if ($eligible === 0) {
            return '0%';
        }

        $voted = $this->votes()->count();

        return round(($voted / $eligible) * 100, 1).'%';
    }

    public function getWinnerAttribute(): ?ElectionCandidate
    {
        return $this->candidates()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->first();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeResultsVisible($query)
    {
        return $query->where('results_visible', true);
    }

    public function scopeLive($query)
    {
        return $query->where('is_test_ballot', false);
    }

    public function scopeAcceptingApplications($query)
    {
        return $query->whereNotNull('applications_starts_at')
            ->where('applications_starts_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('applications_ends_at')->orWhere('applications_ends_at', '>=', now()));
    }

    public function isAcceptingApplications(): bool
    {
        return $this->applications_starts_at
            && $this->applications_starts_at->isPast()
            && (! $this->applications_ends_at || $this->applications_ends_at->isFuture());
    }

    public function scopeTestBallot($query)
    {
        return $query->where('is_test_ballot', true);
    }
}
