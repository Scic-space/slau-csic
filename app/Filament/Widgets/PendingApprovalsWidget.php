<?php

namespace App\Filament\Widgets;

use App\Models\FineAppeal;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\Widget;

class PendingApprovalsWidget extends Widget
{
    protected string $view = 'filament.widgets.pending-approvals';

    protected static ?string $heading = 'Pending Approvals';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('approve_members')
            || $user->can('approve_expenditures')
            || $user->hasAnyRole(['admin', 'super-admin', 'president', 'treasurer']);
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['items' => collect()];
        }

        $items = collect();

        if ($user->can('approve_members')) {
            User::where('membership_status', 'pending')
                ->latest()
                ->limit(5)
                ->get()
                ->each(fn ($u) => $items->push([
                    'type' => 'Pending Member',
                    'details' => $u->name.' ('.$u->email.')',
                    'date' => $u->created_at,
                    'urgency' => 'medium',
                ]));
        }

        if ($user->can('approve_expenditures')) {
            Transaction::pending()
                ->latest()
                ->limit(5)
                ->get()
                ->each(fn ($t) => $items->push([
                    'type' => 'Transaction',
                    'details' => 'UGX '.number_format($t->amount, 0).' - '.$t->description,
                    'date' => $t->created_at,
                    'urgency' => $t->amount > 50000 ? 'high' : 'medium',
                ]));
        }

        if ($user->hasAnyRole(['admin', 'super-admin', 'president', 'head_discipline'])) {
            FineAppeal::where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get()
                ->each(fn ($a) => $items->push([
                    'type' => 'Fine Appeal',
                    'details' => 'Appeal by #'.$a->fine_id,
                    'date' => $a->created_at,
                    'urgency' => 'medium',
                ]));
        }

        return [
            'items' => $items->sortByDesc('date')->take(10),
        ];
    }
}
