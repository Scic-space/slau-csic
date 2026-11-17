@php
    $statusClasses = match ($meeting->status) {
        'ongoing' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'scheduled' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    };
    $statusLabel = match ($meeting->status) {
        'ongoing' => 'Live',
        'scheduled' => 'Upcoming',
        'completed' => 'Completed',
        'past' => 'Past',
        'cancelled' => 'Cancelled',
        default => ucfirst($meeting->status),
    };
    $canToggleInterest = ! $meeting->isCancelled()
        && ! $meeting->hasEnded()
        && ! ($meeting->isPast() && ! $meeting->attendance_open);
@endphp

<div class="py-4 sm:py-5">
    <div class="mx-auto max-w-3xl space-y-4">
        <a href="{{ route('meetings.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            Back to meetings
        </a>

        <div class="rounded-sm border border-gray-200 bg-white p-6 shadow-sm dark:border-border dark:bg-card">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
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

            <h1 class="mt-3 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $meeting->title }}</h1>

            @if ($meeting->isCancelled())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">This meeting has been cancelled.</p>
                    @if ($meeting->cancellation_reason)
                        <p class="mt-1 text-sm text-red-700 dark:text-red-400">Reason: {{ $meeting->cancellation_reason }}</p>
                    @endif
                </div>
            @endif

            @if ($canToggleInterest)
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button" wire:click="toggleInterest" wire:loading.attr="disabled" wire:target="toggleInterest"
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition disabled:opacity-60 {{ $isInterested ? 'border-indigo-300 bg-indigo-50 text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-border dark:text-gray-300 dark:hover:bg-card-hover' }}">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">{{ $isInterested ? 'bookmark_added' : 'bookmark_add' }}</span>
                        {{ $isInterested ? 'Interested' : "I'm interested" }}
                    </button>

                    @if ($interestCount > 0)
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $interestCount }} interested</span>
                    @endif

                    @if ($meeting->hasMeetingLink() && $meeting->isJoinable())
                        <a href="{{ $meeting->meeting_link }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 rounded-full bg-gray-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">videocam</span>
                            Join meeting
                        </a>
                    @endif
                </div>
            @endif

            <dl class="mt-5 grid grid-cols-1 gap-4 border-t border-gray-100 pt-5 text-sm dark:border-border sm:grid-cols-2">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Date &amp; time</dt>
                    <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $meeting->scheduled_at?->format('l, F j, Y \a\t g:i A') }}</dd>
                </div>
                @if ($meeting->duration_minutes)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Duration</dt>
                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $meeting->duration_minutes }} minutes</dd>
                    </div>
                @endif
                @if ($meeting->location)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Location</dt>
                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $meeting->location }}</dd>
                    </div>
                @endif
                @if ($meeting->creator)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Organized by</dt>
                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $meeting->creator->name }}</dd>
                    </div>
                @endif
                @if ($meeting->instructor)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Instructor</dt>
                        <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $meeting->instructor->name }}</dd>
                    </div>
                @endif
            </dl>

            @if ($meeting->description)
                <div class="mt-5 border-t border-gray-100 pt-5 text-sm text-gray-600 dark:border-border dark:text-gray-400">
                    {!! $meeting->description !!}
                </div>
            @endif
        </div>

        @if ($meeting->hasEnded() || $meeting->isPast())
            <div class="rounded-sm border p-4 shadow-sm {{ in_array($attendanceStatus, ['present', 'late'], true) ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-gray-200 bg-white dark:border-border dark:bg-card' }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium {{ in_array($attendanceStatus, ['present', 'late'], true) ? 'text-green-800 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                            @if ($attendanceStatus === 'present')
                                You attended this meeting
                            @elseif ($attendanceStatus === 'late')
                                You attended this meeting (marked late)
                            @elseif ($attendanceStatus === 'absent')
                                You were marked absent
                            @else
                                No attendance record
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Attendance is recorded by the meeting organizer.</p>
                    </div>
                    <a href="{{ route('attendance.calendar') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">fact_check</span>
                        Attendance calendar
                    </a>
                </div>
            </div>
        @endif

        @if ($meeting->agendaItems->isNotEmpty())
            <div class="rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-border">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Agenda</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($meeting->agendaItems as $item)
                        <div class="px-6 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->title }}</h3>
                                @if ($item->duration_minutes)
                                    <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ $item->duration_minutes }} min</span>
                                @endif
                            </div>
                            @if ($item->description)
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($meeting->attachments->isNotEmpty())
            <div class="rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-border">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Materials</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($meeting->attachments as $attachment)
                        <div class="flex items-center gap-3 px-6 py-3">
                            <span class="material-symbols-outlined text-[20px] text-gray-400" aria-hidden="true">description</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $attachment->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($meeting->minutes_status === 'published' && $meeting->minutes)
            <div class="rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-border">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Meeting Minutes</h2>
                </div>
                <div class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                    {!! $meeting->minutes !!}
                </div>
            </div>
        @endif
    </div>
</div>
