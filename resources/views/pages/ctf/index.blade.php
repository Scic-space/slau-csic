@extends('layouts.app')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03] md:p-8">
        <div class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
            <div class="space-y-4">
                <span class="inline-flex items-center rounded-md bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-500">
                    CTF Arena
                </span>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Capture The Flag</h1>
                <p class="max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                    Solve cybersecurity challenges across web exploitation, cryptography, forensics, binary exploitation, reverse engineering, OSINT, and more. Earn points, climb the scoreboard, and submit writeups.
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('ctf.dashboard') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        My Dashboard
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 xl:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                    <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Competitions</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $competitionCount }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                    <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Challenges</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalChallenges }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                    <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Solved</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $solvedCount }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                    <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Points</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $userTotalPoints }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Flash messages --}}
    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ session('status') }}</p>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @forelse ($competitionStats as $stat)
        @php $competition = $stat['competition']; @endphp
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-border dark:bg-white/[0.03]">
            <a href="{{ route('ctf.competition', $competition) }}" class="block p-6 md:p-8">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10">
                            <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2-2-2-2m0 6l2-2-2-2m2-4l2-2-2-2m-4 10l4 4 4-4m-8-8l4-4 4 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $competition->title }}</h2>
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                @if ($competition->start_date)
                                    <span>{{ $competition->start_date->format('M d, Y') }}</span>
                                @endif
                                @if ($competition->end_date)
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                    <span>{{ $competition->end_date->format('M d, Y') }}</span>
                                @endif
                            </div>
                            @if ($competition->description)
                                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">{{ Str::limit($competition->description, 200) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-4">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="rounded-lg border border-gray-200 bg-background px-4 py-2.5 dark:border-border dark:bg-background/60">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stat['total'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Challenges</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-background px-4 py-2.5 dark:border-border dark:bg-background/60">
                                <p class="text-lg font-bold {{ $stat['solved'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-white' }}">{{ $stat['solved'] }}/{{ $stat['total'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Solved</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-background px-4 py-2.5 dark:border-border dark:bg-background/60">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stat['points'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Points</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-gray-300 transition group-hover:translate-x-0.5 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </section>
    @empty
        <div class="rounded-xl border border-gray-200 bg-white p-16 text-center shadow-sm dark:border-border dark:bg-white/[0.03]">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2-2-2-2m0 6l2-2-2-2m2-4l2-2-2-2m-4 10l4 4 4-4m-8-8l4-4 4 4"/>
                </svg>
            </div>
            <p class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No active competitions</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Active CTF competitions will appear here. Check back soon.</p>
        </div>
    @endforelse
</div>
@endsection