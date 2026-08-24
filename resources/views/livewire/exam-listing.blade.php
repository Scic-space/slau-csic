<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="flex items-center gap-2 text-2xl font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">quiz</span>Internal Exams</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Test your knowledge and earn certifications</p>
        </div>

        @if ($exams->isEmpty())
            <div class="rounded-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No exams available</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Exams will appear here once published by instructors.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($exams as $exam)
                    @php
                        $attempt = $exam->attempts->first();
                    @endphp
                    <div class="rounded-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="flex items-center gap-2 truncate text-lg font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">assignment</span>{{ $exam->title }}</h3>
                                @if ($exam->description)
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $exam->description }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $exam->duration_minutes }} min
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                {{ $exam->questions_count }} questions
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                Pass: {{ $exam->passing_score }}%
                            </span>
                        </div>

                        <div class="mt-4">
                            @if ($attempt)
                                @if ($attempt->is_completed)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $attempt->passed ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                                {{ $attempt->passed ? 'Passed' : 'Failed' }}
                                                — {{ $attempt->total_score }}%
                                            </span>
                                        </div>
                                        <a href="{{ route('exams.result', $attempt) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View Result</a>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-yellow-600 dark:text-yellow-400">In Progress</span>
                                        <a href="{{ route('exams.take', $exam) }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">Continue</a>
                                    </div>
                                @endif
                            @else
                                <a href="{{ route('exams.take', $exam) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                                    Start Exam
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
