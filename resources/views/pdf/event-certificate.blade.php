<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Attendance</title>
    <style>
        @page { margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; background: #f8fafc; }
        .certificate {
            width: 800px; height: 560px; margin: 40px auto; position: relative;
            background: white; border: 8px solid #1e40af; border-radius: 12px;
            box-shadow: 0 0 30px rgba(0,0,0,0.15); overflow: hidden;
        }
        .certificate::before {
            content: ''; position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px;
            border: 2px solid #93c5fd; border-radius: 6px; pointer-events: none;
        }
        .header { text-align: center; padding: 40px 40px 0; }
        .header h1 { font-size: 36px; color: #1e3a5f; margin: 0; letter-spacing: 2px; text-transform: uppercase; }
        .header .subtitle { font-size: 14px; color: #64748b; margin-top: 8px; letter-spacing: 4px; text-transform: uppercase; }
        .divider { width: 120px; height: 3px; background: #3b82f6; margin: 20px auto; border-radius: 2px; }
        .body-text { text-align: center; padding: 10px 60px; }
        .body-text .label { font-size: 18px; color: #475569; }
        .body-text .name { font-size: 42px; color: #1e3a5f; margin: 16px 0; font-weight: bold; }
        .body-text .description { font-size: 16px; color: #64748b; line-height: 1.6; }
        .body-text .event-title { font-size: 22px; color: #1e40af; font-weight: bold; margin: 12px 0; }
        .details { display: flex; justify-content: center; gap: 60px; padding: 20px 40px; text-align: center; }
        .details .item .value { font-size: 18px; color: #1e3a5f; font-weight: bold; }
        .details .item .label { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .footer { position: absolute; bottom: 30px; left: 40px; right: 40px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .footer .cert-id { font-size: 11px; color: #94a3b8; letter-spacing: 1px; }
        .badge { position: absolute; top: 50px; right: 50px; width: 80px; height: 80px; border: 3px solid #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #3b82f6; font-weight: bold; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="badge">&#x2713;</div>
        <div class="header">
            <h1>Certificate of Attendance</h1>
            <div class="subtitle">SLAU CSIC Event Recognition</div>
        </div>
        <div class="divider"></div>
        <div class="body-text">
            <div class="label">This certifies that</div>
            <div class="name">{{ $user->name }}</div>
            <div class="description">has attended the event</div>
            <div class="event-title">{{ $event->title }}</div>
        </div>
        <div class="details">
            <div class="item">
                <div class="value">{{ $event->start_date->format('M j, Y') }}</div>
                <div class="label">Date</div>
            </div>
            @if ($event->end_date && !$event->end_date->isSameDay($event->start_date))
                <div class="item">
                    <div class="value">{{ $event->end_date->format('M j, Y') }}</div>
                    <div class="label">End Date</div>
                </div>
            @endif
            <div class="item">
                <div class="value">{{ $registration->attended_at?->format('M j, Y g:i A') ?? $event->end_date?->format('M j, Y') }}</div>
                <div class="label">Attendance Verified</div>
            </div>
        </div>
        <div class="footer"><div class="cert-id">Certificate ID: {{ $certificateId }}</div></div>
    </div>
</body>
</html>
