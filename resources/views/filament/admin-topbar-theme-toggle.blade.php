<button
    type="button"
    class="admin-topbar-icon-button"
    x-data="{ currentTheme: $store.theme }"
    x-effect="currentTheme = $store.theme"
    x-on:click="
        const nextTheme = $store.theme === 'dark' ? 'light' : 'dark';
        $dispatch('theme-changed', nextTheme);
    "
    x-bind:aria-label="currentTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
    x-bind:title="currentTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
>
    <span class="material-symbols-outlined" aria-hidden="true" x-text="currentTheme === 'dark' ? 'light_mode' : 'dark_mode'"></span>
</button>
