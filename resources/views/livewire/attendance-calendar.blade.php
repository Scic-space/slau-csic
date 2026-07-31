<div class="py-6" x-data="attendanceCalendar()">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance Calendar</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your meeting attendance history</p>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Meetings</p>
            </div>
            <div class="rounded-xl border border-green-200 dark:border-green-900/40 bg-green-50 dark:bg-green-900/20 p-4 shadow-sm">
                <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['present'] }}</p>
                <p class="text-xs text-green-600 dark:text-green-400">Present</p>
            </div>
            <div class="rounded-xl border border-yellow-200 dark:border-yellow-900/40 bg-yellow-50 dark:bg-yellow-900/20 p-4 shadow-sm">
                <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $stats['late'] }}</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400">Late</p>
            </div>
            <div class="rounded-xl border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/20 p-4 shadow-sm">
                <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['absent'] }}</p>
                <p class="text-xs text-red-600 dark:text-red-400">Absent</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['rate'] }}%</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Attendance Rate</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['streak'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Current Streak</p>
            </div>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row">
            <div class="w-full shrink-0 space-y-4 lg:w-64">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Legend</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="h-3 w-3 rounded-full bg-green-500"></span> Present
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="h-3 w-3 rounded-full bg-yellow-500"></span> Late
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="h-3 w-3 rounded-full bg-red-500"></span> Absent
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Click a record to see details</p>
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <button @click="prevMonth" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="monthYear"></h2>
                            <button @click="nextMonth" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-7 gap-px text-center text-xs font-medium text-gray-500 dark:text-gray-400 mb-px">
                            <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                                <div class="py-2" x-text="day"></div>
                            </template>
                        </div>

                        <div class="grid grid-cols-7 gap-px">
                            <template x-for="(day, idx) in calendarDays" :key="idx">
                                <div
                                    class="min-h-[80px] p-1 text-sm"
                                    :class="{
                                        'bg-gray-50 dark:bg-gray-900/50': !day.isCurrentMonth,
                                        'bg-white dark:bg-gray-800': day.isCurrentMonth,
                                        'cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20': day.events.length > 0
                                    }"
                                >
                                    <div class="mb-1 text-xs font-medium"
                                        :class="{
                                            'text-gray-400 dark:text-gray-600': !day.isCurrentMonth,
                                            'text-gray-900 dark:text-white': day.isCurrentMonth,
                                            'text-indigo-600 dark:text-indigo-400': day.isToday
                                        }"
                                        x-text="day.date"></div>
                                    <template x-for="evt in day.events.slice(0, 2)" :key="evt.id">
                                        <div
                                            @click="openDetail(evt)"
                                            class="mb-0.5 truncate rounded px-1 py-0.5 text-[10px] font-medium text-white cursor-pointer hover:opacity-80"
                                            :style="{ backgroundColor: evt.color }"
                                            x-text="evt.title"
                                        ></div>
                                    </template>
                                    <div x-show="day.events.length > 2" class="text-[10px] text-gray-400 dark:text-gray-500 px-1" x-text="'+' + (day.events.length - 2) + ' more'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Modal --}}
        <div x-show="detailRecord" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="detailRecord = null"></div>
            <div class="relative w-full max-w-lg rounded-xl bg-white dark:bg-gray-900 p-6 shadow-xl">
                <button @click="detailRecord = null" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300': detailRecord?.status === 'present',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300': detailRecord?.status === 'late',
                                'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300': detailRecord?.status === 'absent',
                            }"
                            x-text="detailRecord?.status ? detailRecord.status.charAt(0).toUpperCase() + detailRecord.status.slice(1) : ''"></span>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="detailRecord?.meeting_title"></h2>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span x-text="detailRecord?.start ? new Date(detailRecord.start).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }) : ''"></span>
                        </div>
                        <div x-show="detailRecord?.check_in_time" class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="'Checked in at ' + detailRecord?.check_in_time"></span>
                        </div>
                        <div x-show="detailRecord?.location" class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span x-text="detailRecord?.location"></span>
                        </div>
                    </div>
                    <p x-show="detailRecord?.notes" class="text-sm text-gray-700 dark:text-gray-300" x-text="detailRecord?.notes"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function attendanceCalendar() {
            return {
                currentMonth: new Date().getMonth(),
                currentYear: new Date().getFullYear(),
                detailRecord: null,
                records: @js($records),

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
                        const events = this.records.filter(e => e.start.startsWith(dateStr));
                        days.push({ date: d, isCurrentMonth: true, isToday, events });
                    }
                    const remaining = 42 - days.length;
                    for (let d = 1; d <= remaining; d++) {
                        days.push({ date: d, isCurrentMonth: false, isToday: false, events: [] });
                    }
                    return days;
                },

                prevMonth() {
                    if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; }
                    else { this.currentMonth--; }
                },

                nextMonth() {
                    if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; }
                    else { this.currentMonth++; }
                },

                openDetail(evt) {
                    this.detailRecord = evt;
                }
            }
        }
    </script>
</div>
