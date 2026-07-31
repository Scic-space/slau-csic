@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        @if ($flash = session('flash'))
            <div class="rounded-lg border p-4 shadow-sm {{ $flash['error'] ?? false ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20' }}">
                <p class="text-sm font-medium {{ $flash['error'] ?? false ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                    {{ $flash['error'] ?? $flash['success'] }}
                </p>
            </div>
        @endif

        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] md:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500">Internal Online Classes</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Access class links, schedules, registrations, and attendance-ready sessions.</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                Internal classes are now tied to real event records. Admin can manage dates, meeting links, registration, and attendance from the event management area while members access upcoming sessions here.
            </p>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Scheduled class events</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($classes as $class)
                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/60">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ ucfirst(str_replace('_', ' ', $class->type)) }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $class->title }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $class->description }}</p>
                                </div>
                                <div class="rounded-md border border-gray-200 bg-white px-3 py-3 text-sm dark:border-gray-700 dark:bg-white/[0.03]">
                                    <div class="text-gray-500 dark:text-gray-400">{{ $class->start_date->format('M d, Y') }}</div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $class->start_date->format('g:i A') }}</div>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ $class->location }}</span>
                                <span>{{ $class->registered_count }} registered</span>
                                <span>{{ $class->attended_count }} attended</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="{{ route('events.show', $class->slug) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">View details</a>
                                @if ($class->external_link)
                                    <a href="{{ $class->external_link }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-md bg-emerald-500 px-3 py-2 text-sm font-medium text-slate-950">Join link</a>
                                @endif
                                @auth
                                    @php $registration = $userRegistrations[$class->id] ?? null; @endphp
                                    @if ($registration && $registration->status === 'registered')
                                        <span class="inline-flex items-center rounded-md bg-emerald-100 px-3 py-2 text-sm font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Registered</span>
                                        <form method="POST" action="{{ route('events.unregister', $class->slug) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-md border border-red-300 px-3 py-2 text-sm font-medium text-red-700 dark:border-red-700 dark:text-red-300">Unregister</button>
                                        </form>
                                    @elseif ($registration && $registration->status === 'waitlist')
                                        <span class="inline-flex items-center rounded-md bg-amber-100 px-3 py-2 text-sm font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Waitlisted</span>
                                        <form method="POST" action="{{ route('events.unregister', $class->slug) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-md border border-red-300 px-3 py-2 text-sm font-medium text-red-700 dark:border-red-700 dark:text-red-300">Unregister</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('events.register', $class->slug) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white dark:bg-white dark:text-slate-900">Registration</button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">No online classes have been published yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Class resource links</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($resources as $resource)
                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/60">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ $resource->platform }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->title }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $resource->summary }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ optional($resource->user_progress)->progress_percentage ?? 0 }}%</p>
                                    <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">personal progress</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3">
                                @if ($resource->external_url && $resource->external_url !== '#')
                                    <a href="{{ $resource->external_url }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-md bg-emerald-500 px-3 py-2 text-sm font-medium text-slate-950">{{ $resource->cta_label ?: 'Open class' }}</a>
                                @endif
                                <form method="POST" action="{{ route('portal.progress.update', $resource) }}" class="flex flex-wrap items-center gap-3">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ optional($resource->user_progress)->status ?? 'in_progress' }}">
                                    <input type="hidden" name="progress_percentage" value="{{ optional($resource->user_progress)->progress_percentage ?? 0 }}">
                                    <input type="hidden" name="completed_units" value="{{ optional($resource->user_progress)->completed_units ?? 0 }}">
                                    <input type="hidden" name="score" value="{{ optional($resource->user_progress)->score ?? 0 }}">
                                    <input type="hidden" name="ranking" value="{{ optional($resource->user_progress)->ranking }}">
                                    <input type="hidden" name="notes" value="{{ optional($resource->user_progress)->notes }}">
                                    <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Keep tracked</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
