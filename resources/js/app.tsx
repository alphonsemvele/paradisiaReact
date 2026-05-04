import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';

const appName = import.meta.env.VITE_APP_NAME || 'Paradisia';

const pages = import.meta.glob<{ default: ComponentType }>('./pages/**/*.tsx');

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`];
        if (!page) throw new Error(`Page ${name} not found`);
        const module = await page();
        return module.default;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#10b981',
    },
});