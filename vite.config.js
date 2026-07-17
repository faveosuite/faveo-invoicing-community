/* jshint node: true */
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { sentryVitePlugin } from '@sentry/vite-plugin';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

/* jshint -W079 */
const __dirname = path.dirname(fileURLToPath(import.meta.url));

function getAppVersion() {
    const contents = fs.readFileSync(path.resolve(__dirname, 'config/app.php'), 'utf-8');
    const match = contents.match(/'version'\s*=>\s*'([^']+)'/);
    return match?.[1];
}

export default defineConfig({
    base: './',
    plugins: [
        laravel({
            input: [
                'resources/assets/js/admin.js',
                'resources/assets/js/client.js',
            ],
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
        process.env.SENTRY_AUTH_TOKEN && sentryVitePlugin({
            org: 'ladybird-web-solution-pvt-ltd',
            project: 'faveo-invoicing',
            authToken: process.env.SENTRY_AUTH_TOKEN,
            release: { name: getAppVersion() },
            sourcemaps: { filesToDeleteAfterUpload: ['public/build/**/*.map'] },
        }),
    ].filter(Boolean),
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/assets/js'),
            '@recaptcha': path.resolve(__dirname, 'app/Plugins/Recaptcha/resources/assets/js/vue'),
        },
    },
    build: {
        chunkSizeWarningLimit: 1000,
        outDir: 'public/build',
        manifest: 'manifest.json',
        sourcemap: !!process.env.SENTRY_AUTH_TOKEN,
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: { host: 'localhost' },
        cors: {
            origin: '*',
            methods: ['GET', 'POST', 'PUT', 'DELETE'],
            allowedHeaders: ['Content-Type'],
        },
    },
});
