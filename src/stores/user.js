import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'
import jsSHA from 'jssha'

export const useUser = defineStore('user', () => {
    const router = useRouter()
    const token = ref(null)
    const refreshToken = ref(null)
    const id = ref(null)
    const fname = ref(null)
    const lname = ref(null)
    const roles = ref([])
    const email = ref([])
    const pendingEmail = ref([])
    const verifyId = ref(null)
    const hash = ref(null)

    const authenticated = computed(() => !!token.value)
    const hasVerification = computed(() => !!verifyId.value)

    const logout = () => {
        token.value = null
        id.value = null
        fname.value = null
        lname.value = null
        roles.value = []
        email.value = []
        pendingEmail.value = []
        verifyId.value = null
        hash.value = null

        localStorage.removeItem('user')
        localStorage.removeItem('hash')

        console.log('logout', router)
        return router.push('/signin')
    }

    const hmac = (data) => {
        const shaObj = new jsSHA('SHA-256', 'TEXT')
        shaObj.setHMACKey(data.token, 'TEXT')
        shaObj.update(JSON.stringify(data))
        return shaObj.getHMAC('HEX')
    }

    const login = (data) => {
        token.value = data?.token
        refreshToken.value = data?.refresh_token
        id.value = data?.data?.id
        fname.value = data?.data?.fname
        lname.value = data?.data?.lname
        roles.value = data?.data?.roles ?? []
        email.value = data?.data?.email ?? []
        pendingEmail.value = data?.data?.pending_email ?? []
        hash.value = hmac(data)

        localStorage.setItem('user', JSON.stringify(data))
        localStorage.setItem('hash', hash.value)
    }

    const init = (cb = () => undefined) => {
        const data = JSON.parse(localStorage.getItem('user'))
        const hash = localStorage.getItem('hash')

        if (data && hash) {
            const newHash = hmac(data)

            if (newHash === hash) {
                login(data)
                return cb()
            }
        }

        return logout()
    }

    const setVerifyId = (id) => verifyId.value = id

    const parseRoles = (roles) => {
        const rolesArray = Array.isArray(roles) ? roles : roles.split(/[,| ]/)
        return rolesArray.map((role) => role.trim().toUpperCase())
    }

    const guard = (allowedRoles) => parseRoles(allowedRoles).some((role) => roles.value.includes(role))

    return {
        token,
        refreshToken,
        id,
        fname,
        lname,
        roles,
        email,
        pendingEmail,
        authenticated,
        verifyId,
        hasVerification,
        guard,
        logout,
        login,
        parseRoles,
        setVerifyId,
        init,
    }
})
