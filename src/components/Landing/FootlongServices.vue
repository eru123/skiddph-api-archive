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
  cards: {
    type: Array,
    required: true,
  },
})
</script>
<template>
  <div class="footlong-mo">
    <div class="content">
      <h1 class="title">{{ props.title }}</h1>
      <p class="subtitle">{{ props.subtitle }}</p>
      <div class="cards">
        <div class="card" v-for="card in props.cards">
          <div class="card-content">
            <img :src="card.img" :alt="card.title" />
            <h2 class="card-title">{{ card.title }}</h2>
            <p class="card-body">{{ card.content }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.footlong-mo {
  @apply text-center py-20 w-full;

  .content {
    @apply w-full px-6;

    .title {
      @apply text-4xl font-bold uppercase;
    }

    .subtitle {
      @apply text-xl pt-8 pb-12;
    }

    .cards {
      @apply flex flex-col sm:flex-row flex-wrap justify-center items-stretch;

      .card {
        @apply bg-white rounded-lg shadow-lg flex-[0_1_220px] my-2 sm:mx-2;

        .card-content {
          @apply flex flex-col justify-center items-center;

          img {
            @apply w-full rounded-b-none rounded-t-lg;
          }

          .card-title {
            @apply text-lg font-bold pt-2 pb-1;
            @apply md:text-xl md:pt-4 md:pb-2;
          }

          .card-body {
            @apply pt-2 pb-4 px-2;
          }
        }
      }
    }
  }
}
</style>
