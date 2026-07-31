@php
    $config = \App\Notifications\NotificationTypeConfig::for($notification->type);
    $data = $notification->data;
    $message = $data['message'] ?? $data['subject'] ?? 'Notification';
    $actionUrl = $data['action_url'] ?? null;
    $eventSlug = $data['event_slug'] ?? null;
    $fullMessage = $data['message'] ?? $data['subject'] ?? '';
    $isUnread = is_null($notification->read_at);
@endphp
<div class="py-6 lg:py-8">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

        {{-- Back link --}}
        <div class="mb-6">
            <a href="{{ route('notifications.index') }}"
               wire:navigate
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to all notifications
            </a>
        </div>

        {{-- Notification Card --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

            {{-- Header --}}
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-700/50">
                <div class="flex items-start gap-4">
                    {{-- Type Icon --}}
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $config['iconBg'] }}">
                            @if ($config['icon'] === 'calendar')
                                <svg class="h-6 w-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @elseif ($config['icon'] === 'ballot')
                                <svg class="h-6 w-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            @elseif ($config['icon'] === 'dollar')
                                <svg class="h-6 w-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif ($config['icon'] === 'user')
                                <svg class="h-6 w-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @elseif ($config['icon'] === 'academic')
                                <svg class="h-6 w-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                            @elseif ($config['icon'] === 'trophy')
                                <svg class="h-6 w-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            @else
                                <svg class="h-6 w-6 {{ $config['textColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @endif
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $message }}
                            </h1>
                            @if ($isUnread)
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">New</span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $config['iconBg'] }} {{ $config['textColor'] }}">
                                {{ ucfirst($config['category']) }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $notification->created_at->format('M d, Y \a\t g:i A') }}
                            </span>
                            <span class="text-sm text-gray-400 dark:text-gray-500">
                                ({{ $notification->created_at->diffForHumans() }})
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-6">
                <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 dark:prose-invert">
                    <p>{!! nl2br(e($fullMessage)) !!}</p>
                </div>

                {{-- Data fields --}}
                @if (count($data) > 1)
                    <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Details</h3>
                        <dl class="mt-3 space-y-2">
                            @foreach ($data as $key => $value)
                                @if (!in_array($key, ['message', 'subject', 'action_url']) && is_string($value))
                                    <div class="flex items-baseline gap-3">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 w-24 sm:w-32 flex-shrink-0">{{ str_replace('_', ' ', ucfirst($key)) }}</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>

            {{-- Footer Actions --}}
            <div class="flex items-center gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-700/50">
                @if ($actionUrl)
                    <a href="{{ url($actionUrl) }}"
                       wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500">
                        View Details
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif

                <div class="flex-1"></div>

                <button wire:click="deleteNotification"
                        wire:confirm="Delete this notification?"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition-colors hover:bg-red-50 hover:text-red-600 hover:border-red-200 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-red-900/20 dark:hover:text-red-400 dark:hover:border-red-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
