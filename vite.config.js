import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS
                'resources/css/app.css',
                'resources/css/global.css',
                'resources/css/timesheets/edit.css',
                'resources/css/util/dataTables.dataTables.min.css',

                // JS
                'resources/js/app.js',
                'resources/js/util/dataTables.min.js',
                'resources/js/leave_requests/index.js',
                'resources/js/timesheets/index.js',
                'resources/js/timesheets/edit.js',
            ],
            refresh: true,
        }),
    ],
    resolve : {
        alias: {
            '$':'jQuery',
        }
    },
});
