<?php

namespace App\Livewire;

use App\Models\ElectionVote;
use Livewire\Component;

class VerifyReceipt extends Component
{
    public string $receiptCode = '';

    public ?array $result = null;

    public ?string $error = null;

    public function mount(?string $code = null): void
    {
        $this->receiptCode = $code ?? request()->query('code', '');
    }

    public function verify(): void
    {
        $this->validate([
            'receiptCode' => ['required', 'string', 'max:64'],
        ]);

        $vote = ElectionVote::findByReceiptHash($this->receiptCode);

        if (! $vote) {
            $this->result = null;
            $this->error = 'No vote found with this receipt code.';

            return;
        }

        $vote->load(['election', 'candidate']);

        $this->result = [
            'election_title' => $vote->election->title,
            'election_position' => $vote->election->position,
            'candidate_name' => $vote->candidate->name,
            'voted_at' => $vote->created_at->format('M j, Y g:i A'),
        ];
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.verify-receipt');
    }
}
