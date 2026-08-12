<?php

namespace App\Http\Controllers;

use App\Models\FinePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class FineReceiptController extends Controller
{
    public function download(FinePayment $payment): Response
    {
        if ($payment->fine->user_id !== auth()->id() && ! auth()->user()?->hasAnyRole(['admin', 'Treasurer', 'President', 'super-admin'])) {
            abort(403);
        }

        $payment->loadMissing(['fine.fineType', 'fine.user', 'recordedBy', 'submittedBy']);

        $pdf = Pdf::loadView('pdf.fine-receipt', [
            'payment' => $payment,
            'fine' => $payment->fine,
            'user' => $payment->fine->user,
        ]);

        $filename = 'receipt-fine-'.$payment->fine_id.'-'.$payment->id.'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
