<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; }
        .receipt { max-width: 600px; margin: 0 auto; border: 2px solid #16a34a; border-radius: 8px; padding: 30px; }
        h1 { text-align: center; color: #16a34a; font-size: 22px; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 2px; }
        .subtitle { text-align: center; color: #64748b; font-size: 11px; margin-bottom: 20px; }
        .divider { border-top: 2px dashed #e2e8f0; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; }
        .label { color: #64748b; width: 120px; }
        .value { font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #94a3b8; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-recorded, .status-confirmed { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="receipt">
        <h1>Payment Receipt</h1>
        <div class="subtitle">SLAU CSIC Club</div>

        <div class="divider"></div>

        <table>
            <tr><td class="label">Receipt #</td><td class="value">{{ $payment->receipt_number ?? 'N/A' }}</td></tr>
            <tr><td class="label">Fine ID</td><td class="value">#{{ $fine->id }}</td></tr>
            <tr><td class="label">Member</td><td class="value">{{ $user->name }}</td></tr>
            <tr><td class="label">Amount</td><td class="value">UGX {{ number_format($payment->amount, 0) }}</td></tr>
            <tr><td class="label">Method</td><td class="value">{{ ucfirst($payment->payment_method) }}</td></tr>
            <tr><td class="label">Payment Date</td><td class="value">{{ $payment->payment_date->format('F j, Y') }}</td></tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">
                    <span class="status-badge status-{{ $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
            </tr>
            <tr><td class="label">Recorded By</td><td class="value">{{ $payment->recordedBy?->name ?? ($payment->submittedBy?->name ?? 'Self') }}</td></tr>
            @if ($payment->notes)
                <tr><td class="label">Notes</td><td class="value">{{ $payment->notes }}</td></tr>
            @endif
        </table>

        <div class="divider"></div>

        <table>
            <tr><td class="label">Fine Amount</td><td class="value">UGX {{ number_format($fine->amount, 0) }}</td></tr>
            <tr><td class="label">Total Paid</td><td class="value">UGX {{ number_format($fine->amount_paid, 0) }}</td></tr>
            <tr><td class="label">Remaining</td><td class="value">UGX {{ number_format($fine->balance, 0) }}</td></tr>
            <tr><td class="label">Fine Status</td><td class="value">{{ ucfirst(str_replace('_', ' ', $fine->status)) }}</td></tr>
        </table>

        <div class="footer">
            Generated on {{ now()->format('F j, Y \a\t g:i A') }} &bull; SLAU CSIC Club Management System
        </div>
    </div>
</body>
</html>
