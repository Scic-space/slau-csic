<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query(): Builder
    {
        $user = auth()->user();

        return Transaction::where('created_by', $user->id)
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID', 'Type', 'Category', 'Amount', 'Description',
            'Status', 'Payment Method', 'Paid To/From', 'Date',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->type,
            $transaction->category,
            (float) $transaction->amount,
            $transaction->description,
            $transaction->status,
            $transaction->payment_method,
            $transaction->paid_to_from,
            $transaction->created_at->format('Y-m-d'),
        ];
    }
}
