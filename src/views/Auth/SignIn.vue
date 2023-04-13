<script setup>
import { ref } from 'vue'
import { useApi } from '@/stores/api'
import { useUser } from '@/stores/user'

const loading = ref(false)
const error = ref(null)
const email = ref('')
const pass = ref('')

const api = useApi()
const user = useUser()

const login = () => {
  loading.value = true
  error.value = null

  api
    .login({ user: email.value, pass: pass.value })
    .catch((err) => (error.value = err))
    .finally(() => (loading.value = false))
}
</script>
<template>
  <div class="page-auth">
    <h1>Sign In your Account</h1>
    <form v-if="!user.authenticated" @submit.prevent="login">
      <div class="group">
        <label for="email">Username or Email</label>
        <input type="text" id="email" v-model="email" @keydown="error = null" />
      </div>
      <div class="group">
        <label for="pass">Password</label>
        <input
          type="password"
          id="pass"
          v-model="pass"
          @keydown="error = null"
        />
      </div>
      <div class="error" v-if="error">{{ error }}</div>
      <div class="actions">
        <button :disabled="loading">
          {{ loading ? 'Loading...' : 'Login' }}
        </button>
      </div>
      <p class="message">
        Not registered?
        <router-link to="/signup">Create an account</router-link>
      </p>
      <p class="message">
        Forgot your password?
        <router-link to="/reset">Reset it</router-link>
      </p>
    </form>
  </div>
</template>
