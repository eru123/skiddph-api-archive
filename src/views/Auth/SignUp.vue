<script setup>
import { ref } from 'vue'
import { useApi } from '@/stores/api'
import { useUser } from '@/stores/user'

const loading = ref(false)
const error = ref(null)
const username = ref('')
const email = ref('')
const pass = ref('')
const cpass = ref('')
const fname = ref('')
const lname = ref('')

const api = useApi()
const user = useUser()

const login = () => {
  loading.value = true
  error.value = null

  if (
    !email.value ||
    !pass.value ||
    !cpass.value ||
    !fname.value ||
    !lname.value ||
    !username.value
  ) {
    error.value = 'Please fill all fields'
    loading.value = false
    return
  }

  if (pass.value !== cpass.value) {
    error.value = 'Passwords do not match'
    loading.value = false
    return
  }

  api
    .signup({
      email: email.value,
      pass: pass.value,
      user: username.value,
      fname: fname.value,
      lname: lname.value,
    })
    .catch((err) => (error.value = err))
    .finally(() => (loading.value = false))
}
</script>
<template>
  <div class="page-auth">
    <h1>Create an Account</h1>
    <form v-if="!user.authenticated" @submit.prevent="login">
      <div class="group">
        <label for="fname">First name</label>
        <input type="text" id="fname" v-model="fname" @keydown="error = null" />
      </div>
      <div class="group">
        <label for="lname">Last name</label>
        <input type="text" id="lname" v-model="lname" @keydown="error = null" />
      </div>
      <div class="group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          v-model="username"
          @keydown="error = null"
        />
      </div>
      <div class="group">
        <label for="email">Email</label>
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
      <div class="group">
        <label for="cpass">Confirm Password</label>
        <input
          type="password"
          id="cpass"
          v-model="cpass"
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
        Already have an Account?
        <router-link to="/signin">Sign In</router-link>
      </p>
    </form>
  </div>
</template>
