import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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

    // server: {
    //     host: '192.168.1.108',
    //     hmr: {
    //         protocol: 'ws',
    //         host: '192.168.1.108',
    //     },
    //     cors: true,
    // },
})
