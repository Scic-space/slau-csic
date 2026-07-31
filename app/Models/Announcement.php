<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'type',
        'audience',
        'target_roles',
        'is_published',
        'send_email',
        'send_push',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Announcement $announcement) {
            if (empty($announcement->created_by) && Auth::check()) {
                $announcement->created_by = Auth::id();
            }

            if (empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title);
            }
        });

        static::updating(function (Announcement $announcement) {
            if ($announcement->isDirty('title') && empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title);
            }
        });

        static::saved(function (Announcement $announcement) {
            if ($announcement->is_published && $announcement->wasChanged('is_published')) {
                \App\Jobs\PostAnnouncementToDiscord::dispatch($announcement);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function views(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_views')
            ->withTimestamps()
            ->withPivot('viewed_at');
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'send_email' => 'boolean',
            'send_push' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'target_roles' => 'array',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
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

    public function isViewedBy(User $user): bool
    {
        return $this->views()->where('user_id', $user->id)->exists();
    }

    public function markAsViewedBy(User $user): void
    {
        $this->views()->syncWithoutDetaching([
            $user->id => ['viewed_at' => now()],
        ]);
    }

    public function getViewCountAttribute(): int
    {
        return $this->views()->count();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
