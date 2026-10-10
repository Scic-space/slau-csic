<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Attendance</title>
    <style>
        @page {
            margin: 0;
            size: 1056px 750px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        .certificate {
            width: 1056px;
            height: 750px;
            position: relative;
            background: #ffffff;
            overflow: hidden;
        }

        /* === TOP ACCENT BAR === */
        .accent-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: #B01117;
        }
        .accent-bar-gold {
            position: absolute;
            top: 8px;
            left: 0;
            right: 0;
            height: 3px;
            background: #EED214;
        }

        /* === BORDER FRAME === */
        .border-outer {
            position: absolute;
            top: 20px; left: 20px; right: 20px; bottom: 20px;
            border: 2px solid #B01117;
        }
        .border-inner {
            position: absolute;
            top: 26px; left: 26px; right: 26px; bottom: 26px;
            border: 0.5px solid #d4a8a8;
        }

        /* === CORNER ORNAMENTS === */
        .corner {
            position: absolute;
            width: 50px;
            height: 50px;
            border-color: #313894;
            border-style: solid;
        }
        .corner-tl { top: 23px; left: 23px; border-width: 3px 0 0 3px; }
        .corner-tr { top: 23px; right: 23px; border-width: 3px 3px 0 0; }
        .corner-bl { bottom: 23px; left: 23px; border-width: 0 0 3px 3px; }
        .corner-br { bottom: 23px; right: 23px; border-width: 0 3px 3px 0; }

        /* === HEADER === */
        .header {
            position: relative;
            z-index: 1;
            text-align: center;
            padding-top: 60px;
        }
        .university-name {
            font-size: 13px;
            color: #313894;
            letter-spacing: 5px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .club-name {
            font-size: 10px;
            color: #B01117;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .title {
            font-size: 40px;
            color: #B01117;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .subtitle {
            font-size: 10px;
            color: #313894;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* === DIVIDER === */
        .divider {
            text-align: center;
            margin: 18px 0 10px;
        }
        .divider-line {
            display: inline-block;
            width: 220px;
            height: 1.5px;
            background: #EED214;
            vertical-align: middle;
        }
        .divider-diamond {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #B01117;
            margin: 0 10px;
            transform: rotate(45deg);
            vertical-align: middle;
        }

        /* === BODY === */
        .body {
            text-align: center;
            padding: 0 120px;
            position: relative;
            z-index: 1;
        }
        .presented-to {
            font-size: 11px;
            color: #888;
            letter-spacing: 5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .recipient-name {
            font-size: 46px;
            color: #313894;
            font-weight: bold;
            margin: 0 0 8px;
            line-height: 1.2;
        }
        .name-underline {
            width: 350px;
            height: 1.5px;
            background: #EED214;
            margin: 0 auto 12px;
        }
        .description {
            font-size: 13px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 4px;
        }
        .event-name {
            font-size: 22px;
            color: #B01117;
            font-weight: bold;
            margin: 6px 0 2px;
        }
        .motto {
            font-size: 9px;
            color: #B01117;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-style: italic;
            margin-top: 8px;
        }

        /* === DETAILS === */
        .details {
            display: flex;
            justify-content: center;
            gap: 70px;
            margin: 20px 0 10px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .detail-item .value {
            font-size: 18px;
            color: #313894;
            font-weight: bold;
        }
        .detail-item .label {
            font-size: 8px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 3px;
        }

        /* === SIGNATURES === */
        .signatures {
            display: flex;
            justify-content: space-between;
            padding: 0 120px;
            margin-top: 10px;
            position: relative;
            z-index: 1;
        }
        .signature-block {
            text-align: center;
            width: 220px;
        }
        .signature-line {
            width: 100%;
            height: 1px;
            background: #313894;
            margin-bottom: 6px;
        }
        .signature-name {
            font-size: 12px;
            color: #313894;
            font-weight: bold;
        }
        .signature-role {
            font-size: 8px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 2px;
        }

        /* === CLUB LOGO === */
        .club-logo {
            position: absolute;
            bottom: 60px;
            right: 60px;
            width: 90px;
            height: 90px;
            z-index: 1;
        }

        /* === FOOTER === */
        .footer {
            position: absolute;
            bottom: 30px;
            left: 40px;
            right: 40px;
            text-align: center;
            border-top: 0.5px solid #e5e5e5;
            padding-top: 8px;
            z-index: 1;
        }
        .footer-cert-id {
            font-size: 8px;
            color: #999;
            letter-spacing: 1px;
        }

        /* === WATERMARK === */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            color: rgba(49, 56, 148, 0.04);
            font-weight: bold;
            letter-spacing: 8px;
            text-transform: uppercase;
            white-space: nowrap;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="certificate">

        <!-- Top accent bar -->
        <div class="accent-bar"></div>
        <div class="accent-bar-gold"></div>

        <!-- Watermark -->
        <div class="watermark">SLAU CSIC</div>

        <!-- Border frames -->
        <div class="border-outer"></div>
        <div class="border-inner"></div>

        <!-- Corner ornaments -->
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <!-- Header -->
        <div class="header">
            <div class="university-name">St. Lawrence University</div>
            <div class="club-name">Cyber Security &amp; Innovations Club</div>
            <div class="title">Certificate of Attendance</div>
            <div class="subtitle">Event Recognition</div>
        </div>

        <!-- Divider -->
        <div class="divider">
            <span class="divider-line"></span>
            <span class="divider-diamond"></span>
            <span class="divider-line"></span>
        </div>

        <!-- Body -->
        <div class="body">
            <div class="presented-to">This certificate is proudly presented to</div>
            <div class="recipient-name">{{ $user->name }}</div>
            <div class="name-underline"></div>
            <div class="description">for attending and actively participating in</div>
            <div class="event-name">{{ $event->title }}</div>
            <div class="motto">Light Your Candle &mdash; Faith and Truth</div>
        </div>

        <!-- Details -->
        <div class="details">
            <div class="detail-item">
                <div class="value">{{ $event->start_date?->format('M j, Y') }}</div>
                <div class="label">Event Date</div>
            </div>
            <div class="detail-item">
                <div class="value">{{ $attendedAt }}</div>
                <div class="label">Attendance Verified</div>
            </div>
            <div class="detail-item">
                <div class="value">{{ $eventType }}</div>
                <div class="label">Event Type</div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">Mr. Mukibi Zakaria</div>
                <div class="signature-role">Club Patron</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">Nantume Joseline</div>
                <div class="signature-role">Club President</div>
            </div>
        </div>

        <!-- Club Logo -->
        <img src="{{ $clubLogo }}" alt="SLAU CSIC" class="club-logo">

        <!-- Footer -->
        <div class="footer">
            <div class="footer-cert-id">{{ $certificateId }}</div>
        </div>
    </div>
</body>
</html>
