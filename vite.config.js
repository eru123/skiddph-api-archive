import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import packageJson from './package.json'
import { fileURLToPath, URL } from 'node:url'
import legacy from '@vitejs/plugin-legacy'
import Icons from 'unplugin-icons/vite'
import image from '@rollup/plugin-image'

export default defineConfig({
  plugins: [
    {
      ...image(),
      enforce: 'pre',
    },
    vue(),
    Icons({ compiler: 'vue3' }),
    legacy({
      targets: ['defaults', 'not IE 11'],
    })
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  publicDir: 'private_http_static',
  base: './',
  build: {
    manifest: true,
    outDir: packageJson?.config?.skiddph?.dist || 'dist',
    emptyOutDir: true,
    assetsDir: '__',
    rollupOptions: {
      input: {
        main: packageJson?.config?.skiddph?.main || 'src/main.js'
      }
    }
  },
  server: {
    port: packageJson?.config?.skiddph?.port || 3000
  }
})
