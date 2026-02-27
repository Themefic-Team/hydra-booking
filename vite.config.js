const path = require('node:path');
const { defineConfig } = require('vite');
const vue = require('@vitejs/plugin-vue');

// https://vitejs.dev/config/
module.exports = defineConfig({
  plugins: [
    vue(),
  ], 
 
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
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
});
