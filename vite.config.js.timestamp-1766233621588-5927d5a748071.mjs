// vite.config.js
import { defineConfig } from "file:///mnt/c/xampp/htdocs/storeflow/node_modules/vite/dist/node/index.js";
import laravel from "file:///mnt/c/xampp/htdocs/storeflow/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///mnt/c/xampp/htdocs/storeflow/node_modules/@vitejs/plugin-vue/dist/index.mjs";
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
  build: {
    chunkSizeWarningLimit: 1e3,
    rollupOptions: {
      output: {
        manualChunks: void 0
      }
    }
  },
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
    cors: true,
    // Enable CORS for all origins
    hmr: {
      host: "192.168.1.235",
      // Windows host IP for network access
      protocol: "ws"
    },
    watch: {
      usePolling: true,
      // Required for WSL2 file system watching
      interval: 100
      // Check for changes every 100ms
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
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCIvbW50L2MveGFtcHAvaHRkb2NzL3N0b3JlZmxvd1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiL21udC9jL3hhbXBwL2h0ZG9jcy9zdG9yZWZsb3cvdml0ZS5jb25maWcuanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL21udC9jL3hhbXBwL2h0ZG9jcy9zdG9yZWZsb3cvdml0ZS5jb25maWcuanNcIjtpbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tIFwidml0ZVwiO1xuaW1wb3J0IGxhcmF2ZWwgZnJvbSBcImxhcmF2ZWwtdml0ZS1wbHVnaW5cIjtcbmltcG9ydCB2dWUgZnJvbSBcIkB2aXRlanMvcGx1Z2luLXZ1ZVwiO1xuXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xuICAgIHBsdWdpbnM6IFtcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogW1wicmVzb3VyY2VzL2Nzcy9hcHAuY3NzXCIsIFwicmVzb3VyY2VzL2pzL2FwcC5qc1wiXSxcbiAgICAgICAgICAgIHJlZnJlc2g6IHRydWUsXG4gICAgICAgIH0pLFxuICAgICAgICB2dWUoe1xuICAgICAgICAgICAgdGVtcGxhdGU6IHtcbiAgICAgICAgICAgICAgICB0cmFuc2Zvcm1Bc3NldFVybHM6IHtcbiAgICAgICAgICAgICAgICAgICAgYmFzZTogbnVsbCxcbiAgICAgICAgICAgICAgICAgICAgaW5jbHVkZUFic29sdXRlOiBmYWxzZSxcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgfSxcbiAgICAgICAgfSksXG4gICAgXSxcbiAgICBidWlsZDoge1xuICAgICAgICBjaHVua1NpemVXYXJuaW5nTGltaXQ6IDEwMDAsXG4gICAgICAgIHJvbGx1cE9wdGlvbnM6IHtcbiAgICAgICAgICAgIG91dHB1dDoge1xuICAgICAgICAgICAgICAgIG1hbnVhbENodW5rczogdW5kZWZpbmVkLFxuICAgICAgICAgICAgfSxcbiAgICAgICAgfSxcbiAgICB9LFxuICAgIHJlc29sdmU6IHtcbiAgICAgICAgYWxpYXM6IHtcbiAgICAgICAgICAgIFwiQFwiOiBcIi9yZXNvdXJjZXMvanNcIixcbiAgICAgICAgfSxcbiAgICB9LFxuICAgIHNlcnZlcjoge1xuICAgICAgICBob3N0OiBcIjAuMC4wLjBcIiwgLy8gTGlzdGVuIG9uIGFsbCBuZXR3b3JrIGludGVyZmFjZXNcbiAgICAgICAgcG9ydDogNTE3MyxcbiAgICAgICAgc3RyaWN0UG9ydDogdHJ1ZSxcbiAgICAgICAgY29yczogdHJ1ZSwgLy8gRW5hYmxlIENPUlMgZm9yIGFsbCBvcmlnaW5zXG4gICAgICAgIGhtcjoge1xuICAgICAgICAgICAgaG9zdDogXCIxOTIuMTY4LjEuMjM1XCIsIC8vIFdpbmRvd3MgaG9zdCBJUCBmb3IgbmV0d29yayBhY2Nlc3NcbiAgICAgICAgICAgIHByb3RvY29sOiBcIndzXCIsXG4gICAgICAgIH0sXG4gICAgICAgIHdhdGNoOiB7XG4gICAgICAgICAgICB1c2VQb2xsaW5nOiB0cnVlLCAvLyBSZXF1aXJlZCBmb3IgV1NMMiBmaWxlIHN5c3RlbSB3YXRjaGluZ1xuICAgICAgICAgICAgaW50ZXJ2YWw6IDEwMCwgLy8gQ2hlY2sgZm9yIGNoYW5nZXMgZXZlcnkgMTAwbXNcbiAgICAgICAgfSxcbiAgICB9LFxuICAgIC8vIEZpeCBmb3IgV1NMMiBjcnlwdG8uaGFzaCBpc3N1ZVxuICAgIG9wdGltaXplRGVwczoge1xuICAgICAgICBlc2J1aWxkT3B0aW9uczoge1xuICAgICAgICAgICAgdGFyZ2V0OiBcImVzbmV4dFwiLFxuICAgICAgICB9LFxuICAgIH0sXG59KTtcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBeVEsU0FBUyxvQkFBb0I7QUFDdFMsT0FBTyxhQUFhO0FBQ3BCLE9BQU8sU0FBUztBQUVoQixJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUN4QixTQUFTO0FBQUEsSUFDTCxRQUFRO0FBQUEsTUFDSixPQUFPLENBQUMseUJBQXlCLHFCQUFxQjtBQUFBLE1BQ3RELFNBQVM7QUFBQSxJQUNiLENBQUM7QUFBQSxJQUNELElBQUk7QUFBQSxNQUNBLFVBQVU7QUFBQSxRQUNOLG9CQUFvQjtBQUFBLFVBQ2hCLE1BQU07QUFBQSxVQUNOLGlCQUFpQjtBQUFBLFFBQ3JCO0FBQUEsTUFDSjtBQUFBLElBQ0osQ0FBQztBQUFBLEVBQ0w7QUFBQSxFQUNBLE9BQU87QUFBQSxJQUNILHVCQUF1QjtBQUFBLElBQ3ZCLGVBQWU7QUFBQSxNQUNYLFFBQVE7QUFBQSxRQUNKLGNBQWM7QUFBQSxNQUNsQjtBQUFBLElBQ0o7QUFBQSxFQUNKO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDTCxPQUFPO0FBQUEsTUFDSCxLQUFLO0FBQUEsSUFDVDtBQUFBLEVBQ0o7QUFBQSxFQUNBLFFBQVE7QUFBQSxJQUNKLE1BQU07QUFBQTtBQUFBLElBQ04sTUFBTTtBQUFBLElBQ04sWUFBWTtBQUFBLElBQ1osTUFBTTtBQUFBO0FBQUEsSUFDTixLQUFLO0FBQUEsTUFDRCxNQUFNO0FBQUE7QUFBQSxNQUNOLFVBQVU7QUFBQSxJQUNkO0FBQUEsSUFDQSxPQUFPO0FBQUEsTUFDSCxZQUFZO0FBQUE7QUFBQSxNQUNaLFVBQVU7QUFBQTtBQUFBLElBQ2Q7QUFBQSxFQUNKO0FBQUE7QUFBQSxFQUVBLGNBQWM7QUFBQSxJQUNWLGdCQUFnQjtBQUFBLE1BQ1osUUFBUTtBQUFBLElBQ1o7QUFBQSxFQUNKO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
