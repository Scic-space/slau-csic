<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class FineNoticeController extends Controller
{
    public function download(Fine $fine): Response
    {
        if ($fine->user_id !== auth()->id() && ! auth()->user()?->hasAnyRole(['admin', 'Treasurer', 'President', 'Technical Lead', 'super-admin'])) {
            abort(403);
        }

        $fine->loadMissing(['fineType', 'user', 'issuedBy']);

        $pdf = Pdf::loadView('pdf.fine-notice', [
            'fine' => $fine,
            'user' => $fine->user,
        ]);

        $filename = 'fine-notice-'.$fine->id.'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
