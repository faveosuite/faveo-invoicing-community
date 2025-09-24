import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/assets/js/main.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/assets/js'),
        }
    },
    build: {
        chunkSizeWarningLimit: 1000,
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
})
