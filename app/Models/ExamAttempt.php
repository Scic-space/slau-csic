<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExamAttempt extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'exam_id',
        'user_id',
        'started_at',
        'submitted_at',
        'time_remaining_seconds',
        'total_score',
        'passed',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'passed' => 'boolean',
            'total_score' => 'integer',
            'time_remaining_seconds' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'exam_attempt_id');
    }

    public function certificateEligibility(): HasOne
    {
        return $this->hasOne(CertificateEligibility::class, 'exam_attempt_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['total_score', 'passed', 'submitted_at', 'admin_notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function getIsCompletedAttribute(): bool
    {
        return ! is_null($this->submitted_at);
    }
}
