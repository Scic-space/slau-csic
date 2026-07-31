<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionExportController extends Controller
{
    public function csv(): BinaryFileResponse
    {
        return Excel::download(new TransactionsExport, 'my-transactions-'.now()->format('Y-m-d').'.csv');
    }
}
