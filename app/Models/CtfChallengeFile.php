<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class CtfChallengeFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'ctf_challenge_id',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(CtfChallenge::class, 'ctf_challenge_id');
    }

    public function getDownloadUrl(): string
    {
        return URL::route('ctf.file.download', ['file' => $this->id]);
    }

    public function getSizeForHumans(): string
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
