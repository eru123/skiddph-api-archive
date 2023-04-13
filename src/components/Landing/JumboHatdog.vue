<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    required: true,
  },
  buttons: {
    type: Array,
    required: true,
  },
})
</script>
<template>
  <div class="jumbo-hatdog">
    <div class="content">
      <h1 class="title">{{ props.title }}</h1>
      <p class="subtitle">{{ props.subtitle }}</p>
      <div class="buttons">
        <template v-for="button in props.buttons">
          <button v-if="button?.click" @click="button.click">
            {{ button.text }}
          </button>
          <router-link v-else-if="button?.to" :to="button.to">
            {{ button.text }}
          </router-link>
        </template>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.jumbo-hatdog {
  @apply bg-gradient-to-r from-teal-900 to-gray-900 text-white text-center py-20 lg:rounded-b-xl;
  @apply flex flex-col justify-center items-center;

  .content {
    @apply max-w-screen-sm w-[90%] md:w-[50%];

    .title {
      @apply text-4xl font-bold uppercase;
    }

    .subtitle {
      @apply text-xl py-8;
    }

    .buttons {
      @apply flex flex-row justify-center mt-4;

      button, a {
        @apply font-bold py-2 px-4 rounded mx-2 uppercase transition-all duration-200;
        @apply text-teal-700 bg-teal-50 hover:text-teal-900;

        &:not(:hover) {
          @apply animate-pulse;
        }
      }
    }
  }
}
</style>
