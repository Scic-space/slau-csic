<div class="py-8">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Create Event</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set up a new club event for members</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Event Details</h2>
            </div>
            <div class="space-y-6 p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                        <input type="text" wire:model="title" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white" placeholder="e.g. Python for Pentesters">
                        @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea wire:model="description" rows="4" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white" placeholder="Describe your event..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                        <select wire:model="type" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @foreach ($eventTypes as $t)
                                <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                        <input type="text" wire:model="location" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white" placeholder="Room 301 or virtual">
                        @error('location') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date & Time</label>
                        <input type="datetime-local" wire:model="startDate" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                        @error('startDate') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date & Time</label>
                        <input type="datetime-local" wire:model="endDate" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                        @error('endDate') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Registration & Capacity</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Participants</label>
                            <input type="number" wire:model="maxParticipants" min="1" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white" placeholder="Leave empty for unlimited">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Fee (UGX)</label>
                            <input type="number" wire:model="registrationFee" min="0" step="0.01" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white" placeholder="0 = Free">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Deadline</label>
                            <input type="datetime-local" wire:model="registrationDeadline" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('registrationDeadline') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">RSVP Deadline</label>
                            <input type="datetime-local" wire:model="rsvpDeadline" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('rsvpDeadline') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-6 sm:col-span-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="registrationRequired" class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-white">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Requires Registration</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="waitlistEnabled" class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-white">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Enable Waitlist</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="isPublic" class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-white">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Public Event</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Categories</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($categories as $cat)
                            <button type="button" wire:click="toggleCategory({{ $cat['id'] }})"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition focus:ring-2 focus:ring-gray-900 dark:focus:ring-white"
                                :class="{
                                    'ring-2 ring-offset-1': {{ in_array($cat['id'], $selectedCategories) ? 'true' : 'false' }}
                                }"
                                style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }}">
                                {{ $cat['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">External Link (optional)</label>
                    <input type="url" wire:model="externalLink" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white" placeholder="https://forms.google.com/...">
                    @error('externalLink') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 pt-6 dark:border-gray-700">
                    <a href="{{ route('events.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">&larr; Back to Events</a>
                    <button wire:click="create" wire:loading.attr="disabled"
                            class="rounded-lg bg-gray-900 px-6 py-2 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                        <span wire:loading.remove>Create Event</span>
                        <span wire:loading>Creating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
