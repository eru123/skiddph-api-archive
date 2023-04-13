<script setup>
import JumboHatdog from '../../components/Landing/JumboHatdog.vue'
import FootlongServices from '../../components/Landing/FootlongServices.vue'
import { useUser } from '@/stores/user'
import { useRouter } from 'vue-router'
import { watchEffect } from 'vue'
const user = useUser()
const router = useRouter()

watchEffect(() => {
  if (user.authenticated) {
    router.push('/home')
  } else {
    router.push('/signin')
  }
})

const jumboHatdogButtons = [
  {
    text: 'Order Now',
    to: '/order',
  },
]

const footlongServicesCards = [
  {
    img: 'https://via.placeholder.com/500',
    title: 'Title 1',
    content:
      'Random text 1 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati.',
  },
  {
    img: 'https://via.placeholder.com/500',
    title: 'Title 2',
    content:
      'Random text 2 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati.',
  },
  {
    img: 'https://via.placeholder.com/500',
    title: 'Title 3',
    content:
      'Random text 3 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati.',
  },
  {
    img: 'https://via.placeholder.com/500',
    title: 'Title 4',
    content:
      'Random text 4 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati.',
  },
  {
    img: 'https://via.placeholder.com/500',
    title: 'Title 5',
    content:
      'Random text 5 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati.',
  },
  {
    img: 'https://via.placeholder.com/500',
    title: 'Title 6',
    content:
      'Random text 6 Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati.',
  },
]
</script>
<template>
  <jumbo-hatdog
    v-once
    title="Jumbo Hatdog"
    subtitle=" Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati. "
    :buttons="jumboHatdogButtons"
  />

  <footlong-services
    v-once
    title="Footlong Services"
    subtitle=" Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa tempore officiis modi minima nobis. Totam ut quod minima corrupti in, a voluptates officia, veritatis sint exercitationem voluptatem provident porro obcaecati. "
    :cards="footlongServicesCards"
  />
</template>
