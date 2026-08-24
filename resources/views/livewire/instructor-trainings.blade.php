<div class="py-4 sm:py-5">
    <div>
        {{-- Page Header --}}
        <div class="mb-4">
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">model_training</span>My Trainings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage and monitor your assigned training programs</p>
        </div>

        {{-- Filters --}}
        <div class="mb-3 flex flex-wrap gap-3 rounded-sm border border-gray-200 bg-white p-3 shadow-sm sm:p-4 dark:border-gray-700 dark:bg-gray-800">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Search trainings..."
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
            />
            <select wire:model.live="category" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                <option value="">All Categories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                <option value="">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>
        </div>

        @if ($trainings->isEmpty())
            {{-- Empty State --}}
            <div class="rounded-sm border border-gray-200 bg-white p-8 text-center shadow-sm sm:p-10 dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No training programs found</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You have no assigned training programs yet.</p>
            </div>
        @else
            {{-- Training Cards Grid --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($trainings as $training)
                    <a href="{{ route('trainings.show', $training->slug) }}" wire:navigate class="dashboard-card group rounded-sm border border-gray-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400"><span class="material-symbols-outlined text-indigo-500" aria-hidden="true">school</span>{{ $training->title }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $training->description }}</p>
                            </div>
                        </div>

                        {{-- Badges --}}
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($training->category)
                                @php
                                    $categoryColors = [
                                        'ethical_hacking' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        'digital_forensics' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'network_security' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'web_security' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                        'mobile_security' => 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
                                        'ctf' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                        'programming' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                        'other' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $categoryColors[$training->category] ?? $categoryColors['other'] }}">
                                    {{ $categories[$training->category] ?? ucfirst($training->category) }}
                                </span>
                            @endif
                            @if ($training->difficulty)
                                @php
                                    $difficultyColors = [
                                        'beginner' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'intermediate' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'advanced' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $difficultyColors[$training->difficulty] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ ucfirst($training->difficulty) }}
                                </span>
                            @endif
                        </div>

                        {{-- Stats --}}
                        <div class="mt-4 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $training->enrollments_count }} enrolled
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $training->enrollments()->where('status', 'completed')->count() }} completed
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                {{ $training->modules_count }} modules
                            </span>
                        </div>

                        {{-- Status & Duration --}}
                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $training->is_published ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                {{ $training->is_published ? 'Published' : 'Draft' }}
                            </span>
                            @if ($training->duration_hours)
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $training->duration_hours }}h</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $trainings->links() }}
            </div>
        @endif
    </div>
</div>
