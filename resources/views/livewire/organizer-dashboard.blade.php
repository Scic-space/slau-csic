<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">My Events Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage registrations, attendance, and feedback for your events</p>
        </div>

        @if ($events->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-border dark:bg-card">
                <p class="text-sm font-medium text-gray-900 dark:text-white">No events yet</p>
                <p class="mt-1 text-xs text-gray-500">Create your first event to get started.</p>
                <a href="{{ route('events.create') }}" wire:navigate class="mt-4 inline-block rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    Create Event
                </a>
            </div>
        @else
            {{-- Event selector --}}
            <div class="mb-6 flex flex-wrap gap-2">
                @foreach ($events as $e)
                    <button wire:click="selectEvent({{ $e['id'] }})" wire:key="evt-{{ $e['id'] }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition focus:ring-2 focus:ring-gray-900 dark:focus:ring-white"
                        :class="selectedEventId === {{ $e['id'] }} ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'">
                        {{ $e['title'] }}
                    </button>
                @endforeach
            </div>

            @if ($selectedEventId)
                @php $evt = $events->firstWhere('id', $selectedEventId); @endphp
                @if ($evt)
                    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ $evt['status'] }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Registered</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $evt['registered_count'] }}<span class="text-xs font-normal text-gray-500"> / {{ $evt['max_participants'] ?? '∞' }}</span></p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Attended</p>
                            <p class="mt-1 text-sm font-semibold text-green-600 dark:text-green-400">{{ $evt['attended_count'] }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Waitlisted</p>
                            <p class="mt-1 text-sm font-semibold text-amber-600 dark:text-amber-400">{{ $evt['waitlist_count'] }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Avg Rating</p>
                            <p class="mt-1 text-sm font-semibold {{ $averageRating && $averageRating >= 4 ? 'text-green-600 dark:text-green-400' : ($averageRating && $averageRating >= 3 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white') }}">
                                {{ $averageRating ? $averageRating . ' / 5' : 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        {{-- Registrations --}}
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card overflow-hidden">
                            <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Registrations ({{ $registrations->count() }})</h2>
                            </div>
                            @if ($registrations->isEmpty())
                                <div class="p-8 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No registrations yet.</p>
                                </div>
                            @else
                                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                                    @foreach ($registrations as $r)
                                        <div class="px-5 py-3 flex items-center justify-between">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $r['name'] }}</p>
                                                <div class="mt-0.5 flex flex-wrap gap-1.5">
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium
                                                        {{ $r['status'] === 'registered' || $r['status'] === 'attended' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                        {{ $r['status'] === 'waitlist' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                                        {{ $r['status'] === 'cancelled' || $r['status'] === 'no_show' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $r['status'])) }}
                                                    </span>
                                                    @if ($r['rsvp_status'])
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium
                                                            {{ $r['rsvp_status'] === 'attending' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                            {{ $r['rsvp_status'] === 'maybe' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                                            {{ $r['rsvp_status'] === 'not_attending' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                                            {{ ucfirst($r['rsvp_status']) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($r['attended_at'])
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Checked in: {{ $r['attended_at'] }}</p>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 shrink-0 ml-2">{{ $r['registered_at'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Feedback --}}
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card overflow-hidden">
                            <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Feedback ({{ $feedbackData->count() }})</h2>
                            </div>
                            @if ($feedbackData->isEmpty())
                                <div class="p-8 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No feedback yet.</p>
                                </div>
                            @else
                                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                                    @foreach ($feedbackData as $f)
                                        <div class="px-5 py-3">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $f['name'] }}</p>
                                                <div class="flex items-center gap-0.5">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg class="h-3.5 w-3.5 {{ $i <= $f['rating'] ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                    @endfor
                                                </div>
                                            </div>
                                            @if ($f['feedback_text'])
                                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $f['feedback_text'] }}</p>
                                            @endif
                                            @if ($f['suggestions'])
                                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500 italic">Suggestion: {{ $f['suggestions'] }}</p>
                                            @endif
                                            <p class="mt-1 text-[10px] text-gray-400">{{ $f['created_at'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Links --}}
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('events.show', $evt['slug']) }}" wire:navigate class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">View Event</a>
                        <a href="{{ route('events.edit', $evt['slug']) }}" wire:navigate class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover">Edit Event</a>
                        <a href="{{ route('events.checkin') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover">Check-In</a>
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
