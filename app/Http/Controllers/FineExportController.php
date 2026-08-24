<?php

namespace App\Http\Controllers;

use App\Exports\FinesExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FineExportController extends Controller
{
    public function xlsx(): BinaryFileResponse
    {
        return Excel::download(new FinesExport, 'my-fines-'.now()->format('Y-m-d').'.xlsx');
    }

    public function csv(): BinaryFileResponse
    {
        return Excel::download(new FinesExport, 'my-fines-'.now()->format('Y-m-d').'.csv');
    }
}
