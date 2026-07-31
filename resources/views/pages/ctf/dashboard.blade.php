@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-500">My CTF Dashboard</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Your CTF Stats</h1>
    </section>

    {{-- Global stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Total Solves</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $globalStats['total_solves'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Total Points</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $globalStats['total_points'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Incorrect Attempts</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $globalStats['total_incorrect'] }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Competitions</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $globalStats['competitions_participated'] }}</p>
        </div>
    </div>

    {{-- Competition breakdown --}}
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Competition Progress</h2>

        @forelse ($competitionData as $data)
            @php
                $competition = $data['competition'];
                $pct = $data['total_challenges'] > 0 ? round(($data['solved_count'] / $data['total_challenges']) * 100) : 0;
            @endphp
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('ctf.competition', $competition) }}" class="text-base font-semibold text-gray-900 hover:text-emerald-600 dark:text-white dark:hover:text-emerald-400">{{ $competition->title }}</a>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $data['solved_count'] }}/{{ $data['total_challenges'] }} solved</span>
                            <span>{{ $data['total_points'] }} pts</span>
                            @if ($data['incorrect_count'] > 0)
                            <span class="text-red-500">{{ $data['incorrect_count'] }} incorrect</span>
                            @endif
                            @if ($competition->end_date && $competition->end_date->isPast())
                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Ended</span>
                            @elseif ($competition->start_date && $competition->start_date->isFuture())
                            <span class="rounded bg-blue-500/10 px-1.5 py-0.5 text-[10px] font-medium text-blue-600 dark:text-blue-400">Upcoming</span>
                            @else
                            <span class="rounded bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-medium text-emerald-600 dark:text-emerald-400">Active</span>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $pct }}%</p>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>

                {{-- Recent solves --}}
                @if ($data['recent_solves']->count() > 0)
                <div class="mt-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Recent Solves</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($data['recent_solves'] as $solve)
                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-2 py-1 text-[11px] font-medium text-emerald-700 dark:text-emerald-300">
                            {{ $solve->challenge?->title ?? 'Unknown' }}
                            <span class="text-emerald-500/70">+{{ $solve->points_awarded }}</span>
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Unsolved challenges --}}
                @if ($data['unsolved_challenges']->count() > 0)
                <div class="mt-3">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Unsolved ({{ $data['unsolved_challenges']->count() }})</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($data['unsolved_challenges']->take(10) as $chal)
                        <a href="{{ route('ctf.competition', $competition) }}#{{ $chal->slug }}"
                           class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-2 py-1 text-[11px] text-gray-600 hover:border-emerald-300 hover:text-emerald-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-emerald-700 dark:hover:text-emerald-400">
                            {{ $chal->title }}
                            <span class="text-gray-400 dark:text-gray-500">{{ $chal->points }}pts</span>
                        </a>
                        @endforeach
                        @if ($data['unsolved_challenges']->count() > 10)
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">+{{ $data['unsolved_challenges']->count() - 10 }} more</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        @empty
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-12 text-center dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-gray-600 dark:text-gray-400">No competitions found.</p>
            </div>
        @endforelse
    </div>

    {{-- Recent activity --}}
    @if ($recentActivity->count() > 0)
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
        <div class="space-y-2">
            @foreach ($recentActivity as $submission)
            <div class="flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-4 py-2.5 dark:border-gray-800 dark:bg-gray-900/60">
                <div class="flex items-center gap-3 min-w-0">
                    @if ($submission->is_correct)
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500">
                        <svg class="h-3.5 w-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    @else
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-500">
                        <svg class="h-3.5 w-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $submission->challenge?->title ?? 'Unknown challenge' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $submission->challenge?->competition?->title ?? '' }}
                            @if ($submission->is_correct)
                                <span class="text-emerald-600 dark:text-emerald-400">+{{ $submission->points_awarded }} pts</span>
                            @endif
                        </p>
                    </div>
                </div>
                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $submission->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
