import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/fontawesome.css',
                'resources/css/templatemo-lugx-gaming.css',
                'resources/css/templatemo-stand-blog.css',
                'resources/css/owl.css',
                'resources/css/animate.css',
                'resources/js/app.js',
                'resources/js/shop.js',
                'resources/js/blog.js',
                'resources/js/customblog.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
