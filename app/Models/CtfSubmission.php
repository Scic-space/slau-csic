<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtfSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctf_challenge_id',
        'user_id',
        'ctf_team_id',
        'submitted_flag',
        'is_correct',
        'points_awarded',
        'attempt_number',
        'ip_address',
        'submitted_at',
    ];

    protected $casts = [
        'points_awarded' => 'integer',
        'attempt_number' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(CtfChallenge::class, 'ctf_challenge_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(CtfTeam::class, 'ctf_team_id');
    }

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    public function scopeByUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForChallenge($query, CtfChallenge $challenge)
    {
        return $query->where('ctf_challenge_id', $challenge->id);
    }

    public function isCorrect(): bool
    {
        return $this->is_correct;
    }

    public function isFirstSolve(): bool
    {
        return $this->attempt_number === 1 && $this->is_correct;
    }
}
