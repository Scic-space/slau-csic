<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'file_path',
        'url',
        'type',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isFile(): bool
    {
        return ! is_null($this->file_path);
    }

    public function isUrl(): bool
    {
        return ! is_null($this->url);
    }

    public function supportsDocxDownload(): bool
    {
        $path = parse_url((string) $this->file_path, PHP_URL_PATH);

        return $path && in_array(strtolower(pathinfo(rawurldecode($path), PATHINFO_EXTENSION)), [
            'docx', 'pdf', 'html', 'htm', 'txt', 'md', 'markdown',
        ], true);
    }

    public function getDisplayUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/'.$this->file_path) : $this->url;
    }
}
