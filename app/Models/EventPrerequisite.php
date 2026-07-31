<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPrerequisite extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'prerequisite_event_id',
        'required_badge_id',
        'required_skill_level',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function prerequisiteEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'prerequisite_event_id');
    }

    public function requiredBadge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'required_badge_id');
    }
}
