@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03] md:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500">Cabinet Voting</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Vote for cabinet positions with real candidates, ballots, and results.</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                Each election below is position-based. Members can cast one ballot per election, review candidates, and see results where the election has been closed or results have been made visible.
            </p>
        </section>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="space-y-6">
            @forelse ($elections as $election)
                @php
                    $userVote = $election->votes->first();
                    $totalVotes = $election->candidates->sum(fn ($candidate) => $candidate->votes->count());
                @endphp
                <article class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03]">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ $election->position }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $election->title }}</h2>
                            @if ($election->description)
                                <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $election->description }}</p>
                            @endif
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-background px-4 py-4 text-right dark:border-border dark:bg-background/60">
                            <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Status</div>
                            <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ ucfirst($election->status) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 xl:grid-cols-2">
                        @foreach ($election->candidates as $candidate)
                            <div class="rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                                <div class="flex items-start gap-4">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-800">
                                        @if ($candidate->photo)
                                            <img src="{{ asset('storage/'.$candidate->photo) }}" alt="{{ $candidate->name }}" class="h-full w-full object-cover object-top">
                                        @elseif ($candidate->user?->avatar_url)
                                            <img src="{{ $candidate->user->avatar_url }}" alt="{{ $candidate->name }}" class="h-full w-full object-cover object-top">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $candidate->name }}</h3>
                                        @if ($candidate->manifesto)
                                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $candidate->manifesto }}</p>
                                        @endif
                                        @if ($candidate->agenda)
                                            <div class="mt-3 rounded-md border border-gray-200 bg-white px-3 py-3 text-sm text-gray-600 dark:border-border dark:bg-white/[0.03] dark:text-gray-400">
                                                {{ $candidate->agenda }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if (! $election->isOpen() || $election->results_visible)
                                    <div class="mt-4 flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-3 text-sm dark:border-border dark:bg-white/[0.03]">
                                        <span class="text-gray-500 dark:text-gray-400">Votes</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $candidate->votes->count() }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                        @if ($userVote)
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Vote recorded</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    Your pick: <span class="font-semibold text-gray-900 dark:text-white">{{ $userVote->candidate->name }}</span>
                                </span>
                            </div>
                        @elseif ($election->isOpen())
                            <span class="text-sm text-gray-500 dark:text-gray-400">Voting is open.</span>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">Voting is not currently open.</span>
                        @endif

                        @if ($election->isOpen() && ! $userVote)
                            <form method="POST" action="{{ route('portal.voting.cast', $election) }}" class="flex flex-wrap items-center gap-3">
                                @csrf
                                <select name="candidate_id" class="rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">
                                    <option value="">Select candidate</option>
                                    @foreach ($election->candidates as $candidate)
                                        <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center rounded-md bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-slate-950">Cast ballot</button>
                            </form>
                        @elseif (! $userVote && ($election->results_visible || $election->status === 'closed'))
                            <div class="rounded-md border border-gray-200 bg-background px-4 py-3 text-sm dark:border-border dark:bg-background/60">
                                Total votes cast: <span class="font-semibold text-gray-900 dark:text-white">{{ $totalVotes }}</span>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <article class="rounded-lg border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-border dark:bg-white/[0.03]">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">No elections are available yet.</h2>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Once cabinet elections are created from admin, candidates and ballots will appear here.</p>
                </article>
            @endforelse
        </div>
    </div>
@endsection
