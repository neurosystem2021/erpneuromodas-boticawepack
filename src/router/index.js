import Vue from 'vue'
import Router from 'vue-router'
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from '@/views/Movimiento.vue'


Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Movimiento',
      component: Movimiento
    }
  ]
})
