<?php

namespace App\Livewire;

use App\Models\BudgetCategory;
use App\Models\Fine;
use App\Models\FinePayment;
use App\Models\Transaction;
use Livewire\Component;

class TreasurerDashboard extends Component
{
    public function render()
    {
        $totalFinesIssued = Fine::sum('amount');
        $totalFinesCollected = Fine::sum('amount_paid');
        $totalFinesOutstanding = Fine::whereIn('status', ['pending', 'partially_paid'])->sum('balance');
        $overdueFinesCount = Fine::overdue()->count();
        $pendingPaymentsCount = FinePayment::pending()->count();

        $totalIncome = Transaction::where('type', 'income')->where('status', 'approved')->sum('amount');
        $totalExpenses = Transaction::where('type', 'expense')->where('status', 'approved')->sum('amount');

        $recentTransactions = Transaction::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'category' => $t->category,
                'amount' => (float) $t->amount,
                'status' => $t->status,
                'description' => str($t->description)->limit(60),
                'created_at' => $t->created_at->format('M j, Y'),
            ]);

        $monthlyLabels = collect();
        $monthlyIncomeData = collect();
        $monthlyExpenseData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels->push($month->format('M Y'));
            $ym = $month->format('Y-m');
            $monthlyIncomeData->push((float) Transaction::where('type', 'income')
                ->where('status', 'approved')
                ->whereRaw("strftime('%Y-%m', created_at) = ?", [$ym])
                ->sum('amount'));
            $monthlyExpenseData->push((float) Transaction::where('type', 'expense')
                ->where('status', 'approved')
                ->whereRaw("strftime('%Y-%m', created_at) = ?", [$ym])
                ->sum('amount'));
        }

        $budgetCategories = BudgetCategory::active()
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'allocated' => (float) $cat->allocated_amount,
                'spent' => (float) Transaction::where('category', $cat->name)
                    ->where('status', 'approved')
                    ->where('type', $cat->type)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
            ])
            ->filter(fn ($cat) => $cat['allocated'] > 0)
            ->values();

        $pendingPayments = FinePayment::pending()
            ->with(['fine.user', 'fine.fineType'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'fine_id' => $p->fine_id,
                'amount' => (float) $p->amount,
                'method' => $p->payment_method,
                'member' => $p->fine->user->name,
                'type' => $p->fine->fineType?->name ?? 'General',
                'submitted_at' => $p->created_at->format('M j, Y g:i A'),
                'has_receipt' => ! is_null($p->receipt_path),
                'receipt_url' => $p->receipt_url,
            ]);

        return view('livewire.treasurer-dashboard', [
            'stats' => [
                'total_fines_issued' => (float) $totalFinesIssued,
                'total_fines_collected' => (float) $totalFinesCollected,
                'total_fines_outstanding' => (float) $totalFinesOutstanding,
                'overdue_fines_count' => $overdueFinesCount,
                'pending_payments_count' => $pendingPaymentsCount,
                'total_income' => (float) $totalIncome,
                'total_expenses' => (float) $totalExpenses,
                'net_balance' => (float) ($totalIncome - $totalExpenses),
            ],
            'recentTransactions' => $recentTransactions,
            'chartLabels' => $monthlyLabels,
            'chartIncome' => $monthlyIncomeData,
            'chartExpenses' => $monthlyExpenseData,
            'budgetCategories' => $budgetCategories,
            'pendingPayments' => $pendingPayments,
        ]);
    }

    public function confirmPayment(int $paymentId): void
    {
        $payment = FinePayment::findOrFail($paymentId);
        $payment->confirm();

        $this->dispatch('toast-show', message: 'Payment confirmed successfully.', type: 'success');
    }

    public function rejectPayment(int $paymentId): void
    {
        $payment = FinePayment::findOrFail($paymentId);
        $payment->reject('Rejected by treasurer');

        $this->dispatch('toast-show', message: 'Payment rejected.', type: 'success');
    }
}
