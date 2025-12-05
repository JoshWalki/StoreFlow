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
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
    server: {
        hmr: {
            host: "localhost",
        },
        watch: {
            usePolling: true,
        },
    },
    // Fix for WSL2 crypto.hash issue
    optimizeDeps: {
        esbuildOptions: {
            target: "esnext",
        },
    },
});
