import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import packageJson from './package.json'

export default defineConfig({
  plugins: [vue()],
  alias: {
    '@/': '/src/',
    '@http_static/': '/private_http_static/'
  },
  build: {
    manifest: true,
    outDir: 'dist',
    assetsDir: '__',
    rollupOptions: {
      input: {
        main: './src/main.js'
      },
      external: [
        '/vite.svg'
      ]
    }
  },
  base: '/',
  server: {
    port: packageJson?.config?.skiddph?.port || 3000
  }
})
