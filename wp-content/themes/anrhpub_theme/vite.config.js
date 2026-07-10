import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: resolve(__dirname, 'assets/js'),
    emptyOutDir: false,
    rollupOptions: {
      input: resolve(__dirname, 'assets/js/src/main.js'),
      output: {
        entryFileNames: 'main.js',
        format: 'iife',
        name: 'AnrhpubTheme',
        inlineDynamicImports: true,
      },
    },
    minify: false,
    sourcemap: false,
  },
});
