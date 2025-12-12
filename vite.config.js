import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
    server: {
        host: "0.0.0.0", // Listen on all network interfaces
        port: 5173,
        strictPort: true,
        hmr: {
            host: "172.21.145.17", // Use localhost for local development
            protocol: "ws",
        },
        watch: {
            usePolling: false, // Disabled for better performance
            // Only use polling if file changes aren't detected
        },
    },
    // Fix for WSL2 crypto.hash issue
    optimizeDeps: {
        esbuildOptions: {
            target: "esnext",
        },
    },
});
