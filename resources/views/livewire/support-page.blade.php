<div class="py-6 lg:py-8">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Help & Support</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Find answers below or send us a message — we usually respond within 24 hours.
            </p>
        </div>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,_1fr)_minmax(0,_340px)] items-start">

            {{-- Left column: FAQ + Form --}}
            <div class="space-y-8">

                {{-- FAQ --}}
                <section x-data="{ openFaq: null }">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Frequently Asked Questions</h2>

                    <div class="space-y-3">
                        @php
                            $faqs = [
                                [
                                    'question' => 'How do I renew my membership?',
                                    'answer' => 'Go to your Profile page and click the "Renew Membership" button. You can also pay via the Treasurer on campus.',
                                ],
                                [
                                    'question' => 'How do I register for an event?',
                                    'answer' => 'Visit the Events page, find the event you want, and click "Register" or "RSVP". You will receive a confirmation notification.',
                                ],
                                [
                                    'question' => 'Where can I download my certificate?',
                                    'answer' => 'After passing an exam, go to Exams → My Certificates. You can download your certificate as a PDF from there.',
                                ],
                                [
                                    'question' => 'How do I check my fines?',
                                    'answer' => 'Navigate to the Fines page from the sidebar. You can view all fines, their status, and pay outstanding amounts.',
                                ],
                                [
                                    'question' => 'How do I update my profile photo?',
                                    'answer' => 'Go to your Profile page, click the camera icon on your avatar, upload a new photo, and save.',
                                ],
                                [
                                    'question' => 'How do I change my password?',
                                    'answer' => 'Go to your Profile page and scroll to the "Change Password" section. You will need your current password.',
                                ],
                            ];
                        @endphp

                        @foreach($faqs as $i => $faq)
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
                                 :class="openFaq === {{ $i }} ? 'shadow-sm' : ''">
                                <button
                                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-medium text-gray-900 dark:text-white transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}">
                                    <span>{{ $faq['question'] }}</span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
                                         :class="{ 'rotate-180': openFaq === {{ $i }} }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="openFaq === {{ $i }}" x-collapse x-cloak>
                                    <div class="border-t border-gray-100 px-5 py-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
                                        {{ $faq['answer'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Contact Form --}}
                <section>
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Send Us a Message</h2>

                    @if($submitted)
                        <div class="rounded-xl border border-green-200 bg-green-50 p-6 text-center dark:border-green-800 dark:bg-green-900/20">
                            <svg class="mx-auto h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-3 text-sm font-semibold text-green-800 dark:text-green-300">Message Sent</h3>
                            <p class="mt-1 text-sm text-green-600 dark:text-green-400">Thank you! We will get back to you soon.</p>
                            <button wire:click="$set('submitted', false)"
                                    class="mt-4 text-sm font-medium text-green-700 underline hover:text-green-900 dark:text-green-300 dark:hover:text-green-100">
                                Send another message
                            </button>
                        </div>
                    @else
                        <form wire:submit="submit" class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="support-name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <input type="text" id="support-name" wire:model="name"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-400"
                                           placeholder="Type your name">
                                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="support-email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input type="email" id="support-email" wire:model="email"
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-400"
                                           placeholder="Type your email address">
                                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="support-subject" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Topic</label>
                                <select id="support-subject" wire:model="topic"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-indigo-400">
                                    <option value="">Select a topic</option>
                                    <option value="Membership">Membership</option>
                                    <option value="Events">Events</option>
                                    <option value="Fines">Fines</option>
                                    <option value="Exams & Certificates">Exams & Certificates</option>
                                    <option value="Technical Issue">Technical Issue</option>
                                    <option value="General">General</option>
                                </select>
                                @error('topic') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="support-message" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                                <textarea id="support-message" wire:model="message" rows="5"
                                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-400"
                                          placeholder="Describe your question or issue"></textarea>
                                @error('message') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                    <svg wire:loading.remove class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <svg wire:loading class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Send Message
                                </button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>

            {{-- Right column: Officers --}}
            <aside class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Club Officers</h3>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Reach out directly to the right person for your concern.</p>

                    <div class="space-y-3">
                        @php
                            $officers = [
                                ['role' => 'President', 'scope' => 'General concerns, club direction'],
                                ['role' => 'Vice President', 'scope' => 'Event coordination, member support'],
                                ['role' => 'Treasurer', 'scope' => 'Fines, payments, transactions'],
                                ['role' => 'Secretary', 'scope' => 'Membership records, documentation'],
                            ];
                        @endphp

                        @foreach($officers as $officer)
                            <div class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3.5 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/40">
                                    <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $officer['role'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $officer['scope'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('notifications.index') }}" wire:navigate
                               class="flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                View Notifications
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('profile.edit') }}" wire:navigate
                               class="flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Edit Profile
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('fines.index') }}" wire:navigate
                               class="flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                                Check My Fines
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

        </div>
    </div>
</div>
