import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useApp = defineStore('app', () => {
    const name = ref(import.meta?.env?.VITE_APP_NAME ?? 'App')

    return {
        name
    }
})
