<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'uploaded_by',
        'title',
        'description',
        'type',
        'file_path',
        'url',
        'visibility',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isFile(): bool
    {
        return ! is_null($this->file_path);
    }

    public function isUrl(): bool
    {
        return ! is_null($this->url);
    }

    public function getDisplayUrlAttribute(): string
    {
        return $this->file_path ? asset('storage/'.$this->file_path) : ($this->url ?? '#');
    }
}
