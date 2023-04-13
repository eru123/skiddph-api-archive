import { defineStore } from 'pinia'
import { useUser } from './user'

export const useApi = defineStore('api', () => {
    const user = useUser()
    const BASE_URL = import.meta.env.VITE_API

    const post = async (url, requestData = null, options) => {
        const defaultOptions = {
            method: 'POST',
            credentials: 'include',
            mode: 'cors',
        }

        const getBody = () => {
            if (requestData instanceof FormData) {
                return { body: requestData }
            }

            return requestData ? { body: JSON.stringify(requestData) } : {}
        }

        const getHeaders = () => {
            const headers = {}

            if (user.token) {
                headers['Authorization'] = `Bearer ${user.token}`
            }

            if (requestData instanceof FormData) {
                return headers
            }

            headers['Content-Type'] = 'application/json'
            headers['Accept'] = 'application/json'
            return headers
        }

        const requestOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...getHeaders(),
                ...options?.headers,
            },
            ...getBody(),
        }

        const response = await fetch(BASE_URL + url, requestOptions)
        const data = await response.json()
        if (!response.ok) {
            const error = (data && data.error) || response.status
            return Promise.reject(error)
        }

        if (data?.error) {
            return Promise.reject(data.error)
        }

        if (data?.token && data?.refresh_token && data?.data) {
            user.login(data)
        }

        if (data?.verify_id) {
            user.setVerifyId(data.verify_id)
        }

        return data
    }

    const get = async (url, options) => {
        const requestOptions = {
            ...options,
            method: 'GET',
        }

        return post(url, null, requestOptions)
    }

    const login = ({ user, pass }) => post('/api/v1/auth/signin', { user, pass })
    const signup = ({ user, pass, email, fname, lname }) => post('/api/v1/auth/signup', { user, pass, email, fname, lname })
    const getUser = (id = null) => get('/api/v1/auth/user' + (id ? `/${id}` : ''))

    return {
        user,
        login,
        signup,
        post,
        get,
        getUser,
    }
})
