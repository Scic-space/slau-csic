<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionVote extends Model
{
    /** @use HasFactory<\Database\Factories\ElectionVoteFactory> */
    use HasFactory;

    protected $fillable = [
        'election_id',
        'election_candidate_id',
        'user_id',
        'receipt_code',
        'receipt_token',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ElectionCandidate::class, 'election_candidate_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateReceiptCode(): string
    {
        return strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 12));
    }

    public static function receiptHash(string $code): string
    {
        return hash('sha256', $code);
    }

    public static function findByReceipt(string $receiptCode): ?self
    {
        return static::where('receipt_code', $receiptCode)->first();
    }

    public static function findByReceiptHash(string $receiptCode): ?self
    {
        $hash = static::receiptHash($receiptCode);

        return static::where('receipt_code', $hash)->first();
    }
}
