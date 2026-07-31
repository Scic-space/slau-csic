<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_bank_question_id',
        'custom_marks',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'custom_marks' => 'integer',
            'order' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBankQuestion::class, 'question_bank_question_id');
    }

    public function getEffectiveMarksAttribute(): int
    {
        return $this->custom_marks ?? $this->question?->marks ?? 0;
    }
}
