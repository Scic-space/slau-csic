<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membership Pending Approval | SLAU Cybersecurity Club</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-gray-950 p-4 sm:p-6">

    <div class="w-full max-w-lg">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-0.5 shadow-2xl">
            <div class="relative rounded-2xl bg-gray-900 px-6 py-10 sm:px-10">

                {{-- Decorative glow --}}
                <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-2xl">
                    <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-purple-500/10 blur-3xl"></div>
                </div>

                <div class="relative">

                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ asset('images/university_logo.png') }}" alt="University" class="h-8 w-auto brightness-0 invert sm:h-9">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-300 sm:text-xs">SLAU Cybersecurity Club</p>
                                <h1 class="text-sm font-bold text-white sm:text-base">Membership Card</h1>
                            </div>
                        </div>
                        <img src="{{ asset('images/club/clublogo.png') }}" alt="SLAU CSIC" class="h-12 w-auto brightness-0 invert sm:h-14">
                    </div>

                    {{-- Icon + Status --}}
                    <div class="mt-8 flex flex-col items-center text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-400/15 ring-1 ring-amber-400/40 sm:h-20 sm:w-20">
                            <svg class="h-8 w-8 text-amber-400 sm:h-10 sm:w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="mt-4 inline-flex items-center rounded-full bg-amber-400/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-400 ring-1 ring-amber-400/40">
                            Pending Approval
                        </span>
                    </div>

                    {{-- Message --}}
                    <div class="relative mt-6 text-center">
                        <h2 class="text-xl font-bold tracking-tight text-white sm:text-2xl">Your membership is awaiting approval</h2>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-gray-400">
                            Thanks for signing up, {{ $user->name }}! An administrator is reviewing your
                            application and your membership card will unlock as soon as you are approved.
                        </p>
                    </div>

                    {{-- What happens next --}}
                    <div class="relative mt-7 space-y-3">
                        <div class="flex items-start gap-3 rounded-xl bg-white/5 p-4 ring-1 ring-white/10">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-bold text-indigo-300">1</span>
                            <p class="text-sm text-gray-300">An administrator reviews your membership application.</p>
                        </div>
                        <div class="flex items-start gap-3 rounded-xl bg-white/5 p-4 ring-1 ring-white/10">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-bold text-indigo-300">2</span>
                            <p class="text-sm text-gray-300">You'll receive a notification once your membership is approved.</p>
                        </div>
                        <div class="flex items-start gap-3 rounded-xl bg-white/5 p-4 ring-1 ring-white/10">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-bold text-indigo-300">3</span>
                            <p class="text-sm text-gray-300">Your digital membership card becomes available right away.</p>
                        </div>
                    </div>

                    {{-- Meta --}}
                    <div class="relative mt-7 flex items-center justify-between rounded-xl bg-white/5 px-4 py-3 text-xs ring-1 ring-white/10">
                        <span class="text-gray-500">Member since</span>
                        <span class="font-medium text-gray-300">{{ $memberSince }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="relative mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            Back to Dashboard
                        </a>
                    </div>

                    {{-- Footer --}}
                    <p class="relative mt-6 text-center text-[11px] leading-relaxed text-gray-600">
                        Having trouble? Contact the club administration for assistance.<br>
                        You'll be able to print your card once your membership is active.
                    </p>

                </div>
            </div>
        </div>

    </div>
</body>
</html>
