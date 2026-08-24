<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">co_present</span>Teaching Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your trainings, sessions, and materials</p>
        </div>

        {{-- Stats Cards --}}
        <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['trainings'] }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-indigo-500" aria-hidden="true">model_training</span>Trainings</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_enrollments'] }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-blue-500" aria-hidden="true">groups</span>Active Students</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['completed_students'] }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-green-500" aria-hidden="true">task_alt</span>Completed</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['teaching_sessions'] }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-purple-500" aria-hidden="true">event_note</span>Sessions</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['materials'] }}</p>
                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-[18px] text-amber-500" aria-hidden="true">menu_book</span>Materials</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('instructor.trainings') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                My Trainings
            </a>
            <a href="{{ route('instructor.sessions') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Sessions
            </a>
            <a href="{{ route('instructor.materials') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Materials
            </a>
            <a href="{{ route('grades.index') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Grade Book
            </a>
        </div>

        {{-- Recent Activity --}}
        <div class="grid gap-3 lg:grid-cols-3">
            {{-- Recent Enrollments --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Enrollments</h2>
                </div>
                @if ($recentEnrollments->isEmpty())
                    <div class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No enrollments yet</div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recentEnrollments as $enrollment)
                            <div class="px-5 py-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $enrollment->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->training->title ?? 'Unknown' }} · {{ $enrollment->enrolled_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Upcoming Sessions --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Upcoming Sessions</h2>
                </div>
                @if ($recentSessions->isEmpty())
                    <div class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No upcoming sessions</div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recentSessions as $session)
                            <div class="px-5 py-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $session->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $session->scheduled_at->format('M j, g:i A') }}@if($session->training) · {{ $session->training->title }}@endif</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Materials --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Materials</h2>
                </div>
                @if ($recentMaterials->isEmpty())
                    <div class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No materials uploaded</div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recentMaterials as $material)
                            <div class="px-5 py-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $material->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($material->type) }}@if($material->training) · {{ $material->training->title }}@endif</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
