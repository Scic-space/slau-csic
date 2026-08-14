@php
    $currentPath = request()->path();
    $isPendingApproval = auth()->check() && auth()->user()->isPendingApproval();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200"
    x-data="{
        openSubmenus: {},
        init() {
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            if (currentPath.startsWith('dashboard') || currentPath.startsWith('club/')) {
                this.openSubmenus['dashboard'] = true;
            }
            if (currentPath.startsWith('admin/users')) {
                this.openSubmenus['admin'] = true;
            }
            if (currentPath.startsWith('admin/fines')) {
                this.openSubmenus['admin'] = true;
            }
            if (currentPath.startsWith('admin/fine-types')) {
                this.openSubmenus['admin'] = true;
            }
            if (currentPath.startsWith('/fines')) {
                this.openSubmenus['main'] = true;
            }
            if (currentPath.startsWith('voting')) {
                this.openSubmenus['voting'] = true;
            }
        },
        toggleSubmenu(menuKey) {
            const newState = !this.openSubmenus[menuKey];

            if (newState) {
                this.openSubmenus = {};
            }

            this.openSubmenus[menuKey] = newState;
        },
        isSubmenuOpen(menuKey) {
            return this.openSubmenus[menuKey] || false;
        },
        isActive(path) {
            return window.location.pathname === path ||
                   '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }"
    :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">

    <!-- Logo Section -->
    <div class="pt-8 pb-7 flex"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
        'xl:justify-center' :
        'justify-start pl-3'">
        <a href="/" class="flex items-center gap-3">
            <div x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                <img src="/images/logo/logo-icon.svg" alt="SC" width="28" height="32" />
            </div>
            <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                <img class="dark:hidden" src="/images/logo/logo.svg" alt="SLAU CSIC" width="120" height="32" />
                <img class="hidden dark:block" src="/images/logo/logo-dark.svg" alt="SLAU CSIC" width="120" height="32" />
            </div>
        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar"
        x-ref="sidebarNav"
        x-init="$nextTick(() => {
            const saved = localStorage.getItem('sidebar-scroll-position');
            if (saved) $refs.sidebarNav.scrollTop = parseInt(saved, 10);
        })"
        @scroll="localStorage.setItem('sidebar-scroll-position', $el.scrollTop)">
        <nav class="mb-6">
            <div class="flex flex-col gap-4">

                @if ($isPendingApproval)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2ZM0 12C0 5.373 5.373 0 12 0s12 5.373 12 12-5.373 12-12 12S0 17.627 0 12ZM12 8a1 1 0 011 1v3.586l2.707 2.707A1 1 0 0114 18v.05a1 1 0 01-.55.9 1 1 0 01-1.05-.05L10 16.293V14.5a1 1 0 011-1h1.5a1 1 0 010 2H13a1 1 0 01-1-1V9a1 1 0 011-1z" fill="currentColor"></path></svg>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Membership Pending Approval</h3>
                                <p class="mt-0.5 text-xs leading-relaxed text-amber-700 dark:text-amber-300">
                                    Your account is awaiting approval by the club administration. Club activities will be unlocked once you are approved.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section: Member -->
                <div>
                    <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                        'lg:justify-center' : 'justify-start'">
                        <template
                            x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                            <span>Member</span>
                        </template>
                        <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                            </svg>
                        </template>
                    </h2>

                    <ul class="flex flex-col gap-1">
                        <!-- Dashboard -->
                        <li>
                            <a href="{{ route('dashboard') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/dashboard') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Dashboard
                                </span>
                            </a>
                        </li>

                        <!-- Profile -->
                        <li>
                            <a href="{{ route('profile.edit') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/user-profile') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/user-profile') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2.25C9.37665 2.25 7.25 4.37665 7.25 7C7.25 9.62335 9.37665 11.75 12 11.75C14.6234 11.75 16.75 9.62335 16.75 7C16.75 4.37665 14.6234 2.25 12 2.25ZM8.75 7C8.75 5.20507 10.2051 3.75 12 3.75C13.7949 3.75 15.25 5.20507 15.25 7C15.25 8.79493 13.7949 10.25 12 10.25C10.2051 10.25 8.75 8.79493 8.75 7ZM5.75 14.25C5.75 13.4216 6.42157 12.75 7.25 12.75H16.75C17.5784 12.75 18.25 13.4216 18.25 14.25V19.25C18.25 20.0784 17.5784 20.75 16.75 20.75H7.25C6.42157 20.75 5.75 20.0784 5.75 19.25V14.25ZM7.25 14.25V19.25H16.75V14.25H7.25Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Profile
                                </span>
                            </a>
                        </li>

                        <!-- Membership Card -->
                        <li>
                            <a href="{{ route('membership.card') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/membership-card') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/membership-card') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 8a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V8Zm3-1.5A1.5 1.5 0 0 0 3.5 8v8A1.5 1.5 0 0 0 5 17.5h14a1.5 1.5 0 0 0 1.5-1.5V8A1.5 1.5 0 0 0 19 6.5H5Zm2 3.5a1 1 0 0 0 0 2h3a1 1 0 0 0 0-2H7Zm-1 5a1 1 0 0 1 1-1h6a1 1 0 0 1 0 2H7a1 1 0 0 1-1-1Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Membership Card
                                </span>
                            </a>
                        </li>

                        <!-- Members -->
                        <li>
                            <a href="{{ route('members.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/members') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/members') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 13C3.25 12.5858 3.58579 12.25 4 12.25H14C14.4142 12.25 14.75 12.5858 14.75 13C14.75 13.4142 14.4142 13.75 14 13.75H4C3.58579 13.75 3.25 13.4142 3.25 13ZM3.25 16C3.25 15.5858 3.58579 15.25 4 15.25H14C14.4142 15.25 14.75 15.5858 14.75 16C14.75 16.4142 14.4142 16.75 14 16.75H4C3.58579 16.75 3.25 16.4142 3.25 16ZM3.25 19C3.25 18.5858 3.58579 18.25 4 18.25H14C14.4142 18.25 14.75 18.5858 14.75 19C14.75 19.4142 14.4142 19.75 14 19.75H4C3.58579 19.75 3.25 19.4142 3.25 19ZM17 12.25C16.5858 12.25 16.25 12.5858 16.25 13C16.25 13.4142 16.5858 13.75 17 13.75H20C20.4142 13.75 20.75 13.4142 20.75 13C20.75 12.5858 20.4142 12.25 20 12.25H17ZM17 15.25C16.5858 15.25 16.25 15.5858 16.25 16C16.25 16.4142 16.5858 16.75 17 16.75H20C20.4142 16.75 20.75 16.4142 20.75 16C20.75 15.5858 20.4142 15.25 20 15.25H17ZM17 18.25C16.5858 18.25 16.25 18.5858 16.25 19C16.25 19.4142 16.5858 19.75 17 19.75H20C20.4142 19.75 20.75 19.4142 20.75 19C20.75 18.5858 20.4142 18.25 20 18.25H17Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Members
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                @if (! $isPendingApproval)

                <!-- Section: Events -->
                <div>
                    <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                        'lg:justify-center' : 'justify-start'">
                        <template
                            x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                            <span>Events</span>
                        </template>
                        <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                            </svg>
                        </template>
                    </h2>

                    <ul class="flex flex-col gap-1">
                        <!-- Browse Events -->
                        <li>
                            <a href="{{ route('events.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/events') && !isActive('/my-events') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/events') && !isActive('/my-events') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Browse Events
                                </span>
                            </a>
                        </li>

                        <!-- My Events -->
                        <li>
                            <a href="{{ route('my-events') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/my-events') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/my-events') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    My Events
                                </span>
                            </a>
                        </li>

                        <!-- Event Calendar -->
                        <li>
                            <a href="{{ route('events.calendar') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/events/calendar') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/events/calendar') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Event Calendar
                                </span>
                            </a>
                        </li>

                        <!-- Attendance -->
                        <li>
                            <a href="{{ route('attendance.calendar') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/attendance/calendar') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/attendance/calendar') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Attendance
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Section: Finance -->
                <div>
                    <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                        'lg:justify-center' : 'justify-start'">
                        <template
                            x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                            <span>Finance</span>
                        </template>
                        <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                            </svg>
                        </template>
                    </h2>

                    <ul class="flex flex-col gap-1">
                        <!-- Fines -->
                        <li>
                            <a href="{{ route('fines.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/fines') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/fines') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Fines
                                </span>
                            </a>
                        </li>

                        <!-- Transactions -->
                        <li>
                            <a href="{{ route('my-transactions') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/my-transactions') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/my-transactions') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V3h-8v18zm0-8h8v-6h-8v6z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Transactions
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Section: Activities -->
                <div>
                    <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                        'lg:justify-center' : 'justify-start'">
                        <template
                            x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                            <span>Activities</span>
                        </template>
                        <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                            </svg>
                        </template>
                    </h2>

                    <ul class="flex flex-col gap-1">
                        <!-- Competitions -->
                        <li>
                            <a href="{{ route('portal.competitions') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/club/competitions') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/club/competitions') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M7 4.75h10A2.25 2.25 0 0 1 19.25 7v10A2.25 2.25 0 0 1 17 19.25H7A2.25 2.25 0 0 1 4.75 17V7A2.25 2.25 0 0 1 7 4.75Zm1.5 3.5v7h7v-7h-7Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Competitions
                                </span>
                            </a>
                        </li>

                        <!-- CTF Arena -->
                        <li>
                            <a href="{{ route('ctf.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/ctf') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/ctf') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.75 7A2.25 2.25 0 0 1 7 4.75h4.75v14.5H7A2.25 2.25 0 0 1 4.75 17V7Zm8.5-2.25H17A2.25 2.25 0 0 1 19.25 7v10A2.25 2.25 0 0 1 17 19.25h-3.75V4.75Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    CTF Arena
                                </span>
                            </a>
                        </li>


                    </ul>
                </div>

                <!-- Section: Learning -->
                <div>
                    <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                        'lg:justify-center' : 'justify-start'">
                        <template
                            x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                            <span>Learning</span>
                        </template>
                        <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                            </svg>
                        </template>
                    </h2>

                    <ul class="flex flex-col gap-1">
                        <!-- Internal Classes -->
                        <li>
                            <a href="{{ route('portal.classes') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/club/classes') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/club/classes') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M5.75 6A2.25 2.25 0 0 1 8 3.75h8A2.25 2.25 0 0 1 18.25 6v12A2.25 2.25 0 0 1 16 20.25H8A2.25 2.25 0 0 1 5.75 18V6Zm3 2.25v1.5h6.5v-1.5h-6.5Zm0 4h6.5v-1.5h-6.5v1.5Zm0 4h4v-1.5h-4v1.5Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Internal Classes
                                </span>
                            </a>
                        </li>

                        <!-- Exams -->
                        <li>
                            <a href="{{ route('exams.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/exams') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/exams') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Exams
                                </span>
                            </a>
                        </li>

                        <!-- My Grades -->
                        <li>
                            <a href="{{ route('grades.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/grades') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/grades') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    My Grades
                                </span>
                            </a>
                        </li>

                        <!-- Certificates -->
                        <li>
                            <a href="{{ route('exams.certificates') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/exams/certificates') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/exams/certificates') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 8a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V8Zm3-1.5A1.5 1.5 0 0 0 3.5 8v8A1.5 1.5 0 0 0 5 17.5h14a1.5 1.5 0 0 0 1.5-1.5V8A1.5 1.5 0 0 0 19 6.5H5Zm2 3.5a1 1 0 0 0 0 2h3a1 1 0 0 0 0-2H7Zm-1 5a1 1 0 0 1 1-1h6a1 1 0 0 1 0 2H7a1 1 0 0 1-1-1Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Certificates
                                </span>
                            </a>
                        </li>

                        <!-- Resource Library -->
                        <li>
                            <a href="{{ route('resources.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/resources') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/resources') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Resource Library
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Section: Community -->
                <div>
                    <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                        'lg:justify-center' : 'justify-start'">
                        <template
                            x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                            <span>Community</span>
                        </template>
                        <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                            </svg>
                        </template>
                    </h2>

                    <ul class="flex flex-col gap-1">
                        <!-- Voting -->
                        <li>
                            <a href="{{ route('voting.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/voting') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/voting') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6.75 5.75h10.5v4.5H6.75v-4.5Zm-2 6h14.5v6.5a2 2 0 0 1-2 2H6.75a2 2 0 0 1-2-2v-6.5Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Cabinet Voting
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('voting.nominations') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/voting/nominations') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/voting/nominations') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2Zm3.5 11h-3v3a1 1 0 11-2 0v-3H7.5a1 1 0 110-2h3v-3a1 1 0 112 0v3h3a1 1 0 110 2Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Nominate
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('voting.my-applications') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/voting/my-applications') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/voting/my-applications') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    My Applications
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('voting.results') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/voting/results') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/voting/results') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M3 3v18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 16V10m4 6V7m4 9V4m4 12V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Results
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('voting.verify.form') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/vote/verify') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/vote/verify') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" class="menu-item-text flex items-center gap-2">
                                    Verify Vote
                                </span>
                            </a>
                        </li>

                        <!-- Announcements -->
                        <li>
                            <a href="{{ route('announcements.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/announcements') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/announcements') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Announcements
                                </span>
                            </a>
                        </li>

                        <!-- Polls -->
                        <li>
                            <a href="{{ route('polls.index') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/polls') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/polls') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Polls
                                </span>
                            </a>
                        </li>

                    </ul>
                </div>

                <!-- Teacher Menu Items -->
                @can('content.view')
                    <div>
                        <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                            :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                            'lg:justify-center' : 'justify-start'">
                            <template
                                x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                                <span>Teaching</span>
                            </template>
                            <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                                </svg>
                            </template>
                        </h2>

                        <ul class="flex flex-col gap-1">
                            <li>
                                <a href="{{ route('instructor.dashboard') }}" wire:navigate class="menu-item group"
                                    :class="[
                                        isActive('/instructor') ? 'menu-item-active' : 'menu-item-inactive',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                        'xl:justify-center' : 'justify-start'
                                    ]">
                                    <span :class="isActive('/instructor') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V3h-8v18zm0-8h8v-6h-8v6z" fill="currentColor"></path></svg>
                                    </span>
                                    <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="menu-item-text flex items-center gap-2">
                                        Dashboard
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('instructor.trainings') }}" wire:navigate class="menu-item group"
                                    :class="[
                                        isActive('/instructor/trainings') ? 'menu-item-active' : 'menu-item-inactive',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                        'xl:justify-center' : 'justify-start'
                                    ]">
                                    <span :class="isActive('/instructor/trainings') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" fill="currentColor"></path></svg>
                                    </span>
                                    <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="menu-item-text flex items-center gap-2">
                                        My Trainings
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('instructor.sessions') }}" wire:navigate class="menu-item group"
                                    :class="[
                                        isActive('/instructor/sessions') ? 'menu-item-active' : 'menu-item-inactive',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                        'xl:justify-center' : 'justify-start'
                                    ]">
                                    <span :class="isActive('/instructor/sessions') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" fill="currentColor"></path></svg>
                                    </span>
                                    <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="menu-item-text flex items-center gap-2">
                                        Teaching Sessions
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('instructor.materials') }}" wire:navigate class="menu-item group"
                                    :class="[
                                        isActive('/instructor/materials') ? 'menu-item-active' : 'menu-item-inactive',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                        'xl:justify-center' : 'justify-start'
                                    ]">
                                    <span :class="isActive('/instructor/materials') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" fill="currentColor"></path></svg>
                                    </span>
                                    <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="menu-item-text flex items-center gap-2">
                                        Course Materials
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('grades.index') }}" wire:navigate class="menu-item group"
                                    :class="[
                                        isActive('/grades') ? 'menu-item-active' : 'menu-item-inactive',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                        'xl:justify-center' : 'justify-start'
                                    ]">
                                    <span :class="isActive('/grades') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" fill="currentColor"></path></svg>
                                    </span>
                                    <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="menu-item-text flex items-center gap-2">
                                        Grade Book
                                    </span>
                                </a>
                            </li>

                            @can('portfolio.view')
                            <li>
                                <a href="{{ route('teacher.portfolios') }}" wire:navigate class="menu-item group"
                                    :class="[
                                        isActive('/teacher/portfolios') ? 'menu-item-active' : 'menu-item-inactive',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                        'xl:justify-center' : 'justify-start'
                                    ]">
                                    <span :class="isActive('/teacher/portfolios') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" fill="currentColor"></path></svg>
                                    </span>
                                    <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="menu-item-text flex items-center gap-2">
                                        Portfolios
                                    </span>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                @endcan

                <!-- Section: Administration -->
                @hasanyrole('admin|super-admin|President|Treasurer|General Secretary')
                <div>
                    <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                        'lg:justify-center' : 'justify-start'">
                        <template
                            x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                            <span>Administration</span>
                        </template>
                        <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                            </svg>
                        </template>
                    </h2>

                    <ul class="flex flex-col gap-1">
                        <!-- Admin Dashboard -->
                        @hasanyrole('admin|super-admin')
                        <li>
                            <a href="{{ url('/admin') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 3L4 9v12h16V9l-8-6z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Dashboard
                                </span>
                            </a>
                        </li>

                        <!-- User Management -->
                        <li>
                            <a href="{{ url('/admin/users') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/users') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/users') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    User Management
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/users?tab=pending') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/pending-members') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/pending-members') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2ZM0 12C0 5.373 5.373 0 12 0s12 5.373 12 12-5.373 12-12 12S0 17.627 0 12ZM12 8a1 1 0 011 1v3.586l2.707 2.707A1 1 0 0114 18v.05a1 1 0 01-.55.9 1 1 0 01-1.05-.05L10 16.293V14.5a1 1 0 011-1h1.5a1 1 0 010 2H13a1 1 0 01-1-1V9a1 1 0 011-1z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Pending Approvals
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/users?tab=alumni') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/alumni') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/alumni') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2.25C9.37665 2.25 7.25 4.37665 7.25 7C7.25 9.62335 9.37665 11.75 12 11.75C14.6234 11.75 16.75 9.62335 16.75 7C16.75 4.37665 14.6234 2.25 12 2.25ZM8.75 7C8.75 5.20507 10.2051 3.75 12 3.75C13.7949 3.75 15.25 5.20507 15.25 7C15.25 8.79493 13.7949 10.25 12 10.25C10.2051 10.25 8.75 8.79493 8.75 7ZM5.75 14.25C5.75 13.4216 6.42157 12.75 7.25 12.75H16.75C17.5784 12.75 18.25 13.4216 18.25 14.25V19.25C18.25 20.0784 17.5784 20.75 16.75 20.75H7.25C6.42157 20.75 5.75 20.0784 5.75 19.25V14.25ZM7.25 14.25V19.25H16.75V14.25H7.25Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Alumni
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/roles') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/roles-permissions') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/roles-permissions') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Roles & Permissions
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/manage-announcements') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/manage-announcements') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/announcements') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Announcements
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/meetings') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/meetings') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/meetings') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Meetings
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/questions') }}" class="menu-item group"
                                :class="[
                                    isActive('/admin/questions') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/questions') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2ZM4 12c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8-8-3.582-8-8Zm8-2c-.934 0-1.791.32-2.478.854l1.463 1.454a5.39 5.39 0 00-.634.692c-.394-.524-.631-1.167-.631-1.857 0-.376.062-.739.175-1.083l-1.238.619c-.17.408-.269.843-.269 1.321 0 .152.013.301.038.447A5.406 5.406 0 008.116 9.54l1.464-1.463A7.972 7.972 0 0112 4c.322 0 .639.019.951.054l-.512 1.577c-.217-.022-.434-.031-.651-.031-.69 0-1.352.102-1.974.293l.74 1.425c.425-.137.871-.21 1.326-.21Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Questions
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/membership-statistics') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/membership-statistics') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/membership-statistics') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V3h-8v18zm0-8h8v-6h-8v6z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Membership Statistics
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/member-export') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/member-export') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/member-export') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13 12H3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Export Members
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/manage-badges') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/badges') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/badges') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Badges & Gamification
                                </span>
                            </a>
                        </li>
                        @endhasanyrole

                        <!-- Financial Management -->
                        @hasanyrole('Treasurer|President|super-admin')
                        <li>
                            <a href="{{ url('/admin/financial-report') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/treasurer-dashboard') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/treasurer-dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V3h-8v18zm0-8h8v-6h-8v6z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Treasurer Dashboard
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/transactions') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/transactions') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/transactions') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Transactions
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/budget-categories') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/budget-categories') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/budget-categories') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Budget Categories
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/manage-fines') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/manage-fines') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/fines') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Fines Management
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/manage-fine-types') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/manage-fine-types') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/fine-types') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7 7h10v3H7V7zm0 4h10v3H7v-3zm0 4h10v3H7v-3z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Fine Types
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/admin/financial-report') }}" wire:navigate class="menu-item group"
                                :class="[
                                    isActive('/admin/financial-report') ? 'menu-item-active' : 'menu-item-inactive',
                                    (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                    'xl:justify-center' : 'justify-start'
                                ]">
                                <span :class="isActive('/admin/financial-reports') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9 17h6m0 0v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2H2a2 2 0 002-2zm0 0V9a2 2 0 012-2h-2a2 2 0 00-2 2v6a2 2 0 002 2H2A2 2 0 002-2zm0 0H8v-2a2 2 0 012-2h-2a2 2 0 00-2 2V2z" fill="currentColor"></path></svg>
                                </span>
                                <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                    class="menu-item-text flex items-center gap-2">
                                    Financial Reports
                                </span>
                            </a>
                        </li>
                        @endhasanyrole
                    </ul>
                </div>
                @endhasanyrole

                @endif

            </div>
        </nav>

        @impersonating($guard = null)
            <a href="{{ route('impersonate.leave') }}">Leave impersonation</a>
        @endImpersonating

    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    class="fixed z-50 h-screen w-full bg-gray-900/50"></div>
