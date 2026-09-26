<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $event->title }} — Description</title>
    <style>
        @page { margin: 32pt 36pt; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.4; }
        .header { border-bottom: 1pt solid #cbd5e1; padding-bottom: 10pt; margin-bottom: 16pt; }
        .header h1 { font-size: 19pt; line-height: 1.2; margin: 0 0 6pt; }
        .metadata { font-size: 9pt; color: #475569; margin: 3pt 0; }
        h1, h2, h3, h4, h5, h6 { line-height: 1.3; margin: 13pt 0 6pt; page-break-after: avoid; }
        h1 { font-size: 17pt; }
        h2 { font-size: 14pt; }
        h3 { font-size: 12pt; }
        h4, h5, h6 { font-size: 10pt; }
        p, ul, ol, blockquote, pre, table, figure { margin: 0 0 8pt; }
        ul, ol { padding-left: 21pt; }
        li { margin-bottom: 3pt; }
        blockquote { border-left: 2pt solid #cbd5e1; padding-left: 10pt; color: #475569; }
        a { color: #1d4ed8; text-decoration: underline; overflow-wrap: break-word; }
        pre, code { font-family: 'DejaVu Sans Mono', monospace; font-size: 8pt; }
        pre { white-space: pre-wrap; overflow-wrap: break-word; padding: 8pt; background: #f1f5f9; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 0.5pt solid #cbd5e1; padding: 5pt; text-align: left; vertical-align: top; overflow-wrap: break-word; }
        th { background: #f1f5f9; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        hr { border: 0; border-top: 0.5pt solid #cbd5e1; margin: 12pt 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $event->title }}</h1>
        <p class="metadata">Lesson description</p>
        @if ($event->start_date)
            <p class="metadata">{{ $event->start_date->format('F j, Y · g:i A') }}</p>
        @endif
        @if ($event->location)
            <p class="metadata">{{ $event->location }}</p>
        @endif
    </div>

    <div class="description">{!! $descriptionHtml !!}</div>
</body>
</html>
