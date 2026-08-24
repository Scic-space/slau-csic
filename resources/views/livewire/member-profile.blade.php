<div class="py-4 sm:py-5" x-data="{ flash: '' }" x-on:flash.window="flash = $event.detail.message; setTimeout(() => flash = '', 3500)">
    <div>

        {{-- Flash message --}}
        <div x-show="flash" x-transition.duration.300ms
             class="mb-4 rounded-sm border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                <p class="text-sm text-gray-900 dark:text-white" x-text="flash"></p>
            </div>
        </div>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Profile</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your personal information and preferences</p>
        </div>

        {{-- Photo + Identity card --}}
        <div class="dashboard-card mb-3 rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-5">
            <div class="flex flex-col items-center gap-4 sm:flex-row">
                <div class="relative shrink-0">
                    <div class="h-20 w-20 overflow-hidden rounded-full ring-2 ring-gray-200 dark:ring-gray-600">
                        <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    </div>
                    <label for="profile-photo-upload"
                           class="absolute -bottom-1 -right-1 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-gray-900 text-white shadow-sm transition hover:bg-gray-700 dark:border-gray-800">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">photo_camera</span>
                    </label>
                    <input type="file" id="profile-photo-upload" class="hidden" wire:model="profile_photo" accept="image/*">
                </div>
                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        @if ($user->rank)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">Rank: {{ $user->rank }}</span>
                        @endif
                        @if ($membership)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ ucfirst($membership->type) }} &middot; {{ ucfirst($membership->status) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($completionPercent < 100)
        {{-- Profile completion bar --}}
        <div class="dashboard-card mb-3 rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">task_alt</span>Profile Completion</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $filledFields }} of {{ $totalFields }} fields filled</p>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $completionPercent }}%</span>
            </div>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div class="h-full rounded-full bg-gray-900 transition-all duration-500 dark:bg-white" style="width: {{ $completionPercent }}%"></div>
            </div>
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($profileFields as $field => $filled)
                    @unless ($filled)
                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">{{ str_replace('_', ' ', $field) }}</span>
                    @endunless
                @endforeach
            </div>
        </div>
        @endif

        {{-- Two-column sections --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">

            {{-- Personal Information --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">person</span>Personal Information</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Update your name, academic details, and bio</p>
                </div>
                <form wire:submit="updateProfile" class="profile-form px-5 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                            <input type="text" wire:model="name"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('name') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" wire:model="email"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('email') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Registration No.</label>
                                <input type="text" wire:model="registration_number"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                @error('registration_number') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input type="text" wire:model="phone"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('phone') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Headline</label>
                            <input type="text" wire:model="headline" placeholder="e.g. Cybersecurity Enthusiast"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('headline') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Faculty</label>
                            <select wire:model.live="faculty"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                <option value="">Select faculty</option>
                                @foreach ($faculties as $facultyOption)
                                    <option value="{{ $facultyOption['name'] }}">{{ $facultyOption['name'] }}</option>
                                @endforeach
                            </select>
                            @error('faculty') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Program</label>
                            <select wire:model="program"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white dark:disabled:opacity-50">
                                <option value="">Select a faculty first</option>
                                @foreach ($programsForFaculty as $programOption)
                                    <option value="{{ $programOption }}">{{ $programOption }}</option>
                                @endforeach
                            </select>
                            @error('program') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Year of Study</label>
                            <select wire:model="year_of_study"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                <option value="">Select year</option>
                                @foreach (range(1, 6) as $year)
                                    <option value="{{ $year }}">Year {{ $year }}</option>
                                @endforeach
                            </select>
                            @error('year_of_study') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Intake</label>
                                <select wire:model="intake"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                    <option value="">Select intake</option>
                                    <option value="august">August</option>
                                    <option value="january">January</option>
                                    <option value="may">May</option>
                                </select>
                                @error('intake') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Intake Year</label>
                                <select wire:model="intake_year"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                    <option value="">Select year</option>
                                    @foreach (range(now()->year - 5, now()->year) as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('intake_year') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Bio</label>
                            <textarea wire:model="bio" rows="3"
                                      class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"></textarea>
                            @error('bio') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-sm bg-gray-900 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            <span wire:loading.remove.delay class="inline-flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">save</span>Save</span>
                            <span wire:loading class="inline-flex items-center gap-2"><span class="material-symbols-outlined animate-spin" aria-hidden="true">progress_activity</span>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Membership & Stats --}}
            <div class="space-y-3">
                <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">badge</span>Membership</h3>
                    </div>
                    <div class="px-6 py-5">
                        @if ($membership)
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Type</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($membership->type) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Status</span>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $membership->isActive() ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($membership->isPending() ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300') }}">{{ ucfirst($membership->status) }}</span>
                                </div>
                                @if ($membership->joined_at)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Member Since</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $membership->joined_at->format('M j, Y') }}</span>
                                    </div>
                                @endif
                                @if ($membership->approved_at)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Approved</span>
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $membership->approved_at->format('M j, Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">No membership record found.</p>
                        @endif
                    </div>
                </div>

                @if ($gamification)
                    <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">monitoring</span>Stats</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-0 divide-x divide-gray-100 dark:divide-gray-700">
                            <div class="px-6 py-4 text-center">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $gamification->score ?? 0 }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Score</p>
                            </div>
                            <div class="px-6 py-4 text-center">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $gamification->attendance_count ?? 0 }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Attendance</p>
                            </div>
                            <div class="px-6 py-4 text-center">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $gamification->current_streak ?? 0 }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Current Streak</p>
                            </div>
                            <div class="px-6 py-4 text-center">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $gamification->longest_streak ?? 0 }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Best Streak</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Social Links --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">link</span>Social Links</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Connect your GitHub, LinkedIn, and Discord</p>
                </div>
                <form wire:submit="updateProfile" class="profile-form px-6 py-5">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">GitHub</label>
                            <div class="profile-input-group">
                                <span class="inline-flex items-center border-r border-gray-300 bg-gray-50 px-3 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">github.com/</span>
                                <input type="text" wire:model="github_username"
                                       class="block w-full rounded-r-lg border-0 bg-white px-3 py-2 text-sm text-gray-900 focus:ring-1 focus:ring-gray-900 dark:bg-gray-900 dark:text-white dark:focus:ring-white">
                            </div>
                            @error('github_username') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">LinkedIn</label>
                            <input type="url" wire:model="linkedin_url" placeholder="https://linkedin.com/in/..."
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('linkedin_url') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Discord</label>
                            <input type="text" wire:model="discord_username"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('discord_username') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-sm bg-gray-900 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            <span wire:loading.remove.delay class="inline-flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">save</span>Save</span>
                            <span wire:loading class="inline-flex items-center gap-2"><span class="material-symbols-outlined animate-spin" aria-hidden="true">progress_activity</span>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Privacy Settings --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">shield_person</span>Privacy</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Control what appears on your public profile</p>
                </div>
                <form wire:submit="updatePrivacy" class="px-6 py-5">
                    <div class="space-y-1">
                        @php
                            $toggles = [
                                'show_email' => ['Show email', 'Display your email to other members'],
                                'show_phone' => ['Show phone', 'Display your phone number'],
                                'show_discord' => ['Show Discord', 'Display your Discord username'],
                                'show_attendance' => ['Show attendance', 'Include attendance stats on your profile'],
                                'show_program' => ['Show program', 'Display your academic program'],
                                'show_year' => ['Show year of study', 'Display which academic year you are in'],
                                'show_profile' => ['Public profile', 'Make your profile visible to non-members'],
                            ];
                        @endphp
                        @foreach ($toggles as $field => $info)
                            <label class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2.5 transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $info[0] }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $info[1] }}</p>
                                </div>
                                <button type="button" wire:click="$set('{{ $field }}', {{ ${$field} ? 'false' : 'true' }})"
                                        class="relative h-6 w-11 shrink-0 rounded-full transition-colors duration-200 {{ ${$field} ? 'bg-gray-900 dark:bg-white' : 'bg-gray-200 dark:bg-gray-700' }}">
                                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-200 dark:bg-gray-900 {{ ${$field} ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-sm bg-gray-900 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            <span wire:loading.remove.delay class="inline-flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">save</span>Save</span>
                            <span wire:loading class="inline-flex items-center gap-2"><span class="material-symbols-outlined animate-spin" aria-hidden="true">progress_activity</span>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Notification Preferences --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">notifications</span>Notifications</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Choose which notifications you receive</p>
                </div>
                <form wire:submit="updateNotificationPreferences" class="px-6 py-5">
                    <div class="space-y-1">
                        @php
                            $notifToggles = [
                                'notify_event_reminders' => ['Event reminders', 'Get notified about upcoming events'],
                                'notify_event_cancellations' => ['Event cancellations', 'Alerted when events are cancelled'],
                                'notify_challenge_solved' => ['Challenge solved', 'When a challenge you submitted is solved'],
                                'notify_membership_updates' => ['Membership updates', 'Approvals, rejections, suspensions'],
                                'notify_broadcast_messages' => ['Broadcast messages', 'Club-wide announcements'],
                                'notify_fine_notifications' => ['Fine notifications', 'Fines issued and payment confirmations'],
                                'notify_weekly_digest' => ['Weekly digest', 'Summary of club activity each week'],
                            ];
                        @endphp
                        @foreach ($notifToggles as $field => $info)
                            <label class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2.5 transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $info[0] }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $info[1] }}</p>
                                </div>
                                <button type="button" wire:click="$set('{{ $field }}', {{ ${$field} ? 'false' : 'true' }})"
                                        class="relative h-6 w-11 shrink-0 rounded-full transition-colors duration-200 {{ ${$field} ? 'bg-gray-900 dark:bg-white' : 'bg-gray-200 dark:bg-gray-700' }}">
                                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-200 dark:bg-gray-900 {{ ${$field} ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-sm bg-gray-900 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            <span wire:loading.remove.delay class="inline-flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">save</span>Save</span>
                            <span wire:loading class="inline-flex items-center gap-2"><span class="material-symbols-outlined animate-spin" aria-hidden="true">progress_activity</span>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Change Password --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">lock</span>Password</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Update your account password</p>
                </div>
                <form wire:submit="updatePassword" class="profile-form px-6 py-5">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                            <input type="password" wire:model="current_password"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('current_password') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">New Password</label>
                            <input type="password" wire:model="new_password"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                            @error('new_password') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                            <input type="password" wire:model="new_password_confirmation"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-sm bg-gray-900 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            <span wire:loading.remove.delay class="inline-flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">lock_reset</span>Update Password</span>
                            <span wire:loading class="inline-flex items-center gap-2"><span class="material-symbols-outlined animate-spin" aria-hidden="true">progress_activity</span>Updating...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Sessions --}}
            <div class="dashboard-card rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">devices</span>Sessions</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Manage your active sessions</p>
                </div>
                <div class="px-6 py-5">
                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">Log out of all other sessions across your devices. You will need to enter your password to confirm.</p>
                    <form wire:submit="logoutOtherDevices" class="profile-form">
                        <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-end">
                            <div class="flex-1">
                                <input type="password" wire:model="logout_password" placeholder="Enter your password"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white">
                                @error('logout_password') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-sm bg-gray-900 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                <span wire:loading.remove.delay class="inline-flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">logout</span>Log Out Other Devices</span>
                                <span wire:loading class="inline-flex items-center gap-2"><span class="material-symbols-outlined animate-spin" aria-hidden="true">progress_activity</span>Logging out...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Badges --}}
        <div class="dashboard-card mt-3 rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><span class="material-symbols-outlined text-gray-400" aria-hidden="true">military_tech</span>Badges</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $earnedBadgeCount }} of {{ $totalBadges }} earned</p>
                    </div>
                    @if ($totalBadges > 0)
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $totalBadges > 0 ? round(($earnedBadgeCount / $totalBadges) * 100) : 0 }}% complete</span>
                    @endif
                </div>
            </div>
            <div class="px-6 py-5">
                @if ($badges->isEmpty())
                    <div class="flex flex-col items-center py-6 text-center">
                        <svg class="mb-2 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No badges available</p>
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-6">
                        @foreach ($badges as $item)
                            <div class="group relative flex flex-col items-center rounded-lg p-3 text-center transition {{ $item['earned'] ? 'bg-gray-50 dark:bg-gray-700/50' : 'opacity-40' }}">
                                <div class="mb-1.5 flex h-10 w-10 items-center justify-center rounded-full {{ $item['earned'] ? 'bg-gray-200 dark:bg-gray-600' : 'bg-gray-100 dark:bg-gray-800' }}">
                                    @if ($item['badge']->icon)
                                        <img src="{{ $item['badge']->icon }}" alt="{{ $item['badge']->name }}" class="h-6 w-6">
                                    @else
                                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    @endif
                                </div>
                                <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $item['badge']->name }}</p>
                                @if ($item['earned'] && $item['earned_at'])
                                    <p class="mt-0.5 text-[10px] text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($item['earned_at'])->format('M j, Y') }}</p>
                                @else
                                    <p class="mt-0.5 text-[10px] text-gray-400 dark:text-gray-500">Not earned</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="dashboard-card mt-3 rounded-sm border border-red-200 bg-white shadow-sm dark:border-red-900/50 dark:bg-gray-800">
            <div class="border-b border-red-100 px-6 py-4 dark:border-red-900/30">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-red-600 dark:text-red-400"><span class="material-symbols-outlined" aria-hidden="true">warning</span>Danger Zone</h3>
            </div>
            <div class="px-6 py-5">
                @if ($confirmingDelete)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-900/20">
                        <p class="mb-3 text-sm font-medium text-red-700 dark:text-red-300">Are you sure you want to delete your account? This action is permanent and cannot be undone.</p>
                        <form wire:submit="deleteAccount">
                            <div class="flex items-end gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-red-700 dark:text-red-300">Enter your password to confirm</label>
                                    <input type="password" wire:model="delete_password"
                                           class="mt-1 block w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-red-500 focus:ring-1 focus:ring-red-500 dark:border-red-700 dark:bg-gray-900 dark:text-white">
                                    @error('delete_password') <span class="mt-1 text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" wire:loading.attr="disabled"
                                        class="inline-flex shrink-0 items-center gap-2 rounded-sm bg-red-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 disabled:opacity-50">
                                    <span wire:loading.remove.delay class="inline-flex items-center gap-2"><span class="material-symbols-outlined" aria-hidden="true">delete_forever</span>Delete Account</span>
                                    <span wire:loading class="inline-flex items-center gap-2"><span class="material-symbols-outlined animate-spin" aria-hidden="true">progress_activity</span>Deleting...</span>
                                </button>
                                <button type="button" wire:click="cancelDelete" class="inline-flex shrink-0 items-center gap-2 rounded-sm border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"><span class="material-symbols-outlined" aria-hidden="true">close</span>Cancel</button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Delete Account</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Permanently remove your account and all associated data</p>
                        </div>
                        <button type="button" wire:click="confirmDelete" class="inline-flex items-center gap-2 rounded-sm border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-700 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20"><span class="material-symbols-outlined" aria-hidden="true">delete</span>Delete Account</button>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
