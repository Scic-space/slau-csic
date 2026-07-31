<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtfHintPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctf_challenge_id',
        'user_id',
        'hint_tier',
        'points_spent',
        'purchased_at',
    ];

    protected $casts = [
        'hint_tier' => 'integer',
        'purchased_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(CtfChallenge::class, 'ctf_challenge_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
