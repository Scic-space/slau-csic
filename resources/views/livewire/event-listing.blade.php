<div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Events</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Browse upcoming and past club events</p>
        </div>

        @if ($isGuest)
            <div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800/50 dark:bg-indigo-900/20">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-800/50">
                            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Want to join our events?</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sign in to register, track attendance, and earn certificates.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('auth.login') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500">
                            Sign In
                        </a>
                        <a href="{{ route('auth.register') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Register
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filters (members only) --}}
        @if (!$isGuest)
            <div class="dashboard-card mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
                    <div class="lg:col-span-2">
                        <input wire:model.live.debounce="search" type="text" placeholder="Search events..."
                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                    </div>
                    <div>
                        <select wire:model.live="category"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat['slug'] }}">{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="type"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            <option value="">All Types</option>
                            @foreach ($eventTypes as $t)
                                <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input wire:model.live="dateFrom" type="date" placeholder="From date"
                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                    </div>
                    <div>
                        <input wire:model.live="dateTo" type="date" placeholder="To date"
                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                    </div>
                    <div>
                        <select wire:model.live="filter"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            <option value="">All Events</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="past">Past</option>
                            @auth
                                <option value="favorites">Favorites</option>
                            @endauth
                        </select>
                    </div>
                </div>
            </div>
        @endif

        {{-- Next Upcoming Event --}}
        @if ($featuredEvent)
            <a href="{{ route('events.show', $featuredEvent['slug']) }}" wire:navigate
               class="dashboard-card mb-6 block rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col sm:flex-row">
                    <div class="flex items-center gap-4 bg-gray-50 px-6 py-4 sm:flex-col sm:justify-center sm:border-r sm:border-gray-200 sm:bg-transparent dark:bg-gray-800/50 dark:sm:border-gray-700">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($featuredEvent['start_date'])->format('d') }}</p>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($featuredEvent['start_date'])->format('M') }}</p>
                        </div>
                        <div class="text-left text-xs text-gray-400 dark:text-gray-500 sm:text-center">
                            {{ \Carbon\Carbon::parse($featuredEvent['start_date'])->diffForHumans() }}
                        </div>
                    </div>
                    <div class="flex flex-1 items-center justify-between gap-4 px-6 py-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $featuredEvent['type'] === 'workshop' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                    {{ $featuredEvent['type'] === 'competition' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                    {{ $featuredEvent['type'] === 'ctf' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                    {{ $featuredEvent['type'] === 'bootcamp' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                    {{ $featuredEvent['type'] === 'talk' ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' : '' }}
                                    {{ $featuredEvent['type'] === 'social' ? 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' : '' }}
                                    {{ $featuredEvent['type'] === 'hackathon' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                    {{ $featuredEvent['type'] === 'awareness_campaign' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}">
                                    {{ collect($eventTypes)->firstWhere('value', $featuredEvent['type'])['label'] ?? ucfirst($featuredEvent['type']) }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-gray-900 px-2.5 py-0.5 text-xs font-medium text-white dark:bg-white dark:text-gray-900">Next Up</span>
                            </div>
                            <h3 class="mt-2 text-base font-semibold text-gray-900 dark:text-white">{{ $featuredEvent['title'] }}</h3>
                            @if ($featuredEvent['description'])
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{!! Str::limit(strip_tags($featuredEvent['description']), 150) !!}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ \Carbon\Carbon::parse($featuredEvent['start_date'])->format('M j, g:i A') }}
                                </span>
                                @if ($featuredEvent['location'])
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $featuredEvent['location'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
        @endif

        {{-- Event Grid --}}
        @if ($events->isEmpty())
            <div class="dashboard-card rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">No events found</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($events as $event)
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
                                {{ collect($eventTypes)->firstWhere('value', $event['type'])['label'] ?? ucfirst($event['type']) }}
                            </span>
                            @if ($event['is_full'])
                                <span class="shrink-0 inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Full</span>
                            @endif
                        </div>

                        <h3 class="mt-3 text-sm font-semibold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-300">{{ $event['title'] }}</h3>

                        @if ($event['description'])
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{!! Str::limit(strip_tags($event['description']), 150) !!}</p>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
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

                        <div class="mt-3 flex items-center justify-between">
                            @if ($event['categories']->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($event['categories'] as $cat)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs" style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }}">{{ $cat['name'] }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if ($event['registration_required'])
                                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                                    {{ $event['registered_count'] }}/{{ $event['max_participants'] ?? '∞' }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
