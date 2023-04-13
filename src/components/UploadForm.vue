<script setup>
import { ref, watchEffect } from 'vue'
import { useUser } from '@/stores/user'
import { useFileuploader } from '@/stores/fileuploader'

const emit = defineEmits(['uploaded'])
const props = defineProps({
  multiple: {
    type: Boolean,
    default: false,
  },
  accept: {
    type: String,
    default: 'image/*',
  },
  name: {
    type: String,
    default: 'file',
  },
})

const file = ref(null)
const fileuploader = useFileuploader()
const user = useUser()
const previewImage = ref([])

const onFileChange = (e) => {
  const file = e.target.files
  previewImage.value = []
  for (let i = 0; i < file.length; i++) {
    const reader = new FileReader()
    reader.onload = (e) => {
      previewImage.value.push(e.target.result)
    }
    reader.readAsDataURL(file[i])
  }

  if (!file.length) {
    previewImage.value = null
  }
}

const onSubmit = (e) => {
  e.preventDefault()
  fileuploader.upload(file.value)
    .then(() => fileuploader.list())
    .then(() => previewImage.value = null)
}

watchEffect(() => fileuploader.list(), { flush: 'post' })
</script>
<template>
  <div class="upload-form">
    <div class="upload-form__container">
      <div class="upload-form__container__header">
        <h1 class="upload-form__container__header__title">Upload your file</h1>
        <p class="upload-form__container__header__subtitle">
          File should be Jpeg, Png, ...
        </p>
      </div>
      <div class="upload-form__container__body">
        <div class="upload-form__container__body__input">
          <input
            type="file"
            :accept="props.accept"
            :name="`${props.name}${props.multiple ? '[]' : ''}`"
            :multiple="props.multiple"
            ref="file"
            @change="onFileChange"
          />
          <label for="file" class="upload-form__container__body__input__label">
            <span class="upload-form__container__body__input__label__text">
              Choose a file
            </span>
            <span class="upload-form__container__body__input__label__icon">
              <i class="fas fa-cloud-upload-alt"></i>
            </span>
          </label>
        </div>
        <div class="upload-form__container__body__preview">
          <div
            v-if="previewImage"
            v-for="image in previewImage"
            :key="image"
            class="upload-form__container__body__preview__img"
          >
            <img :src="image" alt="preview" />
          </div>
          <p v-else class="upload-form__container__body__preview__text">
            No file chosen, yet.
          </p>
        </div>
      </div>
      <div class="upload-form__container__footer">
        <button
          class="upload-form__container__footer__button"
          @click="onSubmit"
        >
          Upload
        </button>
      </div>
    </div>
  </div>
</template>
