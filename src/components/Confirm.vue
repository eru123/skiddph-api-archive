<script setup>
const emit = defineEmits(['confirm', 'cancel'])
const props = defineProps({
  title: {
    type: String,
    required: false,
    default: 'Confirm',
  },
  content: {
    type: String,
    required: false,
    default: 'Are you sure?',
  },
  confirmText: {
    type: String,
    required: false,
    default: 'Confirm',
  },
  cancelText: {
    type: String,
    required: false,
    default: 'Cancel',
  },
})
</script>
<template>
  <teleport to="#global-dialogs">
    <div class="dialog">
      <div class="dialog-content">
        <slot name="content">
          <h1 class="dialog-title">{{ props.title }}</h1>
          <p class="dialog-body">{{ props.content }}</p>
          <slot name="actions">
            <div class="dialog-actions">
              <button class="dialog-action" @click="emit('cancel')">
                {{ props.cancelText }}
              </button>
              <button class="dialog-action" @click="emit('confirm')">
                {{ props.confirmText }}
              </button>
            </div>
          </slot>
        </slot>
      </div>
    </div>
  </teleport>
</template>
<style lang="scss" scoped>
.dialog {
  @apply fixed top-0 left-0 w-full h-full flex justify-center items-center bg-black bg-opacity-50 px-2;

    .dialog-content {
        @apply bg-white rounded-lg p-4 w-full max-w-[350px] animate-[dialog-slide-in_0.2s_ease-out];

        .dialog-title {
            @apply text-lg mb-2;
        }

        .dialog-body {
            @apply text-gray-500 mb-8;
        }

        .dialog-actions {
            @apply flex justify-end mt-4;

            .dialog-action {
                @apply bg-gray-200 text-gray-500 px-4 py-2 rounded-lg mr-2;

                &:hover {
                    @apply bg-gray-300;
                }

                &:last-child {
                    @apply bg-blue-500 text-white;

                    &:hover {
                        @apply bg-blue-600;
                    }
                }
            }
        }
    }
}
</style>
