import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
  ], 
 
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    outDir: './build',
    assetsDir: 'assets',

    rollupOptions: {
      output: {
        inlineDynamicImports: true,
        entryFileNames: 'assets/tfhb-admin-app-script.js',
        assetFileNames: `assets/tfhb-admin-app.[ext]`
      }
    },
  }
})
