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
        createRoot(el).render(<App {...props} />);
    },
});
