<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtfChallengeSolve extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctf_challenge_id',
        'user_id',
        'ctf_team_id',
        'solve_order',
        'points_awarded',
        'solved_at',
    ];

    protected $casts = [
        'solved_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(CtfChallenge::class, 'ctf_challenge_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(CtfTeam::class, 'ctf_team_id');
    }
}
