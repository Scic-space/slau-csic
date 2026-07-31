<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioExperience extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'organization',
        'description',
        'start_date',
        'end_date',
        'is_current',
        'type',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function types(): array
    {
        return ['experience', 'education', 'volunteer', 'leadership'];
    }
}
