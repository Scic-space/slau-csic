<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membership Card | SLAU Cybersecurity Club</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            @page { margin: 0; size: landscape; }
        }
    </style>
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-gray-100 p-3 dark:bg-gray-900 sm:p-4">
    <div class="no-print mb-3 flex gap-2 sm:mb-4">
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 sm:px-6 sm:py-3 sm:text-sm">
            Print / Save as PDF
        </button>
        <a href="{{ auth()->user()?->hasRole(['admin', 'super-admin']) ? route('filament.admin.pages.dashboard') : route('dashboard') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover sm:px-6 sm:py-3 sm:text-sm">
            Back to Dashboard
        </a>
    </div>

    <div class="w-full max-w-sm sm:max-w-md">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-0.5 shadow-2xl">
            <div class="relative rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-5 sm:p-6">
                <div class="pointer-events-none absolute inset-0 opacity-10">
                    <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white"></div>
                    <div class="absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-white"></div>
                </div>

                {{-- Header --}}
                <div class="relative mb-3 flex items-center justify-between sm:mb-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <img src="{{ asset('images/university_logo.png') }}" alt="University" class="h-8 w-auto brightness-0 invert sm:h-10">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-200 sm:text-xs">SLAU Cybersecurity Club</p>
                            <h1 class="text-base font-bold text-white sm:text-lg">Membership Card</h1>
                        </div>
                    </div>
                    <div>
                        <img src="{{ asset('images/club/clublogo.png') }}" alt="SLAU CSIC" class="h-16 w-auto brightness-0 invert sm:h-20">
                    </div>
                </div>

                {{-- Photo + Info --}}
                <div class="relative mb-3 flex items-center gap-4 sm:mb-4 sm:gap-5">
                    <div class="shrink-0">
                        <img src="{{ $member->profile_photo_url }}" alt="{{ $member->name }}" class="h-16 w-16 rounded-xl border-2 border-white/30 object-cover shadow-lg sm:h-20 sm:w-20">
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-bold text-white truncate sm:text-lg">{{ $member->name }}</h2>
                        @if ($member->headline)
                            <p class="text-xs text-indigo-200 truncate sm:text-sm">{{ $member->headline }}</p>
                        @endif
                        <p class="text-[10px] text-indigo-300/80 truncate sm:text-xs">{{ $member->email }}</p>
                        <div class="mt-1.5 flex items-center gap-1.5 sm:mt-2 sm:gap-2">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide sm:px-3 sm:text-xs {{ ($member->membership_type === 'alumni' ? 'bg-amber-400 text-amber-900' : 'bg-emerald-400 text-emerald-900') }}">
                                {{ $memberTypeLabel }}
                            </span>
                            <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-medium text-white sm:px-3 sm:text-xs">
                                {{ $member->rank ?? 'Member' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Details Grid --}}
                <div class="relative grid grid-cols-2 gap-x-3 gap-y-2 rounded-xl bg-white/10 p-3 backdrop-blur-sm sm:gap-x-4 sm:gap-y-3 sm:p-4">
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-200 sm:text-xs">Member ID</p>
                        <p class="text-xs font-semibold text-white sm:text-sm">{{ $memberId }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-200 sm:text-xs">Reg Number</p>
                        <p class="text-xs font-semibold text-white sm:text-sm">{{ $member->registration_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-200 sm:text-xs">Member Since</p>
                        <p class="text-xs font-semibold text-white sm:text-sm">{{ $memberSince }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-200 sm:text-xs">Expires</p>
                        <p class="text-xs font-semibold text-white sm:text-sm">{{ $expiryFormatted }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-200 sm:text-xs">Program</p>
                        <p class="text-xs font-semibold text-white truncate sm:text-sm" title="{{ $fullProgram ?? $program ?? 'N/A' }}">{{ $program ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-200 sm:text-xs">Card Issued</p>
                        <p class="text-xs font-semibold text-white sm:text-sm">{{ $issueDate }}</p>
                    </div>
                </div>

                {{-- Badges + QR --}}
                <div class="relative mt-3 flex items-end justify-between sm:mt-4">
                    <div class="min-w-0 flex-1">
                        @if ($member->earnedBadges->isNotEmpty())
                            <p class="mb-1.5 text-[10px] font-medium uppercase tracking-wider text-indigo-200 sm:mb-2 sm:text-xs">Badges Earned</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($member->earnedBadges->take(4) as $badge)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-white/15 px-1.5 py-0.5 text-[10px] text-white sm:px-2 sm:py-1 sm:text-xs" title="{{ $badge->description }}">
                                        {{ $badge->icon }} {{ $badge->name }}
                                    </span>
                                @endforeach
                                @if ($member->earnedBadges->count() > 4)
                                    <span class="inline-flex items-center rounded-md bg-white/15 px-1.5 py-0.5 text-[10px] text-white sm:px-2 sm:py-1 sm:text-xs">
                                        +{{ $member->earnedBadges->count() - 4 }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="text-[10px] text-indigo-200 sm:text-xs">Earn badges by attending events and completing challenges</p>
                        @endif
                    </div>
                    @if ($qrCode)
                        <div class="ml-3 shrink-0 rounded-lg bg-white p-0.5 sm:ml-4 sm:p-1">
                            {!! $qrCode !!}
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="relative mt-4 border-t border-white/20 pt-3 sm:mt-5 sm:pt-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <img src="{{ asset('images/university_logo.png') }}" alt="University" class="h-4 w-auto brightness-0 invert opacity-60 sm:h-5">
                            <span class="text-[10px] text-indigo-300 sm:text-xs">SLAU University</span>
                        </div>
                        <p class="text-[10px] text-indigo-200 sm:text-xs">Valid while membership is active</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
