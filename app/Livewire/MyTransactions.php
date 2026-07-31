<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Transactions')]
class MyTransactions extends Component
{
    use WithPagination;

    public string $typeFilter = '';

    public string $statusFilter = '';

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Transaction::where('created_by', $user->id)
            ->with(['creator', 'approver'])
            ->orderBy('created_at', 'desc');

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $transactions = $query->paginate(15)->through(fn ($t) => [
            'id' => $t->id,
            'type' => $t->type,
            'category' => $t->category,
            'amount' => (float) $t->amount,
            'description' => $t->description,
            'status' => $t->status,
            'created_at' => $t->created_at->format('M j, Y'),
        ]);

        $statsQuery = Transaction::where('created_by', $user->id)
            ->where('status', 'approved');

        if ($this->typeFilter) {
            $statsQuery->where('type', $this->typeFilter);
        }

        $totalIncome = (clone $statsQuery)->where('type', 'income')->sum('amount');
        $totalExpenses = (clone $statsQuery)->where('type', 'expense')->sum('amount');

        return view('livewire.my-transactions', [
            'transactions' => $transactions,
            'stats' => [
                'total_income' => (float) $totalIncome,
                'total_expenses' => (float) $totalExpenses,
            ],
        ]);
    }
}
