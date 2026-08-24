<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" rel="stylesheet">

<style>
    :root {
        --radius-md: 0.25rem;
        --radius-lg: 0.25rem;
        --radius-xl: 0.25rem;
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
        border-color: rgb(31 41 55);
        color: rgb(156 163 175);
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
