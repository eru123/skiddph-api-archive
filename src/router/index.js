import { createRouter, createWebHistory } from 'vue-router'
import guest from './guest'
import auth from './auth'
import user from './user'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [...guest, ...auth, ...user],
})

export default router
