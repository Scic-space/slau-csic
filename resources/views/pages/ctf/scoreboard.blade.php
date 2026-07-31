@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-500">CTF Scoreboard</p>
                <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $competition->title }}</h1>
                @if ($competition->allow_teams)
                <p class="mt-1 text-xs text-indigo-600 dark:text-indigo-400">Team competition</p>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('ctf.scoreboard.export', $competition) }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/[0.04]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('ctf.competition', $competition) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                    Back
                </a>
            </div>
        </div>

        @if ($competition->allow_teams)
        <div class="mt-4 flex gap-4 text-xs text-gray-500 dark:text-gray-400">
            <a href="{{ route('ctf.scoreboard', $competition) }}" class="{{ !request()->has('view') ? 'font-semibold text-emerald-600' : '' }}">Teams</a>
            <a href="{{ route('ctf.scoreboard', ['competition' => $competition, 'view' => 'individual']) }}" class="{{ request()->get('view') === 'individual' ? 'font-semibold text-emerald-600' : '' }}">Individuals</a>
        </div>
        @endif
    </section>

    @livewire('ctf-scoreboard', ['competitionId' => $competition->id, 'viewMode' => request()->get('view', 'auto')])
</div>
@endsection
