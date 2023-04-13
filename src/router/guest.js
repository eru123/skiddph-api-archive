import Guest from '@/views/Guest/Layout.vue'
import Landing from '@/views/Guest/Landing.vue'
import About from '@/views/Guest/About.vue'
import Contact from '@/views/Guest/Contact.vue'
import Privacy from '@/views/Guest/Privacy.vue'
import Terms from '@/views/Guest/Terms.vue'

const routes = [
  {
    path: '/',
    name: 'guest',
    component: Guest,
    children: [
      {
        path: 'landing',
        name: 'landing',
        component: Landing,
      },
      {
        path: 'about',
        name: 'about',
        component: About,
      },
      {
        path: 'contact',
        name: 'contact',
        component: Contact,
      },
      {
        path: 'privacy',
        name: 'privacy',
        component: Privacy,
      },
      {
        path: 'terms',
        name: 'terms',
        component: Terms,
      },
    ],
  },
]

export default routes
