<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionNomination extends Model
{
    /** @use HasFactory<\Database\Factories\ElectionNominationFactory> */
    use HasFactory;

    public const STATUSES = [
        'submitted', 'under_review', 'shortlisted', 'approved', 'rejected', 'withdrawn',
    ];

    public const SCORE_CRITERIA = [
        'experience' => 'Experience & Qualifications',
        'vision' => 'Vision & Goals',
        'communication' => 'Communication Skills',
        'overall' => 'Overall Suitability',
    ];

    protected $fillable = [
        'election_id',
        'user_id',
        'statement',
        'manifesto',
        'agenda',
        'photo',
        'scores',
        'documents',
        'status',
        'admin_notes',
        'reviewer_id',
        'reviewed_at',
        'submitted_at',
        'interview_scheduled_at',
        'interview_location',
        'interview_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'interview_scheduled_at' => 'datetime',
            'scores' => 'array',
            'documents' => 'array',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ElectionNominationReview::class, 'nomination_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function isShortlisted(): bool
    {
        return $this->status === 'shortlisted';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['submitted', 'under_review', 'shortlisted']);
    }

    public function canWithdraw(): bool
    {
        return $this->isActive();
    }

    public function canReapply(): bool
    {
        return $this->isRejected() || $this->isWithdrawn();
    }

    public function getScoreAverageAttribute(): ?float
    {
        if (! $this->scores || empty($this->scores)) {
            return null;
        }

        $values = array_values($this->scores);
        $values = array_filter($values, fn ($v) => is_numeric($v));

        if (empty($values)) {
            return null;
        }

        return round(array_sum($values) / count($values), 1);
    }

    public function markUnderReview(User $reviewer): bool
    {
        $from = $this->status;

        return $this->update([
            'status' => 'under_review',
            'reviewer_id' => $reviewer->id,
        ]) && $this->logReview($from, 'under_review');
    }

    public function shortlist(?string $notes = null): bool
    {
        $from = $this->status;

        return $this->update(['status' => 'shortlisted', 'admin_notes' => $notes])
            && $this->logReview($from, 'shortlisted', $notes);
    }

    public function approve(?string $notes = null): bool
    {
        if ($this->isRejected() || $this->isWithdrawn()) {
            return false;
        }

        $from = $this->status;

        return $this->update([
            'status' => 'approved',
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]) && $this->logReview($from, 'approved', $notes);
    }

    public function reject(?string $notes = null): bool
    {
        if ($this->isRejected() || $this->isWithdrawn()) {
            return false;
        }

        $from = $this->status;

        return $this->update([
            'status' => 'rejected',
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]) && $this->logReview($from, 'rejected', $notes);
    }

    public function withdraw(): bool
    {
        $from = $this->status;

        return $this->update(['status' => 'withdrawn'])
            && $this->logReview($from, 'withdrawn', 'Withdrawn by applicant');
    }

    public function reapply(array $data): bool
    {
        $from = $this->status;

        $updateData = array_merge($data, [
            'status' => 'submitted',
            'submitted_at' => now(),
            'reviewer_id' => null,
            'reviewed_at' => null,
            'admin_notes' => null,
            'interview_scheduled_at' => null,
            'interview_location' => null,
            'interview_notes' => null,
        ]);

        return $this->update($updateData)
            && $this->logReview($from, 'submitted', 'Re-applied');
    }

    protected function logReview(?string $from, string $to, ?string $notes = null): bool
    {
        $this->reviews()->create([
            'user_id' => auth()->id() ?? $this->user_id,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
        ]);

        return true;
    }

    public function updateScores(array $scores): bool
    {
        return $this->update(['scores' => $scores]);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review', 'shortlisted']);
    }

    public function scopeByReviewer($query, User $reviewer)
    {
        return $query->where('reviewer_id', $reviewer->id);
    }
}
