<div class="py-6">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        {{-- Back link --}}
        <a href="{{ route('members.index') }}" wire:navigate class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Directory
        </a>

        {{-- Profile Header --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 md:p-8">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                @if ($member['profile_photo_url'])
                    <img src="{{ $member['profile_photo_url'] }}" alt="{{ $member['name'] }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-gray-100 dark:ring-gray-700">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-3xl font-bold text-white ring-4 ring-gray-100 dark:ring-gray-700">
                        {{ strtoupper(substr($member['name'], 0, 1)) }}
                    </div>
                @endif

                <div class="text-center sm:text-left flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $member['name'] }}</h1>
                    @if ($member['headline'])
                        <p class="mt-1 text-lg text-gray-500 dark:text-gray-400">{{ $member['headline'] }}</p>
                    @endif
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 mt-3">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                            {{ $member['membership_status'] === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                            {{ $member['membership_type'] === 'alumni' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : '' }}
                            {{ !in_array($member['membership_status'], ['active']) && $member['membership_type'] !== 'alumni' ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' : '' }}">
                            {{ ucfirst($member['membership_status']) }}
                        </span>
                        @foreach ($member['role_names'] as $role)
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">{{ ucfirst($role) }}</span>
                        @endforeach
                    </div>
                    @if ($member['joined_at'])
                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Member since {{ $member['joined_at'] }}</p>
                    @endif
                </div>
            </div>

            {{-- Bio --}}
            @if ($member['bio'])
                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">About</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $member['bio'] }}</p>
                </div>
            @endif

            {{-- Details --}}
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if ($member['program'])
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Program</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $member['program'] }}</dd>
                        </div>
                    @endif
                    @if ($member['faculty'])
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Faculty</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $member['faculty'] }}</dd>
                        </div>
                    @endif
                    @if ($member['year_of_study'])
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Year of Study</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">Year {{ $member['year_of_study'] }}</dd>
                        </div>
                    @endif
                    @if ($member['email'])
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Email</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $member['email'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Stats --}}
            @if ($member['score'] !== null)
                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Activity</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-center dark:border-gray-700 dark:bg-gray-900/60">
                            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($member['score']) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Points</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-center dark:border-gray-700 dark:bg-gray-900/60">
                            <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ ucfirst($member['rank'] ?? 'N/A') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rank</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-center dark:border-gray-700 dark:bg-gray-900/60">
                            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $member['events_attended_count'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Events</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-center dark:border-gray-700 dark:bg-gray-900/60">
                            <p class="text-xl font-bold text-rose-600 dark:text-rose-400">{{ $member['current_streak'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Streak</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Badges --}}
            @if ($member['badges']->isNotEmpty())
                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Badges</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($member['badges'] as $badge)
                            <div class="group relative inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                @if ($badge['icon'])
                                    <span>{{ $badge['icon'] }}</span>
                                @endif
                                {{ $badge['name'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Recent Events --}}
            @if ($member['events_attended']->isNotEmpty())
                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Recent Activity</h2>
                    <div class="space-y-2">
                        @foreach ($member['events_attended'] as $event)
                            <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-900/60">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $event['title'] }}</span>
                                <span class="ml-auto text-xs text-gray-400">{{ $event['date'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Social Links --}}
            @if ($member['github_username'] || $member['linkedin_url'] || $member['discord_username'])
                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">Connect</h2>
                    <div class="flex flex-wrap gap-3">
                        @if ($member['github_username'])
                            <a href="https://github.com/{{ $member['github_username'] }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                GitHub
                            </a>
                        @endif
                        @if ($member['linkedin_url'])
                            <a href="{{ $member['linkedin_url'] }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                LinkedIn
                            </a>
                        @endif
                        @if ($member['discord_username'])
                            <span class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286z"/></svg>
                                {{ $member['discord_username'] }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
