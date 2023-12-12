import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';


export default defineConfig({
    base: "https://playlists.robbryan.dev/",
    plugins: [
      laravel({
        input: [
          'resources/css/app.css',
          'resources/js/app.js'
        ],
        refresh: true,
        detectTls: true
      }),
      vue({
        template: {
            transformAssetUrls: {
                base: "https://playlists.robbryan.dev/",
                includeAbsolute: false,
            }
        },
        isProduction: true
      })
    ],
    resolve: {
      alias: {
        vue: 'vue/dist/vue.esm-bundler.js',
      }
    },
    server: {
        https: true
    },
  build: {
    manifest: true,
    outDir: 'public/build'
  }
});
