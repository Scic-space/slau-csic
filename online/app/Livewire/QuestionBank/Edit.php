<?php

namespace App\Livewire\QuestionBank;

use App\Models\QuestionBankOption;
use App\Models\QuestionBankQuestion;
use Livewire\Component;

class Edit extends Component
{
    public QuestionBankQuestion $question;

    public string $type = '';

    public string $question_text = '';

    public string $code_block = '';

    public string $code_language = '';

    public int $marks = 1;

    public string $explanation = '';

    public array $options = [];

    public ?int $selectedCorrect = null;

    public function mount(QuestionBankQuestion $question)
    {
        $this->question = $question;

        $this->type = $question->type;
        $this->question_text = $question->question_text;
        $this->code_block = $question->code_block ?? '';
        $this->code_language = $question->code_language ?? '';
        $this->marks = $question->marks;
        $this->explanation = $question->explanation ?? '';

        foreach ($question->options as $index => $option) {
            $this->options[$index] = [
                'option_text' => $option->option_text,
                'is_correct' => (bool) $option->is_correct,
            ];
            if ($option->is_correct && $this->type === 'true_false') {
                $this->selectedCorrect = $index;
            }
        }
    }

    public function updatedType($value)
    {
        $this->options = [];
        $this->selectedCorrect = null;

        if ($value === 'true_false') {
            $this->options = [
                ['option_text' => 'True', 'is_correct' => false],
                ['option_text' => 'False', 'is_correct' => false],
            ];
            $this->selectedCorrect = 0;
        } elseif ($value === 'mcq') {
            $this->options = [
                ['option_text' => '', 'is_correct' => false],
                ['option_text' => '', 'is_correct' => false],
            ];
        }
    }

    public function addOption()
    {
        if (count($this->options) < 6) {
            $this->options[] = ['option_text' => '', 'is_correct' => false];
        }
    }

    public function removeOption($index)
    {
        if (count($this->options) > 2) {
            unset($this->options[$index]);
            $this->options = array_values($this->options);
        }
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|in:mcq,true_false,short_answer,code_snippet',
            'question_text' => 'required|string',
            'code_block' => 'nullable|string',
            'code_language' => 'nullable|string|max:50',
            'marks' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
        ]);

        if (in_array($this->type, ['mcq', 'code_snippet'])) {
            $this->validate([
                'options' => 'required|array|min:2',
                'options.*.option_text' => 'required|string',
            ]);

            $hasCorrect = collect($this->options)->contains('is_correct', true);
            if (! $hasCorrect) {
                session()->flash('error', 'At least one option must be marked as correct.');

                return;
            }
        }

        if ($this->type === 'true_false') {
            if ($this->selectedCorrect === null) {
                session()->flash('error', 'Please select the correct answer.');

                return;
            }
        }

        $this->question->update([
            'type' => $this->type,
            'question_text' => $this->question_text,
            'code_block' => $this->code_block ?: null,
            'code_language' => $this->code_language ?: null,
            'marks' => $this->marks,
            'explanation' => $this->explanation ?: null,
        ]);

        $this->question->options()->delete();

        if (in_array($this->type, ['mcq', 'true_false', 'code_snippet'])) {
            foreach ($this->options as $index => $optionData) {
                $isCorrect = $this->type === 'true_false'
                    ? ($index === $this->selectedCorrect)
                    : ($optionData['is_correct'] ?? false);

                QuestionBankOption::create([
                    'question_id' => $this->question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $isCorrect,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('question-bank.index')->with('success', 'Question updated.');
    }

    public function render()
    {
        return view('livewire.question-bank.edit');
    }
}
