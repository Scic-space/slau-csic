<?php

namespace App\Exports;

use App\Models\Fine;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinesExport implements FromQuery, WithHeadings, WithMapping
{
    public function query(): Builder
    {
        $user = auth()->user();

        return Fine::where('user_id', $user->id)
            ->with(['fineType', 'user'])
            ->orderBy('issue_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID', 'Type', 'Reason', 'Amount', 'Amount Paid', 'Balance',
            'Status', 'Issue Date', 'Due Date', 'Member',
        ];
    }

    public function map($fine): array
    {
        return [
            $fine->id,
            $fine->fineType?->name ?? 'General',
            $fine->reason,
            (float) $fine->amount,
            (float) $fine->amount_paid,
            (float) $fine->balance,
            $fine->status,
            $fine->issue_date?->format('Y-m-d'),
            $fine->due_date?->format('Y-m-d'),
            $fine->user?->name,
        ];
    }
}
