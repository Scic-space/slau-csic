<div class="py-4 sm:py-5">
    {{-- Hero --}}
    <section class="mb-4">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                <span class="material-symbols-outlined text-brand-500" aria-hidden="true">campaign</span>Club Announcements
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Stay in the loop — the latest news, events, and updates from SLAU&nbsp;CSIC
            </p>
        </div>
    </section>

    {{-- Announcement list --}}
    <section>
        @if ($announcements->isEmpty())
            <div class="rounded-sm border border-gray-200 bg-white p-8 text-center shadow-sm sm:p-10 dark:border-border dark:bg-card">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-white/5">
                    <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">No announcements yet</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check back later for updates from the club.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($announcements as $announcement)
                    @php
                        $isActive = $announcement['is_active'];
                        $isExpired = $announcement['is_expired'];
                        $isSeen = $announcement['is_seen'];
                    @endphp
                    <a href="{{ route('announcements.show', $announcement['slug']) }}" wire:navigate
                        class="dashboard-card group block rounded-sm border p-4 shadow-sm transition-all duration-200
                            @if (!$isSeen && $isActive)
                                border-indigo-200/60 bg-indigo-50/50 ring-1 ring-indigo-500/10 hover:border-indigo-300 hover:shadow-md dark:border-indigo-500/20 dark:bg-indigo-500/[0.04] dark:ring-indigo-500/10 dark:hover:border-indigo-500/30
                            @elseif ($isExpired)
                                border-gray-200/60 bg-gray-50/50 opacity-60 hover:opacity-80 hover:shadow-sm dark:border-white/[0.04] dark:bg-white/[0.01] dark:hover:border-white/[0.08]
                            @else
                                border-gray-200 bg-white hover:border-gray-300 hover:shadow-md dark:border-white/[0.06] dark:bg-white/[0.02] dark:hover:border-white/[0.1]
                            @endif">

                        <div class="flex items-start gap-4">
                            {{-- Status indicator --}}
                            <div class="mt-2 flex shrink-0 flex-col items-center gap-1.5">
                                @if (!$isSeen)
                                    <span class="h-2.5 w-2.5 rounded-full bg-indigo-500 ring-4 ring-indigo-100 dark:ring-indigo-500/20"></span>
                                @else
                                    <span class="h-2.5 w-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                @endif
                                @if ($isActive)
                                    <span class="inline-block h-8 w-0.5 rounded-full bg-indigo-200 dark:bg-indigo-500/20"></span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                {{-- Badges --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    @switch($announcement['type'])
                                        @case('urgent')
                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20">Urgent</span>
                                            @break
                                        @case('event')
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">Event</span>
                                            @break
                                        @case('meeting')
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/10 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20">Meeting</span>
                                            @break
                                        @case('achievement')
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20">Achievement</span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">General</span>
                                    @endswitch

                                    @if ($isExpired)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 ring-1 ring-inset ring-gray-500/10 dark:bg-white/5 dark:text-gray-500 dark:ring-white/10">Expired</span>
                                    @elseif (!$isSeen)
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/10 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20">New</span>
                                    @endif
                                </div>

                                {{-- Title --}}
                                <h3 class="mt-2 text-base font-semibold tracking-tight
                                    @if (!$isSeen && $isActive)
                                        text-gray-900 dark:text-white
                                    @elseif ($isExpired)
                                        text-gray-500 dark:text-gray-500
                                    @else
                                        text-gray-900 dark:text-white
                                    @endif">
                                    {{ $announcement['title'] }}
                                </h3>

                                {{-- Content preview --}}
                                <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed
                                    @if ($isExpired)
                                        text-gray-400 dark:text-gray-600
                                    @else
                                        text-gray-600 dark:text-gray-400
                                    @endif">
                                    {{ Str::limit(strip_tags($announcement['content']), 160) }}
                                </p>

                                {{-- Meta --}}
                                <div class="mt-3 flex items-center gap-3 text-xs
                                    @if ($isExpired)
                                        text-gray-400 dark:text-gray-600
                                    @else
                                        text-gray-500 dark:text-gray-500
                                    @endif">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ \Carbon\Carbon::parse($announcement['published_at'])->diffForHumans() }}
                                    </span>
                                    @if ($announcement['view_count'] > 0)
                                        <span class="text-gray-300 dark:text-gray-700">&middot;</span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            {{ $announcement['view_count'] }} {{ Str::plural('view', $announcement['view_count']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Arrow --}}
                            <svg class="mt-2 h-5 w-5 shrink-0 transition-colors
                                @if ($isExpired)
                                    text-gray-300 dark:text-gray-700
                                @else
                                    text-gray-300 group-hover:text-indigo-500 dark:text-gray-600 dark:group-hover:text-indigo-400
                                @endif"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
