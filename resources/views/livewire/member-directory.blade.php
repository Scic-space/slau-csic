<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Members</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Browse the club member directory</p>
        </div>

        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Members</p>
                    <span class="material-symbols-outlined text-gray-400 dark:text-gray-500" aria-hidden="true">groups</span>
                </div>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Members</p>
                    <span class="material-symbols-outlined text-green-500 dark:text-green-400" aria-hidden="true">person_check</span>
                </div>
                <p class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['active'] }}</p>
            </div>
            <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Alumni Members</p>
                    <span class="material-symbols-outlined text-indigo-500 dark:text-indigo-400" aria-hidden="true">school</span>
                </div>
                <p class="mt-1 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['alumni'] }}</p>
            </div>
        </div>

        <div class="mb-3 rounded-sm border border-gray-200 bg-white p-3 shadow-sm sm:p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="member-search" class="sr-only">Search members</label>
                    <input id="member-search" wire:model.live.debounce="search" type="text" placeholder="Search members..." class="block w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="member-filter" class="sr-only">Filter members</label>
                    <select id="member-filter" wire:model.live="filter" class="block w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Members</option>
                        <option value="active">Active</option>
                        <option value="alumni">Alumni</option>
                        <option value="executive">Executive Board</option>
                    </select>
                </div>
                <div>
                    <label for="member-year" class="sr-only">Filter by year</label>
                    <select id="member-year" wire:model.live="year" class="block w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Years</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="member-program" class="sr-only">Filter by program</label>
                    <select id="member-program" wire:model.live="program" class="block w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Programs</option>
                        @foreach ($programs as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if ($members->isEmpty())
            <div class="rounded-sm border border-gray-200 bg-white p-8 text-center shadow-sm sm:p-10 dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 dark:text-white">No members found</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($members as $member)
                    <a href="{{ route('members.show', $member['id']) }}" wire:navigate wire:key="member-{{ $member['id'] }}" class="dashboard-card rounded-sm border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <img src="{{ $member['profile_photo_url'] }}" alt="" class="h-12 w-12 rounded-full object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $member['name'] }}</p>
                                @if ($member['headline'])
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $member['headline'] }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @foreach ($member['role_names'] as $role)
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ $role }}</span>
                            @endforeach
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $member['membership_status'] === 'active' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                {{ $member['membership_status'] === 'inactive' ? 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300' : '' }}">
                                {{ ucfirst($member['membership_status']) }}
                            </span>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                            @if ($member['program'])
                                <span class="min-w-0 break-words">{{ $member['program'] }}</span>
                            @endif
                            @if ($member['year_of_study'])
                                <span>Year {{ $member['year_of_study'] }}</span>
                            @endif
                            <span>{{ $member['event_count'] }} events</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
