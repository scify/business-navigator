import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import process from 'node:process';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode, command }) => {
    // @link https://v4.vitejs.dev/config/#using-environment-variables-in-config
    const env = loadEnv(mode, process.cwd(), '');

    // Development environment configuration:
    const DEV_URL = `${env.VITE_DEV_URL ?? 'https://unknown.ddev.site'}`;
    const DEV_PORT = `${env.VITE_DEV_PORT ?? '5179'}`;
    const IS_DDEV = process.env.IS_DDEV_PROJECT === 'true';
    if (env.APP_ENV === 'local') {
        if (!IS_DDEV) {
            console.warn(
                "\n\x1b[31mWarning: Please always lunch development environment via 'ddev npm run dev'!\x1b[0m\n",
            );
        }
        if (DEV_URL === 'https://unknown.ddev.site/') {
            console.warn(
                "\x1b[31mWarning: Please set VITE_DEV_URL & VITE_DEV_PORT on '.env' based on your DDEV config!\x1b[0m\n",
            );
        } else {
            if (command === 'serve') {
                console.log(
                    `DDEV exposed Vite via \x1b[36m${DEV_URL}:${DEV_PORT}\x1b[0m`,
                );
                console.log(`Enjoy hot-loading at \x1b[36m${DEV_URL}\x1b[0m !`);
            }
        }
    }

    return {
        resolve: {
            alias: {
                '@': '/resources',
            }
        },
        plugins: [
            laravel({
                input: 'resources/scripts/app.ts',
                ssr: 'resources/scripts/ssr.ts',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        optimizeDeps: {
            include: ['mapbox-gl'],
        },
        server: {
            // Vite Development Server configuration: Allows to run & use
            // `ddev npm run dev` for Laravel & Node.js (e.g. Vue) hot-reload.
            // @link https://ddev.com/blog/working-with-vite-in-ddev/#laravel
            host: '0.0.0.0',
            port: DEV_PORT,
            scriptPort: true,
            // Defines the origin of generated asset URLs during development:
            origin: `${DEV_URL}:${DEV_PORT}`,
            cors: {
                origin: [
                    `${DEV_URL}`,
                    `${DEV_URL}:8443`,
                    `${DEV_URL}:${DEV_PORT}`
                ],
            },
        },
        css: {
            preprocessorOptions: {
                scss: {
                    // Required to use modern API for SASS on Vite 4.x:
                    api: 'modern-compiler',
                    // Silences dependencies for Bootstrap 5.3.3 as it still depends on them:
                    silenceDeprecations: [
                        'mixed-decls',
                        'color-functions',
                        'import',
                        'global-builtin',
                    ],
                },
            },
        },
    };
});
