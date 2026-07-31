<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\FineType;
use Illuminate\Http\Request;

class FinanceApiController extends Controller
{
    public function myFines(Request $request)
    {
        $fines = Fine::where('user_id', $request->user()->id)
            ->with('fineType')
            ->orderBy('issue_date', 'desc')
            ->get()
            ->map(fn ($fine) => [
                'id' => $fine->id,
                'type' => $fine->fineType?->name,
                'amount' => $fine->amount,
                'balance' => $fine->balance,
                'reason' => $fine->reason,
                'status' => $fine->status,
                'issue_date' => $fine->issue_date?->toDateString(),
                'due_date' => $fine->due_date?->toDateString(),
            ]);

        return response()->json(['data' => $fines]);
    }

    public function fineTypes()
    {
        $types = FineType::all()->map(fn ($ft) => [
            'id' => $ft->id,
            'name' => $ft->name,
            'description' => $ft->description,
            'amount' => $ft->default_amount,
        ]);

        return response()->json(['data' => $types]);
    }
}
