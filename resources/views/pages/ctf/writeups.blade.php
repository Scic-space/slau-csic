@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500">CTF Writeups</p>
                <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $challenge->title }}</h1>
            </div>
            <a href="{{ route('ctf.competition', $competition) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                Back to Competition
            </a>
        </div>
    </section>

    <div class="space-y-4">
        @forelse ($writeups as $writeup)
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/10 text-sm font-semibold text-emerald-600">
                        {{ strtoupper(substr($writeup->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $writeup->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $writeup->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
            <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300">
                {!! nl2br(e($writeup->content)) !!}
            </div>
        </div>
        @empty
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-12 text-center dark:border-gray-800 dark:bg-gray-900/60">
            <p class="text-gray-600 dark:text-gray-400">No approved writeups yet for this challenge.</p>
        </div>
        @endforelse
    </div>

    <div class="text-center">
        <a href="{{ route('ctf.writeup', ['competition' => $competition, 'challenge' => $challenge]) }}"
           class="inline-flex items-center gap-1.5 rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">
            Submit Your Writeup
        </a>
    </div>
</div>
@endsection
