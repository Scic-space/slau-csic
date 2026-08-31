<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" rel="stylesheet">

<style>
    :root {
        --radius-md: 0.25rem;
        --radius-lg: 0.25rem;
        --radius-xl: 0.25rem;

        /* System theme (Light/Dark) — Filament ships its own precompiled CSS
           pipeline, separate from resources/css/app.css, so the admin panel's
           chrome, cards, tables, modals, dropdowns and form inputs are
           re-themed here using the same semantic values as the SPA layout. */
        --admin-background: oklch(96.8% 0.007 247.896);
        --admin-sidebar: #ffffff;
        --admin-card: #ffffff;
        --admin-card-hover: #f9fafb;
        --admin-border: #e4e7ec;
        --admin-input: #ffffff;
        --admin-foreground: #101828;
        --admin-muted-foreground: #667085;
    }

    .dark {
        --admin-background: oklch(18% 0.035 255);
        --admin-sidebar: oklch(14% 0.035 255);
        --admin-card: oklch(23% 0.035 255);
        --admin-card-hover: oklch(26% 0.04 255);
        --admin-border: oklch(31% 0.035 255);
        --admin-input: oklch(25% 0.035 255);
        --admin-foreground: oklch(96% 0.01 255);
        --admin-muted-foreground: oklch(70% 0.025 255);
    }

    .fi-body,
    .fi-sidebar,
    .fi-sidebar-header,
    .fi-topbar {
        background-color: var(--admin-sidebar);
    }

    .fi-topbar {
        min-height: 4.75rem;
        gap: 1rem;
        border-bottom: 1px solid var(--admin-border);
        padding-inline: 1.5rem;
        box-shadow: none;
        font-family: 'Google Sans Flex', 'Google Sans', ui-sans-serif, sans-serif;
    }

    .fi-topbar-start {
        margin-inline-end: 1rem;
        gap: 1rem;
    }

    .fi-topbar-end {
        gap: .625rem;
    }

    .admin-topbar-icon-button,
    .fi-topbar-database-notifications-btn,
    .fi-user-menu-trigger,
    .fi-topbar-open-sidebar-btn,
    .fi-topbar-close-sidebar-btn,
    .fi-topbar-open-collapse-sidebar-btn,
    .fi-topbar-close-collapse-sidebar-btn {
        min-width: 2.75rem;
        min-height: 2.75rem;
        border: 1px solid var(--admin-border);
        border-radius: .5rem;
        background: transparent;
        color: var(--admin-muted-foreground);
        transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
    }

    .admin-topbar-icon-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .admin-topbar-icon-button:hover,
    .fi-topbar-database-notifications-btn:hover,
    .fi-user-menu-trigger:hover,
    .fi-topbar-open-sidebar-btn:hover,
    .fi-topbar-close-sidebar-btn:hover,
    .fi-topbar-open-collapse-sidebar-btn:hover,
    .fi-topbar-close-collapse-sidebar-btn:hover {
        border-color: color-mix(in srgb, var(--admin-muted-foreground) 35%, var(--admin-border));
        background: var(--admin-card-hover);
        color: var(--admin-foreground);
    }

    .admin-topbar-icon-button:focus-visible,
    .fi-topbar-database-notifications-btn:focus-visible,
    .fi-user-menu-trigger:focus-visible,
    .fi-topbar-open-sidebar-btn:focus-visible,
    .fi-topbar-close-sidebar-btn:focus-visible,
    .fi-topbar-open-collapse-sidebar-btn:focus-visible,
    .fi-topbar-close-collapse-sidebar-btn:focus-visible {
        outline: 2px solid var(--primary-500);
        outline-offset: 2px;
    }

    .fi-user-menu-trigger {
        width: auto;
        gap: .5rem;
        padding: .25rem .5rem;
    }

    .fi-user-menu-trigger .fi-user-avatar {
        width: 2rem;
        height: 2rem;
    }

    .admin-user-name {
        max-width: 10rem;
        overflow: hidden;
        color: var(--admin-foreground);
        font-size: .875rem;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-topbar-material-icon {
        font-size: 1.375rem;
    }

    .admin-user-menu-chevron {
        font-size: 1.125rem;
    }

    .fi-global-search {
        display: none !important;
    }

    .admin-dashboard-greeting {
        display: block;
        color: var(--admin-foreground);
        font-family: 'Google Sans Flex', 'Google Sans', ui-sans-serif, sans-serif;
    }

    .admin-dashboard-title {
        display: block;
    }

    .admin-dashboard-greeting-copy {
        color: var(--admin-muted-foreground);
        font-size: inherit;
        font-weight: inherit;
        line-height: inherit;
    }

    .admin-dashboard-greeting-name {
        margin-inline-start: .35rem;
        color: var(--admin-foreground);
        font-size: inherit;
        font-weight: inherit;
        line-height: inherit;
    }

    .admin-dashboard-title {
        margin-top: .75rem;
    }

    @media (max-width: 639px) {
        .fi-topbar {
            min-height: 4rem;
            gap: .625rem;
            padding-inline: .75rem;
        }

        .fi-topbar-end {
            gap: .375rem;
        }

        .admin-user-name,
        .admin-user-menu-chevron {
            display: none;
        }

        .admin-topbar-icon-button,
        .fi-topbar-database-notifications-btn,
        .fi-user-menu-trigger,
        .fi-topbar-open-sidebar-btn,
        .fi-topbar-close-sidebar-btn {
            min-width: 2.25rem;
            min-height: 2.25rem;
        }
    }

    .fi-body {
        background-color: var(--admin-background);
    }

    .fi-section,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat,
    .fi-modal-window,
    .fi-dropdown-panel,
    .fi-fo-repeater-item {
        background-color: var(--admin-card);
        border-color: var(--admin-border);
    }

    .fi-ta-header-cell {
        background-color: var(--admin-sidebar);
    }

    .fi-ta-row {
        border-color: var(--admin-border);
    }

    .dark .fi-ta-row:hover {
        background-color: var(--admin-card-hover);
    }

    .fi-input,
    .fi-select-input,
    .fi-textarea {
        background-color: var(--admin-input);
    }

    .dark .fi-section,
    .dark .fi-ta-ctn,
    .dark .fi-wi-stats-overview-stat,
    .dark .fi-modal-window,
    .dark .fi-dropdown-panel,
    .dark .fi-ta-header-cell,
    .dark .fi-input,
    .dark .fi-select-input,
    .dark .fi-textarea {
        border-color: var(--admin-border);
    }

    /* Custom admin pages (e.g. the Event Calendar) predate this project's
       theme tokens and reference stray, non-standard class names — map
       them onto the same admin tokens rather than leaving them undefined. */
    .border-stroke {
        border-color: #e4e7ec;
    }

    .dark .border-strokedark {
        border-color: var(--admin-border);
    }

    .bg-boxdark {
        background-color: var(--admin-card);
    }

    .material-symbols-outlined {
        display: inline-block;
        flex-shrink: 0;
        font-family: 'Material Symbols Outlined';
        font-size: 1.375rem;
        font-style: normal;
        font-weight: normal;
        font-feature-settings: 'liga';
        letter-spacing: normal;
        line-height: 1;
        text-transform: none;
        vertical-align: middle;
        white-space: nowrap;
        -webkit-font-smoothing: antialiased;
    }

    .fi-section,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat,
    .fi-fo-repeater-item,
    .fi-modal-window,
    .fi-dropdown-panel {
        border-radius: 0.25rem;
    }

    .fi-main {
        padding-inline: 1rem;
    }

    .fi-page-header-main-ctn {
        gap: 1rem;
        padding-block: 1rem;
    }

    .fi-page-content {
        row-gap: 1rem;
    }

    .fi-page .fi-grid-layout.fi-sc-has-gap {
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .fi-main {
            padding-inline: 1.25rem;
        }

        .fi-page-header-main-ctn {
            padding-block: 1.25rem;
        }
    }

    @media (min-width: 768px) {
        .fi-main {
            padding-inline: 1.5rem;
        }
    }

    .system-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        width: 100%;
        border-top: 1px solid rgb(229 231 235);
        padding: 0.75rem 1.5rem;
        color: rgb(107 114 128);
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .system-footer a {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    .system-footer a:hover {
        color: rgb(217 119 6);
    }

    .system-footer .material-symbols-outlined {
        font-size: 1rem;
    }

    .dark .system-footer {
        border-color: var(--admin-border);
        color: var(--admin-muted-foreground);
    }

    @media (max-width: 639px) {
        .system-footer {
            flex-direction: column;
            justify-content: center;
            padding-inline: 1rem;
            text-align: center;
        }
    }
</style>
