import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const pages = import.meta.glob('./pages/**/*.tsx', { eager: true });

createInertiaApp({
    resolve: (name) => {
        const path = `./pages/${name}.tsx`;
        const page = pages[path];
        if (!page) {
            throw new Error(`Page not found: ${path}`);
        }

        return page;
    },
    setup({ el, App, props }) {
        const pageComponent = props.initialPage.component;
        const isStandalonePage = pageComponent.startsWith('auth/');

        createRoot(el).render(
            <div
                data-inertia-page={pageComponent}
                className={isStandalonePage ? 'inertia-root inertia-root--standalone' : 'inertia-root'}
            >
                <App {...props} />
            </div>,
        );
    },
});
