<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">ballot</span>Polls</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vote on active polls and see results</p>
        </div>

        @if ($polls->isEmpty())
            <div class="rounded-sm border border-gray-200 bg-white p-8 text-center shadow-sm sm:p-10 dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No polls available</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check back later for new polls.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($polls as $poll)
                    @php
                        $isActive = $poll['is_active'];
                        $hasVoted = $poll['has_voted'];
                    @endphp
                    <a href="{{ route('polls.show', $poll['slug']) }}" wire:navigate
                        class="dashboard-card group block rounded-sm border p-4 shadow-sm transition-all
                            @if (!$hasVoted && $isActive)
                                border-blue-200 bg-blue-50/60 ring-1 ring-blue-100 hover:border-blue-300 hover:shadow-md dark:border-blue-800 dark:bg-blue-900/10 dark:ring-blue-800/50 dark:hover:border-blue-700
                            @else
                                border-gray-200 bg-white hover:border-gray-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600
                            @endif">

                        <div class="flex items-start gap-4">
                            <div class="mt-2 flex shrink-0 flex-col items-center gap-1.5">
                                @if (!$hasVoted && $isActive)
                                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500 ring-4 ring-blue-100 dark:ring-blue-900/30"></span>
                                @else
                                    <span class="h-2.5 w-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                @endif
                                @if ($isActive)
                                    <span class="inline-block h-8 w-0.5 rounded-full bg-blue-200 dark:bg-blue-800"></span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    @if (!$hasVoted && $isActive)
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">New</span>
                                    @endif

                                    @if ($poll['allow_multiple'])
                                        <span class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">Multiple Choice</span>
                                    @endif

                                    @if ($poll['expires_at'])
                                        @if ($isActive)
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                Expires {{ \Carbon\Carbon::parse($poll['expires_at'])->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">Expired</span>
                                        @endif
                                    @endif
                                </div>

                                <h3 class="mt-2 text-base font-semibold tracking-tight
                                    @if (!$hasVoted && $isActive)
                                        text-blue-900 dark:text-blue-100
                                    @else
                                        text-gray-900 dark:text-white
                                    @endif">
                                    {{ $poll['question'] }}
                                </h3>

                                @if ($poll['description'])
                                    <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                        {{ $poll['description'] }}
                                    </p>
                                @endif

                                <div class="mt-3 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">list</span>
                                        {{ $poll['options_count'] }} {{ Str::plural('option', $poll['options_count']) }}
                                    </span>
                                    <span class="text-gray-300 dark:text-gray-600">·</span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $poll['votes_count'] }} {{ Str::plural('vote', $poll['votes_count']) }}
                                    </span>
                                </div>
                            </div>

                            <svg class="mt-2 h-5 w-5 shrink-0
                                @if (!$hasVoted && $isActive)
                                    text-blue-400 group-hover:text-blue-600 dark:group-hover:text-blue-300
                                @else
                                    text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300
                                @endif"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
