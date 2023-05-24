import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import packageJson from './package.json'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [
    vue()
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  publicDir: 'src/public',
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
