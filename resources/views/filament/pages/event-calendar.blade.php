<x-filament-panels::page>
    <div class="w-full space-y-4" x-data="adminCalendar()" x-init="init()">
        <div class="flex w-full flex-col gap-4">
            <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(18rem,0.7fr)_minmax(28rem,1.3fr)]">
                <div class="rounded-sm border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <button type="button" x-on:click="prevMonth()" class="inline-flex h-9 w-9 items-center justify-center rounded-sm text-gray-600 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-primary-500 dark:text-gray-400 dark:hover:bg-white/5" aria-label="Previous month" title="Previous month">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_left</span>
                        </button>
                        <div class="text-center">
                            <span class="block text-base font-bold text-gray-900 dark:text-white" x-text="monthYear"></span>
                            <button type="button" x-on:click="goToToday()" class="mt-0.5 text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">Go to today</button>
                        </div>
                        <button type="button" x-on:click="nextMonth()" class="inline-flex h-9 w-9 items-center justify-center rounded-sm text-gray-600 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-primary-500 dark:text-gray-400 dark:hover:bg-white/5" aria-label="Next month" title="Next month">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_right</span>
                        </button>
                    </div>
                </div>

                @if($this->categories->count())
                <div class="rounded-sm border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">filter_alt</span>
                            Categories
                        </h3>
                        <button type="button" x-show="selectedCategories.length > 0" x-on:click="selectedCategories = []" class="text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">Clear filters</button>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        @foreach($this->categories as $cat)
                        <label class="flex cursor-pointer items-center gap-2 group">
                            <input type="checkbox" value="{{ $cat->id }}" x-model="selectedCategories" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900">
                            <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $cat->color }}"></span>
                            <span class="text-sm text-gray-700 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white">{{ $cat->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="min-w-0 w-full">
                <div class="w-full overflow-x-auto rounded-sm border border-stroke bg-white shadow-sm dark:border-strokedark dark:bg-boxdark">
                    <div class="min-w-[48rem]">
                    <div class="grid grid-cols-7 border-b border-stroke bg-gray-50/80 dark:border-strokedark dark:bg-white/[0.02]">
                        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                        <div class="py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $d }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                        <template x-for="(day, idx) in calendarDays" :key="idx">
                            <div class="min-h-[7.5rem] bg-white p-2 dark:bg-boxdark" :class="{ 'bg-primary-50/50 dark:bg-primary-500/5': day.isToday, 'bg-gray-50/70 text-gray-400 dark:bg-gray-900/50': !day.isCurrentMonth }">
                                <div class="mb-1">
                                    <span class="inline-flex h-6 w-6 items-center justify-center text-[11px] font-medium"
                                        :class="day.isToday ? 'rounded-full bg-primary-600 text-white font-bold' : 'text-gray-700 dark:text-gray-300'"
                                        x-text="day.date"></span>
                                </div>
                                <div class="space-y-px">
                                    <template x-for="evt in day.events.slice(0, 3)" :key="evt.id">
                                        <button type="button" x-on:click="openModal(evt)" class="w-full truncate rounded-sm px-1.5 py-1 text-left text-[11px] font-semibold leading-tight text-white transition hover:brightness-110 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-primary-500" :style="{ backgroundColor: evt.color }" :title="evt.title" x-text="evt.title"></button>
                                    </template>
                                    <div x-show="day.events.length > 3" class="px-1 pt-0.5 text-[10px] font-medium text-gray-400 dark:text-gray-500" x-text="'+' + (day.events.length - 3) + ' more'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Drawer --}}
        <x-ui.drawer show="modalEvent" on-close="modalEvent = null" :show-close-button="false">
            <div class="flex items-start justify-between border-b border-stroke p-5 dark:border-strokedark" role="dialog" aria-modal="true" aria-label="Event details">
                <div class="flex items-center gap-3">
                    <span class="h-3.5 w-3.5 shrink-0 rounded-full" :style="{ backgroundColor: modalEvent?.color }"></span>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="modalEvent?.title"></h2>
                </div>
                <button type="button" x-on:click="modalEvent = null" class="ml-4 rounded-sm p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5" aria-label="Close event details">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
                </button>
            </div>
            <div class="flex-1 space-y-4 overflow-y-auto p-5">
                <div class="space-y-2.5 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="modalEvent ? formatDateFull(modalEvent.start) : ''"></span>
                    </div>
                    <div x-show="modalEvent?.end" class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="modalEvent?.end ? 'Until ' + formatDateFull(modalEvent.end) : ''"></span>
                    </div>
                    <div x-show="modalEvent?.location" class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-text="modalEvent?.location"></span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusStyle(modalEvent?.status)" x-text="modalEvent?.status"></span>
                    </div>
                </div>
                <div x-show="modalEvent?.description" class="rounded-lg bg-gray-50 p-3 text-sm text-gray-600 dark:bg-gray-800/50 dark:text-gray-400">
                    <p x-text="modalEvent?.description?.substring(0, 300)"></p>
                </div>
            </div>
            <div class="flex gap-3 border-t border-stroke p-5 dark:border-strokedark">
                <a :href="modalEvent?.editUrl" class="flex-1 rounded-lg bg-primary-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-primary-700 transition">Edit Event</a>
                <button x-on:click="modalEvent = null" class="rounded-lg border border-stroke px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition dark:border-strokedark dark:text-gray-300 dark:hover:bg-white/5">Close</button>
            </div>
        </x-ui.drawer>
    </div>

    <script>
        function adminCalendar() {
            return {
                currentMonth: new Date().getMonth(),
                currentYear: new Date().getFullYear(),
                selectedCategories: [],
                modalEvent: null,
                events: @js($this->eventsJson),

                get monthYear() {
                    var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                    return months[this.currentMonth] + ' ' + this.currentYear;
                },

                get filteredEvents() {
                    if (this.selectedCategories.length === 0) return this.events;
                    return this.events.filter(e => e.categoryIds.some(id => this.selectedCategories.includes(String(id))));
                },

                get calendarDays() {
                    var days = [];
                    var firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                    var daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                    var daysInPrev = new Date(this.currentYear, this.currentMonth, 0).getDate();
                    var today = new Date();

                    for (var i = firstDay - 1; i >= 0; i--) {
                        days.push({ date: daysInPrev - i, isCurrentMonth: false, isToday: false, events: [] });
                    }
                    for (var d = 1; d <= daysInMonth; d++) {
                        var isToday = d === today.getDate() && this.currentMonth === today.getMonth() && this.currentYear === today.getFullYear();
                        var dateStr = this.currentYear + '-' + String(this.currentMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                        var dayEvents = this.filteredEvents.filter(e => e.start && e.start.startsWith(dateStr));
                        days.push({ date: d, isCurrentMonth: true, isToday: isToday, events: dayEvents });
                    }
                    var remaining = 42 - days.length;
                    for (var r = 1; r <= remaining; r++) {
                        days.push({ date: r, isCurrentMonth: false, isToday: false, events: [] });
                    }
                    return days;
                },

                init() {},
                prevMonth() { if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; } else { this.currentMonth--; } },
                nextMonth() { if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; } else { this.currentMonth++; } },
                goToToday() { var n = new Date(); this.currentMonth = n.getMonth(); this.currentYear = n.getFullYear(); },
                openModal(evt) { this.modalEvent = evt; },
                formatDateFull(s) { if (!s) return ''; return new Date(s).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }); },
                statusStyle(s) { var m = { published: 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400', ongoing: 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400', draft: 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400', cancelled: 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400' }; return m[s] || m.draft; },
            };
        }
    </script>
</x-filament-panels::page>
