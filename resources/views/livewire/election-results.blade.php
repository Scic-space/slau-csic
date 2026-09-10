<div class="py-4 sm:py-5">
    <div>
        {{-- Header --}}
        <div class="mb-4">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-500">Election Results</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                <span class="material-symbols-outlined text-brand-500" aria-hidden="true">poll</span>Past Election Outcomes
            </h1>
            <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
                Transparent results for all completed elections. Results are displayed once an election
                has been closed and published by the administration.
            </p>
        </div>

        {{-- Filters --}}
        <div class="mb-3 flex flex-wrap items-center gap-2">
            @foreach ([
                'all' => 'All Results',
                'published' => 'Published',
                'closed' => 'Awaiting Publication',
            ] as $key => $label)
                <button wire:click="setFilter('{{ $key }}')"
                    class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $filter === $key ? 'bg-brand-500 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-400 dark:hover:bg-card-hover' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($elections->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-border dark:bg-card">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">No results available</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Results will appear here once elections are closed and published.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($elections as $election)
                    <div class="rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-card overflow-hidden">
                        {{-- Election Header --}}
                        <div class="border-b border-gray-100 p-6 dark:border-border">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-500">{{ $election['position'] }}</p>
                                    <h2 class="mt-2 flex items-center gap-2 text-2xl font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">leaderboard</span>{{ $election['title'] }}</h2>
                                    @if ($election['ends_at'])
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Ended {{ \Carbon\Carbon::parse($election['ends_at'])->format('F j, Y') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 text-right">
                                    <div>
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $election['total_votes'] }}</div>
                                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Votes</div>
                                    </div>
                                    <div>
                                        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $election['turnout'] }}%</div>
                                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Turnout</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Winner Banner --}}
                        @if ($election['winner'] && $election['results_visible'])
                            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 px-6 py-4 dark:from-emerald-900/20 dark:to-emerald-900/10">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">👑</span>
                                    <div>
                                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                                            Winner: {{ $election['winner']['name'] }}
                                        </p>
                                        <p class="text-xs text-emerald-600 dark:text-emerald-500">
                                            {{ $election['winner']['votes_count'] }} votes ({{ $election['winner']['percentage'] }}% of total)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Candidates --}}
                        @if ($election['results_visible'])
                            <div class="p-6">
                                <div class="space-y-4">
                                    @foreach ($election['candidates'] as $candidate)
                                        @php
                                            $maxVotes = $election['candidates']->max('votes_count') ?: 1;
                                        @endphp
                                        <div class="group">
                                            <div class="flex items-center gap-4">
                                                {{-- Photo --}}
                                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                    @if ($candidate['photo'])
                                                        <img src="{{ $candidate['photo'] }}" alt="{{ $candidate['name'] }}" class="h-full w-full object-cover" />
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center text-sm font-bold text-gray-400">
                                                            {{ strtoupper(substr($candidate['name'], 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Name + bar --}}
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $candidate['name'] }}</span>
                                                            @if ($candidate['is_winner'])
                                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Winner</span>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center gap-2 text-sm">
                                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $candidate['votes_count'] }}</span>
                                                            <span class="text-gray-500 dark:text-gray-400">({{ $candidate['percentage'] }}%)</span>
                                                        </div>
                                                    </div>
                                                    <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                                        <div class="h-full rounded-full transition-all duration-700 ease-out {{ $candidate['is_winner'] ? 'bg-emerald-500' : 'bg-gray-400 dark:bg-gray-500' }}"
                                                            style="width: {{ ($candidate['votes_count'] / $maxVotes) * 100 }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center">
                                <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-background px-4 py-3 text-sm text-gray-500 dark:border-border dark:bg-background/60 dark:text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    Results have not been published yet.
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
