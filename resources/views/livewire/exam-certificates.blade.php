<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="flex items-center gap-2 text-2xl font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">workspace_premium</span>Certificates</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Download your earned certificates for completed exams</p>
        </div>

        @if ($eligibilities->isEmpty())
            <div class="rounded-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No certificates yet</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Complete exams with a passing score to earn certificates.</p>
                <a href="{{ route('exams.index') }}" class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                    Browse Exams
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($eligibilities as $eligibility)
                    <div class="rounded-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="flex items-center gap-2 truncate text-lg font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-green-500" aria-hidden="true">verified</span>{{ $eligibility->exam->title }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Score: {{ $eligibility->examAttempt->total_score }}%
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Earned {{ $eligibility->created_at->format('M j, Y') }}
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('exams.certificates.download', $eligibility) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download PDF
                            </a>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
