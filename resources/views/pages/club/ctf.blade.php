@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] md:p-8">
            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500"><span class="material-symbols-outlined" aria-hidden="true">sports_esports</span> CTF Arena</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Practice harder, track progress, earn badges, and watch rankings move.</h1>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        This arena combines internal CTF competitions, challenge tracks, and connected Hack The Box profile data.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/60">
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">sync</span> Hack The Box Sync</h2>
                    @if (! empty($htbData))
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Username</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $htbData['username'] ?? auth()->user()->htb_username }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Profile completion</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $htbData['profile_completion'] ?? 0 }}%</span>
                            </div>
                            <p class="rounded-md border border-gray-200 bg-white px-3 py-3 leading-6 text-gray-600 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400">
                                {{ $htbData['summary'] ?: 'Summary not available on the connected public HTB profile.' }}
                            </p>
                        </div>
                    @else
                        <p class="mt-4 text-sm leading-7 text-gray-600 dark:text-gray-400">Connect a public HTB profile URL from your profile page to enable automatic sync when you open the portal.</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- Active CTF Competitions --}}
        @if (isset($activeCompetitions) && $activeCompetitions->count() > 0)
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="flex items-center gap-2 text-xl font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">emoji_events</span> Active Competitions</h2>
                <a href="{{ route('ctf.index') }}" class="text-sm text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">View All →</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($activeCompetitions as $comp)
                <a href="{{ route('ctf.competition', $comp) }}" class="rounded-lg border border-gray-200 bg-gray-50 p-4 hover:border-emerald-300 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-emerald-700 transition">
                    <h3 class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-[20px] text-emerald-500" aria-hidden="true">flag</span> {{ $comp->title }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $comp->challenges_count }} challenges</p>
                    @if ($comp->end_date)
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Ends {{ $comp->end_date->format('M d, H:i') }}</p>
                    @endif
                </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Stats summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between gap-2"><p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Active CTFs</p><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">terminal</span></div>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $activeCompetitions?->count() ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between gap-2"><p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">My Solves</p><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">task_alt</span></div>
                <p class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $userSolves ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between gap-2"><p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">My Points</p><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">stars</span></div>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $userPoints ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between gap-2"><p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Tracks</p><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">route</span></div>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $resources->count() }}</p>
            </div>
        </div>

        <section class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="flex items-center gap-2 text-2xl font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">military_tech</span> Badges and ranks</h2>
                <div class="mt-5 flex flex-wrap gap-3">
                    @forelse ($badges as $badge)
                        <span class="inline-flex items-center rounded-md bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-500">{{ $badge }}</span>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">Start completing challenge tracks to unlock internal badges.</p>
                    @endforelse
                </div>

                <div class="mt-8">
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">leaderboard</span> Internal leaderboard</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($leaderboard as $index => $entry)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/60">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-slate-900 text-xs font-semibold text-white dark:bg-emerald-500 dark:text-slate-950">{{ $index + 1 }}</span>
                                    <img src="{{ $entry['photo'] }}" alt="{{ $entry['name'] }}" class="h-10 w-10 rounded-md object-cover object-top">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $entry['name'] }}</p>
                                        <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ $entry['completed'] }} tracks completed</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $entry['score'] }} pts</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $entry['average_progress'] }}% avg progress</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600 dark:text-gray-400">No CTF leaderboard data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="flex items-center gap-2 text-2xl font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">account_tree</span> Challenge tracks</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($resources as $resource)
                        @php $progress = optional($resource->user_progress); @endphp
                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/60">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ $resource->platform }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->title }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $resource->summary }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $progress->progress_percentage ?? 0 }}%</p>
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ $progress->score ?? 0 }} pts</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3">
                                @if ($resource->external_url && $resource->external_url !== '#')
                                    <a href="{{ $resource->external_url }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-md bg-emerald-500 px-3 py-2 text-sm font-medium text-slate-950">{{ $resource->cta_label ?: 'Open track' }}</a>
                                @endif
                                <a href="{{ route('portal.competitions') }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Related competitions</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="flex items-center gap-2 text-2xl font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-emerald-500" aria-hidden="true">update</span> Track progress updates</h2>
                <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">Update your completion state, score, and ranking per challenge track so your dashboard, badges, and leaderboard standing stay current.</p>
            </div>

            @foreach ($resources as $resource)
                @php $progress = optional($resource->user_progress); @endphp
                <form method="POST" action="{{ route('portal.progress.update', $resource) }}" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    @csrf
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->title }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $resource->platform }} · {{ $resource->difficulty }}</p>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <select name="status" class="rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach (['not_started' => 'Not started', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $value => $label)
                                <option value="{{ $value }}" @selected(($progress->status ?? 'not_started') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="progress_percentage" min="0" max="100" value="{{ $progress->progress_percentage ?? 0 }}" placeholder="Progress %" class="rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <input type="number" name="completed_units" min="0" value="{{ $progress->completed_units ?? 0 }}" placeholder="Completed units" class="rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <input type="number" name="score" min="0" value="{{ $progress->score ?? 0 }}" placeholder="Score" class="rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <input type="text" name="ranking" value="{{ $progress->ranking }}" placeholder="Ranking" class="rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <textarea name="notes" rows="3" placeholder="Notes on what you solved, what blocked you, or what you learned." class="mt-4 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ $progress->notes }}</textarea>
                    <button type="submit" class="mt-4 inline-flex items-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-emerald-500 dark:text-slate-950 dark:hover:bg-emerald-400">Save CTF progress</button>
                </form>
            @endforeach
        </section>
    </div>
@endsection
