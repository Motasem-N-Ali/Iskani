import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fontsource } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Arabic pages. Preload only the two weights the first screen needs.
                fontsource('Cairo', {
                    weights: [400, 600, 700],
                    subsets: ['arabic', 'latin'],
                    display: 'swap',
                    preload: [{ weight: 400 }, { weight: 700 }],
                }),
                // English: Montserrat for interface text, Lora for headlines and long text.
                fontsource('Montserrat', {
                    weights: [400, 500, 600, 700],
                    subsets: ['latin'],
                    display: 'swap',
                    preload: false,
                }),
                fontsource('Lora', {
                    weights: [400, 600, 700],
                    subsets: ['latin'],
                    display: 'swap',
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
