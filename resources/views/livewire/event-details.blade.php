<div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        @php
            $isAuthenticated = auth()->check();
            $user = auth()->user();
            $isOrganizer = $isAuthenticated && $user->id === $event->organizer?->id;
            $isAdmin = $isAuthenticated && $user->hasAnyRole(['admin', 'super-admin']);
            $canEdit = $isOrganizer || $isAdmin;

            $registration = $userRegistration;
            $isRegistered = $registration && $registration->status === 'registered';
            $isWaitlisted = $registration && $registration->status === 'waitlist';
            $isAttending = $registration && $registration->rsvp_status === 'attending';
            $isMaybe = $registration && $registration->rsvp_status === 'maybe';

            $statusColors = [
                'scheduled' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'published' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                'ongoing' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                'completed' => 'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            ];

            $typeColors = [
                'workshop' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                'competition' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                'ctf' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                'bootcamp' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'talk' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
                'social' => 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300',
                'hackathon' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                'awareness_campaign' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
            ];

            $shareUrl = url()->current();
            $isFuture = $event->start_date->isFuture();
            $gCalStart = $event->start_date->utc()->format('Ymd\THis\Z');
            $gCalEnd = ($event->end_date ?? $event->start_date->copy()->addHours(2))->utc()->format('Ymd\THis\Z');
            $twitterShareText = rawurlencode('Check out this event: ' . $event->title);
        @endphp

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <a href="{{ route('events.index') }}" wire:navigate
                   class="mb-3 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Events
                </a>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    @foreach ($event->categories as $cat)
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                              style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}">
                            <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $cat->color }}"></span>
                            {{ $cat->name }}
                        </span>
                    @endforeach
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $event->title }}</h1>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if ($canEdit)
                    <a href="{{ route('events.edit', $event->slug) }}" wire:navigate
                       class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                @endif
                @if ($isAuthenticated)
                    <button wire:click="toggleFavorite"
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium transition hover:bg-gray-50 dark:border-border dark:bg-card dark:hover:bg-card-hover focus:ring-2 focus:ring-gray-900 dark:focus:ring-white"
                            title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
                        <svg class="w-4 h-4 {{ $isFavorited ? 'text-red-500 fill-red-500' : 'text-gray-500' }}" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </button>
                @endif
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusColors[$event->status] ?? 'bg-gray-50 text-gray-600' }}">
                    {{ ucfirst($event->status) }}
                </span>
            </div>
        </div>

        {{-- Banner --}}
        @if ($event->banner_image)
            <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-border">
                <div class="h-48 sm:h-64 bg-cover bg-center" style="background-image: url({{ asset('storage/' . $event->banner_image) }})"></div>
            </div>
        @endif

        {{-- Main Content --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Left Column — Main Details --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- About card --}}
                @if ($event->description)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">About</h2>
                        </div>
                        <div class="px-5 py-4">
                            <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-400 leading-relaxed">
                                {!! $event->description !!}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Requirements card --}}
                @if ($event->requirements)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Requirements</h2>
                        </div>
                        <div class="px-5 py-4">
                            <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-400 leading-relaxed">
                                {!! $event->requirements !!}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Agenda card --}}
                @if ($agendaItems->isNotEmpty())
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Agenda</h2>
                        </div>
                        <div class="px-5 py-4">
                            <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                @foreach ($agendaItems as $item)
                                    <li class="py-3 first:pt-0 last:pb-0">
                                        <div class="flex items-start gap-3">
                                            @if ($item->start_time)
                                                <div class="shrink-0 w-16 text-right">
                                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ substr($item->start_time, 0, 5) }}</p>
                                                    @if ($item->end_time)
                                                        <p class="text-[10px] text-gray-400">{{ substr($item->end_time, 0, 5) }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->title }}</p>
                                                @if ($item->description)
                                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item->description }}</p>
                                                @endif
                                                @if ($item->speaker)
                                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->speaker }}</p>
                                                @endif
                                            </div>
                                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                {{ ucfirst($item->type) }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Instructors card --}}
                @if ($event->instructors->isNotEmpty())
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Instructors</h2>
                        </div>
                        <div class="px-5 py-4">
                            <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                @foreach ($event->instructors as $inst)
                                    <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-sm font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                            {{ $inst->name[0] }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $inst->name }}</p>
                                            <p class="text-xs text-gray-500">{{ str_replace('_', ' ', $inst->pivot->role) }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Resources card --}}
                @if ($event->resources->isNotEmpty())
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Resources</h2>
                        </div>
                        <div class="px-5 py-4">
                            <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                @foreach ($event->resources as $res)
                                    <li class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $res->title }}</p>
                                            <p class="text-xs text-gray-500">{{ ucfirst($res->type) }}</p>
                                        </div>
                                        @if ($res->display_url)
                                            <a href="{{ $res->display_url }}" target="_blank" rel="noopener noreferrer"
                                               class="ml-3 shrink-0 rounded bg-gray-900 px-1.5 py-0.5 text-[10px] font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                                View
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Community Feedback --}}
                @if ($feedbackStats['feedback_count'] > 0)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Community Feedback</h2>
                        </div>
                        <div class="px-5 py-4 space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $feedbackStats['average_rating'] }}</p>
                                    <div class="text-yellow-500 text-sm">
                                        {{ str_repeat('★', (int) round($feedbackStats['average_rating'])) }}{{ str_repeat('☆', 5 - (int) round($feedbackStats['average_rating'])) }}
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Based on {{ $feedbackStats['feedback_count'] }} {{ Str::plural('response', $feedbackStats['feedback_count']) }}
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                @foreach (range(5, 1) as $star)
                                    @php
                                        $count = $feedbackStats['rating_distribution'][$star] ?? 0;
                                        $pct = $feedbackStats['feedback_count'] > 0 ? round(($count / $feedbackStats['feedback_count']) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="w-8 text-right text-gray-500 dark:text-gray-400">{{ $star }}★</span>
                                        <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-2 rounded-full bg-yellow-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="w-8 text-xs text-gray-400 dark:text-gray-500">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Recent Feedback --}}
                @if ($recentFeedbacks->isNotEmpty())
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Feedback</h2>
                        </div>
                        <div class="px-5 py-4 space-y-4">
                            @foreach ($recentFeedbacks as $fb)
                                <div class="{{ !$loop->last ? 'border-b border-gray-100 dark:border-border pb-4' : '' }}">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            @if ($fb->is_anonymous)
                                                <span class="text-sm text-gray-500 dark:text-gray-400 italic">Anonymous</span>
                                            @else
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fb->user->name }}</span>
                                            @endif
                                        </div>
                                        <span class="text-yellow-500 text-sm">{{ str_repeat('★', $fb->rating) }}{{ str_repeat('☆', 5 - $fb->rating) }}</span>
                                    </div>
                                    @if ($fb->feedback_text)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ Str::limit($fb->feedback_text, 200) }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- User Feedback card --}}
                @if ($userFeedback)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Your Feedback</h2>
                        </div>
                        <div class="px-5 py-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Overall</span>
                                <span class="text-yellow-500">{{ str_repeat('★', $userFeedback->rating) }}{{ str_repeat('☆', 5 - $userFeedback->rating) }}</span>
                            </div>
                            @if ($userFeedback->content_quality)
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Content</span>
                                    <span class="text-yellow-500">{{ str_repeat('★', $userFeedback->content_quality) }}{{ str_repeat('☆', 5 - $userFeedback->content_quality) }}</span>
                                </div>
                            @endif
                            @if ($userFeedback->instructor_rating)
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Instructor</span>
                                    <span class="text-yellow-500">{{ str_repeat('★', $userFeedback->instructor_rating) }}{{ str_repeat('☆', 5 - $userFeedback->instructor_rating) }}</span>
                                </div>
                            @endif
                            @if ($userFeedback->pace_rating)
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Pace</span>
                                    <span class="text-yellow-500">{{ str_repeat('★', $userFeedback->pace_rating) }}{{ str_repeat('☆', 5 - $userFeedback->pace_rating) }}</span>
                                </div>
                            @endif
                            @if ($userFeedback->feedback_text)
                                <p class="text-gray-600 dark:text-gray-400 italic mt-2">"{{ $userFeedback->feedback_text }}"</p>
                            @endif
                            @if ($userFeedback->suggestions)
                                <div class="mt-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/50">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Suggestions</p>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $userFeedback->suggestions }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($canSubmitFeedback && !$feedbackOpen)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="px-5 py-4 text-center">
                            <button wire:click="$set('feedbackOpen', true)"
                                    class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                                Leave Feedback
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Feedback Form --}}
                @if ($canSubmitFeedback && $feedbackOpen)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Share Your Feedback</h2>
                            <button wire:click="$set('feedbackOpen', false)"
                                    class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">Cancel</button>
                        </div>
                        <div class="px-5 py-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Overall Rating *</label>
                                <div class="flex gap-1">
                                    @foreach ([1, 2, 3, 4, 5] as $n)
                                        <button type="button" wire:click="$set('feedbackRating', {{ $n }})"
                                                class="text-2xl transition-colors {{ $n <= $this->feedbackRating ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }} hover:text-yellow-400">★</button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content Quality</label>
                                <div class="flex gap-1">
                                    @foreach ([1, 2, 3, 4, 5] as $n)
                                        <button type="button" wire:click="$set('feedbackContentQuality', {{ $n }})"
                                                class="text-2xl transition-colors {{ $n <= ($this->feedbackContentQuality ?? 0) ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }} hover:text-yellow-400">★</button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instructor</label>
                                <div class="flex gap-1">
                                    @foreach ([1, 2, 3, 4, 5] as $n)
                                        <button type="button" wire:click="$set('feedbackInstructorRating', {{ $n }})"
                                                class="text-2xl transition-colors {{ $n <= ($this->feedbackInstructorRating ?? 0) ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }} hover:text-yellow-400">★</button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pace</label>
                                <div class="flex gap-1">
                                    @foreach ([1, 2, 3, 4, 5] as $n)
                                        <button type="button" wire:click="$set('feedbackPaceRating', {{ $n }})"
                                                class="text-2xl transition-colors {{ $n <= ($this->feedbackPaceRating ?? 0) ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' }} hover:text-yellow-400">★</button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comments</label>
                                <textarea wire:model="feedbackText" rows="3"
                                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-border dark:bg-card dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white"
                                          placeholder="What did you think of the event?"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Suggestions</label>
                                <textarea wire:model="feedbackSuggestions" rows="2"
                                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-border dark:bg-card dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white"
                                          placeholder="Any suggestions for improvement?"></textarea>
                            </div>
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                <input type="checkbox" wire:model="feedbackAnonymous"
                                       class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-border dark:bg-gray-900 dark:focus:ring-white">
                                Submit anonymously
                            </label>
                            <button wire:click="submitFeedback" wire:loading.attr="disabled"
                                    class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                                <span wire:loading.remove>Submit Feedback</span>
                                <span wire:loading>Submitting...</span>
                            </button>
                        </div>
                    </div>
                @endif
                {{-- Related Events --}}
                @if ($relatedEvents->isNotEmpty())
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Related Events</h2>
                        </div>
                        <div class="px-5 py-4">
                            <ul class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                @foreach ($relatedEvents as $rel)
                                    <li class="py-2.5 first:pt-0 last:pb-0">
                                        <a href="{{ route('events.show', $rel) }}" wire:navigate class="flex items-center gap-3 group">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-300">{{ $rel->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $rel->start_date->format('D, M j · g:i A') }}</p>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column — Sidebar --}}
            <div class="space-y-4">

                {{-- Details card --}}
                <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Details</h3>
                    </div>
                    <div class="px-5 py-4 space-y-3 text-sm">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColors[$event->type] ?? 'bg-gray-50 text-gray-600' }}">
                                {{ $eventTypes[$event->type] ?? ucfirst($event->type) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $event->start_date->format('D, M j, Y g:i A') }}</span>
                        </div>
                        @if ($event->end_date)
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Until {{ $event->end_date->format('D, M j, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if ($event->location)
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $event->location }}</span>
                            </div>
                        @endif
                        @if ($event->organizer)
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Organized by {{ $event->organizer->name }}</span>
                            </div>
                        @endif
                        @if ($event->is_recurring)
                            <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                Recurring
                            </span>
                        @endif
                        @if ($event->registration_fee > 0)
                            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Fee: UGX {{ number_format($event->registration_fee) }}</span>
                            </div>
                        @endif
                        @if ($event->max_participants)
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-500 dark:text-gray-400">Capacity</span>
                                    <span class="font-medium {{ $event->is_full ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $event->registered_count }}/{{ $event->max_participants }}
                                    </span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-2 rounded-full bg-gray-900 dark:bg-white"
                                         style="width: {{ min(100, ($event->registered_count / $event->max_participants) * 100) }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $event->remaining_spots }} spot{{ $event->remaining_spots !== 1 ? 's' : '' }} remaining
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Countdown --}}
                @if ($isFuture)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card"
                         x-data="{
                             target: new Date('{{ $event->start_date->format('Y/m/d H:i:s') }}').getTime(),
                             now: new Date().getTime(),
                             diff: 0,
                             init() { this.tick(); setInterval(() => this.tick(), 1000); },
                             tick() { this.now = new Date().getTime(); this.diff = Math.max(0, this.target - this.now); },
                             get d() { return Math.floor(this.diff / 86400000); },
                             get h() { return Math.floor((this.diff % 86400000) / 3600000); },
                             get m() { return Math.floor((this.diff % 3600000) / 60000); },
                             get s() { return Math.floor((this.diff % 60000) / 1000); }
                         }">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Starts In</h3>
                        </div>
                        <div class="px-5 py-4">
                            <div class="grid grid-cols-4 gap-2 text-center" x-show="diff > 0">
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="d"></p>
                                    <p class="text-xs text-gray-500">days</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="h"></p>
                                    <p class="text-xs text-gray-500">hours</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="m"></p>
                                    <p class="text-xs text-gray-500">mins</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="s"></p>
                                    <p class="text-xs text-gray-500">secs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Add to Calendar --}}
                <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Add to Calendar</h3>
                    </div>
                    <div class="px-5 py-4 space-y-2">
                        <a href="https://www.google.com/calendar/render?action=TEMPLATE&text={{ urlencode($event->title) }}&dates={{ $gCalStart }}/{{ $gCalEnd }}&details={{ urlencode(strip_tags($event->description ?? '')) }}&location={{ urlencode($event->location ?? '') }}"
                           target="_blank" rel="noopener noreferrer"
                           class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path d="M7.5 2.5a1.5 1.5 0 0 1 3 0v1h3v-1a1.5 1.5 0 0 1 3 0v1H19a2 2 0 0 1 2 2v1H3V5.5a2 2 0 0 1 2-2h2.5v-1zM3 9.5h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-10zm5 3a1 1 0 1 0 0 2h8a1 1 0 1 0 0-2H8zm0 4a1 1 0 1 0 0 2h5a1 1 0 1 0 0-2H8z"/></svg>
                            Google Calendar
                        </a>
                        <a href="data:text/calendar;charset=utf-8,BEGIN:VCALENDAR%0AVERSION:2.0%0ABEGIN:VEVENT%0ADTSTART:{{ $gCalStart }}%0ADTEND:{{ $gCalEnd }}%0ASUMMARY:{{ rawurlencode($event->title) }}%0ADESCRIPTION:{{ rawurlencode(strip_tags($event->description ?? '')) }}%0ALOCATION:{{ rawurlencode($event->location ?? '') }}%0AEND:VEVENT%0AEND:VCALENDAR"
                           download="{{ $event->slug }}.ics"
                           class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            iCal (.ics)
                        </a>
                    </div>
                </div>

                {{-- Share --}}
                <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Share</h3>
                    </div>
                    <div class="px-5 py-4 space-y-2">
                        <button x-data="{ copied: false }"
                                x-on:click="navigator.clipboard.writeText('{{ $shareUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="flex items-center gap-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                        </button>
                        <a href="https://twitter.com/intent/tweet?text={{ $twitterShareText }}&url={{ urlencode($shareUrl) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            <svg class="h-4 w-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            Share on X
                        </a>
                    </div>
                </div>

                {{-- Map --}}
                @if ($event->location)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Location</h3>
                        </div>
                        <div class="px-5 py-4">
                            <a href="https://www.openstreetmap.org/search?query={{ urlencode($event->location) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $event->location }}</span>
                                <svg class="h-3 w-3 ml-auto shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- QR Code --}}
                @if ($checkInCode)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Your Check-In Code</h3>
                        </div>
                        <div class="px-5 py-4 text-center">
                            <div class="inline-block bg-white dark:bg-white p-2 rounded-lg mb-3">
                                {!! QrCode::size(140)->generate($checkInCode) !!}
                            </div>
                            <p class="text-xs font-mono tracking-wider text-gray-500 dark:text-gray-400 select-all">{{ $checkInCode }}</p>
                        </div>
                    </div>
                @endif

                {{-- External Link --}}
                @if ($event->external_link)
                    <a href="{{ $event->external_link }}" target="_blank" rel="noopener noreferrer"
                       class="dashboard-card flex items-center justify-center gap-2 rounded-sm border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md dark:border-border dark:bg-card focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Register on External Site</span>
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif

                {{-- Registration --}}
                @if ($event->registration_required && !$event->external_link)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="px-5 py-4 text-center">
                            @if (!$isAuthenticated)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Login to register</p>
                                <a href="{{ route('auth.login') }}"
                                   class="block rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:focus:ring-white text-center">
                                    Login
                                </a>
                            @elseif ($isRegistered)
                                <div>
                                    <svg class="w-12 h-12 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414L10 11.586l-1.293-1.293z" clip-rule="evenodd"/></svg>
                                </div>
                                <p class="font-semibold text-gray-900 dark:text-white mb-1">You're Registered!</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                    Registered {{ $registration?->registered_at?->format('M j, Y') }}
                                </p>
                                @if ($hasCertificate)
                                    <a href="{{ route('events.certificate', [$event->slug, 'registration' => $registration?->id]) }}"
                                       target="_blank"
                                       class="block mb-3 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-500 focus:ring-2 focus:ring-green-600 focus:ring-offset-2 dark:bg-green-500 dark:hover:bg-green-400 text-center">
                                        Download Certificate
                                    </a>
                                @endif
                                <button wire:click="unregister" wire:loading.attr="disabled"
                                         class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">

                                    <span wire:loading.remove>Cancel Registration</span>
                                    <span wire:loading>Processing...</span>
                                </button>
                            @elseif ($isWaitlisted)
                                <div>
                                    <svg class="w-12 h-12 mx-auto text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                </div>
                                <p class="font-semibold text-gray-900 dark:text-white mb-1">On the Waitlist</p>
                                @php $waitlistPos = $event->getWaitlistPositionForUser(auth()->user()); @endphp
                                @if ($waitlistPos)
                                    <p class="text-sm font-medium text-amber-600 dark:text-amber-400 mb-1">Position #{{ $waitlistPos }}</p>
                                @endif
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">You'll be auto-promoted if a spot opens.</p>
                                <button wire:click="unregister" wire:loading.attr="disabled"
                                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                                    <span wire:loading.remove>Leave Waitlist</span>
                                    <span wire:loading>Processing...</span>
                                </button>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    {{ $event->is_full && $event->waitlist_enabled
                                        ? 'Event is full — join the waitlist'
                                        : ($event->is_full
                                            ? 'This event is full'
                                            : $event->remaining_spots . ' spot' . ($event->remaining_spots !== 1 ? 's' : '') . ' remaining')
                                    }}
                                </p>
                                @if ($event->registration_deadline)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        Deadline: {{ $event->registration_deadline->format('M j, Y g:i A') }}
                                    </p>
                                @endif
                                @if ($event->is_full && !$event->waitlist_enabled)
                                    <p class="text-sm font-medium text-red-600 dark:text-red-400">This event is full</p>
                                @else
                                    <button wire:click="register" wire:loading.attr="disabled"
                                            class="mt-2 rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50 focus:ring-2 focus:ring-offset-2
                                                {{ $event->is_full
                                                    ? 'bg-amber-600 hover:bg-amber-500 focus:ring-amber-500'
                                                    : 'bg-gray-900 hover:bg-gray-800 focus:ring-gray-900 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white' }}">
                                        <span wire:loading.remove>{{ $event->is_full ? 'Join Waitlist' : 'Register Now' }}</span>
                                        <span wire:loading>Processing...</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                {{-- RSVP card --}}
                <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">RSVP</h3>
                    </div>
                    <div class="px-5 py-4">
                        @if (!$isAuthenticated)
                            <p class="text-sm text-gray-500 dark:text-gray-400">Login to RSVP</p>
                        @elseif ($event->rsvp_deadline && $event->rsvp_deadline->isPast())
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">RSVP Closed</p>
                        @elseif ($isAttending)
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Going
                                </span>
                                <div class="flex gap-2">
                                    <button wire:click="rsvpMaybe" wire:loading.attr="disabled"
                                            class="text-sm text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                        Maybe
                                    </button>
                                    <button wire:click="$set('confirmCancelRsvpId', '{{ $registration->id }}')"
                                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                        Can't Go
                                    </button>
                                </div>
                            </div>
                            @if ($confirmCancelRsvpId === $registration?->id)
                                <div class="mt-3 space-y-2 rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-border dark:bg-card/50">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Cancel your RSVP?</p>
                                    <div class="flex gap-2">
                                        <button wire:click="cancelRsvp" wire:loading.attr="disabled"
                                                class="flex-1 rounded-lg bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                                            <span wire:loading.remove>Yes, Cancel</span>
                                            <span wire:loading>Processing...</span>
                                        </button>
                                        <button wire:click="$set('confirmCancelRsvpId', null)" wire:loading.attr="disabled"
                                                class="flex-1 rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                            Keep
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @elseif ($isMaybe)
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-sm font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    Tentative
                                </span>
                                <div class="flex gap-2">
                                    <button wire:click="rsvp" wire:loading.attr="disabled"
                                            class="text-sm text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                        Going
                                    </button>
                                    <button wire:click="$set('confirmCancelRsvpId', '{{ $registration->id }}')"
                                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                        Can't Go
                                    </button>
                                </div>
                            </div>
                            @if ($confirmCancelRsvpId === $registration?->id)
                                <div class="mt-3 space-y-2 rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-border dark:bg-card/50">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Cancel your RSVP?</p>
                                    <div class="flex gap-2">
                                        <button wire:click="cancelRsvp" wire:loading.attr="disabled"
                                                class="flex-1 rounded-lg bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                                            <span wire:loading.remove>Yes, Cancel</span>
                                            <span wire:loading>Processing...</span>
                                        </button>
                                        <button wire:click="$set('confirmCancelRsvpId', null)" wire:loading.attr="disabled"
                                                class="flex-1 rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                            Keep
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @elseif ($event->rsvp_deadline && $event->rsvp_deadline->isPast())
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">RSVP Closed</p>
                        @elseif ($event->is_full && !$event->waitlist_enabled)
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">Event Full</p>
                        @else
                            <div class="flex flex-col gap-2">
                                @if ($event->rsvp_deadline)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">RSVP by {{ $event->rsvp_deadline->format('M j, Y g:i A') }}</p>
                                @endif
                                <div class="flex gap-2">
                                    <button wire:click="rsvp" wire:loading.attr="disabled"
                                            class="flex-1 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                                        <span wire:loading.remove>Going</span>
                                        <span wire:loading>Processing...</span>
                                    </button>
                                    <button wire:click="rsvpMaybe" wire:loading.attr="disabled"
                                            class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:focus:ring-white">
                                        <span wire:loading.remove>Maybe</span>
                                        <span wire:loading>Processing...</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
