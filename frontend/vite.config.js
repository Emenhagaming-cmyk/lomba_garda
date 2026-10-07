import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    envDir: '../backend',
    plugins: [
        laravel({
            input: ['src/app.css', 'src/app.js'],
            publicDirectory: '../backend/public',
            hotFile: '../backend/public/hot',
            refresh: [
                '../backend/app/**/*.php',
                '../backend/config/*.php',
                '../backend/resources/views/**/*.php',
                '../backend/routes/*.php',
            ],
        }),
        tailwindcss(),
        vue(),
    ],
    build: {
        emptyOutDir: true,
    },
    server: {
        watch: {
            ignored: [
                '**/vendor/**',
                '**/node_modules/**',
                '**/storage/framework/views/**',
            ],
        },
    },
});
