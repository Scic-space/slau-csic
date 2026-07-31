<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fine Notice</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; }
        .notice { max-width: 600px; margin: 0 auto; border: 2px solid #dc2626; border-radius: 8px; padding: 30px; }
        h1 { text-align: center; color: #dc2626; font-size: 22px; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 2px; }
        .subtitle { text-align: center; color: #64748b; font-size: 11px; margin-bottom: 20px; }
        .divider { border-top: 2px dashed #e2e8f0; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; }
        .label { color: #64748b; width: 120px; }
        .value { font-weight: bold; }
        .amount { font-size: 24px; color: #dc2626; font-weight: bold; text-align: center; padding: 10px 0; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-partially_paid { background: #fef9c3; color: #854d0e; }
        .status-waived { background: #e2e8f0; color: #475569; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #94a3b8; }
        .reason { background: #f8fafc; border-left: 4px solid #dc2626; padding: 10px 14px; margin: 12px 0; border-radius: 4px; font-style: italic; color: #475569; }
    </style>
</head>
<body>
    <div class="notice">
        <h1>Fine Notice</h1>
        <div class="subtitle">SLAU CSIC Club</div>

        <div class="divider"></div>

        <table>
            <tr><td class="label">Fine #</td><td class="value">{{ $fine->id }}</td></tr>
            <tr><td class="label">Member</td><td class="value">{{ $user->name }}</td></tr>
            <tr><td class="label">Type</td><td class="value">{{ $fine->fineType?->name ?? 'General' }}</td></tr>
            <tr><td class="label">Issue Date</td><td class="value">{{ $fine->issue_date->format('F j, Y') }}</td></tr>
            <tr><td class="label">Due Date</td><td class="value">{{ $fine->due_date->format('F j, Y') }}</td></tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">
                    <span class="status-badge status-{{ $fine->status }}">
                        {{ ucfirst(str_replace('_', ' ', $fine->status)) }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="amount">UGX {{ number_format($fine->amount, 0) }}</div>

        @if ($fine->reason)
            <div class="reason">{{ $fine->reason }}</div>
        @endif

        <div class="divider"></div>

        <table>
            <tr><td class="label">Paid Amount</td><td class="value">UGX {{ number_format($fine->amount_paid, 0) }}</td></tr>
            <tr><td class="label">Balance Due</td><td class="value">UGX {{ number_format($fine->balance, 0) }}</td></tr>
            @if ($fine->issuedBy)
                <tr><td class="label">Issued By</td><td class="value">{{ $fine->issuedBy->name }}</td></tr>
            @endif
            @if ($fine->waivedBy)
                <tr><td class="label">Waived By</td><td class="value">{{ $fine->waivedBy->name }}</td></tr>
            @endif
            @if ($fine->waived_reason)
                <tr><td class="label">Waive Reason</td><td class="value">{{ $fine->waived_reason }}</td></tr>
            @endif
        </table>

        <div class="footer">
            Generated on {{ now()->format('F j, Y \a\t g:i A') }} &bull; SLAU CSIC Club Management System
        </div>
    </div>
</body>
</html>
