@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500">CTF Writeup</p>
                <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $challenge->title }}</h1>
            </div>
            <a href="{{ route('ctf.competition', $competition) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-border dark:text-gray-200">
                Back to Competition
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
            <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</p>
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-white/[0.03]">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Submit a writeup explaining how you solved this challenge. Your writeup will be reviewed by an admin.
        </p>

        <form method="POST" action="{{ route('ctf.writeup.submit', ['competition' => $competition, 'challenge' => $challenge]) }}">
            @csrf
            <div class="mb-4">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Solution Explanation
                </label>
                <textarea
                    name="content"
                    id="content"
                    rows="12"
                    class="mt-1 block w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-border dark:bg-card"
                    placeholder="Explain your approach, tools used, and the steps you took to solve this challenge..."
                >{{ $existingWriteup?->content }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Minimum 100 characters. Markdown supported.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                    {{ $existingWriteup ? 'Update Writeup' : 'Submit Writeup' }}
                </button>
            </div>
        </form>

        @if ($existingWriteup)
        <div class="mt-6 rounded-lg border border-gray-200 bg-background p-4 dark:border-border dark:bg-background/40">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:
                <span class="inline-flex rounded-md px-2 py-1 text-xs font-semibold
                    @if($existingWriteup->status === 'approved') bg-green-100 text-green-700
                    @elseif($existingWriteup->status === 'rejected') bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ $existingWriteup->status }}
                </span>
            </p>
        </div>
        @endif
    </div>
</div>
@endsection