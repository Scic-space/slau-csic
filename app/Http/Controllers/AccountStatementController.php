<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Models\FinePayment;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class AccountStatementController extends Controller
{
    public function download(?User $user = null)
    {
        $targetUser = $user ?? auth()->user();

        if ($targetUser->id !== auth()->id() && ! auth()->user()?->hasAnyRole(['admin', 'Treasurer', 'President', 'super-admin'])) {
            abort(403);
        }

        $fines = Fine::where('user_id', $targetUser->id)
            ->with(['fineType', 'issuedBy'])
            ->orderBy('issue_date', 'desc')
            ->get();

        $payments = FinePayment::whereHas('fine', fn ($q) => $q->where('user_id', $targetUser->id))
            ->with(['fine.fineType', 'recordedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions = Transaction::where('user_id', $targetUser->id)
            ->orWhere('created_by', $targetUser->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $totalFines = $fines->sum('amount');
        $totalPaid = $fines->sum('amount_paid');
        $outstanding = $totalFines - $totalPaid;

        $pdf = Pdf::loadView('pdf.account-statement', [
            'user' => $targetUser,
            'fines' => $fines,
            'payments' => $payments,
            'transactions' => $transactions,
            'totalFines' => $totalFines,
            'totalPaid' => $totalPaid,
            'outstanding' => $outstanding,
        ]);

        $filename = 'account-statement-'.str($targetUser->name)->slug().'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
