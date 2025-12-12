// vite.config.js
import { defineConfig } from "file:///mnt/c/xampp/htdocs/StoreFlow/node_modules/vite/dist/node/index.js";
import laravel from "file:///mnt/c/xampp/htdocs/StoreFlow/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///mnt/c/xampp/htdocs/StoreFlow/node_modules/@vitejs/plugin-vue/dist/index.mjs";
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    })
  ],
  resolve: {
    alias: {
      "@": "/resources/js"
    }
  },
  server: {
    host: "0.0.0.0",
    // Listen on all network interfaces
    port: 5173,
    strictPort: true,
    hmr: {
      host: "localhost",
      // Use localhost for local development
      protocol: "ws"
    },
    watch: {
      usePolling: false
      // Disabled for better performance
      // Only use polling if file changes aren't detected
    }
  },
  // Fix for WSL2 crypto.hash issue
  optimizeDeps: {
    esbuildOptions: {
      target: "esnext"
    }
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCIvbW50L2MveGFtcHAvaHRkb2NzL1N0b3JlRmxvd1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiL21udC9jL3hhbXBwL2h0ZG9jcy9TdG9yZUZsb3cvdml0ZS5jb25maWcuanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL21udC9jL3hhbXBwL2h0ZG9jcy9TdG9yZUZsb3cvdml0ZS5jb25maWcuanNcIjtpbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tIFwidml0ZVwiO1xuaW1wb3J0IGxhcmF2ZWwgZnJvbSBcImxhcmF2ZWwtdml0ZS1wbHVnaW5cIjtcbmltcG9ydCB2dWUgZnJvbSBcIkB2aXRlanMvcGx1Z2luLXZ1ZVwiO1xuXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xuICAgIHBsdWdpbnM6IFtcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogW1wicmVzb3VyY2VzL2Nzcy9hcHAuY3NzXCIsIFwicmVzb3VyY2VzL2pzL2FwcC5qc1wiXSxcbiAgICAgICAgICAgIHJlZnJlc2g6IHRydWUsXG4gICAgICAgIH0pLFxuICAgICAgICB2dWUoe1xuICAgICAgICAgICAgdGVtcGxhdGU6IHtcbiAgICAgICAgICAgICAgICB0cmFuc2Zvcm1Bc3NldFVybHM6IHtcbiAgICAgICAgICAgICAgICAgICAgYmFzZTogbnVsbCxcbiAgICAgICAgICAgICAgICAgICAgaW5jbHVkZUFic29sdXRlOiBmYWxzZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgfSxcbiAgICAgICAgfSksXG4gICAgXSxcbiAgICByZXNvbHZlOiB7XG4gICAgICAgIGFsaWFzOiB7XG4gICAgICAgICAgICBcIkBcIjogXCIvcmVzb3VyY2VzL2pzXCIsXG4gICAgICAgIH0sXG4gICAgfSxcbiAgICBzZXJ2ZXI6IHtcbiAgICAgICAgaG9zdDogJzAuMC4wLjAnLCAvLyBMaXN0ZW4gb24gYWxsIG5ldHdvcmsgaW50ZXJmYWNlc1xuICAgICAgICBwb3J0OiA1MTczLFxuICAgICAgICBzdHJpY3RQb3J0OiB0cnVlLFxuICAgICAgICBobXI6IHtcbiAgICAgICAgICAgIGhvc3Q6ICdsb2NhbGhvc3QnLCAvLyBVc2UgbG9jYWxob3N0IGZvciBsb2NhbCBkZXZlbG9wbWVudFxuICAgICAgICAgICAgcHJvdG9jb2w6ICd3cycsXG4gICAgICAgIH0sXG4gICAgICAgIHdhdGNoOiB7XG4gICAgICAgICAgICB1c2VQb2xsaW5nOiBmYWxzZSwgLy8gRGlzYWJsZWQgZm9yIGJldHRlciBwZXJmb3JtYW5jZVxuICAgICAgICAgICAgLy8gT25seSB1c2UgcG9sbGluZyBpZiBmaWxlIGNoYW5nZXMgYXJlbid0IGRldGVjdGVkXG4gICAgICAgIH0sXG4gICAgfSxcbiAgICAvLyBGaXggZm9yIFdTTDIgY3J5cHRvLmhhc2ggaXNzdWVcbiAgICBvcHRpbWl6ZURlcHM6IHtcbiAgICAgICAgZXNidWlsZE9wdGlvbnM6IHtcbiAgICAgICAgICAgIHRhcmdldDogXCJlc25leHRcIixcbiAgICAgICAgfSxcbiAgICB9LFxufSk7XG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQXlRLFNBQVMsb0JBQW9CO0FBQ3RTLE9BQU8sYUFBYTtBQUNwQixPQUFPLFNBQVM7QUFFaEIsSUFBTyxzQkFBUSxhQUFhO0FBQUEsRUFDeEIsU0FBUztBQUFBLElBQ0wsUUFBUTtBQUFBLE1BQ0osT0FBTyxDQUFDLHlCQUF5QixxQkFBcUI7QUFBQSxNQUN0RCxTQUFTO0FBQUEsSUFDYixDQUFDO0FBQUEsSUFDRCxJQUFJO0FBQUEsTUFDQSxVQUFVO0FBQUEsUUFDTixvQkFBb0I7QUFBQSxVQUNoQixNQUFNO0FBQUEsVUFDTixpQkFBaUI7QUFBQSxRQUNyQjtBQUFBLE1BQ0o7QUFBQSxJQUNKLENBQUM7QUFBQSxFQUNMO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDTCxPQUFPO0FBQUEsTUFDSCxLQUFLO0FBQUEsSUFDVDtBQUFBLEVBQ0o7QUFBQSxFQUNBLFFBQVE7QUFBQSxJQUNKLE1BQU07QUFBQTtBQUFBLElBQ04sTUFBTTtBQUFBLElBQ04sWUFBWTtBQUFBLElBQ1osS0FBSztBQUFBLE1BQ0QsTUFBTTtBQUFBO0FBQUEsTUFDTixVQUFVO0FBQUEsSUFDZDtBQUFBLElBQ0EsT0FBTztBQUFBLE1BQ0gsWUFBWTtBQUFBO0FBQUE7QUFBQSxJQUVoQjtBQUFBLEVBQ0o7QUFBQTtBQUFBLEVBRUEsY0FBYztBQUFBLElBQ1YsZ0JBQWdCO0FBQUEsTUFDWixRQUFRO0FBQUEsSUFDWjtBQUFBLEVBQ0o7QUFDSixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=
