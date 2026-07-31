<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('competitions.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Competitions
            </a>
        </div>

        <div class="mb-6 flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-50 dark:bg-indigo-900/30 px-4 py-1.5 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                {{ $competition['type'] }}
            </span>
            @if ($competition['is_team_based'])
                <span class="inline-flex items-center gap-1.5 rounded-full border border-purple-500/20 bg-purple-50 dark:bg-purple-900/30 px-4 py-1.5 text-sm font-medium text-purple-700 dark:text-purple-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Team-based
                </span>
            @endif
            @if ($competition['status'])
                <span class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-medium
                    @if ($competition['status_color'] === 'success') border-green-500/20 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300
                    @elseif ($competition['status_color'] === 'indigo') border-indigo-500/20 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300
                    @else border-gray-500/20 bg-gray-50 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300 @endif">
                    {{ $competition['status'] }}
                </span>
            @endif
        </div>

        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $competition['name'] }}</h1>

            @auth
                <div class="shrink-0">
                    @if ($competition['user_participation'])
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-green-500/20 bg-green-50 dark:bg-green-900/30 px-4 py-1.5 text-sm font-medium text-green-700 dark:text-green-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Joined
                            </span>
                            @if (!$showLeaveConfirm)
                                <button wire:click="confirmLeave" class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-50 dark:bg-red-900/30 px-4 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                                    Leave
                                </button>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Are you sure?</span>
                                    <button wire:click="leave" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700 transition-colors">Yes, leave</button>
                                    <button wire:click="cancelLeave" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">Cancel</button>
                                </div>
                            @endif
                        </div>
                    @else
                        @if (!$showJoinForm)
                            <button wire:click="toggleJoinForm" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Join Competition
                            </button>
                        @endif
                    @endif
                </div>
            @endauth
        </div>

        @if ($showJoinForm)
            <div class="mb-8 rounded-xl border border-indigo-500/20 bg-indigo-50 dark:bg-indigo-900/10 p-6 shadow-sm">
                <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Join this Competition</h3>
                <form wire:submit="join" class="space-y-4">
                    @if ($competition['is_team_based'])
                        <div>
                            <label for="teamName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team Name (optional)</label>
                            <input type="text" id="teamName" wire:model="teamName" placeholder="Enter your team name" class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('teamName') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role (optional)</label>
                        <select id="role" wire:model="role" class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a role...</option>
                            <option value="member">Member</option>
                            <option value="leader">Leader</option>
                        </select>
                        @error('role') <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">Confirm Join</button>
                        <button type="button" wire:click="toggleJoinForm" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">Cancel</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @if ($competition['description'])
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line">{{ $competition['description'] }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                        <div class="mb-1 flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Start Date</p>
                        </div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($competition['start_date'])->format('F j, Y') }}</p>
                    </div>
                    @if ($competition['end_date'])
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                            <div class="mb-1 flex items-center gap-2">
                                <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">End Date</p>
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($competition['end_date'])->format('F j, Y') }}</p>
                        </div>
                    @endif
                    @if ($competition['location'])
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                            <div class="mb-1 flex items-center gap-2">
                                <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</p>
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $competition['location'] }}</p>
                        </div>
                    @endif
                    @if ($competition['participation_status'])
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                            <div class="mb-1 flex items-center gap-2">
                                <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Participation Status</p>
                            </div>
                            <p class="font-medium capitalize text-gray-900 dark:text-white">{{ $competition['participation_status'] }}</p>
                        </div>
                    @endif
                </div>

                @if ($competition['achievements'])
                    <div class="rounded-xl border border-amber-500/20 bg-amber-50 dark:bg-amber-900/10 p-5 shadow-sm">
                        <div class="mb-2 flex items-center gap-2">
                            <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            <p class="text-sm font-medium text-amber-700 dark:text-amber-300">Achievements</p>
                        </div>
                        <p class="text-sm text-amber-600 dark:text-amber-200/70">{{ $competition['achievements'] }}</p>
                    </div>
                @endif

                @if ($competition['website_url'])
                    <a href="{{ $competition['website_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-2.5 text-sm text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Competition Website
                    </a>
                @endif
            </div>

            <div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Participants ({{ count($competition['participants']) }})</h3>
                    </div>
                    @if (count($competition['participants']) === 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400">No participants yet. Be the first to join!</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($competition['participants'] as $p)
                                <li class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-4 py-2.5 text-sm">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $p['name'] }}</span>
                                    <div class="flex gap-1.5">
                                        @if ($p['team_name'])
                                            <span class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $p['team_name'] }}</span>
                                        @endif
                                        @if ($p['role'])
                                            <span class="rounded-lg border border-indigo-500/20 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 text-xs text-indigo-700 dark:text-indigo-300">{{ $p['role'] }}</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8">
            <livewire:competition-challenges :competition="$this->competition" :key="'challenges-'.$competition['id']" wire:key="challenges" />
        </div>
    </div>
</div>
