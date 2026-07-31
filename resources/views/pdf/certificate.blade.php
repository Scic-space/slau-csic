<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
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
            margin-bottom: 14px;
        }
        .title {
            font-size: 40px;
            color: #313894;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: bold;
            margin: 0;
            line-height: 1.2;
        }

        /* === DECORATIVE LINE === */
        .divider-container {
            text-align: center;
            margin: 12px 0 10px;
        }
        .divider-line-left {
            display: inline-block;
            width: 200px;
            height: 1.5px;
            background: #B01117;
            vertical-align: middle;
        }
        .divider-diamond {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #EED214;
            transform: rotate(45deg);
            vertical-align: middle;
            margin: 0 10px;
        }
        .divider-line-right {
            display: inline-block;
            width: 200px;
            height: 1.5px;
            background: #B01117;
            vertical-align: middle;
        }

        /* === BODY TEXT === */
        .body {
            text-align: center;
            padding: 0 100px;
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
            color: #B01117;
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
        .exam-name {
            font-size: 20px;
            color: #313894;
            font-weight: bold;
            margin: 4px 0;
            letter-spacing: 0.5px;
        }
        .motto {
            font-size: 9px;
            color: #B01117;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-style: italic;
            margin-top: 6px;
        }

        /* === SCORE & DATE === */
        .details {
            display: flex;
            justify-content: center;
            gap: 80px;
            margin: 18px 0 10px;
            text-align: center;
        }
        .detail-item .value {
            font-size: 20px;
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
            margin-top: 6px;
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
            font-size: 11px;
            color: #333;
            font-weight: bold;
        }
        .signature-role {
            font-size: 8px;
            color: #888;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* === CLUB LOGO === */
        .club-logo {
            position: absolute;
            bottom: 70px;
            right: 70px;
            z-index: 2;
            width: 110px;
            height: 110px;
            object-fit: contain;
        }

        /* === FOOTER === */
        .footer {
            position: absolute;
            bottom: 32px;
            left: 40px;
            right: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 0.5px solid #d4d4d4;
            padding-top: 8px;
        }
        .footer-cert-id {
            font-size: 8px;
            color: #999;
            letter-spacing: 1px;
        }
        .footer-qr {
            text-align: center;
        }
        .footer-qr svg {
            width: 45px;
            height: 45px;
        }
        .footer-qr-text {
            font-size: 6px;
            color: #999;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* === WATERMARK === */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            color: rgba(49, 56, 148, 0.03);
            font-weight: bold;
            letter-spacing: 8px;
            text-transform: uppercase;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
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
            <div class="title">Certificate of Completion</div>
        </div>

        <!-- Divider -->
        <div class="divider-container">
            <span class="divider-line-left"></span>
            <span class="divider-diamond"></span>
            <span class="divider-line-right"></span>
        </div>

        <!-- Body -->
        <div class="body">
            <div class="presented-to">This certificate is awarded to</div>
            <div class="recipient-name">{{ $user->name }}</div>
            <div class="name-underline"></div>
            <div class="description">In recognition of the successful completion of the examination and demonstration<br>of professional competency in</div>
            <div class="exam-name">{{ $exam->title }}</div>
            <div class="motto">Light Your Candle &mdash; Faith and Truth</div>
        </div>

        <!-- Score & Date -->
        <div class="details">
            <div class="detail-item">
                <div class="value">{{ $score }}%</div>
                <div class="label">Score Achieved</div>
            </div>
            <div class="detail-item">
                <div class="value">{{ $passedAt }}</div>
                <div class="label">Date of Issue</div>
            </div>
            <div class="detail-item">
                <div class="value">{{ $exam->passing_score }}%</div>
                <div class="label">Passing Standard</div>
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
