<div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Welcome back, {{ $user->name }}</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    @if ($membership)
                        {{ ucfirst($membership->type) }} &middot; {{ ucfirst($membership->status) }}
                    @else
                        {{ ucfirst($user->membership_status) }}
                    @endif
                    Member &middot; {{ $stats['membership_duration'] }} month{{ $stats['membership_duration'] !== 1 ? 's' : '' }}
                    @if ($stats['projects_led'] + $stats['projects_participated'] > 0)
                        &middot; {{ $stats['projects_participated'] }} project{{ $stats['projects_participated'] !== 1 ? 's' : '' }}
                    @endif
                    @if ($user->rank)
                        &middot; Rank: {{ $user->rank }}
                    @endif
                    @if ($user->membership_expires_at)
                        &middot; Expires {{ $user->membership_expires_at->diffForHumans() }}
                    @endif
                    @if ($stats['expiring_soon'])
                        <span class="ml-1.5 inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Renewal due</span>
                    @endif
                </p>
            </div>
            @if ($unreadNotificationCount > 0)
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <span class="flex h-2 w-2 rounded-full bg-amber-500"></span>
                    {{ $unreadNotificationCount }} unread
                </span>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2ZM0 12C0 5.373 5.373 0 12 0s12 5.373 12 12-5.373 12-12 12S0 17.627 0 12ZM12 8a1 1 0 011 1v3.586l2.707 2.707A1 1 0 0114 18v.05a1 1 0 01-.55.9 1 1 0 01-1.05-.05L10 16.293V14.5a1 1 0 011-1h1.5a1 1 0 010 2H13a1 1 0 01-1-1V9a1 1 0 011-1z" fill="currentColor"></path></svg>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        @if ($user->isPendingApproval())
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2ZM0 12C0 5.373 5.373 0 12 0s12 5.373 12 12-5.373 12-12 12S0 17.627 0 12ZM12 8a1 1 0 011 1v3.586l2.707 2.707A1 1 0 0114 18v.05a1 1 0 01-.55.9 1 1 0 01-1.05-.05L10 16.293V14.5a1 1 0 011-1h1.5a1 1 0 010 2H13a1 1 0 01-1-1V9a1 1 0 011-1z" fill="currentColor"></path></svg>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Membership Pending Approval</h3>
                        <p class="mt-0.5 text-xs leading-relaxed text-amber-700 dark:text-amber-300">
                            Your account is awaiting approval by the club administration. Club activities (events, trainings, CTF, voting and more) will be unlocked once you are approved.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Profile Completion --}}
        @php
            $profileFields = [
                'name' => !empty($user->name),
                'email' => !empty($user->email),
                'registration_number' => !empty($user->registration_number),
                'phone' => !empty($user->memberProfile?->phone),
                'bio' => !empty($user->memberProfile?->bio),
                'headline' => !empty($user->memberProfile?->headline),
                'program' => !empty($user->memberProfile?->program),
                'faculty' => !empty($user->memberProfile?->faculty),
                'year_of_study' => !empty($user->memberProfile?->year_of_study),
                'github_username' => !empty($user->socialLinks?->github_username),
                'linkedin_url' => !empty($user->socialLinks?->linkedin_url),
                'discord_username' => !empty($user->socialLinks?->discord_username ?? $user->discord_username),
                'profile_photo' => !empty($user->profile_photo),
            ];
            $profileFilledCount = collect($profileFields)->filter()->count();
            $profileTotalCount = count($profileFields);
            $profilePercentage = $profileTotalCount > 0 ? round(($profileFilledCount / $profileTotalCount) * 100) : 0;
            $profileMissingFields = collect($profileFields)->reject(fn ($v) => $v)->keys()->map(fn ($key) => match ($key) {
                'name' => 'Full Name',
                'email' => 'Email Address',
                'registration_number' => 'Registration Number',
                'phone' => 'Phone Number',
                'bio' => 'Bio',
                'headline' => 'Headline',
                'program' => 'Program',
                'faculty' => 'Faculty',
                'year_of_study' => 'Year of Study',
                'github_username' => 'GitHub Username',
                'linkedin_url' => 'LinkedIn URL',
                'discord_username' => 'Discord Username',
                'profile_photo' => 'Profile Photo',
            });
        @endphp

        @if($profilePercentage < 100)
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                <div class="flex items-center gap-4">
                    <div class="relative flex-shrink-0">
                        <svg class="h-14 w-14 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke-width="3" class="stroke-amber-200 dark:stroke-amber-800" />
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke-width="3" class="stroke-amber-500 dark:stroke-amber-400"
                                stroke-dasharray="{{ $profilePercentage }}, 100"
                                stroke-linecap="round" />
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-amber-700 dark:text-amber-300">{{ $profilePercentage }}%</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Complete Your Profile</h3>
                        <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-300">
                            Your profile is {{ $profilePercentage }}% complete.
                            @if($profileMissingFields->count() <= 3)
                                Missing: {{ $profileMissingFields->implode(', ') }}.
                            @else
                                {{ $profileMissingFields->count() }} fields remaining.
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('profile.edit') }}" wire:navigate
                       class="flex-shrink-0 rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-amber-500 dark:bg-amber-500 dark:hover:bg-amber-400">
                        Complete
                    </a>
                </div>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div class="dashboard-stat rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Events Attended</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['events_attended'] }}</p>
            </div>
            <div class="dashboard-stat rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Sessions</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_sessions'] }}</p>
            </div>
            <div class="dashboard-stat rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Streak</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['current_streak'] }}<span class="text-base font-normal text-gray-400 dark:text-gray-500">d</span></p>
                @if ($stats['longest_streak'] > $stats['current_streak'])
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Best: {{ $stats['longest_streak'] }}d</p>
                @endif
            </div>
            <div class="dashboard-stat rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Points</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['score'] }}</p>
            </div>
            <div class="dashboard-stat rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Attendance Rate</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['attendance_rate'] }}<span class="text-base font-normal text-gray-400 dark:text-gray-500">%</span></p>
            </div>
            <div class="dashboard-stat rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Competitions</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['competition_entries'] }}</p>
            </div>
        </div>

        {{-- Alerts bar --}}
        @php
            $hasAlerts = $unreadNotifications->isNotEmpty()
                || $activeElections->isNotEmpty()
                || $joinableMeetings->isNotEmpty()
                || $finesSummary['pending_count'] + $finesSummary['overdue_count'] > 0
                || $user->htb_username;
        @endphp
        @if ($hasAlerts)
            <div class="mb-8 flex flex-wrap gap-2">
                @if ($unreadNotifications->isNotEmpty())
                    <div class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $unreadNotificationCount }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">notification{{ $unreadNotificationCount !== 1 ? 's' : '' }}</span>
                        <div class="ml-0.5 flex gap-0.5">
                            @foreach ($unreadNotifications->take(2) as $notification)
                                <button wire:click="markNotificationAsRead({{ $notification->id }})"
                                        class="rounded px-1 py-0.5 text-[10px] text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                        title="Dismiss: {{ $notification->title }}">✕</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($activeElections->isNotEmpty())
                    <div class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $activeElections->count() }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">election{{ $activeElections->count() !== 1 ? 's' : '' }}</span>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">closes {{ $activeElections->first()->ends_at?->diffForHumans() ?? 'soon' }}</span>
                    </div>
                @endif

                @php $firstMeeting = $joinableMeetings->first(); @endphp
                @if ($firstMeeting)
                    <div class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <span class="h-1.5 w-1.5 rounded-full {{ $firstMeeting->attendance_open ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $firstMeeting->scheduled_at->format('D, g:i A') }}</span>
                        @if ($joinableMeetings->count() > 1)
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">+{{ $joinableMeetings->count() - 1 }}</span>
                        @endif
                        @if ($firstMeeting->hasMeetingLink() && $firstMeeting->isJoinable())
                            <a href="{{ $firstMeeting->meeting_link }}" target="_blank" rel="noopener noreferrer"
                               class="ml-0.5 rounded bg-gray-900 px-1.5 py-0.5 text-[10px] font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">Join</a>
                        @endif
                    </div>
                @endif

                @if ($finesSummary['pending_count'] > 0 || $finesSummary['overdue_count'] > 0)
                    <div class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">${{ number_format($finesSummary['total_outstanding'], 2) }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">due</span>
                        @if ($finesSummary['overdue_count'] > 0)
                            <span class="rounded bg-red-50 px-1 py-0.5 text-[10px] font-medium text-red-600 dark:bg-red-900/30 dark:text-red-400">{{ $finesSummary['overdue_count'] }} overdue</span>
                        @endif
                    </div>
                @endif

                @if ($user->htb_username)
                    <div class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        @if ($user->htb_profile_data)
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        @else
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-300"></span>
                        @endif
                        <span class="text-xs font-medium text-gray-900 dark:text-white">HTB</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">@ {{ $user->htb_username }}</span>
                        @if ($user->htb_last_synced_at)
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ $user->htb_last_synced_at->diffForHumans() }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- Content cards --}}
        <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Events card --}}
            <div class="dashboard-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Upcoming Events</h3>
                </div>
                <div class="px-5 py-4">
                    @if ($upcomingEvents->isEmpty())
                        <div class="flex flex-col items-center py-6 text-center">
                            <svg class="mb-2 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500">No events coming up</p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Check the events page for upcoming activities</p>
                        </div>
                    @else
                        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">{{ $upcomingEvents->count() }} event{{ $upcomingEvents->count() !== 1 ? 's' : '' }} ahead — plan your schedule</p>
                        <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach ($upcomingEvents as $event)
                                <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->start_date->format('D, M j · g:i A') }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Training card --}}
            <div class="dashboard-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Training Progress</h3>
                        @if ($activeTrainings->isNotEmpty() || $stats['trainings_completed'] > 0)
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $stats['trainings_completed'] }} completed
                                @if ($activeTrainings->isNotEmpty())
                                    · {{ $activeTrainings->count() }} in progress
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
                <div class="px-5 py-4">
                    @if ($activeTrainings->isEmpty() && $stats['club_portal_active_tracks'] === 0)
                        <div class="flex flex-col items-center py-6 text-center">
                            <svg class="mb-2 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500">No training in progress</p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Enroll in a course to get started</p>
                        </div>
                    @else
                        @if ($stats['club_portal_active_tracks'] > 0)
                            <div class="mb-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Club Resources</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $stats['club_portal_active_tracks'] }} active · {{ $stats['club_portal_completed_tracks'] }} completed</span>
                                </div>
                                @if ($stats['club_portal_average_progress'] > 0)
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ $stats['club_portal_average_progress'] }}%"></div>
                                        </div>
                                        <span class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $stats['club_portal_average_progress'] }}%</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if ($activeTrainings->isNotEmpty())
                            <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                @foreach ($activeTrainings as $enrollment)
                                    <li class="py-2.5 first:pt-0 last:pb-0">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $enrollment->training?->title ?? 'Training' }}</p>
                                                <div class="mt-1.5 flex items-center gap-2">
                                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                                        <div class="h-full rounded-full bg-gray-900 dark:bg-white" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                                    </div>
                                                    <span class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $enrollment->progress_percentage }}%</span>
                                                </div>
                                                <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ ucfirst($enrollment->status) }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Achievements card --}}
            <div class="dashboard-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Achievements</h3>
                        @if ($user->earnedBadges->isNotEmpty())
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->earnedBadges->count() }} unlocked{{ $unearnedBadgeCount > 0 ? ' · ' . $unearnedBadgeCount . ' remaining' : '' }}</p>
                        @endif
                    </div>
                </div>
                <div class="px-5 py-4">
                    @if ($user->earnedBadges->isEmpty())
                        <div class="flex flex-col items-center py-6 text-center">
                            <svg class="mb-2 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500">No achievements unlocked</p>
                            @if ($unearnedBadgeCount > 0)
                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $unearnedBadgeCount }} badges you can still earn</p>
                            @endif
                        </div>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->earnedBadges as $badge)
                                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 dark:border-gray-700 dark:bg-gray-900" title="{{ $badge->description }}">
                                    <span class="text-lg">{{ $badge->icon }}</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $badge->name }}</p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">{{ $badge->rarity->name }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($unearnedBadgeCount > 0)
                            <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">{{ $unearnedBadgeCount }} more badges you can earn</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Club Activity --}}
        <div class="dashboard-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Club Activity</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">What's happening across the club</p>
            </div>
            <div class="px-5 py-4">
                @if ($clubActivity->isEmpty())
                    <div class="flex flex-col items-center py-6 text-center">
                        <svg class="mb-2 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No recent activity</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach ($clubActivity as $activity)
                            @php
                                $causer = $activity->causer;
                                $description = $activity->description;
                                $subjectName = $activity->subject?->title ?? $activity->subject?->name ?? '';
                                $iconBg = match(true) {
                                    str_contains($description, 'approved') => 'bg-green-100 dark:bg-green-900/30',
                                    str_contains($description, 'rejected') || str_contains($description, 'suspended') => 'bg-red-100 dark:bg-red-900/30',
                                    str_contains($description, 'election_opened') => 'bg-indigo-100 dark:bg-indigo-900/30',
                                    str_contains($description, 'election_closed') || str_contains($description, 'results') => 'bg-amber-100 dark:bg-amber-900/30',
                                    default => 'bg-gray-100 dark:bg-gray-700',
                                };
                                $iconColor = match(true) {
                                    str_contains($description, 'approved') => 'text-green-600 dark:text-green-400',
                                    str_contains($description, 'rejected') || str_contains($description, 'suspended') => 'text-red-600 dark:text-red-400',
                                    str_contains($description, 'election_opened') => 'text-indigo-600 dark:text-indigo-400',
                                    str_contains($description, 'election_closed') || str_contains($description, 'results') => 'text-amber-600 dark:text-amber-400',
                                    default => 'text-gray-500 dark:text-gray-400',
                                };
                                $iconPath = match(true) {
                                    str_contains($description, 'approved') => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    str_contains($description, 'rejected') || str_contains($description, 'suspended') => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
                                    str_contains($description, 'election_opened') => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                                    str_contains($description, 'election_closed') || str_contains($description, 'results') => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                    default => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                                };
                                $friendlyDescription = match(true) {
                                    str_contains($description, 'approved') => 'was approved as a member',
                                    str_contains($description, 'rejected') => 'application was rejected',
                                    str_contains($description, 'suspended') => 'was suspended',
                                    str_contains($description, 'election_opened') => 'A new election has been opened',
                                    str_contains($description, 'election_closed') => 'An election has been closed',
                                    str_contains($description, 'results_published') => 'Election results have been published',
                                    default => $description,
                                };
                            @endphp
                            <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                @if ($causer && $causer->avatar_url)
                                    <img src="{{ $causer->avatar_url }}" alt="{{ $causer->name }}" class="mt-0.5 h-8 w-8 shrink-0 rounded-full object-cover">
                                @else
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400">
                                        {{ strtoupper(substr($causer->name ?? 'U', 0, 1)) }}
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-gray-700 dark:text-gray-300">
                                        @if (str_contains($description, 'Member'))
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $subjectName ?: ($causer->name ?? 'Someone') }}</span>
                                            {{ $friendlyDescription }}
                                        @else
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $causer->name ?? 'System' }}</span>
                                            {{ $friendlyDescription }}
                                            @if ($subjectName)
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $subjectName }}</span>
                                            @endif
                                        @endif
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $iconBg }}">
                                    <svg class="h-3 w-3 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/></svg>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
