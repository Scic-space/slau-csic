<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBankQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'question_text',
        'code_block',
        'code_language',
        'marks',
        'explanation',
    ];

    protected $casts = [
        'marks' => 'integer',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(QuestionBankOption::class)->orderBy('order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function toExamShieldFormat(): array
    {
        return [
            'type' => $this->type,
            'question_text' => $this->question_text,
            'marks' => $this->marks,
            'code_block' => $this->code_block,
            'language' => $this->code_language,
            'options' => $this->options->map(fn ($opt) => [
                'option_text' => $opt->option_text,
                'is_correct' => (bool) $opt->is_correct,
            ])->toArray(),
        ];
    }
}
