<?php

namespace App\Livewire\QuestionBank;

use App\Models\QuestionBankQuestion;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?string $typeFilter = null;

    protected $queryString = ['search', 'typeFilter'];

    public function delete($id)
    {
        QuestionBankQuestion::find($id)?->delete();
        session()->flash('message', 'Question deleted.');
    }

    public function render()
    {
        $query = QuestionBankQuestion::with('options');

        if ($this->search) {
            $query->where('question_text', 'like', "%{$this->search}%");
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        $questions = $query->orderByDesc('created_at')->paginate(20);

        return view('livewire.question-bank.index', compact('questions'));
    }
}
