<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">grade</span>My Grades</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your performance across all trainings and exams</p>
        </div>

        <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="flex items-center justify-between gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">Trainings Enrolled<span class="material-symbols-outlined" aria-hidden="true">school</span></p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['trainings_enrolled'] }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="flex items-center justify-between gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">Trainings Completed<span class="material-symbols-outlined text-green-500" aria-hidden="true">task_alt</span></p>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['trainings_completed'] }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="flex items-center justify-between gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">Exams Taken<span class="material-symbols-outlined" aria-hidden="true">quiz</span></p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['exams_taken'] }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="flex items-center justify-between gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">Exams Passed<span class="material-symbols-outlined text-green-500" aria-hidden="true">verified</span></p>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['exams_passed'] }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="flex items-center justify-between gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">Average Score<span class="material-symbols-outlined text-blue-500" aria-hidden="true">monitoring</span></p>
                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['average_score'] !== null ? number_format($stats['average_score'], 1).'%' : 'N/A' }}</p>
            </div>
        </div>

        <div class="dashboard-card overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @if ($grades->isEmpty())
                <div class="p-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">No grades yet</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Your training and exam grades will appear here once available.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($grades as $grade)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $grade['type'] === 'training' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                        {{ $grade['type'] === 'exam' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}">
                                        {{ ucfirst($grade['type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $grade['name'] }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $grade['status'] === 'completed' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                        {{ $grade['status'] === 'in_progress' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                        {{ $grade['status'] === 'enrolled' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                        {{ $grade['status'] === 'passed' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                        {{ $grade['status'] === 'failed' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $grade['status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if ($grade['progress'] !== null)
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div class="h-full rounded-full bg-green-500" style="width: {{ $grade['progress'] }}%"></div>
                                            </div>
                                            <span class="text-xs">{{ $grade['progress'] }}%</span>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $grade['score'] !== null ? number_format($grade['score'], 1).'%' : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $grade['completed_at'] ? \Carbon\Carbon::parse($grade['completed_at'])->format('M j, Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </div>
    </div>
</div>
