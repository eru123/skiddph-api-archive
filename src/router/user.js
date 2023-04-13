import User from '@/views/User/Layout.vue'
import Home from '@/views/User/Home.vue'

const routes = [
    {
        path: '/',
        name: 'user',
        component: User,
        children: [
            {
                path: 'home',
                name: 'home',
                component: Home,
            },
        ],
    },
]

export default routes
