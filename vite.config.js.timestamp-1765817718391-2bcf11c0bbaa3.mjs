// vite.config.js
import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "file:///D:/local%20wp%20-%20site/hydra/app/public/wp-content/plugins/hydra-booking/node_modules/vite/dist/node/index.js";
import vue from "file:///D:/local%20wp%20-%20site/hydra/app/public/wp-content/plugins/hydra-booking/node_modules/@vitejs/plugin-vue/dist/index.mjs";
var __vite_injected_original_import_meta_url = "file:///D:/local%20wp%20-%20site/hydra/app/public/wp-content/plugins/hydra-booking/vite.config.js";
var vite_config_default = defineConfig({
  plugins: [
    vue()
  ],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", __vite_injected_original_import_meta_url))
    }
  },
  build: {
    outDir: "./build",
    assetsDir: "assets",
    rollupOptions: {
      output: {
        inlineDynamicImports: true,
        entryFileNames: "assets/tfhb-admin-app-script.js",
        assetFileNames: `assets/tfhb-admin-app.[ext]`
      }
    }
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJEOlxcXFxsb2NhbCB3cCAtIHNpdGVcXFxcaHlkcmFcXFxcYXBwXFxcXHB1YmxpY1xcXFx3cC1jb250ZW50XFxcXHBsdWdpbnNcXFxcaHlkcmEtYm9va2luZ1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiRDpcXFxcbG9jYWwgd3AgLSBzaXRlXFxcXGh5ZHJhXFxcXGFwcFxcXFxwdWJsaWNcXFxcd3AtY29udGVudFxcXFxwbHVnaW5zXFxcXGh5ZHJhLWJvb2tpbmdcXFxcdml0ZS5jb25maWcuanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL0Q6L2xvY2FsJTIwd3AlMjAtJTIwc2l0ZS9oeWRyYS9hcHAvcHVibGljL3dwLWNvbnRlbnQvcGx1Z2lucy9oeWRyYS1ib29raW5nL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZmlsZVVSTFRvUGF0aCwgVVJMIH0gZnJvbSAnbm9kZTp1cmwnXHJcblxyXG5pbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tICd2aXRlJ1xyXG5pbXBvcnQgdnVlIGZyb20gJ0B2aXRlanMvcGx1Z2luLXZ1ZSdcclxuXHJcbi8vIGh0dHBzOi8vdml0ZWpzLmRldi9jb25maWcvXHJcbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XHJcbiAgcGx1Z2luczogW1xyXG4gICAgdnVlKCksXHJcbiAgXSwgXHJcbiBcclxuICByZXNvbHZlOiB7XHJcbiAgICBhbGlhczoge1xyXG4gICAgICAnQCc6IGZpbGVVUkxUb1BhdGgobmV3IFVSTCgnLi9zcmMnLCBpbXBvcnQubWV0YS51cmwpKVxyXG4gICAgfVxyXG4gIH0sXHJcbiAgYnVpbGQ6IHtcclxuICAgIG91dERpcjogJy4vYnVpbGQnLFxyXG4gICAgYXNzZXRzRGlyOiAnYXNzZXRzJyxcclxuXHJcbiAgICByb2xsdXBPcHRpb25zOiB7XHJcbiAgICAgIG91dHB1dDoge1xyXG4gICAgICAgIGlubGluZUR5bmFtaWNJbXBvcnRzOiB0cnVlLFxyXG4gICAgICAgIGVudHJ5RmlsZU5hbWVzOiAnYXNzZXRzL3RmaGItYWRtaW4tYXBwLXNjcmlwdC5qcycsXHJcbiAgICAgICAgYXNzZXRGaWxlTmFtZXM6IGBhc3NldHMvdGZoYi1hZG1pbi1hcHAuW2V4dF1gXHJcbiAgICAgIH1cclxuICAgIH0sXHJcbiAgfVxyXG59KVxyXG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQW9aLFNBQVMsZUFBZSxXQUFXO0FBRXZiLFNBQVMsb0JBQW9CO0FBQzdCLE9BQU8sU0FBUztBQUgrTyxJQUFNLDJDQUEyQztBQU1oVCxJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUMxQixTQUFTO0FBQUEsSUFDUCxJQUFJO0FBQUEsRUFDTjtBQUFBLEVBRUEsU0FBUztBQUFBLElBQ1AsT0FBTztBQUFBLE1BQ0wsS0FBSyxjQUFjLElBQUksSUFBSSxTQUFTLHdDQUFlLENBQUM7QUFBQSxJQUN0RDtBQUFBLEVBQ0Y7QUFBQSxFQUNBLE9BQU87QUFBQSxJQUNMLFFBQVE7QUFBQSxJQUNSLFdBQVc7QUFBQSxJQUVYLGVBQWU7QUFBQSxNQUNiLFFBQVE7QUFBQSxRQUNOLHNCQUFzQjtBQUFBLFFBQ3RCLGdCQUFnQjtBQUFBLFFBQ2hCLGdCQUFnQjtBQUFBLE1BQ2xCO0FBQUEsSUFDRjtBQUFBLEVBQ0Y7QUFDRixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=
