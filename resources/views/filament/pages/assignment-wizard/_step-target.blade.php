<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 text-sm font-bold text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300">1</span>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">What are you staffing?</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose the context for this assignment, or create a custom one.</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
                ['type' => 'event', 'label' => 'Event', 'desc' => 'Staff an event with organizers and helpers', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['type' => 'project', 'label' => 'Project', 'desc' => 'Build a project team', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ['type' => 'custom', 'label' => 'Custom', 'desc' => 'Any other role assignment', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
            ] as $opt)
                <button
                    type="button"
                    wire:click="$set('targetType', '{{ $opt['type'] }}')"
                    class="group rounded-xl border-2 p-5 text-left transition-all duration-200
                        {{ $targetType === $opt['type']
                            ? 'border-primary-500 bg-primary-50 shadow-sm dark:border-primary-400 dark:bg-primary-900/20'
                            : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm dark:border-border dark:bg-card dark:hover:border-gray-600' }}"
                >
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg transition-all duration-200 {{ $targetType === $opt['type'] ? 'bg-primary-100 text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $opt['icon'] }}"/></svg>
                        </div>
                        <span class="font-bold {{ $targetType === $opt['type'] ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">{{ $opt['label'] }}</span>
                    </div>
                    <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $opt['desc'] }}</p>
                </button>
            @endforeach
        </div>

        <div class="space-y-4">
            @if ($targetType === 'event')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Event</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <select wire:model="targetId" class="block w-full rounded-lg border bg-white py-2.5 pl-10 pr-3 text-sm shadow-sm transition focus:outline-none focus:ring-2 dark:text-white
                            {{ isset($validationErrors['targetId'])
                                ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500 dark:bg-red-900/10'
                                : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500/30 dark:border-border dark:bg-gray-700' }}">
                            <option value="">Choose an event...</option>
                            @foreach ($this->events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }} — {{ $event->start_date?->format('M j, Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (isset($validationErrors['targetId']))
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $validationErrors['targetId'] }}</p>
                    @endif
                </div>
            @elseif ($targetType === 'project')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Project</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <select wire:model="targetId" class="block w-full rounded-lg border bg-white py-2.5 pl-10 pr-3 text-sm shadow-sm transition focus:outline-none focus:ring-2 dark:text-white
                            {{ isset($validationErrors['targetId'])
                                ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500 dark:bg-red-900/10'
                                : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500/30 dark:border-border dark:bg-gray-700' }}">
                            <option value="">Choose a project...</option>
                            @foreach ($this->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (isset($validationErrors['targetId']))
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $validationErrors['targetId'] }}</p>
                    @endif
                </div>
            @else
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Assignment Name <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <input
                            type="text"
                            wire:model="customName"
                            placeholder="e.g., Website Redesign Team"
                            class="block w-full rounded-lg border py-2.5 pl-10 pr-3 text-sm shadow-sm transition focus:outline-none focus:ring-2 dark:text-white
                                {{ isset($validationErrors['customName'])
                                    ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500 dark:bg-red-900/10'
                                    : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500/30 dark:border-border dark:bg-gray-700' }}"
                        >
                    </div>
                    @if (isset($validationErrors['customName']))
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $validationErrors['customName'] }}</p>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                        <select wire:model="priority" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-border dark:bg-gray-700 dark:text-white">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <input
                            type="datetime-local"
                            wire:model="deadline"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-border dark:bg-gray-700 dark:text-white"
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Optional</p>
                </div>
            </div>

            <div x-data="{ count: {{ strlen($description) }} }" x-init="$watch('$wire.description', v => count = (v || '').length)">
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <span class="text-xs text-gray-400 dark:text-gray-500" x-text="count + '/500'">0/500</span>
                </div>
                <textarea
                    wire:model="description"
                    rows="3"
                    placeholder="What is this assignment for? Describe the context, goals, and any special requirements."
                    maxlength="500"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-border dark:bg-gray-700 dark:text-white"
                    x-on:input="count = $el.value.length"
                ></textarea>
            </div>
        </div>
    </div>
</div>
