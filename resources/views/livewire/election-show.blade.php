<div class="py-6">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        {{-- Back link --}}
        <a href="{{ route('voting.index') }}" wire:navigate class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Elections
        </a>

        {{-- Header --}}
        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 md:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold uppercase tracking-widest text-emerald-500">{{ $election['position'] }}</p>
                        @php
                            $phaseLabels = [
                                'voting' => ['label' => 'Voting Live', 'color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
                                'nominations' => ['label' => 'Accepting Applications', 'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                                'upcoming' => ['label' => 'Upcoming', 'color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                                'results' => ['label' => 'Results Published', 'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
                                'ended' => ['label' => 'Ended', 'color' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'],
                            ];
                            $phaseInfo = $phaseLabels[$election['phase']] ?? ['label' => ucfirst($election['status']), 'color' => 'bg-gray-100 text-gray-600'];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $phaseInfo['color'] }}">{{ $phaseInfo['label'] }}</span>
                    </div>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $election['title'] }}</h1>
                    @if ($election['description'])
                        <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $election['description'] }}</p>
                    @endif
                </div>

                {{-- Timeline --}}
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/60">
                    @php
                        $steps = [
                            ['key' => 'nominations', 'label' => 'Applications'],
                            ['key' => 'upcoming', 'label' => 'Scheduled'],
                            ['key' => 'voting', 'label' => 'Voting'],
                            ['key' => 'results', 'label' => 'Results'],
                        ];
                        $phaseOrder = ['nominations' => 0, 'upcoming' => 1, 'voting' => 2, 'ended' => 2, 'results' => 3];
                        $currentIndex = $phaseOrder[$election['phase']] ?? 0;
                    @endphp
                    <div class="flex items-center gap-1.5 text-xs">
                        @foreach ($steps as $i => $step)
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $i <= $currentIndex ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-400 dark:bg-gray-700' }}">
                                    {{ $i < $currentIndex ? '✓' : $i + 1 }}
                                </div>
                                <span class="{{ $i <= $currentIndex ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">{{ $step['label'] }}</span>
                                @if ($i < count($steps) - 1)
                                    <div class="h-px w-4 {{ $i < $currentIndex ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Stats bar --}}
            <div class="mt-6 flex flex-wrap items-center gap-6 border-t border-gray-100 pt-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                @if ($election['starts_at'] || $election['ends_at'])
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        @if ($election['starts_at'] && $election['ends_at'])
                            {{ \Carbon\Carbon::parse($election['starts_at'])->format('M j') }} – {{ \Carbon\Carbon::parse($election['ends_at'])->format('M j, Y') }}
                        @elseif ($election['starts_at'])
                            Starts {{ \Carbon\Carbon::parse($election['starts_at'])->format('M j, Y') }}
                        @endif
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    {{ $election['total_votes'] }} vote{{ $election['total_votes'] !== 1 ? 's' : '' }} cast
                </span>
                <span class="inline-flex items-center gap-1.5">
                    Turnout: {{ $election['turnout'] }}%
                </span>
                @if ($election['user_has_voted'])
                    <span class="inline-flex items-center gap-1.5 font-medium text-emerald-600 dark:text-emerald-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        You voted for: {{ collect($election['candidates'])->firstWhere('id', $election['user_vote_candidate_id'])['name'] ?? 'Unknown' }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Receipt --}}
        @if ($receiptCode && $showReceipt && $election['user_has_voted'])
            <div class="mb-8 rounded-xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-800">
                                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h2 class="text-base font-semibold text-emerald-900 dark:text-emerald-200">Your Vote Receipt</h2>
                        </div>
                        <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">
                            Use this receipt code to verify your vote was counted:
                        </p>
                        <div class="mt-3 inline-flex items-center gap-3 rounded-lg border border-emerald-200 bg-white px-4 py-3 dark:border-emerald-700 dark:bg-gray-800/50">
                            <p class="font-mono text-2xl font-bold tracking-widest text-emerald-800 dark:text-emerald-100">{{ $receiptCode }}</p>
                            <button type="button" x-data x-clipboard="{{ $receiptCode }}" x-on:click="show = true; setTimeout(() => show = false, 2000)" class="relative text-emerald-500 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-200">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </button>
                        </div>
                        <a href="{{ route('voting.verify.form', ['code' => $receiptCode]) }}" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-600 hover:underline dark:text-emerald-300">
                            Verify your vote →
                        </a>
                    </div>
                    <button wire:click="dismissReceipt" class="shrink-0 rounded-md p-1 text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-800/50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Errors --}}
        @if ($errors->has('candidate') || $errors->has('vote'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $errors->first('candidate') ?: $errors->first('vote') }}</p>
            </div>
        @endif

        {{-- Winner Banner --}}
        @if ($election['winner'] && $election['results_visible'])
            <div class="mb-8 overflow-hidden rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-emerald-100 dark:border-emerald-800 dark:from-emerald-900/20 dark:to-emerald-900/10">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500 text-3xl text-white shadow-lg">
                            👑
                        </div>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Winner</p>
                            <h2 class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-100">{{ $election['winner']['name'] }}</h2>
                            <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
                                {{ $election['winner']['votes_count'] }} vote{{ $election['winner']['votes_count'] !== 1 ? 's' : '' }}
                                ({{ $election['total_votes'] > 0 ? round(($election['winner']['votes_count'] / $election['total_votes']) * 100) : 0 }}% of total)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Candidates --}}
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Candidates
                <span class="ml-1 text-sm font-normal text-gray-500 dark:text-gray-400">({{ count($election['candidates']) }})</span>
            </h2>
            @if ($election['is_open'] && !$election['user_has_voted'])
                <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Select a candidate below to cast your vote</p>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($election['candidates'] as $candidate)
                @php
                    $isSelected = ($selectedCandidates[$election['id']] ?? null) === $candidate['id'];
                    $isUserVote = $election['user_vote_candidate_id'] === $candidate['id'];
                @endphp
                <div wire:click="{{ $election['is_open'] && !$election['user_has_voted'] ? "selectCandidate({$election['id']}, {$candidate['id']})" : '' }}"
                    class="group relative rounded-xl border-2 p-5 transition-all
                        {{ $isSelected ? 'border-emerald-500 bg-emerald-50 shadow-md dark:border-emerald-500 dark:bg-emerald-900/10' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600' }}
                        {{ $election['is_open'] && !$election['user_has_voted'] ? 'cursor-pointer' : '' }}">

                    {{-- Selection indicator --}}
                    @if ($isSelected)
                        <div class="absolute -right-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-white shadow">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </div>
                    @endif

                    <div class="flex items-start gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-gray-200 dark:bg-gray-700 ring-2 {{ $isSelected ? 'ring-emerald-500' : 'ring-transparent' }} transition-all">
                            @if ($candidate['photo'])
                                <img src="{{ $candidate['photo'] }}" alt="{{ $candidate['name'] }}" class="h-full w-full object-cover object-top" />
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl font-bold text-gray-400">
                                    {{ strtoupper(substr($candidate['name'], 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $candidate['name'] }}</h3>
                                @if ($candidate['is_winner'] && $election['results_visible'])
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Winner</span>
                                @endif
                                @if ($isUserVote)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Your Vote</span>
                                @endif
                            </div>
                            @if ($candidate['manifesto'])
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400 line-clamp-3">{{ $candidate['manifesto'] }}</p>
                            @endif
                            @if ($candidate['agenda'])
                                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-400">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Agenda:</span> {{ Str::limit($candidate['agenda'], 150) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Results bar --}}
                    @if ($election['results_visible'] || !$election['is_open'])
                        @php
                            $sortedCandidates = collect($election['candidates'])->sortByDesc('votes_count');
                            $maxVotes = $sortedCandidates->max('votes_count') ?: 1;
                        @endphp
                        <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $candidate['votes_count'] }} vote{{ $candidate['votes_count'] !== 1 ? 's' : '' }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $candidate['percentage'] }}%</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full rounded-full transition-all duration-700 {{ $candidate['is_winner'] ? 'bg-emerald-500' : 'bg-blue-400' }}"
                                    style="width: {{ ($candidate['votes_count'] / $maxVotes) * 100 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Vote button --}}
        @if ($election['is_open'])
            <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        @if ($selectedCandidates[$election['id']] ?? null)
                            @php
                                $selectedName = collect($election['candidates'])->firstWhere('id', $selectedCandidates[$election['id']])['name'] ?? '';
                            @endphp
                            <p class="text-sm text-gray-600 dark:text-gray-400">You are about to vote for:</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $selectedName }}</p>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Select a candidate above, then cast your ballot.</p>
                        @endif
                    </div>
                    <button
                        wire:click="requestConfirm({{ $election['id'] }})"
                        wire:loading.attr="disabled"
                        {{ !($selectedCandidates[$election['id']] ?? null) ? 'disabled' : '' }}
                        class="inline-flex items-center rounded-xl bg-emerald-500 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50 transition-all">
                        <span wire:loading.remove wire:target="castVote">Cast Ballot</span>
                        <span wire:loading wire:target="castVote">Casting...</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Confirmation Drawer --}}
        <div x-data="{ open: @entangle('showConfirmModal') }" wire:key="vote-confirm-drawer">
            <x-ui.drawer show="open" on-close="$wire.cancelConfirm()" title="Confirm Your Vote" width="sm">
                <div class="flex-1 space-y-4 overflow-y-auto p-5">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        You are about to cast your ballot for <strong class="text-gray-900 dark:text-white">{{ $confirmCandidateName }}</strong> in the <strong class="text-gray-900 dark:text-white">{{ $election['title'] }}</strong> election.
                    </p>
                    <p class="text-xs text-amber-600 dark:text-amber-400">
                        @if ($election['allow_vote_changes'])
                            You can change your vote later if this election allows vote changes.
                        @else
                            This action cannot be undone. You will not be able to change your vote.
                        @endif
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 p-5 dark:border-gray-800">
                    <button wire:click="cancelConfirm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="castVote" wire:loading.attr="disabled" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50 transition-colors">
                        <span wire:loading.remove wire:target="castVote">Confirm Vote</span>
                        <span wire:loading wire:target="castVote">Casting...</span>
                    </button>
                </div>
            </x-ui.drawer>
        </div>
    </div>
</div>
