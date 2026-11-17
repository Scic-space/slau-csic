<div class="py-4 sm:py-5">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Meetings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Club meetings, executive sessions, workshops, and training sessions</p>
        </div>
        <a href="{{ route('attendance.calendar') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-sm border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">fact_check</span>
            Attendance
        </a>
    </div>

    <div class="dashboard-card mb-4 rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="meeting-search" class="sr-only">Search meetings</label>
                <input id="meeting-search" wire:model.live.debounce.300ms="search" type="text" placeholder="Search meetings..."
                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-border dark:bg-card dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
            </div>
            <div>
                <label for="meeting-type" class="sr-only">Meeting type</label>
                <select id="meeting-type" wire:model.live="type"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-border dark:bg-card dark:text-white dark:focus:border-white dark:focus:ring-white">
                    <option value="">All Types</option>
                    @foreach ($meetingTypes as $meetingType)
                        <option value="{{ $meetingType['value'] }}">{{ $meetingType['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="meeting-filter" class="sr-only">Meeting timeframe</label>
                <select id="meeting-filter" wire:model.live="filter"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-border dark:bg-card dark:text-white dark:focus:border-white dark:focus:ring-white">
                    <option value="upcoming">Upcoming</option>
                    <option value="past">Past</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ $meetings->total() }} meeting{{ $meetings->total() !== 1 ? 's' : '' }} found
            </p>
            @if ($search !== '' || $type !== '' || $filter !== 'upcoming')
                <button type="button" wire:click="resetFilters" class="text-xs font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Clear filters
                </button>
            @endif
        </div>
    </div>

    @if ($meetings->isEmpty())
        <div class="dashboard-card rounded-sm border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-border dark:bg-card">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                <span class="material-symbols-outlined text-3xl text-gray-400 dark:text-gray-500" aria-hidden="true">groups_2</span>
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">No meetings found</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Try changing the timeframe or meeting type.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($meetings as $meeting)
                @php
                    $status = $meeting->status;
                    $isInterested = $meeting->interests->isNotEmpty();
                    $attendance = $meeting->attendance->first();
                    $typeClasses = match ($meeting->type) {
                        'general' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                        'executive' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                        'special' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                        'training' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                        'workshop' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    };
                    $statusClasses = match ($status) {
                        'ongoing' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                        'scheduled' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                        'completed', 'past' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                        default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                    };
                    $statusLabel = match ($status) {
                        'ongoing' => 'Live',
                        'scheduled' => 'Upcoming',
                        'completed' => 'Completed',
                        'past' => 'Past',
                        default => ucfirst($status),
                    };
                @endphp
                <article wire:key="meeting-{{ $meeting->id }}" class="dashboard-card flex min-w-0 flex-col rounded-sm border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-border dark:bg-card">
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeClasses }}">
                                {{ $meeting->type_display }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                            @if ($meeting->audience_label)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $meeting->audience_label }}
                                </span>
                            @endif
                        </div>

                        <h2 class="mt-3 text-base font-semibold text-gray-900 dark:text-white">
                            <a href="{{ route('meetings.show', $meeting) }}" wire:navigate class="transition hover:text-indigo-600 dark:hover:text-indigo-400">{{ $meeting->title }}</a>
                        </h2>

                        @if ($meeting->description)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit(strip_tags($meeting->description), 150) }}</p>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">calendar_today</span>
                                {{ $meeting->scheduled_at?->format('D, M j · g:i A') }}
                            </span>
                            @if ($meeting->duration_minutes)
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">schedule</span>
                                    {{ $meeting->duration_minutes }} min
                                </span>
                            @endif
                            @if ($meeting->location)
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">location_on</span>
                                    {{ $meeting->location }}
                                </span>
                            @endif
                        </div>

                        @if ($meeting->creator)
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Organized by {{ $meeting->creator->name }}</p>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <a href="{{ route('meetings.show', $meeting) }}" wire:navigate
                               class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-border dark:text-gray-400 dark:hover:bg-card-hover">
                                <span class="material-symbols-outlined text-[15px]" aria-hidden="true">open_in_new</span>
                                Details
                            </a>

                            @if (in_array($status, ['scheduled', 'ongoing'], true))
                                <button type="button" wire:click="toggleInterest({{ $meeting->id }})" wire:loading.attr="disabled" wire:target="toggleInterest({{ $meeting->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition disabled:opacity-60 {{ $isInterested ? 'border-indigo-300 bg-indigo-50 text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-border dark:text-gray-400 dark:hover:bg-card-hover' }}">
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">{{ $isInterested ? 'bookmark_added' : 'bookmark_add' }}</span>
                                    {{ $isInterested ? 'Interested' : "I'm interested" }}
                                    @if ($meeting->interests_count > 0)
                                        ({{ $meeting->interests_count }})
                                    @endif
                                </button>

                                @if ($meeting->hasMeetingLink() && $meeting->isJoinable())
                                    <a href="{{ $meeting->meeting_link }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 rounded-full bg-gray-900 px-3 py-1 text-xs font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">videocam</span>
                                        Join
                                    </a>
                                @endif
                            @else
                                @if ($attendance)
                                    @php
                                        $attendanceClasses = match ($attendance->status) {
                                            'present' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                            'late' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                            'absent' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium {{ $attendanceClasses }}">
                                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">fact_check</span>
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">remove</span>
                                        No attendance record
                                    </span>
                                @endif

                                <a href="{{ route('attendance.calendar') }}" wire:navigate
                                   class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-border dark:text-gray-400 dark:hover:bg-card-hover">
                                    <span class="material-symbols-outlined text-[15px]" aria-hidden="true">calendar_month</span>
                                    Attendance
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $meetings->links() }}
        </div>
    @endif
</div>
