// import Auth from '@/views/Auth/Layout.vue'
// import SignIn from '@/views/Auth/SignIn.vue'
// import SignUp from '@/views/Auth/SignUp.vue'

const routes = [
  // {
  //   path: '/',
  //   name: 'auth',
  //   component: Auth,
  //   children: [
  //     {
  //       path: 'signin',
  //       name: 'signin',
  //       component: SignIn,
  //     },
  //     {
  //       path: 'signup',
  //       name: 'signup',
  //       component: SignUp,
  //     },
  //   ],
  // },

  {
    path: '/',
    name: 'auth',
    // component: () => import('@/pages/Auth/Layout.vue'),
    children: [
      {
        path: 'signin',
        name: 'signin',
        component: () => import('@/pages/SignIn.vue'),
      }
    ]
  }
]

export default routes
