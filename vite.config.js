import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'app/License/Resources/js/app.js',
                'app/License/Resources/css/app.scss',
                'resources/assets/js/main.js'
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
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/assets/js'),
        },
    },
    build: {
        chunkSizeWarningLimit: 1000,
        outDir: 'public/build',
        manifest: 'manifest.json',
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Core Vue ecosystem — one shared vendor chunk
                    if (
                        id.includes('/node_modules/vue/') ||
                        id.includes('/node_modules/vue-router/') ||
                        id.includes('/node_modules/pinia/')
                    ) {
                        return 'vendor-vue'
                    }
                    // Everything else from node_modules
                    if (id.includes('/node_modules/')) {
                        return 'vendor'
                    }
                    // Group page bundles to reduce total chunk count
                    if (id.includes('/pages/settings/')) return 'chunk-settings'
                    if (id.includes('/pages/products/')) return 'chunk-products'
                    if (id.includes('/pages/reports/'))  return 'chunk-reports'
                },
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
            credentials: true
        }
    }
});
