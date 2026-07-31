<div class="py-6">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('polls.index') }}" wire:navigate class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Polls
        </a>

        @if ($successMessage)
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ $successMessage }}
            </div>
        @endif

        @if ($errorMessage)
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-500/10 dark:text-red-300">
                {{ $errorMessage }}
            </div>
        @endif

        @if ($poll)
            @php
                $isActive = $poll['is_active'];
                $isExpired = $poll['is_expired'];
                $hasVoted = $poll['has_voted'];
                $showResults = $hasVoted || $isExpired;
            @endphp

            <article class="rounded-xl border shadow-sm overflow-hidden
                @if ($isActive && !$hasVoted)
                    border-blue-200 dark:border-blue-800
                @else
                    border-gray-200 dark:border-gray-700
                @endif">

                @if ($isExpired)
                    <div class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700/50 px-6 py-2 sm:px-8">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            This poll has expired
                        </div>
                    </div>
                @endif

                <div class="px-6 py-6 sm:px-8
                    @if ($isActive && !$hasVoted)
                        bg-gradient-to-b from-blue-50/50 to-transparent dark:from-blue-900/10 dark:to-transparent
                    @else
                        bg-gray-50/50 dark:bg-gray-800/30
                    @endif">

                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($isActive && !$hasVoted)
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">Expired</span>
                        @endif

                        @if ($poll['allow_multiple'])
                            <span class="inline-flex items-center rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">Multiple Choice</span>
                        @endif
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl
                        @if ($isActive && !$hasVoted)
                            text-gray-900 dark:text-white
                        @else
                            text-gray-500 dark:text-gray-400
                        @endif">
                        {{ $poll['question'] }}
                    </h1>

                    @if ($poll['description'])
                        <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            {{ $poll['description'] }}
                        </p>
                    @endif

                    <div class="mt-3 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $poll['author'] }}
                        </span>
                        <span class="text-gray-300 dark:text-gray-600">·</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ \Carbon\Carbon::parse($poll['created_at'])->format('M j, Y') }}
                        </span>
                        @if ($poll['expires_at'])
                            <span class="text-gray-300 dark:text-gray-600">·</span>
                            <span class="inline-flex items-center gap-1.5 @if ($isExpired) text-gray-400 @endif">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Expires {{ \Carbon\Carbon::parse($poll['expires_at'])->format('M j, Y \a\t g:i A') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-6 sm:px-8">
                    @if ($showResults)
                        <div class="space-y-3">
                            @foreach ($poll['options'] as $option)
                                @php
                                    $percentage = $option['percentage'];
                                @endphp
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $option['label'] }}</span>
                                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $percentage }}%</span>
                                    </div>
                                    <div class="h-2.5 w-full rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500
                                            @if ($percentage >= 50)
                                                bg-emerald-500
                                            @elseif ($percentage >= 25)
                                                bg-blue-500
                                            @else
                                                bg-gray-400 dark:bg-gray-500
                                            @endif"
                                            style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $option['votes_count'] }} {{ Str::plural('vote', $option['votes_count']) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Total: {{ $poll['votes_count'] }} {{ Str::plural('vote', $poll['votes_count']) }}
                        </div>
                    @else
                        @if (auth()->check())
                            <form wire:submit="vote">
                                <div class="space-y-2">
                                    @foreach ($poll['options'] as $option)
                                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4 cursor-pointer transition-all hover:border-blue-300 dark:hover:border-blue-700 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-900/20">
                                            <input type="{{ $poll['allow_multiple'] ? 'checkbox' : 'radio' }}"
                                                name="poll_option"
                                                value="{{ $option['id'] }}"
                                                wire:model.live="selectedOptions"
                                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $option['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="mt-4">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        wire:loading.attr="disabled"
                                        wire:target="vote">
                                        <svg wire:loading.remove wire:target="vote" class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg wire:loading wire:target="vote" class="h-4 w-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                        Cast Vote
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                Please <a href="{{ route('auth.login') }}" wire:navigate class="text-blue-600 hover:text-blue-500 dark:text-blue-400 font-medium">sign in</a> to vote.
                            </p>
                        @endif
                    @endif
                </div>
            </article>
        @endif
    </div>
</div>
