<div class="py-4 sm:py-5">
    <div>

        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Resource Library</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Browse and track your learning resources</p>
        </div>

        {{-- Stats Cards --}}
        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400" aria-hidden="true">library_books</span>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Resources</p>
                    </div>
                </div>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30">
                        <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400" aria-hidden="true">pending_actions</span>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['in_progress'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
                    </div>
                </div>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                        <span class="material-symbols-outlined text-green-600 dark:text-green-400" aria-hidden="true">task_alt</span>
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['completed'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search & Filters --}}
        <div class="mb-3">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[18rem_minmax(12rem,1fr)_minmax(12rem,1fr)_minmax(12rem,1fr)]">
                <div class="relative min-w-0">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true">search</span>
                    <input type="search" wire:model.live.debounce.250ms="search" placeholder="Search resources..."
                        aria-label="Search resources"
                        class="block h-11 w-full rounded-sm border border-gray-300 bg-white py-2.5 pl-10 pr-11 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 hover:border-gray-400 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:hover:border-gray-500">
                    @if ($search || $category || $difficulty || $status)
                        <button type="button" wire:click="$set('search', ''); $set('category', ''); $set('difficulty', ''); $set('status', '');" class="absolute right-1 top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-sm text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:bg-gray-100 focus:text-gray-700 dark:hover:bg-gray-600 dark:hover:text-white" aria-label="Clear search and filters">
                            <span class="material-symbols-outlined" aria-hidden="true">filter_alt_off</span>
                        </button>
                    @endif
                </div>

                @foreach ([
                    ['category', 'category', 'Filter by category', 'All Categories', $categories],
                    ['difficulty', 'speed', 'Filter by difficulty', 'All Difficulties', $difficulties],
                    ['status', 'check_circle', 'Filter by status', 'All Statuses', $statuses],
                ] as [$model, $icon, $ariaLabel, $defaultLabel, $options])
                    <div class="group relative min-w-0">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-gray-400 transition group-hover:text-brand-500" aria-hidden="true">{{ $icon }}</span>
                        <select wire:model.live="{{ $model }}" aria-label="{{ $ariaLabel }}"
                            class="block h-11 w-full appearance-none rounded-sm border border-gray-300 bg-white py-2.5 pl-10 pr-10 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                            <option value="">{{ $defaultLabel }}</option>
                            @foreach ($options as $option)
                                <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true">expand_more</span>
                    </div>
                @endforeach

            </div>
        </div>

        {{-- Resource Grid --}}
        @if ($resources->isEmpty())
            <div class="rounded-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No resources found</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or filters.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($resources as $resource)
                    @php
                        $progress = $userProgress->get($resource->id);
                        $categoryColors = [
                            'competition' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'ctf' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            'class' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'learning' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                            'voting' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                        ];
                        $difficultyColors = [
                            'Beginner' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'Intermediate' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                            'Advanced' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                        ];
                        $statusColors = [
                            'open' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'scheduled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                            'active' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                        ];
                    @endphp
                    <a href="{{ route('portal.resources.show', $resource->slug) }}" wire:navigate
                        class="group block rounded-sm border border-gray-200 bg-white p-4 shadow-sm transition-all hover:border-gray-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600">

                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $categoryColors[$resource->category] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ ucfirst($resource->category) }}
                            </span>
                            @if ($resource->difficulty)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $difficultyColors[$resource->difficulty] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $resource->difficulty }}
                                </span>
                            @endif
                            @if ($resource->status)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$resource->status] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ ucfirst($resource->status) }}
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base font-semibold tracking-tight text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">
                            {{ $resource->title }}
                        </h3>

                        @if ($resource->summary)
                            <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                {{ $resource->summary }}
                            </p>
                        @endif

                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                {{ $resource->points }} pts
                            </div>

                            @if ($progress)
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium
                                    @if ($progress->status === 'completed') bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300
                                    @elseif ($progress->status === 'in_progress') bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                                    @else bg-gray-50 text-gray-600 dark:bg-gray-900/30 dark:text-gray-400 @endif">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full
                                        @if ($progress->status === 'completed') bg-green-500
                                        @elseif ($progress->status === 'in_progress') bg-blue-500
                                        @else bg-gray-400 @endif"></span>
                                    {{ str_replace('_', ' ', ucfirst($progress->status)) }}
                                </span>
                            @endif
                        </div>

                        @if ($progress && $progress->progress_percentage > 0)
                            <div class="mt-3">
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="h-full rounded-full transition-all duration-500 ease-out
                                        @if ($progress->progress_percentage === 100) bg-green-500
                                        @else bg-indigo-500 @endif"
                                        style="width: {{ $progress->progress_percentage }}%">
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $progress->progress_percentage }}% complete</p>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $resources->links() }}
            </div>
        @endif
    </div>
</div>
