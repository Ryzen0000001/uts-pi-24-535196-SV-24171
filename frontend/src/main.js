import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { auth } from './auth'
import './style.css'

// Dipicu api.js saat server menjawab 401 pada permintaan bertoken.
window.addEventListener('auth:expired', () => {
  auth.clear()
  router.push('/masuk')
})

createApp(App).use(router).mount('#app')
