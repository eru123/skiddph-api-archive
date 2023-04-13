<script setup>
import Header from '@/components/GuestHeader.vue'
import { useUser } from '@/stores/user'
import { useRouter } from 'vue-router'
import { watchEffect } from 'vue'
const user = useUser()
const router = useRouter()

watchEffect(() => {
  if (!user.authenticated) {
    router.push('/signin')
  }
})
</script>
<template>
  <div class="user-layout">
    <Header />
    <main>
      <div class="container">
        <router-view v-slot="{ Component }">
          <component :is="Component" />
        </router-view>
      </div>
    </main>
  </div>
</template>
<style lang="scss">
.user-layout {
  @apply grid grid-rows-[auto,1fr,auto] h-screen w-screen;

  main {
    @apply w-full max-w-full h-full max-h-full overflow-y-auto overflow-x-hidden flex flex-row justify-center;

    .container {
      @apply max-w-screen-lg;
    }
  }
}
</style>
