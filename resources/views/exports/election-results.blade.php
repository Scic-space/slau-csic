<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $election->title }} - Results</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 30px; }
        h1 { font-size: 22px; margin-bottom: 5px; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
        .winner { background: #e8f5e9; font-weight: bold; }
        .badge-winner { color: #2e7d32; font-size: 12px; }
        .summary { margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        .summary-item { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>{{ $election->title }}</h1>
    <div class="subtitle">
        {{ $election->position }} &mdash; {{ $election->starts_at?->format('M j, Y') }} to {{ $election->ends_at?->format('M j, Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Candidate</th>
                <th>Votes</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php $rank = 1; @endphp
            @foreach ($election->candidates->sortByDesc(fn ($c) => $c->votes->count()) as $candidate)
                @php $isWinner = $rank === 1 && $candidate->votes->count() > 0; @endphp
                <tr @class(['winner' => $isWinner])>
                    <td>{{ $rank }}</td>
                    <td>{{ $candidate->name }} @if ($isWinner)<span class="badge-winner">Winner</span>@endif</td>
                    <td>{{ $candidate->votes->count() }}</td>
                    <td>{{ $totalVotes > 0 ? round(($candidate->votes->count() / $totalVotes) * 100, 1) : 0 }}%</td>
                </tr>
                @php $rank++; @endphp
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-item"><strong>Total Votes:</strong> {{ $totalVotes }}</div>
        <div class="summary-item"><strong>Winner:</strong>
            {{ $election->winner?->name ?? 'N/A' }}
        </div>
    </div>
</body>
</html>
