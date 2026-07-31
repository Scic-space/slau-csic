<div class="py-6">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('trainings.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                &larr; Back to Trainings
            </a>
        </div>

        {{-- Training Header --}}
        <div class="rounded-xl border border-gray-200 bg-white p-8 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $training->title }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $training->description }}</p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @if ($training->category)
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                        {{ ucfirst($training->category) }}
                    </span>
                @endif
                @if ($training->difficulty)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ ucfirst($training->difficulty) }}
                    </span>
                @endif
                @if ($training->duration_hours)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ $training->duration_hours }}h
                    </span>
                @endif
            </div>

            @if ($training->instructor)
                <div class="mt-4 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>Instructor:</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $training->instructor->name }}</span>
                </div>
            @endif

            @if ($training->objectives)
                <div class="mt-6 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Learning Objectives</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $training->objectives }}</p>
                </div>
            @endif
        </div>

        {{-- Enroll / Progress --}}
        @if (! $enrolled)
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-6 text-center dark:border-indigo-800 dark:bg-indigo-900/20">
                <p class="text-indigo-800 dark:text-indigo-300">Enroll in this training to start tracking your progress.</p>
                <button wire:click="enroll" class="mt-3 rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Enroll Now
                </button>
            </div>
        @else
            @php
                $totalModules = $training->modules->count();
                $completedCount = count(array_filter($moduleProgress));
                $progressPercent = $totalModules > 0 ? round(($completedCount / $totalModules) * 100) : 0;
            @endphp
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-800 dark:text-green-300">
                        Progress: {{ $completedCount }}/{{ $totalModules }} modules
                    </span>
                    <span class="text-sm text-green-600 dark:text-green-400">{{ $progressPercent }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-green-200 dark:bg-green-800">
                    <div class="h-full rounded-full bg-green-600 transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>
        @endif

        {{-- Modules --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">Modules</h2>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($training->modules->sortBy('order') as $module)
                    @php
                        $isCompleted = $moduleProgress[$module->id] ?? false;
                    @endphp
                    <div class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $isCompleted ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                @if ($isCompleted)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <span class="text-xs font-medium">{{ $module->order }}</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $module->title }}</h3>
                                @if ($module->duration_minutes)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $module->duration_minutes }} min</p>
                                @endif
                            </div>
                        </div>

                        @if ($enrolled && ! $isCompleted)
                            <button wire:click="completeModule({{ $module->id }})" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Mark Complete
                            </button>
                        @endif
                    </div>

                    @if ($module->content && $enrolled)
                        <div class="px-6 pb-4">
                            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                {!! nl2br(e($module->content)) !!}
                            </div>
                        </div>
                    @endif

                    @if ($module->resources && count($module->resources) > 0 && $enrolled)
                        <div class="px-6 pb-4">
                            <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-400 mb-2">Resources</h4>
                                <div class="space-y-1.5">
                                    @foreach ($module->resources as $resource)
                                        <div class="flex items-center gap-2 text-sm">
                                            @if (is_array($resource))
                                                <a href="{{ $resource['url'] ?? '#' }}" target="_blank" rel="noopener" 
                                                   class="text-blue-600 hover:underline dark:text-blue-400">
                                                    {{ $resource['title'] ?? $resource['name'] ?? 'Resource' }}
                                                </a>
                                                @if (isset($resource['type']))
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $resource['type'] }})</span>
                                                @endif
                                            @else
                                                <span class="text-gray-700 dark:text-gray-300">{{ $resource }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No modules have been added to this training yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
