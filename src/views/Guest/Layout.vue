<script setup>
import Header from '@/components/GuestHeader.vue'
</script>
<template>
  <div class="guest-layout">
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
.guest-layout {
  @apply grid grid-rows-[auto,1fr,auto] h-screen w-screen;

  main {
    @apply w-full max-w-full h-full max-h-full overflow-y-auto overflow-x-hidden flex flex-row justify-center;

    .container {
      @apply max-w-screen-lg;
    }

    .document {
      @apply p-4;

      h1 {
        @apply text-xl md:text-3xl md:mt-4 mb-2;
      }

      h2 {
        @apply text-lg md:text-2xl mt-4 mb-2;
      }

      h3 {
        @apply text-base md:text-xl mt-4 mb-2;
      }

      hr {
        @apply border border-gray-200 mb-2 md:mb-4;
      }

      p {
        @apply text-sm md:text-base text-justify mb-2 md:mb-4;
      }

      q {
        @apply block border-l-[.25rem] border-blue-400 text-sm md:text-base text-justify italic mb-2 md:mb-4 pl-4 bg-gray-100 py-2 md:py-4 rounded-r-xl;
      }

      a {
        @apply text-blue-500 no-underline hover:text-blue-700;
      }

      ol {
        @apply list-decimal list-inside w-full block pl-6 md:pl-8 mb-4;
      }

      ul {
        @apply list-disc list-inside w-full block pl-6 md:pl-8 mb-4;
      }

      ul,
      ol {
        li {
          @apply text-sm md:text-base text-justify mb-1 last:mb-2 marker:font-bold;
        }

        ol,
        ul {
          @apply mt-4 mb-0;
        }
      }

      img {
        @apply block w-full max-w-screen-md mx-auto h-auto rounded-lg shadow-lg mb-4;
      }
    }
  }
}
</style>
