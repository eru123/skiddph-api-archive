<script setup>
import UploadForm from '@/components/UploadForm.vue'

const onUpload = (data) => {
  console.log('onUploaded', data)
}
</script>
<template>
  <upload-form @uploaded="onUpload" multiple />
</template>
