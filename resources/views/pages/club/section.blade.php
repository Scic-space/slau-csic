@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03] md:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500">{{ strtoupper($category) }}</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $heading }}</h1>
                    <p class="mt-4 text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $intro }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-border dark:text-gray-200 dark:hover:bg-white/[0.04]">Back to portal</a>
                    @if ($category === 'ctf')
                        <a href="https://academy.hackthebox.com/" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-md bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-slate-950">Open HTB</a>
                    @endif
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            @foreach ($resources as $resource)
                @php
                    $progress = optional($resource->user_progress);
                @endphp
                <article class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03]">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="max-w-2xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ $resource->platform ?? 'Club' }}</span>
                                @if ($resource->difficulty)
                                    <span class="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:border-border dark:text-gray-400">{{ $resource->difficulty }}</span>
                                @endif
                                <span class="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:border-border dark:text-gray-400">{{ ucfirst($resource->status) }}</span>
                            </div>
                            <h2 class="mt-4 text-2xl font-semibold text-gray-900 dark:text-white">{{ $resource->title }}</h2>
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $resource->summary }}</p>
                        </div>

                        <div class="min-w-[140px] rounded-lg border border-gray-200 bg-background px-4 py-4 text-right dark:border-border dark:bg-background/60">
                            <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Progress</div>
                            <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $progress->progress_percentage ?? 0 }}%</div>
                        </div>
                    </div>

                    @if ($resource->details)
                        <div class="mt-5 rounded-lg border border-gray-200 bg-background px-4 py-4 text-sm leading-7 text-gray-600 dark:border-border dark:bg-background/60 dark:text-gray-400">
                            {{ $resource->details }}
                        </div>
                    @endif

                    <div class="mt-5 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 bg-background px-4 py-4 dark:border-border dark:bg-background/60">
                            <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Target units</div>
                            <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->target_total }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-background px-4 py-4 dark:border-border dark:bg-background/60">
                            <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Points</div>
                            <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->points }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-background px-4 py-4 dark:border-border dark:bg-background/60">
                            <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Ranking</div>
                            <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $progress->ranking ?: 'Not set' }}</div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('portal.resources.show', $resource) }}" class="inline-flex items-center rounded-md bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-emerald-400 transition">{{ $resource->cta_label ?: 'Open Resource' }}</a>
                    </div>

                    <form method="POST" action="{{ route('portal.progress.update', $resource) }}" class="mt-6 space-y-4 rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/60">
                        @csrf
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300" for="status-{{ $resource->id }}">Status</label>
                                <select id="status-{{ $resource->id }}" name="status" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">
                                    @foreach (['not_started' => 'Not started', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $value => $label)
                                        <option value="{{ $value }}" @selected(($progress->status ?? 'not_started') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300" for="progress-{{ $resource->id }}">Progress %</label>
                                <input id="progress-{{ $resource->id }}" type="number" name="progress_percentage" min="0" max="100" value="{{ $progress->progress_percentage ?? 0 }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300" for="units-{{ $resource->id }}">Completed units</label>
                                <input id="units-{{ $resource->id }}" type="number" name="completed_units" min="0" value="{{ $progress->completed_units ?? 0 }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300" for="score-{{ $resource->id }}">Score</label>
                                <input id="score-{{ $resource->id }}" type="number" name="score" min="0" value="{{ $progress->score ?? 0 }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300" for="ranking-{{ $resource->id }}">Ranking</label>
                                <input id="ranking-{{ $resource->id }}" type="text" name="ranking" value="{{ $progress->ranking }}" placeholder="Top 10, quarter finalist..." class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300" for="notes-{{ $resource->id }}">Notes</label>
                                <textarea id="notes-{{ $resource->id }}" name="notes" rows="3" placeholder="What you solved, what blocked you, or what comes next." class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-border dark:bg-card dark:text-white">{{ $progress->notes }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-emerald-500 dark:text-slate-950 dark:hover:bg-emerald-400">
                            Save progress
                        </button>
                    </form>
                </article>
            @endforeach
        </div>
    </div>
@endsection
