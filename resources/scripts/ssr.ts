import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Business Navigator';

createServer((page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => {
                if (!title || title === appName) {
                    return `${appName} - Explore the European AI Landscape`;
                }
                return `${title} - ${appName}`;
            },
            resolve: resolvePage,
            setup: ({ App, props, plugin }) =>
                createSSRApp({ render: () => h(App, props) })
                    .use(plugin)
                    .use(ZiggyVue, {
                        ...page.props.ziggy,
                        location: new URL(page.props.ziggy.location),
                    }),
        }),
    { cluster: true },
);

function resolvePage(name: string) {
    const pages = import.meta.glob<DefineComponent>('../views/pages/**/*.vue');

    return resolvePageComponent<DefineComponent>(`../views/pages/${name}.vue`, pages);
}
