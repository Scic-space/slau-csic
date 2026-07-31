<div class="min-h-screen bg-gradient-to-b from-gray-50 via-white to-gray-50 dark:from-[#0a0a0f] dark:via-[#07070b] dark:to-[#0a0a0f]">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- Back link --}}
        <a href="{{ route('announcements.index') }}" wire:navigate class="mb-8 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Announcements
        </a>

        @if ($announcement)
            @php
                $isActive = $announcement['is_active'];
                $isExpired = $announcement['is_expired'];
            @endphp

            {{-- Expired banner --}}
            @if ($isExpired)
                <div class="mb-6 rounded-2xl border border-gray-200/60 bg-gray-50 p-4 dark:border-white/[0.04] dark:bg-white/[0.01]">
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-500">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        This announcement has expired.
                    </div>
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-8">
                {{-- Badges --}}
                <div class="flex items-center gap-2 flex-wrap mb-4">
                    @switch($announcement['type'])
                        @case('urgent')
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                Urgent
                            </span>
                            @break
                        @case('event')
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Event
                            </span>
                            @break
                        @case('meeting')
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/10 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Meeting
                            </span>
                            @break
                        @case('achievement')
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                Achievement
                            </span>
                            @break
                        @default
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                General
                            </span>
                    @endswitch

                    @if ($isActive)
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/10 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20">Active</span>
                    @endif
                </div>

                {{-- Title --}}
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    {{ $announcement['title'] }}
                </h1>

                {{-- Meta --}}
                <div class="mt-4 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ $announcement['author'] }}
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">&middot;</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ \Carbon\Carbon::parse($announcement['published_at'])->format('M j, Y \a\t g:i A') }}
                    </span>
                    @if ($announcement['expires_at'])
                        <span class="text-gray-300 dark:text-gray-700">&middot;</span>
                        <span class="inline-flex items-center gap-1.5 @if ($isExpired) text-gray-400 dark:text-gray-600 @endif">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Expires {{ \Carbon\Carbon::parse($announcement['expires_at'])->format('M j, Y') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Divider --}}
            <div class="h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent dark:via-white/[0.06]"></div>

            {{-- Content --}}
            <article class="prose prose-gray mt-8 max-w-none prose-headings:font-semibold prose-a:text-indigo-600 prose-img:rounded-2xl dark:prose-invert dark:prose-a:text-indigo-400 @if ($isExpired) opacity-70 @endif">
                {!! $announcement['content'] !!}
            </article>

            {{-- Bottom nav --}}
            <div class="mt-12 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent dark:via-white/[0.06]"></div>
            <div class="mt-6 flex justify-center">
                <a href="{{ route('announcements.index') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-200 transition-all hover:bg-gray-50 hover:text-indigo-600 hover:ring-indigo-200 dark:text-gray-400 dark:ring-white/[0.06] dark:hover:bg-white/[0.03] dark:hover:text-indigo-400 dark:hover:ring-indigo-500/20">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    All Announcements
                </a>
            </div>
        @endif
    </div>
</div>
