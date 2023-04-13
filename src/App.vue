<script setup>
import { RouterView } from 'vue-router'
import { useUser } from './stores/user'
import { useApi } from './stores/api'

const user = useUser()
const api = useApi()
user.init(() => api.getUser())
</script>

<template>
  <RouterView />
  <div id="global-dialogs"></div>
</template>
