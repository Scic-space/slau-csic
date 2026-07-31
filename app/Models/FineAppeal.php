<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FineAppeal extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'fine_id',
        'appeal_reason',
        'explanation',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'decision_notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function fine(): BelongsTo
    {
        return $this->belongsTo(Fine::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reviewed_by', 'decision_notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function approve(?User $reviewer = null, ?string $notes = null): bool
    {
        $updated = $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer?->id ?? auth()->id(),
            'decision_notes' => $notes,
        ]);

        if ($updated) {
            $this->fine->waive($reviewer, $notes ?? 'Fine waived via appeal approval');
        }

        return $updated;
    }

    public function reject(?User $reviewer = null, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer?->id ?? auth()->id(),
            'decision_notes' => $notes,
        ]);
    }

    public static function getAppealReasons(): array
    {
        return [
            'first_offense' => 'First Offense',
            'special_circumstances' => 'Special Circumstances',
            'error' => 'Error',
            'other' => 'Other',
        ];
    }

    public function getFormattedAppealReasonAttribute(): string
    {
        return self::getAppealReasons()[$this->appeal_reason] ?? ucfirst($this->appeal_reason);
    }

    public function canBeReviewed(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return $this->submitted_at->diffInDays(now()) > 7;
    }
}
