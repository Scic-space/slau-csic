@props(['url'])
@php
    $logoPath = public_path('images/club_logo.png');
    $logoData = file_exists($logoPath)
        ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
        : null;
@endphp
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if ($logoData)
                <img src="{{ $logoData }}" class="logo" alt="SLAU Cybersecurity &amp; Innovations Club" style="width: auto; height: auto; max-height: 90px;">
            @else
                <span style="font-size: 20px; font-weight: bold; color: #4338ca;">{{ config('app.name') }}</span>
            @endif
            {!! $slot !!}
        </a>
    </td>
</tr>
