<footer
    @class([
        'system-footer',
        'system-footer-fixed' => $isFixed ?? false,
    ])
    @if ($isFixed ?? false)
        :class="{
            'xl:left-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
            'xl:left-[80px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
            'left-0': $store.sidebar.isMobileOpen
        }"
    @endif
    aria-label="Site footer"
>
    <p>All rights reserved &copy; SCIC Cyber</p>
    <a href="mailto:sciccyber8@gmail.com">
        <span class="material-symbols-outlined" aria-hidden="true">mail</span>
        sciccyber8@gmail.com
    </a>
</footer>
