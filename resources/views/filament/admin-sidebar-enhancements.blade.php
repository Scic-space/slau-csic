<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" rel="stylesheet">

<style>
    .fi-sidebar,
    .fi-body {
        font-family: 'Google Sans Flex', 'Google Sans', Inter, ui-sans-serif, sans-serif;
    }

    .fi-sidebar-nav {
        padding-inline: .75rem;
    }

    .fi-sidebar-group {
        gap: .25rem;
    }

    .fi-sidebar-group-btn {
        min-height: 2.75rem;
        gap: .75rem;
        border-radius: .25rem;
        padding: .625rem .75rem;
        transition: background-color 150ms ease, color 150ms ease;
    }

    .fi-sidebar-group-btn:hover,
    .fi-sidebar-group.fi-active > .fi-sidebar-group-btn {
        background: color-mix(in srgb, var(--primary-500) 10%, transparent);
    }

    .fi-sidebar-group.fi-active > .fi-sidebar-group-btn .fi-sidebar-group-label,
    .fi-sidebar-group.fi-active > .fi-sidebar-group-btn > .material-symbols-outlined {
        color: var(--primary-600);
    }

    .dark .fi-sidebar-group.fi-active > .fi-sidebar-group-btn .fi-sidebar-group-label,
    .dark .fi-sidebar-group.fi-active > .fi-sidebar-group-btn > .material-symbols-outlined {
        color: var(--primary-400);
    }

    .fi-sidebar-group-label {
        color: var(--gray-700);
        font-size: .875rem;
        font-weight: 600;
    }

    .dark .fi-sidebar-group-label {
        color: var(--gray-300);
    }

    .fi-sidebar-group-items {
        margin-inline-start: 1.25rem;
        border-inline-start: 1px solid var(--gray-200);
        padding-inline-start: .625rem;
    }

    .dark .fi-sidebar-group-items {
        border-color: var(--gray-700);
    }

    .fi-sidebar-item-btn {
        min-height: 2.375rem;
        border-radius: .25rem;
        padding-block: .5rem;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        box-shadow: inset 3px 0 0 var(--primary-500);
    }

    .fi-sidebar .material-symbols-outlined {
        display: inline-flex;
        width: 1.375rem;
        height: 1.375rem;
        flex: none;
        align-items: center;
        justify-content: center;
        font-family: 'Material Symbols Outlined';
        font-size: 1.375rem;
        font-style: normal;
        font-weight: 400;
        line-height: 1;
        vertical-align: middle;
    }

    .member-status-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: .75rem;
    }

    .member-status-card {
        display: flex;
        min-height: 8rem;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid var(--gray-200);
        border-radius: .25rem;
        background: white;
        padding: 1rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / .05);
        transition: border-color 150ms ease, box-shadow 150ms ease, transform 150ms ease;
    }

    .member-status-card:hover,
    .member-status-card.is-active {
        border-color: #14b8a6;
        box-shadow: 0 6px 18px rgb(15 118 110 / .12);
        transform: translateY(-2px);
    }

    .member-status-card:focus-visible {
        outline: 2px solid #14b8a6;
        outline-offset: 2px;
    }

    .member-status-card-icon {
        display: inline-flex;
        width: 2.5rem;
        height: 2.5rem;
        align-items: center;
        justify-content: center;
        border-radius: .25rem;
    }

    .transaction-status-card {
        min-height: 7rem;
        flex-direction: row;
        align-items: center;
    }

    .dark .member-status-card {
        border-color: var(--gray-700);
        background: var(--gray-900);
    }

    @media (min-width: 48rem) {
        .member-status-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 64rem) {
        .member-status-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
</style>

<script>
    (() => {
        const sectionIcons = {
            Membership: 'groups',
            Events: 'calendar_month',
            Meetings: 'groups_2',
            Finance: 'account_balance_wallet',
            Fines: 'gavel',
            Exams: 'quiz',
            CTF: 'flag',
            Testimonials: 'reviews',
            Elections: 'how_to_vote',
            Assignments: 'assignment_ind',
            Projects: 'account_tree',
            System: 'settings',
        };

        const itemIcons = {
            Dashboard: 'dashboard',
            'My Profile': 'account_circle',
            Users: 'manage_accounts',
            'Pending Approvals': 'pending_actions',
            Alumni: 'history_edu',
            Badges: 'military_tech',
            Events: 'event',
            Calendar: 'calendar_month',
            Categories: 'category',
            Registrations: 'how_to_reg',
            Attendance: 'fact_check',
            Analytics: 'analytics',
            Meetings: 'groups_2',
            Transactions: 'receipt_long',
            'Budget Categories': 'account_balance',
            Fines: 'gavel',
            'Fine Types': 'rule',
            Appeals: 'support_agent',
            Exams: 'quiz',
            'CTF Dashboard': 'dashboard',
            'CTF Competitions': 'flag',
            Submissions: 'upload_file',
            Writeups: 'article',
            Testimonials: 'reviews',
            'Contact Messages': 'mail',
            Elections: 'how_to_vote',
            'Role Templates': 'assignment_ind',
            Projects: 'account_tree',
            'System Overview': 'monitoring',
            'Roles & Permissions': 'admin_panel_settings',
            Announcements: 'campaign',
            News: 'newspaper',
            Settings: 'settings',
            'Content Pages': 'description',
            'Audit Log': 'history',
        };

        const materialIcon = (name, extraClasses = '') => {
            const icon = document.createElement('span');
            icon.className = `material-symbols-outlined ${extraClasses}`.trim();
            icon.textContent = name;
            icon.setAttribute('aria-hidden', 'true');

            return icon;
        };

        const enhanceAdminSidebar = () => {
            document.querySelectorAll('.fi-sidebar-group[data-group-label]').forEach((group) => {
                const label = group.dataset.groupLabel;
                const button = group.querySelector(':scope > .fi-sidebar-group-btn');

                if (button && sectionIcons[label] && !button.querySelector(':scope > .material-symbols-outlined')) {
                    button.prepend(materialIcon(sectionIcons[label]));
                }

                if (!group.classList.contains('fi-active')) return;

                const collapsedGroups = JSON.parse(localStorage.getItem('collapsedGroups') || '[]');
                const expandedGroups = collapsedGroups.filter((collapsedGroup) => collapsedGroup !== label);
                localStorage.setItem('collapsedGroups', JSON.stringify(expandedGroups));
                group.classList.remove('fi-collapsed');
                group.querySelector(':scope > .fi-sidebar-group-items')?.style.removeProperty('display');

                if (window.Alpine?.store('sidebar')) {
                    window.Alpine.store('sidebar').collapsedGroups = expandedGroups;
                }
            });

            document.querySelectorAll('.fi-sidebar-item').forEach((item) => {
                const label = item.querySelector('.fi-sidebar-item-label')?.textContent.trim();
                const icon = itemIcons[label];
                const button = item.querySelector(':scope > .fi-sidebar-item-btn');
                if (!icon || !button || button.querySelector('.material-symbols-outlined')) return;

                button.querySelector('.fi-sidebar-item-icon')?.remove();
                button.prepend(materialIcon(icon, 'fi-icon fi-size-lg fi-sidebar-item-icon'));
            });
        };

        queueMicrotask(enhanceAdminSidebar);
        document.addEventListener('livewire:navigated', enhanceAdminSidebar);
    })();
</script>
