<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Poll extends Model
{
    /** @use HasFactory<\Database\Factories\PollFactory> */
    use HasFactory;

    protected $fillable = [
        'question',
        'description',
        'created_by',
        'is_published',
        'allow_multiple',
        'expires_at',
        'slug',
        'votes_count',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Poll $poll) {
            if (empty($poll->created_by) && Auth::check()) {
                $poll->created_by = Auth::id();
            }

            if (empty($poll->slug)) {
                $poll->slug = Str::slug($poll->question);
            }
        });

        static::updating(function (Poll $poll) {
            if ($poll->isDirty('question') && empty($poll->slug)) {
                $poll->slug = Str::slug($poll->question);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'allow_multiple' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function voters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'poll_votes')
            ->withTimestamps();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        });
    }

    public function isActive(): bool
    {
        if (! $this->expires_at) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return ! $this->isActive();
    }

    public function hasVoted(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function totalVotes(): int
    {
        return $this->votes_count;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
