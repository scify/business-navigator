// noinspection JSUnusedLocalSymbols

import * as bootstrap from 'bootstrap';
import '../scss/app.scss';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Business Navigator';
const pinia = createPinia();
void bootstrap;

createInertiaApp({
    title: (title) => {
        if (!title || title === appName) {
            return `${appName} - Explore the European AI Landscape`;
        }
        return `${title} - ${appName}`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `../views/pages/${name}.vue`,
            import.meta.glob<DefineComponent>('../views/pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
}).catch((error) => {
    console.error('Failed to initialize app:', error);

    // Displays user-friendly error message:
    const errorElement = document.createElement('div');
    errorElement.innerHTML = `
        <div style="padding: 2rem; text-align: center; font-family: sans-serif;">
            <h2>Application Error</h2>
            <p>Sorry, the application failed to load. Please refresh the page or try again later.</p>
            <button onclick="window.location.reload()" style="padding: 0.5rem 1rem; margin-top: 1rem;">
                Reload Page
            </button>
        </div>
    `;
    document.body.appendChild(errorElement);
});
