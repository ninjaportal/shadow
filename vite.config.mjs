import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

const packageRoot = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    root: packageRoot,
    plugins: [tailwindcss()],
    build: {
        outDir: resolve(packageRoot, 'resources/dist'),
        emptyOutDir: true,
        cssCodeSplit: false,
        lib: {
            entry: resolve(packageRoot, 'resources/assets/shadow-theme.js'),
            name: 'ShadowTheme',
            fileName: () => 'shadow-theme.js',
            cssFileName: 'shadow-theme',
            formats: ['es'],
        },
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        return 'shadow-theme.css';
                    }

                    return assetInfo.name ?? '[name][extname]';
                },
            },
        },
    },
});
