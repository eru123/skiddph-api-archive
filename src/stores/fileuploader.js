import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useApi } from './api'

export const useFileuploader = defineStore('fileuploader', () => {
    const api = useApi()
    const name = ref(import.meta?.env?.VITE_APP_NAME ?? 'App')

    const upload = (el) => {
        if (!el instanceof HTMLInputElement || el.type !== 'file') {
            throw new Error('Element is not an input element with type file')
        }

        const formData = new FormData()
        const name = el.getAttribute('name')

        for (let i = 0; i < el.files.length; i++) {
            formData.append(name, el.files[i])
        }

        return api.post(`/api/v1/fileuploader/upload`, formData)
    }

    const list = (d = {}) => api.post(`/api/v1/fileuploader/files`, {
        mime: d.mime ?? '*/*',
        limit: d.limit ?? 10,
        order: d.order ?? 'desc',
        marker: d.marker ?? '',
    })

    const listImages = (d = {}) => list({ ...d, mime: 'image/*' })

    return {
        name,
        upload,
        list,
        listImages,
    }
})
