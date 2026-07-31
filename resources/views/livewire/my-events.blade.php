<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">My Events</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Events you're registered for, instructing, or need feedback on</p>
        </div>

        @if ($pendingFeedback->isNotEmpty())
            <div class="mb-8">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Pending Feedback</h2>
                <div class="space-y-3">
                    @foreach ($pendingFeedback as $event)
                        <div class="dashboard-card flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 dark:border-amber-800 dark:bg-amber-900/20">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $event['title'] }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($event['end_date'])->format('M j, Y') }}</p>
                            </div>
                            <a href="{{ route('events.show', $event['slug']) }}" wire:navigate class="ml-4 shrink-0 rounded-lg bg-gray-900 px-4 py-2 text-xs font-medium text-white hover:bg-gray-800 transition-colors focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                                Leave Feedback
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($favoriteEvents->isNotEmpty())
            <div class="mb-8">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Favorites ({{ $favoriteEvents->count() }})</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($favoriteEvents as $event)
                        <a href="{{ route('events.show', $event['slug']) }}" wire:navigate
                           class="dashboard-card group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-start justify-between gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $event['type'] === 'workshop' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                    {{ $event['type'] === 'competition' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                    {{ $event['type'] === 'ctf' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                    {{ $event['type'] === 'bootcamp' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                    {{ $event['type'] === 'talk' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' : '' }}
                                    {{ $event['type'] === 'social' ? 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' : '' }}
                                    {{ $event['type'] === 'hackathon' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                    {{ $event['type'] === 'awareness_campaign' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-sm font-semibold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-300">{{ $event['title'] }}</h3>
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ \Carbon\Carbon::parse($event['start_date'])->format('M j, g:i A') }}
                                </span>
                                @if ($event['location'])
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $event['location'] }}
                                    </span>
                                @endif
                            </div>
                            @if ($event['categories']->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($event['categories'] as $cat)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs" style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }}">{{ $cat['name'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($upcomingEvents->isNotEmpty())
            <div class="mb-8">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Upcoming ({{ $upcomingEvents->count() }})</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($upcomingEvents as $event)
                        <a href="{{ route('events.show', $event['slug']) }}" wire:navigate
                           class="dashboard-card group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-start justify-between gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $event['type'] === 'workshop' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                    {{ $event['type'] === 'competition' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                    {{ $event['type'] === 'ctf' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                    {{ $event['type'] === 'bootcamp' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                    {{ $event['type'] === 'talk' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' : '' }}
                                    {{ $event['type'] === 'social' ? 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' : '' }}
                                    {{ $event['type'] === 'hackathon' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                    {{ $event['type'] === 'awareness_campaign' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                                </span>
                                @if ($event['is_full'])
                                    <span class="shrink-0 inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Full</span>
                                @endif
                            </div>
                            <h3 class="mt-3 text-sm font-semibold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-300">{{ $event['title'] }}</h3>
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ \Carbon\Carbon::parse($event['start_date'])->format('M j, g:i A') }}
                                </span>
                                @if ($event['location'])
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $event['location'] }}
                                    </span>
                                @endif
                            </div>
                            @if ($event['categories']->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($event['categories'] as $cat)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs" style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }}">{{ $cat['name'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700">
                                @if ($event['registration_required'])
                                    <span class="text-xs text-gray-400">
                                        {{ $event['registered_count'] }}/{{ $event['max_participants'] ?? '∞' }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($event['start_date'])->diffForHumans() }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($pastEvents->isNotEmpty())
            <div class="mb-8">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Past ({{ $pastEvents->count() }})</h2>
                <div class="dashboard-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($pastEvents as $event)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($event['start_date'])->format('M j, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ $event['type'] === 'workshop' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                            {{ $event['type'] === 'competition' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                            {{ $event['type'] === 'ctf' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                            {{ $event['type'] === 'bootcamp' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                            {{ $event['type'] === 'talk' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' : '' }}
                                            {{ $event['type'] === 'social' ? 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' : '' }}
                                            {{ $event['type'] === 'hackathon' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                            {{ $event['type'] === 'awareness_campaign' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('events.show', $event['slug']) }}" wire:navigate class="text-sm font-medium text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:focus:ring-white">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($instructedEvents->isNotEmpty())
            <div>
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Instructed ({{ $instructedEvents->count() }})</h2>
                <div class="dashboard-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($instructedEvents as $event)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($event['start_date'])->format('M j, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ $event['type'] === 'workshop' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                            {{ $event['type'] === 'competition' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                            {{ $event['type'] === 'ctf' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                            {{ $event['type'] === 'bootcamp' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                            {{ $event['type'] === 'talk' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' : '' }}
                                            {{ $event['type'] === 'social' ? 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' : '' }}
                                            {{ $event['type'] === 'hackathon' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                            {{ $event['type'] === 'awareness_campaign' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ $event['status'] === 'published' || $event['status'] === 'scheduled' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                            {{ $event['status'] === 'draft' ? 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300' : '' }}
                                            {{ $event['status'] === 'cancelled' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                            {{ $event['status'] === 'ongoing' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                            {{ $event['status'] === 'completed' ? 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300' : '' }}">
                                            {{ ucfirst($event['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('events.show', $event['slug']) }}" wire:navigate class="text-sm font-medium text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:focus:ring-white">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($upcomingEvents->isEmpty() && $pastEvents->isEmpty() && $instructedEvents->isEmpty())
            <div class="dashboard-card rounded-xl border border-gray-200 bg-white p-6 sm:p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">No events yet</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">You haven't registered for any events yet.</p>
                <a href="{{ route('events.index') }}" wire:navigate class="mt-4 inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                    Browse Events
                </a>
            </div>
        @endif
    </div>
</div>
