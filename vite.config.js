import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['jquery', 'bootstrap', 'alpinejs', 'sweetalert2'],
                    datatables: ['datatables.net-bs5'],
                },
            },
        },
    },
});
