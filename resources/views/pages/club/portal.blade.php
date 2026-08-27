@extends('layouts.app')

@php
    $categoryConfig = [
        'competition' => [
            'title' => 'Internal Competitions',
            'copy' => 'Red team drills, blue team exercises, and internal scoring events.',
            'route' => route('portal.competitions'),
        ],
        'voting' => [
            'title' => 'Cabinet Voting',
            'copy' => 'Election schedules, voting guidance, and cabinet access points.',
            'route' => route('portal.voting'),
        ],
        'ctf' => [
            'title' => 'CTF Arena',
            'copy' => 'Hack The Box, PicoCTF, and internal practice tracks with progress recording.',
            'route' => route('portal.ctf'),
        ],
        'class' => [
            'title' => 'Online Classes',
            'copy' => 'Internal learning links, structured sessions, and revision tracks.',
            'route' => route('portal.classes'),
        ],
    ];
@endphp

@section('content')
    <div class="space-y-6">
        @if($ongoingTeachingSession)
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => {
                    if (!sessionStorage.getItem('qrNotificationShown')) {
                        show = true;
                        sessionStorage.setItem('qrNotificationShown', 'true');
                    } else {
                        show = false;
                    }
                }, 500)"
                x-show="show"
                x-transition
                class="rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 p-6 text-white shadow-lg relative overflow-hidden"
            >
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiPjxwYXRoIGQ9Ik0wIDBoNjB2NjBIMHYwem0wIDBoNjB2NjBIMHYweiIgZmlsbD0ibm9uZSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idHJhbnNwYXJlbnQiLz48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuMDUiIGZvcm1jdHVyZT0icG9pbnRzKDAvNjAsMCA2MC82LCA2MC82LCAwLzYwKSIvPjwvc3ZnPg==')] opacity-30"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Teaching Session In Progress!</h3>
                            <p class="text-sm text-white/90">{{ $ongoingTeachingSession->title }}</p>
                            <p class="text-xs text-white/70 mt-1">{{ $ongoingTeachingSession->location }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-medium backdrop-blur-sm">
                            Scan QR Code to Check In
                        </span>
                        <p class="text-xs text-white/60">Ask your facilitator for the QR code</p>
                    </div>
                </div>
            </div>
        @endif

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03] md:p-8">
            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500">Club Portal</p>
                    <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">One member space for competitions, voting, labs, classes, and personal progress.</h1>
                    <p class="max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        This dashboard is the operational home for club members. From here, you can reach internal competitions, cabinet voting preparation, practice labs, and internal classes while keeping your activity visible on the portal.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('portal.ctf') }}" class="inline-flex items-center rounded-md bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Open CTF Arena</a>
                        <a href="{{ route('portal.classes') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-border dark:text-gray-200 dark:hover:bg-white/[0.04]">Open Class Links</a>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Active tracks</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $metrics['active_tracks'] }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Completed tracks</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $metrics['completed_tracks'] }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Average progress</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $metrics['average_progress'] }}%</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Portal points</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $metrics['club_points'] }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($categoryConfig as $category => $config)
                @php
                    $items = $resourcesByCategory->get($category, collect());
                    $inProgress = $items->filter(fn ($item) => optional($item->user_progress)->status === 'in_progress')->count();
                @endphp
                <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $config['title'] }}</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $config['copy'] }}</p>
                        </div>
                        <span class="rounded-md bg-emerald-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ $items->count() }}</span>
                    </div>
                    <div class="mt-5 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ $inProgress }} in progress</span>
                        <a href="{{ $config['route'] }}" class="font-medium text-emerald-500">Open</a>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03]">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Priority Access</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Next places to work from</h2>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach (['competition', 'voting', 'ctf', 'class'] as $category)
                        @php
                            $resource = $resourcesByCategory->get($category, collect())->first();
                        @endphp
                        @if ($resource)
                            <article class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/50">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ $resource->platform ?? strtoupper($category) }}</p>
                                        <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->title }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $resource->summary }}</p>
                                    </div>
                                    <div class="min-w-[120px] rounded-md border border-gray-200 bg-white px-3 py-3 text-right dark:border-border dark:bg-white/[0.03]">
                                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Progress</div>
                                        <div class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ optional($resource->user_progress)->progress_percentage ?? 0 }}%</div>
                                    </div>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a href="{{ $categoryConfig[$category]['route'] }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-border dark:text-gray-200">Open section</a>
                                    @if ($resource->external_url && $resource->external_url !== '#')
                                        <a href="{{ $resource->external_url }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-md bg-emerald-500 px-3 py-2 text-sm font-medium text-slate-950">{{ $resource->cta_label ?: 'Launch' }}</a>
                                    @endif
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03]">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Member Snapshot</p>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Events attended</div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $metrics['events_attended'] }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Competition entries</div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $metrics['competition_entries'] }}</div>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Useful extras</h3>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        <li>Use the profile page to keep your public member bio and achievements updated.</li>
                        <li>Track challenge work as you move through Hack The Box, PicoCTF, and internal labs.</li>
                        <li>Use the voting page as the central point for election dates, guidance, and cabinet access.</li>
                        <li>Use internal class links to move from event attendance into regular technical practice.</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
@endsection
