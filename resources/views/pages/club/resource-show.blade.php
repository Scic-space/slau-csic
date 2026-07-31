@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] md:p-8">
            <div class="mb-6">
                <a href="{{ $categoryConfig['back'] }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ $categoryConfig['back_label'] }}
                </a>
            </div>

            <div class="mb-6">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="rounded-md bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-500">{{ $resource->platform ?? $categoryConfig['heading'] }}</span>
                    @if ($resource->difficulty)
                        <span class="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ $resource->difficulty }}</span>
                    @endif
                    <span class="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ ucfirst($resource->status) }}</span>
                </div>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $resource->title }}</h1>
                <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $resource->summary }}</p>
            </div>

            @if ($resource->details)
                <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 text-sm leading-7 text-gray-600 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-400 whitespace-pre-line">
                    {{ $resource->details }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/60">
                    <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Target units</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->target_total }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/60">
                    <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Points</div>
                    <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->points }}</div>
                </div>
                @if ($resource->starts_at)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Start date</div>
                        <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->starts_at->format('M j, Y') }}</div>
                    </div>
                @endif
                @if ($resource->ends_at)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/60">
                        <div class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">End date</div>
                        <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $resource->ends_at->format('M j, Y') }}</div>
                    </div>
                @endif
            </div>

            @if ($resource->external_url)
                <div class="mt-6">
                    <a href="{{ $resource->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        {{ $resource->cta_label ?: 'Open external resource' }}
                    </a>
                </div>
            @endif
        </section>

        <livewire:club-resource-progress :resource="$resource" :key="'progress-'.$resource->id" />
    </div>
@endsection