<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CtfTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctf_competition_id',
        'name',
        'slug',
        'description',
        'invite_code',
        'captain_id',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CtfTeam $team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
            if (empty($team->invite_code)) {
                $team->invite_code = Str::random(16);
            }
        });
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(CtfCompetition::class, 'ctf_competition_id');
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CtfTeamMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->members();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CtfSubmission::class);
    }

    public function solves(): HasMany
    {
        return $this->hasMany(CtfChallengeSolve::class);
    }

    public function getTotalScore(): int
    {
        return (int) $this->submissions()
            ->where('is_correct', true)
            ->select('ctf_challenge_id', 'points_awarded')
            ->distinct('ctf_challenge_id')
            ->sum('points_awarded');
    }

    public function getSolveCount(): int
    {
        return $this->submissions()
            ->where('is_correct', true)
            ->distinct('ctf_challenge_id')
            ->count('ctf_challenge_id');
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isCaptain(User $user): bool
    {
        return $this->captain_id === $user->id;
    }

    public function isOpen(): bool
    {
        return $this->is_open;
    }

    public function scopeOpen($query)
    {
        return $query->where('is_open', true);
    }

    public function scopeForCompetition($query, CtfCompetition $competition)
    {
        return $query->where('ctf_competition_id', $competition->id);
    }
}
