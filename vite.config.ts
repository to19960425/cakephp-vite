import { defineConfig } from 'vite';
import { resolve } from 'path';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [react()],
    build: {
        emptyOutDir: false,
        outDir: './webroot',
        manifest: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'resources/js/main.tsx'),
            },
        },
    },
    server: {
        port: 3000,
        cors: true,
    },
});
