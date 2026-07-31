<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Statement</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { font-size: 20px; color: #1e293b; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 2px; }
        .header .subtitle { color: #64748b; font-size: 11px; }
        .header .date { color: #94a3b8; font-size: 10px; margin-top: 4px; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 14px; color: #334155; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; }
        td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .amount { font-weight: bold; }
        .total-row td { border-top: 2px solid #334155; font-weight: bold; padding-top: 8px; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-overdue { background: #fee2e2; color: #991b1b; }
        .badge-waived { background: #f1f5f9; color: #475569; }
        .summary-grid { display: flex; justify-content: space-between; margin: 16px 0; }
        .summary-item { text-align: center; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 6px; flex: 1; margin: 0 4px; }
        .summary-item .label { font-size: 10px; color: #64748b; }
        .summary-item .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .footer { text-align: center; margin-top: 24px; font-size: 9px; color: #94a3b8; }
        .member-info { margin-bottom: 16px; }
        .member-info td { border: none; padding: 2px 0; }
        .member-info .label { color: #64748b; width: 100px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Account Statement</h1>
        <div class="subtitle">SLAU CSIC Club &mdash; Financial Summary</div>
        <div class="date">Statement as of {{ now()->format('F j, Y') }}</div>
    </div>

    <table class="member-info">
        <tr><td class="label">Member</td><td><strong>{{ $user->name }}</strong></td></tr>
        <tr><td class="label">Email</td><td>{{ $user->email }}</td></tr>
        @if ($user->member_id)
            <tr><td class="label">Member ID</td><td>{{ $user->member_id }}</td></tr>
        @endif
    </table>

    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Fines</div>
            <div class="value" style="color: #dc2626;">UGX {{ number_format($totalFines, 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Paid</div>
            <div class="value" style="color: #16a34a;">UGX {{ number_format($totalPaid, 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Outstanding</div>
            <div class="value" style="color: {{ $outstanding > 0 ? '#dc2626' : '#16a34a' }};">UGX {{ number_format($outstanding, 0) }}</div>
        </div>
    </div>

    @if ($fines->isNotEmpty())
        <div class="section">
            <h2>Fines ({{ $fines->count() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fines as $fine)
                        <tr>
                            <td>#{{ $fine->id }}</td>
                            <td>{{ $fine->fineType?->name ?? 'General' }}</td>
                            <td>{{ $fine->issue_date->format('M j, Y') }}</td>
                            <td>{{ $fine->due_date->format('M j, Y') }}</td>
                            <td class="amount">UGX {{ number_format($fine->amount, 0) }}</td>
                            <td class="amount">UGX {{ number_format($fine->amount_paid, 0) }}</td>
                            <td class="amount">UGX {{ number_format($fine->balance, 0) }}</td>
                            <td>
                                <span class="badge badge-{{ $fine->isOverdue ? 'overdue' : $fine->status }}">
                                    {{ $fine->isOverdue ? 'Overdue' : ucfirst(str_replace('_', ' ', $fine->status)) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($payments->isNotEmpty())
        <div class="section">
            <h2>Payments ({{ $payments->count() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Fine ID</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date?->format('M j, Y') ?? $payment->created_at->format('M j, Y') }}</td>
                            <td>#{{ $payment->fine_id }}</td>
                            <td class="amount">UGX {{ number_format($payment->amount, 0) }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td>
                                <span class="badge badge-{{ $payment->status === 'confirmed' ? 'paid' : $payment->status }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>{{ $payment->recordedBy?->name ?? ($payment->submittedBy?->name ?? 'Self') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($transactions->isNotEmpty())
        <div class="section">
            <h2>Recent Transactions ({{ $transactions->count() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $t)
                        <tr>
                            <td>{{ $t->created_at->format('M j, Y') }}</td>
                            <td>{{ $t->category }}</td>
                            <td>{{ str($t->description)->limit(40) }}</td>
                            <td class="text-right amount {{ $t->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $t->type === 'income' ? '+' : '-' }}UGX {{ number_format($t->amount, 0) }}
                            </td>
                            <td>{{ ucfirst($t->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('F j, Y \a\t g:i A') }} &bull; SLAU CSIC Club Management System
    </div>
</body>
</html>
