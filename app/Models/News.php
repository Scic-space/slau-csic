<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'content_type',
        'source_name',
        'source_url',
        'thumbnail_url',
        'thumbnail_file',
        'video_url',
        'video_file',
        'is_featured',
        'is_published',
        'created_by',
        'published_at',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (News $news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);
            }
        });

        static::updating(function (News $news) {
            if ($news->isDirty('title') && empty($news->slug)) {
                $news->slug = Str::slug($news->title);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'category' => NewsCategory::class,
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function isVideo(): bool
    {
        return $this->content_type === 'video';
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        // Convert YouTube watch URLs to embed URLs (privacy-enhanced nocookie domain)
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->video_url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/'.$matches[1].'?rel=0&modestbranding=1';
        }

        // Already an embed URL — normalise to nocookie domain
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $this->video_url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/'.$matches[1].'?rel=0&modestbranding=1';
        }

        return $this->video_url;
    }

    public function getPublicVideoUrlAttribute(): ?string
    {
        if ($this->video_file && Storage::disk('public')->exists($this->video_file)) {
            return Storage::disk('public')->url($this->video_file);
        }

        return $this->embed_url;
    }

    public function getPublicThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_file && Storage::disk('public')->exists($this->thumbnail_file)) {
            return Storage::disk('public')->url($this->thumbnail_file);
        }

        return $this->thumbnail_url;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
