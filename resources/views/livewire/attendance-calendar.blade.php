<div class="py-4 sm:py-5" x-data="attendanceCalendar(@js($records))"
    @calendar-reminder-saved.window="upsertReminder($event.detail.reminder)"
    @calendar-reminder-deleted.window="removeReminder($event.detail.reminderId)">
    <div>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Attendance Calendar</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track attendance and plan personal reminders</p>
            </div>
            <button type="button" wire:click="openCreateReminder" class="inline-flex w-full items-center justify-center gap-2 rounded-sm bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 sm:w-auto">
                <span class="material-symbols-outlined" aria-hidden="true">add</span>Create Reminder
            </button>
        </div>

        <div class="mb-3 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @foreach ([
                [$stats['total'], 'Total Meetings', 'calendar_month', 'text-gray-900 dark:text-white', 'text-gray-400'],
                [$stats['present'], 'Present', 'check_circle', 'text-green-700 dark:text-green-400', 'text-green-500'],
                [$stats['late'], 'Late', 'schedule', 'text-amber-700 dark:text-amber-400', 'text-amber-500'],
                [$stats['absent'], 'Absent', 'cancel', 'text-red-700 dark:text-red-400', 'text-red-500'],
                [$stats['rate'].'%', 'Attendance Rate', 'monitoring', 'text-gray-900 dark:text-white', 'text-gray-400'],
                [$stats['streak'], 'Current Streak', 'local_fire_department', 'text-indigo-600 dark:text-indigo-400', 'text-indigo-500'],
            ] as [$value, $label, $icon, $valueClasses, $iconClasses])
                <div class="dashboard-stat rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-border dark:bg-card">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="text-3xl font-bold {{ $valueClasses }}">{{ $value }}</p><p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p></div>
                        <span class="material-symbols-outlined {{ $iconClasses }}" aria-hidden="true">{{ $icon }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-3 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-sm border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm dark:border-border dark:bg-card">
            <span class="font-semibold text-gray-900 dark:text-white">Legend</span>
            <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300"><span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>Present</span>
            <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Late</span>
            <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>Absent</span>
            <span class="text-gray-500 dark:text-gray-400">Click a record to see details</span>
        </div>

        <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-border">
                <div class="flex items-center gap-2">
                    <button type="button" @click="goToToday" class="rounded-sm border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-border dark:text-gray-300 dark:hover:bg-card-hover">Today</button>
                    @foreach ([['prevMonth', 'chevron_left', 'Previous month'], ['nextMonth', 'chevron_right', 'Next month']] as [$action, $icon, $label])
                        <button type="button" @click="{{ $action }}" aria-label="{{ $label }}" class="inline-flex h-9 w-9 items-center justify-center rounded-sm border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-border dark:text-gray-400 dark:hover:bg-card-hover"><span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span></button>
                    @endforeach
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="monthYear"></h2>
                <button type="button" wire:click="openCreateReminder" class="hidden items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 sm:inline-flex dark:text-brand-400"><span class="material-symbols-outlined" aria-hidden="true">add_circle</span>Add Event</button>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-[700px]">
                    <div class="grid grid-cols-7 border-b border-gray-200 bg-background text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-border dark:bg-background/50 dark:text-gray-400">
                        <template x-for="name in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="name"><div class="px-2 py-3" x-text="name"></div></template>
                    </div>
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                        <template x-for="day in calendarDays" :key="day.iso">
                            <button type="button" @dblclick="$wire.openCreateReminder(day.iso)" class="min-h-28 bg-white p-2 text-left hover:bg-gray-50 lg:min-h-32 dark:bg-card dark:hover:bg-card-hover/70" :class="{ 'bg-gray-50/80 dark:bg-card/60': !day.isCurrentMonth }">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold" :class="{ 'bg-brand-500 text-white': day.isToday, 'text-gray-900 dark:text-white': day.isCurrentMonth && !day.isToday, 'text-gray-400 dark:text-gray-600': !day.isCurrentMonth }" x-text="day.date"></span>
                                <div class="mt-1.5 flex flex-col gap-1">
                                    <template x-for="event in day.events.slice(0, 3)" :key="event.type + '-' + event.id"><span @click.stop="openEvent(event)" class="block truncate rounded-sm px-2 py-1 text-[11px] font-semibold text-white hover:opacity-85" :style="{ backgroundColor: event.color }" x-text="event.title"></span></template>
                                    <span x-show="day.events.length > 3" class="px-1 text-[11px] font-medium text-gray-500 dark:text-gray-400" x-text="'+' + (day.events.length - 3) + ' more'"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <p class="border-t border-gray-200 px-4 py-2 text-xs text-gray-500 dark:border-border dark:text-gray-400">Tip: double-click a date to create a reminder for that day.</p>
        </div>

        <x-ui.drawer show="detailRecord" on-close="detailRecord = null" :show-close-button="false">
            <div class="flex items-start justify-between border-b border-gray-200 p-5 dark:border-border">
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="{ 'bg-green-100 text-green-800': detailRecord?.status === 'present', 'bg-amber-100 text-amber-800': detailRecord?.status === 'late', 'bg-red-100 text-red-800': detailRecord?.status === 'absent' }" x-text="detailRecord?.status"></span>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="detailRecord?.meeting_title"></h2>
                </div>
                <button type="button" @click="detailRecord = null" aria-label="Close details" class="ml-4 text-gray-400"><span class="material-symbols-outlined" aria-hidden="true">close</span></button>
            </div>
            <div class="flex-1 space-y-3 overflow-y-auto p-5 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">calendar_today</span><span x-text="detailRecord?.start ? new Date(detailRecord.start).toLocaleDateString(undefined, { dateStyle: 'full' }) : ''"></span></div>
                <div x-show="detailRecord?.check_in_time" class="flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">schedule</span><span x-text="'Checked in at ' + detailRecord?.check_in_time"></span></div>
                <div x-show="detailRecord?.location" class="flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">location_on</span><span x-text="detailRecord?.location"></span></div>
                <p x-show="detailRecord?.notes" x-text="detailRecord?.notes"></p>
            </div>
        </x-ui.drawer>

        <div x-data="{ open: @entangle('isReminderDrawerOpen') }" wire:key="reminder-drawer">
            <x-ui.drawer show="open" on-close="$wire.closeReminderDrawer()" title="{{ $editingReminderId ? 'Edit Event' : 'Add Event' }}">
                <form wire:submit="saveReminder" class="flex min-h-0 flex-1 flex-col">
                    <div class="flex-1 space-y-5 overflow-y-auto p-5">
                        <p class="-mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">Plan your next big moment: schedule or edit an event to stay on track</p>
                        <div><label for="reminder-event-title" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">Event Title</label><input id="reminder-event-title" wire:model="reminderTitle" type="text" maxlength="120" placeholder="Enter event title" class="block w-full rounded-sm border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-border dark:bg-card dark:text-white">@error('reminderTitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        <fieldset><legend class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Event Color</legend><div class="grid grid-cols-2 gap-3">
                            @foreach (['danger' => ['Danger', 'bg-red-500'], 'success' => ['Success', 'bg-green-500'], 'primary' => ['Primary', 'bg-brand-500'], 'warning' => ['Warning', 'bg-amber-500']] as $color => [$label, $swatch])
                                <label class="flex cursor-pointer items-center gap-2 rounded-sm border border-gray-200 px-3 py-2.5 text-sm text-gray-700 has-checked:border-brand-500 dark:border-border dark:text-gray-300"><input wire:model="reminderColor" type="radio" value="{{ $color }}" class="sr-only"><span class="h-3 w-3 rounded-full {{ $swatch }}"></span>{{ $label }}</label>
                            @endforeach
                        </div>@error('reminderColor')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</fieldset>
                        @foreach ([['reminder-start', 'reminderStartsOn', 'Enter Start Date', 'calendar_today'], ['reminder-end', 'reminderEndsOn', 'Enter End Date', 'event']] as [$id, $model, $label, $icon])
                            <div>
                                <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $label }}</label>
                                <div class="relative">
                                    <input id="{{ $id }}" x-ref="{{ str_replace('-', '', $id) }}" wire:model="{{ $model }}" type="date" @click="$el.showPicker?.()" class="block w-full cursor-pointer rounded-sm border border-gray-300 bg-white py-2.5 pl-3 pr-11 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-border dark:bg-card dark:text-white">
                                    <button type="button" @click="$refs.{{ str_replace('-', '', $id) }}.showPicker?.(); $refs.{{ str_replace('-', '', $id) }}.focus()" class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center text-gray-400 hover:text-brand-500 focus:text-brand-500 focus:outline-none" aria-label="Open {{ strtolower($label) }} picker">
                                        <span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>
                                    </button>
                                </div>
                                @error($model)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 p-5 sm:flex-row dark:border-border">
                        @if ($editingReminderId)<button type="button" wire:click="deleteReminder" wire:confirm="Delete this reminder?" class="inline-flex items-center justify-center gap-2 rounded-sm border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600"><span class="material-symbols-outlined" aria-hidden="true">delete</span>Delete</button>@endif
                        <div class="flex flex-1 gap-3 sm:justify-end"><button type="button" wire:click="closeReminderDrawer" class="flex-1 rounded-sm border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 sm:flex-none dark:border-border dark:text-gray-300">Close</button><button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-sm bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white sm:flex-none"><span class="material-symbols-outlined" aria-hidden="true">{{ $editingReminderId ? 'save' : 'add' }}</span>{{ $editingReminderId ? 'Save Event' : 'Add Event' }}</button></div>
                    </div>
                </form>
            </x-ui.drawer>
        </div>
    </div>

    <script>
        function attendanceCalendar(initialRecords) {
            return {
                currentMonth: new Date().getMonth(), currentYear: new Date().getFullYear(), detailRecord: null, records: initialRecords,
                get monthYear() { return new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(new Date(this.currentYear, this.currentMonth, 1)); },
                get calendarDays() {
                    const first = new Date(this.currentYear, this.currentMonth, 1); const start = new Date(this.currentYear, this.currentMonth, 1 - first.getDay()); const today = this.dateString(new Date());
                    return Array.from({ length: 42 }, (_, index) => { const date = new Date(start); date.setDate(start.getDate() + index); const iso = this.dateString(date); return { date: date.getDate(), iso, isCurrentMonth: date.getMonth() === this.currentMonth, isToday: iso === today, events: this.records.filter(event => event.start.slice(0, 10) <= iso && event.end.slice(0, 10) >= iso) }; });
                },
                dateString(date) { return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0'); },
                prevMonth() { this.currentMonth--; if (this.currentMonth < 0) { this.currentMonth = 11; this.currentYear--; } },
                nextMonth() { this.currentMonth++; if (this.currentMonth > 11) { this.currentMonth = 0; this.currentYear++; } },
                goToToday() { const today = new Date(); this.currentMonth = today.getMonth(); this.currentYear = today.getFullYear(); },
                openEvent(event) { if (event.type === 'reminder') { this.$wire.editReminder(event.id); } else { this.detailRecord = event; } },
                upsertReminder(reminder) { this.records = [...this.records.filter(event => event.type !== 'reminder' || event.id !== reminder.id), reminder]; },
                removeReminder(reminderId) { this.records = this.records.filter(event => event.type !== 'reminder' || event.id !== reminderId); },
            };
        }
    </script>
</div>
