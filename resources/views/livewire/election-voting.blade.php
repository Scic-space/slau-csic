<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 md:p-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-500">Cabinet Voting</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Elections Dashboard
            </h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                View all cabinet elections, cast your vote, track nominations, and review results.
                Each election is position-based with one ballot per member.
            </p>
        </div>

        {{-- Stats --}}
        <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                <div class="mt-1 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Elections</div>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['active'] }}</div>
                <div class="mt-1 text-xs font-medium uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Active Now</div>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['upcoming'] }}</div>
                <div class="mt-1 text-xs font-medium uppercase tracking-wider text-blue-600 dark:text-blue-400">Upcoming</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['user_votes_cast'] }}</div>
                <div class="mt-1 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Your Votes Cast</div>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="mb-6 flex flex-wrap items-center gap-2">
            @foreach ([
                'all' => 'All Elections',
                'active' => 'Active',
                'upcoming' => 'Upcoming',
                'past' => 'Past',
            ] as $key => $label)
                <button wire:click="setFilter('{{ $key }}')"
                    class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $filter === $key ? 'bg-brand-500 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Election Cards --}}
        @if ($elections->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">No elections found</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    @if ($filter !== 'all')
                        No {{ strtolower($filter) }} elections at the moment.
                    @else
                        Cabinet elections will appear here once created by the administration.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($elections as $election)
                    <a href="{{ route('voting.show', $election['slug']) }}" wire:navigate
                        class="group block rounded-xl border border-gray-200 bg-white p-6 transition-all hover:border-emerald-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-emerald-600">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-500">{{ $election['position'] }}</p>
                                    @php
                                        $phaseColors = [
                                            'voting' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'nominations' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'upcoming' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'results' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                            'ended' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $phaseColors[$election['phase']] ?? '' }}">
                                        {{ $election['status_label'] }}
                                    </span>
                                    @if ($election['user_has_voted'])
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            ✓ Voted
                                        </span>
                                    @endif
                                </div>
                                <h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    {{ $election['title'] }}
                                </h2>
                                @if ($election['description'])
                                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $election['description'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-2 text-right">
                                {{-- Timeline --}}
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if ($election['starts_at'])
                                        <span>{{ \Carbon\Carbon::parse($election['starts_at'])->format('M j') }}</span>
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    @endif
                                    @if ($election['ends_at'])
                                        <span>{{ \Carbon\Carbon::parse($election['ends_at'])->format('M j, Y') }}</span>
                                    @endif
                                </div>

                                {{-- Countdown --}}
                                @if ($election['is_open'] && $election['ends_at'])
                                    @php
                                        $endsAt = \Carbon\Carbon::parse($election['ends_at']);
                                        $now = now();
                                        $diff = $now->diff($endsAt);
                                        $isUrgent = $now->diffInHours($endsAt) < 24;
                                    @endphp
                                    <div class="inline-flex items-center gap-1 rounded-lg {{ $isUrgent ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }} px-3 py-1.5 text-xs font-semibold">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @if ($diff->days > 0)
                                            {{ $diff->days }}d {{ $diff->h }}h left
                                        @elseif ($diff->h > 0)
                                            {{ $diff->h }}h {{ $diff->i }}m left
                                        @else
                                            {{ $diff->i }}m left
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Bottom bar: stats + actions --}}
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    {{ $election['candidates_count'] }} candidate{{ $election['candidates_count'] !== 1 ? 's' : '' }}
                                </span>
                                @if ($election['results_visible'] || !$election['is_open'])
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $election['total_votes'] }} vote{{ $election['total_votes'] !== 1 ? 's' : '' }} cast
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        {{ $election['turnout'] }}% turnout
                                    </span>
                                @endif
                                @if ($election['user_has_voted'])
                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                        Your pick: {{ $election['user_vote_candidate'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($election['is_open'] && !$election['user_has_voted'])
                                    <span class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm group-hover:bg-emerald-600 transition-colors">
                                        Cast Your Vote
                                    </span>
                                @elseif ($election['is_open'] && $election['allow_vote_changes'])
                                    <span class="inline-flex items-center rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-600 dark:border-emerald-700 dark:text-emerald-400">
                                        Update Vote
                                    </span>
                                @elseif ($election['results_visible'])
                                    <span class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-400">
                                        View Results
                                    </span>
                                @endif
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
