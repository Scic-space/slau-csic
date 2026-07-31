<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 text-sm font-bold text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300">5</span>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Review & Approve</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Drag members between roles to adjust. Approve when the lineup looks right.</p>
                </div>
            </div>
            @if ($generatedResults)
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Confidence</p>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full rounded-full bg-primary-500 transition-all" style="width: {{ min(100, $generatedResults['confidence_score'] ?? 0) }}%"></div>
                            </div>
                            <p class="text-sm font-bold text-primary-600 dark:text-primary-400">{{ $generatedResults['confidence_score'] ?? '—' }}%</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Fairness</p>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full rounded-full bg-green-500 transition-all" style="width: {{ min(100, $generatedResults['fairness_score'] ?? 0) }}%"></div>
                            </div>
                            <p class="text-sm font-bold text-green-600 dark:text-green-400">{{ $generatedResults['fairness_score'] ?? '—' }}%</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="p-6">
        @if ($generatedResults)
            <div
                x-data="{
                    dragMember: null,
                    dragRoleId: null,
                    init() {
                        this.$el.querySelectorAll('.member-card').forEach(card => {
                            card.addEventListener('dragstart', (e) => {
                                this.dragMember = card.dataset.memberId;
                                this.dragRoleId = card.dataset.roleId;
                                card.classList.add('opacity-50', 'ring-2', 'ring-primary-400');
                            });
                            card.addEventListener('dragend', () => {
                                card.classList.remove('opacity-50', 'ring-2', 'ring-primary-400');
                                this.dragMember = null;
                                this.dragRoleId = null;
                            });
                        });
                        this.$el.querySelectorAll('.drop-zone').forEach(zone => {
                            zone.addEventListener('dragover', (e) => {
                                e.preventDefault();
                                zone.classList.add('border-primary-400', 'bg-primary-50', 'dark:bg-primary-900/20', 'ring-1', 'ring-primary-300');
                            });
                            zone.addEventListener('dragleave', () => {
                                zone.classList.remove('border-primary-400', 'bg-primary-50', 'dark:bg-primary-900/20', 'ring-1', 'ring-primary-300');
                            });
                            zone.addEventListener('drop', (e) => {
                                e.preventDefault();
                                zone.classList.remove('border-primary-400', 'bg-primary-50', 'dark:bg-primary-900/20', 'ring-1', 'ring-primary-300');
                                if (this.dragMember && zone.dataset.roleId !== this.dragRoleId) {
                                    $wire.moveMember(this.dragMember, zone.dataset.roleId);
                                }
                            });
                        });
                    }
                }"
            >
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
                    @foreach ($generatedResults['roles'] ?? [] as $role)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50/50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/30">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                                        {{ strtoupper(substr($role['name'], 0, 1)) }}
                                    </span>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $role['name'] }}</h4>
                                </div>
                                <span class="text-xs font-semibold {{ count($role['members']) >= $role['seats_required'] ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                    {{ count($role['members']) }}/{{ $role['seats_required'] }}
                                    @if (count($role['members']) >= $role['seats_required'])
                                        <svg class="ml-0.5 inline-block h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </span>
                            </div>
                            <div
                                class="drop-zone min-h-[140px] space-y-2 p-3 transition-all"
                                data-role-id="{{ $role['id'] }}"
                            >
                                @forelse ($role['members'] as $member)
                                    <div
                                        class="member-card cursor-grab rounded-lg border bg-white px-3 py-2.5 shadow-sm transition active:cursor-grabbing dark:bg-gray-800
                                            {{ $member['status'] === 'approved' ? 'border-green-200 dark:border-green-800' : 'border-gray-200 dark:border-gray-700' }}
                                            hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700"
                                        draggable="true"
                                        data-member-id="{{ $member['id'] }}"
                                        data-role-id="{{ $role['id'] }}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300">
                                                    {{ strtoupper(substr($member['user_name'], 0, 2)) }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                        {{ $member['user_name'] }}
                                                        @if ($member['is_lead'])
                                                            <span class="ml-1 inline-flex items-center gap-0.5 text-xs text-yellow-600 dark:text-yellow-400">
                                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                                Lead
                                                            </span>
                                                        @endif
                                                        @if ($member['is_backup'])
                                                            <span class="ml-1 inline-flex items-center gap-0.5 text-xs text-gray-500">
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                                Backup
                                                            </span>
                                                        @endif
                                                    </p>
                                                    @if ($member['reasoning'])
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $member['reasoning'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-1.5">
                                                @if (! empty($member['conflict_flags']))
                                                    <div class="group relative">
                                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-600 dark:bg-red-900/30 dark:text-red-400" title="{{ implode(', ', $member['conflict_flags']) }}">!</span>
                                                        <div class="absolute right-0 top-full z-10 mt-1 hidden w-48 rounded-lg border border-red-200 bg-white p-2 shadow-lg group-hover:block dark:border-red-800 dark:bg-gray-800">
                                                            <p class="text-xs text-red-600 dark:text-red-400">
                                                                @foreach ($member['conflict_flags'] as $flag)
                                                                    &bull; {{ $flag }}<br>
                                                                @endforeach
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($member['confidence_score'])
                                                    <div class="flex items-center gap-1.5 rounded-md bg-primary-50 px-2 py-0.5 dark:bg-primary-900/20">
                                                        <div class="h-1.5 w-8 overflow-hidden rounded-full bg-primary-200 dark:bg-primary-800">
                                                            <div class="h-full rounded-full bg-primary-500" style="width: {{ $member['confidence_score'] }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">{{ $member['confidence_score'] }}%</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                                        <svg class="mb-2 h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        <p class="text-xs">Drop members here</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 py-16 dark:border-gray-700">
                <svg class="mb-4 h-14 w-14 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <h3 class="text-lg font-semibold text-gray-400 dark:text-gray-500">No members assigned yet</h3>
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Go to the Assign step to add members, then come back here to review.</p>
            </div>
        @endif
    </div>
</div>
