<div class="relative" x-data="{
    dropdownOpen: false,
    profilePhotoUrl: @js(auth()->user()?->profile_photo_url),
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()" @profile-photo-updated.window="profilePhotoUrl = $event.detail.url">
    @auth
    <!-- User Button -->
    <button
        class="flex items-center text-gray-700 dark:text-foreground"
        @click.prevent="toggleDropdown()"
        type="button"
        aria-haspopup="true"
        :aria-expanded="dropdownOpen"
    >
        <span class="mr-3 overflow-hidden rounded-full h-11 w-11">
            @php($user = Auth::user())

            <img
                :src="profilePhotoUrl"
                alt="{{ $user->name }}"
                class="object-cover w-full h-full"
                onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=FFFFFF&background=6366f1';"
            />
        </span>

       <span class="hidden mr-1 font-medium text-theme-sm sm:block">{{ Auth::user()->name }}</span>

        <!-- Chevron Icon -->
        <svg
            class="w-5 h-5 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-border dark:bg-popover z-50"
        role="menu"
        style="display: none;"
    >
        <!-- User Info -->
        <div>
            <span class="block font-medium text-gray-700 text-theme-sm dark:text-foreground">{{ Auth::user()->name }}</span>
            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-muted-foreground">{{ Auth::user()->email }}</span>
        </div>

        <!-- Menu Items -->
        <ul class="flex flex-col gap-1 pt-4 pb-3 border-b border-gray-200 dark:border-border" role="none">

            <li>
                <a
                    href="{{ route('user-profile') }}"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-muted-foreground dark:hover:bg-card-hover dark:hover:text-foreground"
                    wire:navigate
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-foreground">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>
                    Edit Profile
                </a>
            </li>

            <li>
                <a
                    href="{{ route('support') }}"
                    class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-muted-foreground dark:hover:bg-card-hover dark:hover:text-foreground"
                    wire:navigate
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-foreground">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM11.9991 6.25C10.8847 6.25 9.98126 7.15342 9.98126 8.26784C9.98126 9.38226 10.8847 10.2857 11.9991 10.2857C13.1135 10.2857 14.0169 9.38226 14.0169 8.26784C14.0169 7.15342 13.1135 6.25 11.9991 6.25ZM8.48126 8.26784C8.48126 6.32499 10.0563 4.75 11.9991 4.75C13.9419 4.75 15.5169 6.32499 15.5169 8.26784C15.5169 10.2107 13.9419 11.7857 11.9991 11.7857C10.0563 11.7857 8.48126 10.2107 8.48126 8.26784ZM12.0001 16.3714C11.5859 16.3714 11.2501 16.0356 11.2501 15.6214V12.9449C11.2501 12.5307 11.5859 12.1949 12.0001 12.1949C12.4143 12.1949 12.7501 12.5307 12.7501 12.9449V15.6214C12.7501 16.0356 12.4143 16.3714 12.0001 16.3714Z"
                            fill="currentColor"
                          />
                        </svg>
                    </span>
                    Help & Support
                </a>
            </li>

        </ul>

        <!-- Sign Out -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex items-center w-full gap-3 px-3 py-2 mt-3 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-muted-foreground dark:hover:bg-card-hover dark:hover:text-foreground"
                @click="closeDropdown()"
            >
                <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </span>
                Sign out
            </button>
        </form>
    </div>
    <!-- Dropdown End -->
    @endauth
</div>
