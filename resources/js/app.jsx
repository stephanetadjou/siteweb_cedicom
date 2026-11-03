import '../css/site.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

createInertiaApp({
    title: (title) => title, // titre complet construit dans Layout (traduit)
    // Seules les pages du site vitrine et du back office sont compilées (les pages Breeze restent hors build).
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob(['./Pages/Site/*.jsx', './Pages/Admin/**/*.jsx'])),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#F47B20',
    },
});
