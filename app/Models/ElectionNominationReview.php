<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionNominationReview extends Model
{
    protected $fillable = [
        'nomination_id',
        'user_id',
        'from_status',
        'to_status',
        'notes',
    ];

    public function nomination(): BelongsTo
    {
        return $this->belongsTo(ElectionNomination::class, 'nomination_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
