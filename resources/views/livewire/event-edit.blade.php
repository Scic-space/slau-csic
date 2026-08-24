<div class="py-6">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('events.show', $event->slug) }}" wire:navigate class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                &larr; Back to event
            </a>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Event</h1>
                    @php
                        $statusColors = [
                            'scheduled' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'published' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'ongoing' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                            'completed' => 'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                            'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$event->status] ?? 'bg-gray-50 text-gray-600' }}">
                        {{ ucfirst($event->status) }}
                    </span>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Title</label>
                        <input type="text" wire:model="title" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                        @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea wire:model="description" rows="5" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white"></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Type</label>
                            <select wire:model="type" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                <option value="workshop">Workshop</option>
                                <option value="competition">Competition</option>
                                <option value="ctf">CTF</option>
                                <option value="bootcamp">Bootcamp</option>
                                <option value="awareness_campaign">Awareness Campaign</option>
                                <option value="talk">Talk/Seminar</option>
                                <option value="social">Social</option>
                                <option value="hackathon">Hackathon</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                            <input type="text" wire:model="location" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date &amp; Time</label>
                            <input type="datetime-local" wire:model="startDate" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('startDate') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date &amp; Time</label>
                            <input type="datetime-local" wire:model="endDate" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('endDate') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Participants</label>
                            <input type="number" min="1" wire:model="maxParticipants" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Fee</label>
                            <input type="number" min="0" step="0.01" wire:model="registrationFee" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('registrationFee') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Deadline</label>
                            <input type="datetime-local" wire:model="registrationDeadline" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('registrationDeadline') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            <label class="mt-3 block text-sm font-medium text-gray-700 dark:text-gray-300">RSVP Deadline</label>
                            <input type="datetime-local" wire:model="rsvpDeadline" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('rsvpDeadline') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">External Link</label>
                            <input type="url" placeholder="https://" wire:model="externalLink" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                            @error('externalLink') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="registrationRequired" class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-white">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Registration Required</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="waitlistEnabled" class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-white">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Enable Waitlist (when full)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="isPublic" class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-white">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Public Event</span>
                    </label>
                </div>

                @if ($categories->isNotEmpty())
                    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <h2 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Categories</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($categories as $cat)
                                <button
                                    type="button"
                                    wire:click="toggleCategory({{ $cat['id'] }})"
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
                                    style="border: 1px solid {{ $cat['color'] }}50; background-color: {{ in_array($cat['id'], $selectedCategories) ? $cat['color'] : $cat['color'] . '18' }}; color: {{ in_array($cat['id'], $selectedCategories) ? '#fff' : $cat['color'] }}"
                                >
                                    {{ $cat['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Agenda</h2>
                        <button type="button" wire:click="addAgendaItem"
                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            + Add Item
                        </button>
                    </div>

                    @if (empty($agendaItems))
                        <p class="text-sm text-gray-400 dark:text-gray-500">No agenda items yet.</p>
                    @endif

                    <div class="space-y-3">
                        @foreach ($agendaItems as $index => $item)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Item {{ $index + 1 }}</span>
                                    <button type="button" wire:click="removeAgendaItem({{ $index }})"
                                            class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                        Remove
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Title</label>
                                        <input type="text" wire:model="agendaItems.{{ $index }}.title"
                                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                                        @error('agendaItems.'.$index.'.title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Start Time</label>
                                        <input type="time" wire:model="agendaItems.{{ $index }}.start_time"
                                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                        @error('agendaItems.'.$index.'.start_time') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">End Time</label>
                                        <input type="time" wire:model="agendaItems.{{ $index }}.end_time"
                                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                        @error('agendaItems.'.$index.'.end_time') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Speaker</label>
                                        <input type="text" wire:model="agendaItems.{{ $index }}.speaker"
                                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Type</label>
                                        <select wire:model="agendaItems.{{ $index }}.type"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                            <option value="session">Session</option>
                                            <option value="break">Break</option>
                                            <option value="workshop">Workshop</option>
                                            <option value="talk">Talk</option>
                                            <option value="panel">Panel</option>
                                            <option value="activity">Activity</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Description</label>
                                        <textarea wire:model="agendaItems.{{ $index }}.description" rows="2"
                                                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition placeholder-gray-400 focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:border-white dark:focus:ring-white"></textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <h2 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Recurrence Settings</h2>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="recurrenceEnabled" class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-white">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Recurring Event</span>
                    </label>

                    @if ($recurrenceEnabled)
                        <div class="ml-6 mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pattern</label>
                                <select wire:model="recurrencePattern" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                                @error('recurrencePattern') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ends At</label>
                                <input type="date" wire:model="recurrenceEndsAt" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" wire:loading.attr="disabled" class="flex-1 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">
                    <span wire:loading.remove>Update Event</span>
                    <span wire:loading>Saving...</span>
                </button>
                <a href="{{ route('events.show', $event->slug) }}" wire:navigate class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-white">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
