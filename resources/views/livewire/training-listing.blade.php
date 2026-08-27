<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Training Programs</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Browse available training programs and track your progress</p>
        </div>

        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap gap-3">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Search trainings..."
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm dark:border-border dark:bg-card dark:text-white"
            />
            <select wire:model.live="category" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm dark:border-border dark:bg-card dark:text-white">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <select wire:model.live="difficulty" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm dark:border-border dark:bg-card dark:text-white">
                <option value="">All Levels</option>
                @foreach ($difficulties as $diff)
                    <option value="{{ $diff }}">{{ ucfirst($diff) }}</option>
                @endforeach
            </select>
        </div>

        @if ($trainings->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-border dark:bg-card">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No training programs found</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check back later for new training opportunities.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($trainings as $training)
                    <a href="{{ route('trainings.show', $training->slug) }}" wire:navigate class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $training->title }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $training->description }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($training->category)
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    {{ ucfirst($training->category) }}
                                </span>
                            @endif
                            @if ($training->difficulty)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ ucfirst($training->difficulty) }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $training->modules_count }} modules</span>
                            <span>{{ $training->enrollments_count }} enrolled</span>
                        </div>

                        @if ($training->instructor)
                            <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-border">
                                <div class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-600"></div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $training->instructor->name }}</span>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
