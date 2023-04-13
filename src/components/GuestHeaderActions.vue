<script setup>
import { ref, watchEffect } from "vue";
import MenuIcon from "~icons/mdi/menu";

const openMenu = ref(false);
const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
});

const handleClickOutside = (e) => {
  if (!e.target.closest(".responsive-menu")) {
    openMenu.value = false;
  }
};

const handleKeydown = (e) => {
  if (e.key === "Escape") {
    openMenu.value = false;
  }
};

const handleFocusin = (e) => {
  if (!e.target.closest(".responsive-menu")) {
    openMenu.value = false;
  }
};

watchEffect(() => {
  if (openMenu.value) {
    document.addEventListener("click", handleClickOutside);
    document.addEventListener("keydown", handleKeydown);
    document.addEventListener("focusin", handleFocusin);
  } else {
    document.removeEventListener("click", handleClickOutside);
    document.removeEventListener("keydown", handleKeydown);
    document.removeEventListener("focusin", handleFocusin);
  }
});

</script>
<template>
  <div class="responsive-menu" v-if="props.items?.length">
    <button @click="openMenu = !openMenu">
      <menu-icon class="icon" />
    </button>
    <div :class="'actions ' + (openMenu ? 'open' : '')">
      <template v-for="item in props.items" :key="item.text">
        <router-link v-if="item.to" :to="item.to" @click="openMenu = false">
          {{ item.text }}
        </router-link>
        <button
          v-if="item?.click"
          @click="
            () => {
              openMenu = false;
              item?.click();
            }
          "
        >
          {{ item.text }}
        </button>
      </template>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.responsive-menu {
  @apply relative flex flex-row justify-center items-center;

  button {
    @apply block md:hidden;
    @apply text-gray-500 hover:text-gray-700 transition-[color] duration-200;
    @apply p-1 outline-none ring-0 focus:border-gray-700 focus:outline-none;
    @apply focus:ring-0 border rounded-md border-gray-300 text-gray-500 hover:text-gray-700 focus:text-gray-700;

    .icon {
      @apply h-6 w-6;
    }
  }

  .actions {
    @apply absolute bottom-[-.5rem] right-[.5rem] z-10 translate-y-[100%] bg-white shadow-lg border rounded-md overflow-hidden;
    @apply hidden flex-col justify-center items-start;
    @apply md:flex md:bottom-0 md:right-0 md:relative md:flex-row md:justify-center md:items-center;
    @apply md:translate-y-0 md:bg-inherit md:shadow-none md:border-none md:rounded-none;

    &.open {
      @apply flex;
    }

    a,
    button {
      @apply rounded-none text-left border-0 h-full block w-full text-sm transition-[color] duration-200 whitespace-nowrap;
      @apply px-6 py-2 focus:bg-gray-700 hover:bg-gray-700 text-gray-700 focus:text-gray-100 hover:text-gray-100;
      @apply md:text-blue-500 md:hover:text-blue-900 md:focus:text-blue-900 md:hover:bg-inherit md:focus:bg-inherit md:p-0 md:ml-4;
    }
  }
}
</style>
