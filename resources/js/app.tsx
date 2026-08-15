import './bootstrap';
import '../css/app.css';
import './lib/pwa'; // enregistre le service worker + capture l'installation PWA

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
        // Rien ne clignote sur une action instantanée ; au-delà de 250 ms la
        // barre et le spinner signalent clairement qu'une tâche est en cours.
        delay: 250,
        showSpinner: true,
    },
});