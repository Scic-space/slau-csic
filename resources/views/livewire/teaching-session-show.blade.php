<div class="py-6">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ url()->previous() }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                &larr; Back
            </a>
        </div>

        {{-- Session Header --}}
        <div class="rounded-xl border border-gray-200 bg-white p-8 dark:border-border dark:bg-card">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                            Teaching Session
                        </span>
                        @php
                            $statusColors = [
                                'completed' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'ongoing' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$meeting->status] ?? $statusColors['scheduled'] }}">
                            {{ ucfirst($meeting->status) }}
                        </span>
                    </div>
                    <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $meeting->title }}</h1>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Date</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $meeting->scheduled_at->format('M j, Y') }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Time</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $meeting->scheduled_at->format('g:i A') }}</p>
                </div>
                @if ($meeting->location)
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Location</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $meeting->location }}</p>
                    </div>
                @endif
                @if ($meeting->duration_minutes)
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Duration</span>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $meeting->duration_minutes }} min</p>
                    </div>
                @endif
            </div>

            @if ($meeting->description)
                <div class="mt-4 border-t border-gray-100 pt-4 dark:border-border">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $meeting->description }}</p>
                </div>
            @endif
        </div>

        {{-- Attendance Status --}}
        <div class="rounded-xl border p-4 {{ $hasAttended ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-gray-200 bg-white dark:border-border dark:bg-card' }}">
            <div class="flex items-center gap-3">
                @if ($hasAttended)
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-green-800 dark:text-green-300">You attended this session</span>
                @else
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Attendance not recorded</span>
                @endif
            </div>
        </div>

        {{-- Linked Training --}}
        @if ($meeting->training)
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Linked Training</p>
                        <p class="mt-1 text-sm font-medium text-indigo-800 dark:text-indigo-300">{{ $meeting->training->title }}</p>
                    </div>
                    <a href="{{ route('trainings.show', $meeting->training->slug) }}" wire:navigate class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                        View Training
                    </a>
                </div>
            </div>
        @endif

        {{-- Agenda --}}
        @if ($meeting->agendaItems->count() > 0)
            <div class="rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-card">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Agenda</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($meeting->agendaItems as $item)
                        <div class="px-6 py-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->title }}</h3>
                                @if ($item->duration_minutes)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->duration_minutes }} min</span>
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

        {{-- Attachments --}}
        @if ($meeting->attachments->count() > 0)
            <div class="rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-card">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Materials</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($meeting->attachments as $attachment)
                        <div class="flex items-center justify-between px-6 py-3">
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $attachment->name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        {{-- Session Feedback --}}
        @if ($hasAttended)
            <div class="rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-card">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Session Feedback</h2>
                        @if (! $hasSubmittedFeedback && ! $showFeedbackForm)
                            <button wire:click="toggleFeedbackForm" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                                Leave Feedback
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Feedback Form --}}
                @if ($showFeedbackForm && ! $hasSubmittedFeedback)
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
                        <form wire:submit="submitFeedback" class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Rating</label>
                                <div class="flex gap-1">
                                    @foreach ([1, 2, 3, 4, 5] as $star)
                                        <button type="button" wire:click="rating = {{ $star }}" class="text-2xl {{ $rating >= $star ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }} transition-colors hover:text-yellow-400">
                                            ★
                                        </button>
                                    @endforeach
                                </div>
                                @error('rating') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Comment (optional)</label>
                                <textarea wire:model="comment" rows="3" maxlength="1000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-border dark:bg-card dark:text-white" placeholder="Share your thoughts on this session..."></textarea>
                                @error('comment') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" wire:loading.attr="disabled">
                                    Submit Feedback
                                </button>
                                <button type="button" wire:click="toggleFeedbackForm" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Submitted Thank You --}}
                @if ($hasSubmittedFeedback)
                    <div class="border-b border-gray-200 px-6 py-3 dark:border-border">
                        <p class="text-sm text-green-700 dark:text-green-400">✓ Thank you for your feedback!</p>
                    </div>
                @endif

                {{-- Existing Feedback --}}
                @if ($meeting->feedback->count() > 0)
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($meeting->feedback as $feedback)
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->user->name ?? 'Anonymous' }}</span>
                                        <span class="text-sm text-yellow-400">{{ str_repeat('★', $feedback->rating) }}{{ str_repeat('☆', 5 - $feedback->rating) }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $feedback->created_at->diffForHumans() }}</span>
                                </div>
                                @if ($feedback->comment)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $feedback->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif (! $hasSubmittedFeedback)
                    <div class="px-6 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        No feedback yet. Be the first to share your thoughts!
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
