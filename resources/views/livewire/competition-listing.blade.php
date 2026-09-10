<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Competitions</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Browse, filter, and track our competitions — from CTFs to hackathons</p>
        </div>

        <div class="mb-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="w-full sm:w-48">
                        <select wire:model.live="type" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            @foreach ($competitionTypes as $t)
                                <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-56">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search competitions..." class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <input type="date" wire:model.live="dateFrom" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <span class="text-gray-400 dark:text-gray-500">—</span>
                    <div>
                        <input type="date" wire:model.live="dateTo" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if ($search || $dateFrom || $dateTo || $type)
                        <button wire:click="resetFilters" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">Clear filters</button>
                    @endif
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $competitions->total() }} competition{{ $competitions->total() !== 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>

        @if ($competitions->isEmpty())
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 5H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2z"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No competitions found</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filter criteria.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($competitions as $c)
                    <a href="{{ route('competitions.show', $c['id']) }}" wire:navigate class="group relative block rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-6 shadow-sm hover:shadow-md hover:border-indigo-500/30 hover:-translate-y-0.5 transition-all">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate">{{ $c['name'] }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full border border-indigo-500/20 bg-indigo-50 px-3 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-500/30 dark:text-indigo-300">{{ $c['type'] }}</span>
                        </div>
                        @if ($c['description'])
                            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $c['description'] }}</p>
                        @endif
                        <div class="mb-3 space-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ \Carbon\Carbon::parse($c['start_date'])->format('M j, Y') }}{{ $c['end_date'] ? ' — '.\Carbon\Carbon::parse($c['end_date'])->format('M j, Y') : '' }}</span>
                            </div>
                            @if ($c['location'])
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ $c['location'] }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                                {{ $c['participants_count'] }} participant{{ $c['participants_count'] !== 1 ? 's' : '' }}
                            </span>
                            @if ($c['club_ranking'])
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    Ranking: #{{ $c['club_ranking'] }}
                                </span>
                            @endif
                            @if ($c['status'] === 'Ongoing')
                                <span class="ml-auto inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">Ongoing</span>
                            @endif
                        </div>
                        @if ($c['achievements'])
                            <div class="mt-3 rounded-lg border border-amber-500/20 bg-amber-50 dark:bg-amber-900/10 px-3 py-1.5 text-xs text-amber-700 dark:text-amber-300">{{ $c['achievements'] }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $competitions->links() }}
            </div>
        @endif
    </div>
</div>
