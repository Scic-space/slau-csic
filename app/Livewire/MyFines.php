<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\Fine;
use App\Models\FineAppeal;
use App\Models\FinePayment;
use App\Notifications\FineAppealSubmittedNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class MyFines extends Component
{
    use GuardsPendingMembers;
    use WithFileUploads;

    public ?int $selectedFineId = null;

    public string $appealReason = '';

    public string $appealExplanation = '';

    public bool $showAppealForm = false;

    public bool $showPaymentForm = false;

    public ?int $paymentFineId = null;

    public string $paymentAmount = '';

    public string $paymentMethod = 'cash';

    public $paymentReceipt = null;

    public function render()
    {
        $user = Auth::user();

        $fines = Fine::where('user_id', $user->id)
            ->with(['fineType', 'payments', 'appeals'])
            ->orderBy('issue_date', 'desc')
            ->get();

        $totalOutstanding = $fines->whereIn('status', ['pending', 'partially_paid'])->sum('balance');
        $totalPaid = $fines->sum('amount_paid');
        $overdueCount = $fines->filter(fn ($fine) => $fine->is_overdue)->count();
        $pendingSubmissions = FinePayment::whereHas('fine', fn ($q) => $q->where('user_id', $user->id))
            ->pending()
            ->with('fine')
            ->count();

        return view('livewire.my-fines', [
            'fines' => $fines,
            'stats' => [
                'total_outstanding' => $totalOutstanding,
                'total_paid' => $totalPaid,
                'overdue_count' => $overdueCount,
                'pending_submissions' => $pendingSubmissions,
            ],
        ]);
    }

    public function openPayment(int $fineId): void
    {
        $fine = Fine::findOrFail($fineId);
        abort_unless($fine->user_id === Auth::id(), 403);

        $this->paymentFineId = $fineId;
        $this->paymentAmount = (string) $fine->balance;
        $this->paymentMethod = 'cash';
        $this->paymentReceipt = null;
        $this->showPaymentForm = true;
    }

    public function closePayment(): void
    {
        $this->showPaymentForm = false;
        $this->paymentFineId = null;
        $this->paymentAmount = '';
        $this->paymentMethod = 'cash';
        $this->paymentReceipt = null;
    }

    public function submitPayment(): void
    {
        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:1'],
            'paymentMethod' => ['required', 'string', 'in:'.implode(',', array_keys(FinePayment::getPaymentMethods()))],
            'paymentReceipt' => ['nullable', 'image', 'max:2048'],
        ]);

        $fine = Fine::findOrFail($this->paymentFineId);
        abort_unless($fine->user_id === Auth::id(), 403);

        $receiptPath = null;
        if ($this->paymentReceipt) {
            $receiptPath = $this->paymentReceipt->store('fine-payments', 'public');
        }

        $payment = $fine->payments()->create([
            'amount' => $this->paymentAmount,
            'payment_date' => now(),
            'payment_method' => $this->paymentMethod,
            'receipt_path' => $receiptPath,
            'submitted_by' => Auth::id(),
            'notes' => 'Submitted by member',
            'status' => 'pending',
        ]);

        $this->closePayment();
        $this->dispatch('toast-show', message: 'Payment submitted for review. An admin will confirm it shortly.', type: 'success');
    }

    public function openAppeal(int $fineId): void
    {
        $this->selectedFineId = $fineId;
        $this->appealReason = '';
        $this->appealExplanation = '';
        $this->showAppealForm = true;
    }

    public function closeAppeal(): void
    {
        $this->showAppealForm = false;
        $this->selectedFineId = null;
        $this->appealReason = '';
        $this->appealExplanation = '';
    }

    public function submitAppeal(): void
    {
        $this->validate([
            'appealReason' => ['required', 'string', 'in:'.implode(',', array_keys(FineAppeal::getAppealReasons()))],
            'appealExplanation' => ['required', 'string', 'max:1000'],
        ]);

        $fine = Fine::findOrFail($this->selectedFineId);

        abort_unless($fine->user_id === Auth::id(), 403);
        abort_unless($fine->canBeAppealed(), 422);

        $appeal = $fine->appeals()->create([
            'appeal_reason' => $this->appealReason,
            'explanation' => $this->appealExplanation,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $adminUsers = \App\Models\User::role(['admin', 'President', 'Technical Lead'])->get();
        foreach ($adminUsers as $admin) {
            $admin->notify(new FineAppealSubmittedNotification($appeal));
        }

        $this->closeAppeal();
        $this->dispatch('toast-show', message: 'Appeal submitted successfully.', type: 'success');
    }
}
