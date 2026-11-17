<?php

namespace App\Models;

use App\Services\ExamService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Exam extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'duration_minutes',
        'passing_score',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'passing_score' => 'integer',
            'status' => 'string',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Exam $exam) {
            if ($exam->status === 'published') {
                app(ExamService::class)->notifyPublished($exam);
            }
        });

        static::updated(function (Exam $exam) {
            if ($exam->status === 'published' && $exam->wasChanged('status')) {
                app(ExamService::class)->notifyPublished($exam);
            }
        });
    }

    /**
     * Whether members can currently access and take this exam.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'published';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id')->orderBy('order');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function certificateEligibilities(): HasMany
    {
        return $this->hasMany(CertificateEligibility::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'duration_minutes', 'passing_score'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function toExamShieldFormat(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'passing_score' => $this->passing_score,
            'status' => $this->status,
            'questions' => $this->questions->map(fn ($eq) => [
                'id' => $eq->id,
                'order' => $eq->order,
                'marks' => $eq->effective_marks,
                'question' => $eq->question?->toExamShieldFormat(),
            ])->toArray(),
        ];
    }
}
