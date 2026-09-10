<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><span class="material-symbols-outlined text-brand-500" aria-hidden="true">co_present</span>Teaching Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your trainings, sessions, and materials</p>
        </div>

        {{-- Analytics Cards --}}
        <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($teachingCards as $card)
                <a
                    href="{{ $card['url'] }}"
                    wire:navigate
                    class="group flex min-h-32 flex-col justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-400 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-500 dark:border-border dark:bg-card"
                    aria-label="{{ $card['label'] }}"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $card['label'] }}</p>
                        <span @class([
                            'material-symbols-outlined inline-flex h-10 w-10 items-center justify-center rounded-sm text-[24px]',
                            'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' => $card['tone'] === 'indigo',
                            'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400' => $card['tone'] === 'teal',
                            'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400' => $card['tone'] === 'green',
                            'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' => $card['tone'] === 'amber',
                            'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' => $card['tone'] === 'blue',
                            'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400' => $card['tone'] === 'orange',
                        ]) aria-hidden="true">{{ $card['icon'] }}</span>
                    </div>

                    <div class="mt-4 flex items-end justify-between gap-3">
                        <p class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($card['count']) }}</p>
                        <span class="material-symbols-outlined text-[20px] text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-teal-500" aria-hidden="true">arrow_forward</span>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Quick Actions --}}
        <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('instructor.trainings') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                My Trainings
            </a>
            <a href="{{ route('instructor.sessions') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Sessions
            </a>
            <a href="{{ route('instructor.materials') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Materials
            </a>
            <a href="{{ route('grades.index') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Grade Book
            </a>
        </div>

        {{-- Recent Activity --}}
        <div class="grid gap-3 lg:grid-cols-3">
            {{-- Recent Enrollments --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white dark:border-border dark:bg-card">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-border">
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
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white dark:border-border dark:bg-card">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-border">
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
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white dark:border-border dark:bg-card">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-border">
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
