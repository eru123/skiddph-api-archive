import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import packageJson from './package.json'

export default defineConfig({
  plugins: [vue()],
  alias: {
    '@/': '/src/'
  },
  publicDir: 'private_http_static',
  base: './',
  build: {
    manifest: true,
    outDir: 'dist',
    emptyOutDir: true,
    assetsDir: '__',
    rollupOptions: {
      input: {
        main: 'src/main.js'
      }
    }
  },
  server: {
    port: packageJson?.config?.skiddph?.port || 3000
  }
})
