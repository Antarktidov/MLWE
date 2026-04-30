import fs from 'fs';
import path from 'path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        svelte({
            compilerOptions: {
                compatibility: {
                    componentApi: 4
                }
            }
        }),
        {
            name: 'laravel-remove-hot-after-build',
            closeBundle() {
                try {
                    fs.rmSync(path.resolve('public/hot'), { force: true })
                } catch {
                    //
                }
            },
        },
    ],
});