<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 text-sm font-bold text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300">2</span>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Define the Roles You Need</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each role is a slot to be filled. Add roles, set skills, then assign people.</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        @if ($this->roleTemplates->isNotEmpty())
            <div class="mb-6 rounded-lg border border-dashed border-primary-200 bg-primary-50/50 p-4 dark:border-primary-800 dark:bg-primary-900/10">
                <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-primary-700 dark:text-primary-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Quick add from templates
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->roleTemplates as $template)
                        <button
                            type="button"
                            wire:click="applyRoleTemplate({{ $template->id }})"
                            class="inline-flex items-center gap-1.5 rounded-full border border-primary-200 bg-white px-3 py-1.5 text-xs font-medium text-primary-700 shadow-sm transition hover:border-primary-300 hover:bg-primary-50 hover:shadow dark:border-primary-800 dark:bg-gray-800 dark:text-primary-300 dark:hover:border-primary-700 dark:hover:bg-primary-900/20"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            {{ $template->name }}
                            @if ($template->required_skills)
                                <span class="ml-0.5 text-primary-400 dark:text-primary-500">({{ implode(', ', $template->required_skills) }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="space-y-4">
            @foreach ($roles as $index => $role)
                <div
                    class="group rounded-xl border p-5 shadow-sm transition-all duration-200
                        {{ isset($validationErrors['roles.'.$index.'.name']) || isset($validationErrors['roles.'.$index.'.seats'])
                            ? 'border-red-300 bg-red-50/50 dark:border-red-500 dark:bg-red-900/10'
                            : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/80 dark:hover:border-gray-600' }}"
                    wire:key="role-{{ $index }}"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-100 text-xs font-bold text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300">{{ $index + 1 }}</span>
                            <div>
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Role #{{ $index + 1 }}</span>
                                @if (!empty($role['name']))
                                    <span class="ml-2 text-xs text-gray-400 dark:text-gray-500">— {{ $role['name'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-0.5">
                            @if ($index > 0)
                                <button type="button" wire:click="moveRoleUp({{ $index }})" class="rounded-lg p-1.5 text-gray-400 opacity-0 transition hover:bg-primary-50 hover:text-primary-600 group-hover:opacity-100 dark:hover:bg-primary-900/20 dark:hover:text-primary-400" title="Move up">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                            @endif
                            @if ($index < count($roles) - 1)
                                <button type="button" wire:click="moveRoleDown({{ $index }})" class="rounded-lg p-1.5 text-gray-400 opacity-0 transition hover:bg-primary-50 hover:text-primary-600 group-hover:opacity-100 dark:hover:bg-primary-900/20 dark:hover:text-primary-400" title="Move down">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            @endif
                            @if (count($roles) > 1)
                                <button type="button" wire:click="removeRole({{ $index }})" class="ml-1 rounded-lg p-1.5 text-red-400 opacity-0 transition hover:bg-red-50 hover:text-red-600 group-hover:opacity-100 dark:hover:bg-red-900/20 dark:hover:text-red-400" title="Remove role">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12">
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Role Name <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="roles.{{ $index }}.name"
                                placeholder="e.g., Team Lead"
                                class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm transition focus:outline-none focus:ring-2 dark:text-white
                                    {{ isset($validationErrors['roles.'.$index.'.name'])
                                        ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500 dark:bg-red-900/10'
                                        : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500/30 dark:border-gray-600 dark:bg-gray-700' }}"
                            >
                            @if (isset($validationErrors['roles.'.$index.'.name']))
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $validationErrors['roles.'.$index.'.name'] }}</p>
                            @endif
                        </div>
                        <div class="sm:col-span-1 lg:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Seats</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <input
                                    type="number"
                                    wire:model.live="roles.{{ $index }}.seats"
                                    min="1"
                                    max="50"
                                    class="block w-full rounded-lg border py-2 pl-8 pr-3 text-sm shadow-sm transition focus:outline-none focus:ring-2 dark:text-white
                                        {{ isset($validationErrors['roles.'.$index.'.seats'])
                                            ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500 dark:bg-red-900/10'
                                            : 'border-gray-300 focus:border-primary-500 focus:ring-primary-500/30 dark:border-gray-600 dark:bg-gray-700' }}"
                                >
                            </div>
                            @if (isset($validationErrors['roles.'.$index.'.seats']))
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $validationErrors['roles.'.$index.'.seats'] }}</p>
                            @endif
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Required Skills</label>
                            <div
                                x-data="{
                                    open: false,
                                    search: '',
                                    skills: @entangle('roles.' . $index . '.skills'),
                                    allSkills: ['PHP', 'JavaScript', 'Python', 'Design', 'Writing', 'Leadership', 'Communication', 'Data Analysis', 'Project Management', 'Development', 'Algorithms', 'Web', 'UI/UX', 'DevOps', 'QA', 'Research'],
                                    get filteredSkills() {
                                        return this.allSkills.filter(s => s.toLowerCase().includes(this.search.toLowerCase()));
                                    }
                                }"
                                class="relative"
                            >
                                <button
                                    type="button"
                                    @click="open = !open; if(open) $nextTick(() => $refs.search.focus())"
                                    class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition hover:border-primary-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:border-primary-500"
                                >
                                    <span class="truncate text-gray-700 dark:text-gray-300">
                                        <template x-if="skills.length === 0">
                                            <span class="text-gray-400 dark:text-gray-500">Select skills...</span>
                                        </template>
                                        <template x-for="(skill, i) in skills" x-key="skill">
                                            <span>
                                                <span x-text="skill"></span><span x-show="i < skills.length - 1">, </span>
                                            </span>
                                        </template>
                                    </span>
                                    <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div
                                    x-show="open"
                                    @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800"
                                >
                                    <div class="border-b border-gray-100 p-2 dark:border-gray-700">
                                        <input
                                            type="text"
                                            x-ref="search"
                                            x-model="search"
                                            placeholder="Search skills..."
                                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs focus:border-primary-400 focus:outline-none focus:ring-1 focus:ring-primary-400 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        >
                                    </div>
                                    <div class="max-h-48 overflow-y-auto p-1.5">
                                        <template x-for="skill in filteredSkills" x-key="skill">
                                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm transition hover:bg-primary-50 dark:hover:bg-primary-900/20">
                                                <input
                                                    type="checkbox"
                                                    :value="skill"
                                                    x-model="skills"
                                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                                >
                                                <span x-text="skill" class="text-gray-700 dark:text-gray-300"></span>
                                            </label>
                                        </template>
                                        <p x-show="filteredSkills.length === 0" class="px-2.5 py-4 text-center text-xs text-gray-400">No skills match your search.</p>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-gray-100 px-3 py-1.5 dark:border-gray-700">
                                        <span class="text-xs text-gray-400" x-text="skills.length + ' selected'"></span>
                                        <button type="button" @click="skills = []" class="text-xs font-medium text-red-500 hover:text-red-600 dark:text-red-400">Clear all</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end pb-2 sm:col-span-1 lg:col-span-2">
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600/50">
                                <input
                                    type="checkbox"
                                    wire:model="roles.{{ $index }}.lead_required"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                >
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Lead required</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button
            type="button"
            wire:click="addRole"
            class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 px-4 py-3.5 text-sm font-medium text-gray-500 transition hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50/50 dark:border-gray-600 dark:text-gray-400 dark:hover:border-primary-400 dark:hover:text-primary-400 dark:hover:bg-primary-900/10"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add Another Role
        </button>

        <div class="mt-4 rounded-lg border border-primary-200 bg-primary-50/50 p-3 dark:border-primary-800 dark:bg-primary-900/10">
            <p class="text-xs text-primary-700 dark:text-primary-300">
                <strong>Tip:</strong> Give each role a descriptive name (e.g., "Social Media Lead" instead of just "Lead"). Skills help the AI suggest better members.
            </p>
        </div>
    </div>
</div>
