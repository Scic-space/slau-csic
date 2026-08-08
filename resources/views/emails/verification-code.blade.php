<x-mail::layout>
{{-- Header --}}
<x-slot:header>
    <x-mail::header :url="config('app.url')">
    </x-mail::header>
</x-slot:header>

{{-- Body --}}
# Hello {{ $name }}!

We received a request to verify the email address for your **{{ config('app.name') }}** account.

Use the code below to complete your registration and unlock access to club activities.

<x-mail::panel>
    <p style="font-family: 'Courier New', Courier, monospace; font-size: 34px; font-weight: 700; letter-spacing: 6px; text-align: center; color: #4338ca; margin: 8px 0 4px;">{{ $code }}</p>
    <p style="font-size: 12px; text-align: center; color: #71717a; margin: 0 0 4px;">Your 6-digit verification code</p>
</x-mail::panel>

This code expires in 15 minutes. If you did not create an account, you can safely ignore this email.

Best regards,<br>
**The {{ config('app.name') }} Team**

{{-- Footer --}}
<x-slot:footer>
    <x-mail::footer>
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </x-mail::footer>
</x-slot:footer>
</x-mail::layout>
