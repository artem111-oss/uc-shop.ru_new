import { defineConfig, loadEnv } from 'vite'
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

const ASSET_URL = process.env.ASSET_URL || '';

const env = loadEnv('production', process.cwd(), '')

export default defineConfig({
  host:env.APP_URL,
  plugins: [
    laravel({
      input: [
        'resources/scss/app.scss',
        'resources/ts/app.ts'
      ],
      refresh: true,
    }),

    vue({
      template: {
        transformAssetUrls: {
          // The Vue plugin will re-write asset URLs, when referenced
          // in Single File Components, to point to the Laravel web
          // server. Setting this to `null` allows the Laravel plugin
          // to instead re-write asset URLs to point to the Vite
          // server instead.
          base: null,

          // The Vue plugin will parse absolute URLs and treat them
          // as absolute paths to files on disk. Setting this to
          // `false` will leave absolute URLs un-touched so they can
          // reference assets in the public directory as expected.
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      "@/": path.join(__dirname, "/resources/ts/"),
      "~": path.join(__dirname, "/node_modules/"),
    },
  },

  server: {
    hmr: {
      host: 'localhost',
    },
  },

  // Production optimizations
  css: {
    postcss: './postcss.config.cjs', // Enable PurgeCSS to remove unused CSS
  },

  build: {
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true, // Remove console.log in production
        drop_debugger: true,
      },
    },
    rollupOptions: {
      output: {
        manualChunks: {
          // Split vendor libraries into separate chunks for better caching
          'vendor-vue': ['vue'],
        },
      },
    },
    cssCodeSplit: true, // Split CSS into separate files
    sourcemap: false, // Disable sourcemaps in production
    chunkSizeWarningLimit: 500, // Warn for chunks larger than 500kb
  },
});
