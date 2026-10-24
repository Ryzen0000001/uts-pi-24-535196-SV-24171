import { createRouter, createWebHistory } from 'vue-router'
import { auth } from './auth'
import AuthView from './views/AuthView.vue'
import LogsView from './views/LogsView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: LogsView, meta: { auth: true } },
    { path: '/masuk', component: AuthView, meta: { guest: true } },
    { path: '/:rest(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  if (to.meta.auth && !auth.isLoggedIn) return '/masuk'
  if (to.meta.guest && auth.isLoggedIn) return '/'
})

export default router
