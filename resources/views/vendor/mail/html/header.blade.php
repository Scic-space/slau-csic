@props(['url'])
@php
    $logoUrl = file_exists(public_path('images/club_logo.png'))
        ? url('images/club_logo.png')
        : null;
@endphp
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" class="logo" alt="SLAU Cybersecurity &amp; Innovations Club" style="width: auto; height: auto; max-height: 90px;">
            @else
                <span style="font-size: 20px; font-weight: bold; color: #4338ca;">{{ config('app.name') }}</span>
            @endif
            {!! $slot !!}
        </a>
    </td>
</tr>
