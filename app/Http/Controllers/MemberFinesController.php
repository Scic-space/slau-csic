<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberFinesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $fines = Fine::where('user_id', $user->id)
            ->with(['fineType', 'payments.recordedBy', 'appeals'])
            ->orderBy('issue_date', 'desc')
            ->get()
            ->map(fn ($fine) => [
                'id' => $fine->id,
                'fine_type' => $fine->fineType?->name,
                'amount' => (float) $fine->amount,
                'amount_paid' => (float) $fine->amount_paid,
                'balance' => (float) $fine->balance,
                'reason' => $fine->reason,
                'issue_date' => $fine->issue_date->toDateString(),
                'due_date' => $fine->due_date->toDateString(),
                'status' => $fine->status,
                'can_appeal' => $fine->canBeAppealed(),
                'payments' => $fine->payments->map(fn ($p) => [
                    'amount' => (float) $p->amount,
                    'payment_date' => $p->payment_date->toDateString(),
                    'recorded_by' => $p->recordedBy?->name,
                ]),
                'appeals' => $fine->appeals->map(fn ($a) => [
                    'appeal_reason' => $a->appeal_reason,
                    'explanation' => $a->explanation,
                    'status' => $a->status,
                    'submitted_at' => $a->submitted_at->toDateString(),
                ]),
            ]);

        $totalOutstanding = Fine::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'partially_paid'])
            ->sum('balance');

        $totalPaid = Fine::where('user_id', $user->id)->sum('amount_paid');

        $overdueCount = Fine::where('user_id', $user->id)->overdue()->count();

        return Inertia::render('members/Fines', [
            'fines' => $fines,
            'stats' => [
                'total_outstanding' => (float) $totalOutstanding,
                'total_paid' => (float) $totalPaid,
                'overdue_count' => $overdueCount,
            ],
        ]);
    }

    public function appeal(Request $request, Fine $fine): RedirectResponse
    {
        $request->validate([
            'appeal_reason' => ['required', 'string'],
            'explanation' => ['required', 'string', 'max:1000'],
        ]);

        abort_unless($fine->user_id === $request->user()->id, 403);
        abort_unless($fine->canBeAppealed(), 422);

        $fine->appeals()->create([
            'appeal_reason' => $request->appeal_reason,
            'explanation' => $request->explanation,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return redirect()->back()->with('flash', ['success' => 'Appeal submitted successfully.']);
    }
}
