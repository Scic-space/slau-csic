<?php

namespace App\Models;

use App\Notifications\BroadcastMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    public const BOARD_ROLES = [
        'President',
        'Vice President',
        'General Secretary',
        'Treasurer',
        'Technical Lead',
        'Public Relations',
        'Head of Projects',
        'Events Lead',
        'CTF Lead',
        'Lead Developer',
        'Workshop Coordinator',
        'Club Mentor',
    ];

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
                $announcement->slug = static::generateUniqueSlug($announcement->title);
            }
        });

        static::updating(function (Announcement $announcement) {
            if ($announcement->isDirty('title') && empty($announcement->slug)) {
                $announcement->slug = Str::slug($announcement->title);
            }
        });

        static::created(function (Announcement $announcement) {
            if ($announcement->is_published) {
                \App\Jobs\PostAnnouncementToDiscord::dispatch($announcement);
                $announcement->deliverNotifications();
            }
        });

        static::updated(function (Announcement $announcement) {
            if ($announcement->is_published && $announcement->wasChanged('is_published')) {
                \App\Jobs\PostAnnouncementToDiscord::dispatch($announcement);
                $announcement->deliverNotifications();
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

    public function scopeActive($query)
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
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

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function targetUsers(): Builder
    {
        $query = User::query();

        return match ($this->audience) {
            'active_members' => $query->where('membership_status', 'active'),
            'board' => $query->role(self::BOARD_ROLES),
            'specific_roles' => filled($this->target_roles)
                ? $query->role($this->target_roles)
                : $query->whereRaw('1 = 0'),
            default => $query->whereNotIn('membership_status', ['rejected', 'left']),
        };
    }

    public function deliverNotifications(): void
    {
        $channels = [];

        if ($this->send_push) {
            $channels[] = 'database';
        }

        if ($this->send_email) {
            $channels[] = 'mail';
        }

        if (empty($channels)) {
            return;
        }

        $subject = $this->title;
        $body = Str::limit(html_entity_decode(strip_tags($this->content ?? '')), 500);

        $this->targetUsers()->chunk(100, function ($users) use ($subject, $body, $channels): void {
            foreach ($users as $user) {
                $user->notify(new BroadcastMessage(
                    subject: $subject,
                    body: $body,
                    channels: $channels,
                ));
            }
        });
    }

    public function getViewCountAttribute(): int
    {
        return $this->views()->count();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'announcement';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
