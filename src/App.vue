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
  <Teleport to="body">
    <div id="global-dialogs"></div>
  </Teleport>
</template>
