<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CtfChallenge extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const string FLAG_PATTERN = '/^SLAU_CSIC\{[^}]+\}$/';

    protected $fillable = [
        'ctf_competition_id',
        'ctf_category_id',
        'title',
        'slug',
        'description',
        'flag_hash',
        'flag_case_sensitive',
        'points',
        'difficulty',
        'is_active',
        'max_attempts',
        'tags',
        'sort_order',
        'dynamic_scoring',
        'min_points',
        'decay_factor',
        'solve_count',
        'depends_on_challenge_id',
    ];

    protected $casts = [
        'points' => 'integer',
        'max_attempts' => 'integer',
        'sort_order' => 'integer',
        'tags' => 'array',
        'dynamic_scoring' => 'boolean',
        'min_points' => 'integer',
        'decay_factor' => 'integer',
        'solve_count' => 'integer',
        'flag_case_sensitive' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'points', 'difficulty', 'is_active', 'ctf_category_id', 'max_attempts'])
            ->logOnlyDirty();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CtfChallenge $challenge) {
            if (empty($challenge->slug)) {
                $challenge->slug = Str::slug($challenge->title);
            }
        });

    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(CtfCompetition::class, 'ctf_competition_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CtfCategory::class, 'ctf_category_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CtfSubmission::class);
    }

    public function writeups(): HasMany
    {
        return $this->hasMany(CtfWriteup::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(CtfChallengeFile::class);
    }

    public function hints(): HasMany
    {
        return $this->hasMany(CtfHint::class)->orderBy('tier');
    }

    public function hintPurchases(): HasMany
    {
        return $this->hasMany(CtfHintPurchase::class);
    }

    public function solves(): HasMany
    {
        return $this->hasMany(CtfChallengeSolve::class);
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(CtfChallenge::class, 'depends_on_challenge_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(CtfChallenge::class, 'depends_on_challenge_id');
    }

    public function hasPurchasedHint(User $user): bool
    {
        return $this->hintPurchases()->where('user_id', $user->id)->exists();
    }

    public function getDynamicPoints(): int
    {
        if (! $this->dynamic_scoring) {
            return $this->points;
        }

        $solves = $this->solve_count;
        $maxPoints = $this->points;
        $minPoints = $this->min_points ?? intval($maxPoints * 0.5);
        $decay = $this->decay_factor ?: 20;

        if ($solves === 0) {
            return $maxPoints;
        }

        $points = intval($maxPoints - ($maxPoints - $minPoints) * (1 - exp(-$solves / $decay)));

        return max($minPoints, $points);
    }

    public function setFlagAttribute($flag)
    {
        $caseSensitive = $this->attributes['flag_case_sensitive'] ?? false;
        $normalized = $caseSensitive ? $flag : strtolower($flag);
        $this->attributes['flag_hash'] = Hash::make($normalized);
    }

    public function verifyFlag($flag): bool
    {
        $stored = $this->flag_hash;
        $normalized = $this->flag_case_sensitive ? $flag : strtolower($flag);

        if (str_starts_with($stored, '$2')) {
            return Hash::check($normalized, $stored);
        }

        if (strlen($stored) === 64 && ctype_xdigit($stored)) {
            return hash_equals($stored, hash('sha256', $normalized));
        }

        return false;
    }

    public function areDependenciesMet(User $user): bool
    {
        if (! $this->depends_on_challenge_id) {
            return true;
        }

        return $this->dependsOn->isSolvedBy($user);
    }

    public function isSolvedBy($user): bool
    {
        return $this->submissions()
            ->where('user_id', $user->id)
            ->where('is_correct', true)
            ->exists();
    }

    public function getSolveCount(): int
    {
        if ($this->relationLoaded('solves')) {
            return $this->solves->count();
        }

        return $this->submissions()
            ->where('is_correct', true)
            ->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
