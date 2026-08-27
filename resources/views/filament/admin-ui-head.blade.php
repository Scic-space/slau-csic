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
