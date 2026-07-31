<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtfHint extends Model
{
    protected $fillable = [
        'ctf_challenge_id',
        'tier',
        'content',
        'cost',
    ];

    protected $casts = [
        'tier' => 'integer',
        'cost' => 'integer',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(CtfChallenge::class, 'ctf_challenge_id');
    }
}
