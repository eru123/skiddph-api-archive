<script setup>
import { ref, watchEffect } from 'vue'
import { useUser } from '@/stores/user'
import { useApp } from '@/stores/app'
import { useRouter } from 'vue-router'
import Actions from '@/components/GuestHeaderActions.vue'
import Confirm from '@/components/Confirm.vue'

const user = useUser()
const app = useApp()
const router = useRouter()
const actionItems = ref([])

const logoutModal = ref(false)
const logoutConfirm = () => {
  logoutModal.value = false
  user.logout()
}
const logoutCancel = () => {
  logoutModal.value = false
}
const logoutModalOpen = () => {
  console.log('out', logoutModal.value)
  logoutModal.value = true
  console.log('out', logoutModal.value)
}

watchEffect(() => {
  if (user.authenticated) {
    actionItems.value = [
      {
        click: logoutModalOpen,
        text: 'Sign Out',
      },
    ]
  } else {
    actionItems.value = [
      //   { to: '/privacy', text: 'Privacy Policy' },
      //   { to: '/terms', text: 'Terms of Service' },
      //   { to: '/about', text: 'About' },
      //   { to: '/contact', text: 'Contact Us' },
      ...(router?.currentRoute?.value?.name === 'signin'
        ? []
        : [{ to: '/signin', text: 'Sign In' }]),
    ]
  }
})
</script>
<template>
  <header>
    <div class="container">
      <div class="banner">
        <router-link to="/">
          <h1>{{ app.name }}</h1>
        </router-link>
      </div>
      <actions :items="actionItems" />
    </div>
  </header>
  <Confirm
    title="Logout"
    content="Are you sure you want to logout?"
    v-if="logoutModal"
    @confirm="logoutConfirm"
    @cancel="logoutCancel"
  />
</template>
<style lang="scss" scoped>
header {
  @apply flex flex-row justify-center items-center shadow-md;

  .container {
    @apply flex flex-row justify-between items-center max-w-screen-xl py-3 md:py-4 mx-4;

    .banner {
      @apply flex flex-row justify-center items-center;

      a {
        @apply focus-within:text-gray-500;

        h1 {
          @apply text-xl font-bold active:text-gray-500 transition-[color] duration-200;
        }
      }
    }
  }
}
</style>
