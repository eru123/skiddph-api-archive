<script setup>
import { watchEffect } from 'vue'
import Header from '@/components/GuestHeader.vue'
import { useRouter } from 'vue-router'
import { useUser } from '@/stores/user'

const router = useRouter()
const user = useUser()

watchEffect(() => {
  if (user.authenticated) {
    router.push('/home')
  }
})
</script>
<template>
  <div class="auth-layout">
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
.auth-layout {
  @apply grid grid-rows-[auto,1fr,auto] h-screen w-screen;

  main {
    @apply w-full max-w-full h-full max-h-full overflow-y-auto overflow-x-hidden flex flex-row justify-center;

    .container {
      @apply max-w-screen-lg w-full flex flex-col  items-center;
    }
  }
}

.page-auth {
  @apply w-full max-w-sm flex flex-col  items-center px-4 py-8;

  & > h1 {
    @apply text-center text-2xl font-bold px-4 py-8 uppercase tracking-wider;
  }

  form {
    @apply w-full max-w-[300px] p-4 rounded bg-gray-100;

    .group {
      @apply flex flex-col mb-4;

      label {
        @apply block text-sm font-bold mb-1;
      }

      input {
        @apply block w-full border border-gray-300 rounded px-2 py-2;
      }
    }

    .error {
      @apply text-red-500 text-sm mb-4 text-center;
    }

    .actions {
      @apply flex flex-row justify-end pt-2 pb-4;

      button {
        @apply block w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 transition-all duration-200;
      }
    }

    .message {
      @apply text-sm text-center mt-4;

      a {
        @apply text-blue-500;
      }
    }

    .forgot {
      @apply text-sm text-right mt-4;

      a {
        @apply text-blue-500;
      }
    }
  }
}
</style>
