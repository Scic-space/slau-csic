<div class="py-4 sm:py-5" x-data="eventCalendar()">
    <div>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Event Calendar</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Browse and discover club events</p>
            </div>
            <div class="flex gap-2">
                <button @click="viewMode = 'month'" class="rounded-lg px-3 py-1.5 text-xs font-medium transition focus:ring-2 focus:ring-gray-900 dark:focus:ring-white"
                    :class="viewMode === 'month' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'">Month</button>
                <button @click="viewMode = 'agenda'" class="rounded-lg px-3 py-1.5 text-xs font-medium transition focus:ring-2 focus:ring-gray-900 dark:focus:ring-white"
                    :class="viewMode === 'agenda' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'">Agenda</button>
            </div>
        </div>

        <div class="dashboard-card mb-3 flex flex-wrap items-center gap-x-5 gap-y-3 rounded-sm border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm dark:border-border dark:bg-card">
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-semibold text-gray-900 dark:text-white">Categories</span>
                @foreach ($categories as $cat)
                    <label class="group inline-flex cursor-pointer items-center gap-1.5">
                        <input type="checkbox" value="{{ $cat['id'] }}" x-model="selectedCategories" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900 dark:border-border dark:bg-gray-900 dark:text-white dark:focus:ring-white">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                        <span class="text-gray-600 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white">{{ $cat['name'] }}</span>
                    </label>
                @endforeach
                <button x-show="selectedCategories.length > 0" @click="selectedCategories = []" class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Clear</button>
            </div>
            <span class="hidden h-5 w-px bg-gray-200 sm:block dark:bg-gray-700" aria-hidden="true"></span>
            <span class="font-semibold text-gray-900 dark:text-white">Legend</span>
            <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300"><span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>You're registered</span>
            <span class="text-gray-500 dark:text-gray-400">Click an event to view details</span>
            <span class="text-gray-500 dark:text-gray-400">Events are colored by category</span>
        </div>

        <div class="min-w-0">
                {{-- Month View --}}
                <div x-show="viewMode === 'month'">
                    <div class="dashboard-card overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="p-4">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button @click="prevMonth" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-card-hover transition focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="monthYear"></h2>
                                    <button @click="nextMonth" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-card-hover transition focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                                <button @click="goToToday" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">Today</button>
                            </div>

                            <div class="grid grid-cols-7 gap-px text-center text-xs font-medium text-gray-500 dark:text-gray-400 mb-px">
                                <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                                    <div class="py-2" x-text="day"></div>
                                </template>
                            </div>

                            <div class="grid grid-cols-7 gap-px">
                                <template x-for="(day, idx) in calendarDays" :key="idx">
                                    <div
                                        class="min-h-[80px] p-1 text-sm transition"
                                        :class="{
                                            'bg-background dark:bg-background/50': !day.isCurrentMonth,
                                            'bg-white dark:bg-card': day.isCurrentMonth,
                                            'cursor-pointer hover:bg-gray-100 dark:hover:bg-card-hover': day.events.length > 0
                                        }"
                                    >
                                        <div class="mb-1 text-xs font-medium"
                                            :class="{
                                                'text-gray-400 dark:text-gray-600': !day.isCurrentMonth,
                                                'text-gray-900 dark:text-white': day.isCurrentMonth && !day.isToday,
                                                'text-white bg-brand-500 rounded-full w-6 h-6 flex items-center justify-center': day.isToday
                                            }"
                                            x-text="day.date"></div>
                                        <template x-for="evt in day.events.slice(0, 2)" :key="evt.id">
                                            <div
                                                @click="openDetail(evt)"
                                                class="mb-0.5 truncate rounded px-1 py-0.5 text-[10px] font-medium text-white cursor-pointer hover:opacity-80 flex items-center gap-1"
                                                :style="{ backgroundColor: evt.color }"
                                            >
                                                <span x-show="evt.is_registered" class="shrink-0">&#10003;</span>
                                                <span x-text="evt.title"></span>
                                            </div>
                                        </template>
                                        <div x-show="day.events.length > 2" class="text-[10px] text-gray-400 dark:text-gray-500 px-1" x-text="'+' + (day.events.length - 2) + ' more'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Agenda View --}}
                <div x-show="viewMode === 'agenda'">
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-border dark:bg-card">
                        <div class="p-4">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Events</h2>
                                <button @click="goToToday" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">Today</button>
                            </div>
                            <div class="space-y-2">
                                <template x-for="evt in agendaEvents" :key="evt.id">
                                    <div @click="openDetail(evt)" class="flex cursor-pointer items-center gap-4 rounded-lg border border-gray-100 p-3 transition hover:bg-gray-50 dark:border-border dark:hover:bg-card-hover">
                                        <div class="w-14 shrink-0 text-center">
                                            <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="new Date(evt.start).getDate()"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="new Date(evt.start).toLocaleDateString('en', { month: 'short' })"></p>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="{ backgroundColor: evt.color }"></span>
                                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white" x-text="evt.title"></p>
                                                <span x-show="evt.is_registered" class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Registered</span>
                                            </div>
                                            <div class="mt-1 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                <span x-text="formatDate(evt.start)"></span>
                                                <span x-show="evt.location" x-text="evt.location"></span>
                                                <span x-show="evt.is_recurring" class="text-purple-600 dark:text-purple-400">Recurring</span>
                                            </div>
                                        </div>
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </template>
                                <div x-show="agendaEvents.length === 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">No upcoming events found.</div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        {{-- Detail Drawer --}}
        <x-ui.drawer show="detailEvent" on-close="detailEvent = null" :show-close-button="false">
            <div class="flex items-start justify-between border-b border-gray-200 p-5 dark:border-border">
                <div class="flex items-center gap-3">
                    <span class="h-4 w-4 shrink-0 rounded-full" :style="{ backgroundColor: detailEvent?.color }"></span>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="detailEvent?.title"></h2>
                </div>
                <button @click="detailEvent = null" aria-label="Close" class="ml-4 text-gray-400 hover:text-gray-700 dark:hover:text-white">
                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                </button>
            </div>
            <div class="flex-1 space-y-4 overflow-y-auto p-5">
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="detailEvent?.start ? formatDate(detailEvent.start) : ''"></span>
                    </div>
                    <div x-show="detailEvent?.end" class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="detailEvent?.end ? 'Until ' + formatDate(detailEvent.end) : ''"></span>
                    </div>
                    <div x-show="detailEvent?.location" class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-text="detailEvent?.location"></span>
                    </div>
                    <span x-show="detailEvent?.is_recurring" class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">Recurring</span>
                    <span x-show="detailEvent?.is_registered" class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">You're Registered</span>
                </div>
                <p x-show="detailEvent?.description" class="text-sm text-gray-700 dark:text-gray-300" x-html="detailEvent?.description?.substring(0, 300)"></p>
            </div>
            <div class="border-t border-gray-200 p-5 dark:border-border">
                <a :href="detailEvent?.url" class="block w-full rounded-lg bg-gray-900 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-gray-800 transition focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 dark:focus:ring-white">View Event</a>
            </div>
        </x-ui.drawer>
    </div>

    <script>
        function eventCalendar() {
            return {
                currentMonth: new Date().getMonth(),
                currentYear: new Date().getFullYear(),
                selectedCategories: [],
                detailEvent: null,
                viewMode: 'month',
                events: @js($events),

                get filteredEvents() {
                    if (this.selectedCategories.length === 0) return this.events;
                    return this.events.filter(e =>
                        e.categoryIds.some(id => this.selectedCategories.includes(id))
                    );
                },

                get monthYear() {
                    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                    return months[this.currentMonth] + ' ' + this.currentYear;
                },

                get calendarDays() {
                    const days = [];
                    const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                    const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                    const daysInPrev = new Date(this.currentYear, this.currentMonth, 0).getDate();
                    const today = new Date();

                    for (let i = firstDay - 1; i >= 0; i--) {
                        const d = daysInPrev - i;
                        days.push({ date: d, isCurrentMonth: false, isToday: false, events: [] });
                    }
                    for (let d = 1; d <= daysInMonth; d++) {
                        const isToday = d === today.getDate() && this.currentMonth === today.getMonth() && this.currentYear === today.getFullYear();
                        const dateStr = this.currentYear + '-' + String(this.currentMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                        const events = this.filteredEvents.filter(e => e.start.startsWith(dateStr));
                        days.push({ date: d, isCurrentMonth: true, isToday, events });
                    }
                    const remaining = 42 - days.length;
                    for (let d = 1; d <= remaining; d++) {
                        days.push({ date: d, isCurrentMonth: false, isToday: false, events: [] });
                    }
                    return days;
                },

                get agendaEvents() {
                    return this.filteredEvents
                        .filter(e => new Date(e.start) >= new Date())
                        .slice(0, 20);
                },

                goToToday() {
                    const today = new Date();
                    this.currentMonth = today.getMonth();
                    this.currentYear = today.getFullYear();
                },

                prevMonth() {
                    if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; }
                    else { this.currentMonth--; }
                },

                nextMonth() {
                    if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; }
                    else { this.currentMonth++; }
                },

                formatDate(dateStr) {
                    return new Date(dateStr).toLocaleDateString('en-US', {
                        weekday: 'short', month: 'short', day: 'numeric', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                },

                openDetail(evt) {
                    this.detailEvent = evt;
                }
            }
        }
    </script>
</div>
