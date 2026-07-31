<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'title',
        'description',
        'type',
        'points',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ChallengeSubmission::class);
    }

    public function userSubmission(int $userId): ?ChallengeSubmission
    {
        return $this->submissions()->where('user_id', $userId)->first();
    }

    public function isSolvedBy(int $userId): bool
    {
        return $this->submissions()->where('user_id', $userId)->where('is_correct', true)->exists();
    }

    public function verifyAnswer(string $submittedAnswer): bool
    {
        return strcasecmp(trim($submittedAnswer), trim($this->answer)) === 0;
    }
}
