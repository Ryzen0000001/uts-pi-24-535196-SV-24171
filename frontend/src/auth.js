import { reactive } from 'vue'
import api from './api'

function readUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
}

const state = reactive({
  token: localStorage.getItem('token'),
  user: readUser(),
})

function save({ token, user }) {
  state.token = token
  state.user = user
  localStorage.setItem('token', token)
  localStorage.setItem('user', JSON.stringify(user))
}

function clear() {
  state.token = null
  state.user = null
  localStorage.removeItem('token')
  localStorage.removeItem('user')
}

export const auth = {
  state,
  clear,
  get isLoggedIn() {
    return Boolean(state.token)
  },
  async login(payload) {
    const { data } = await api.post('/login', payload)
    save(data)
  },
  async logout() {
    try {
      await api.post('/logout')
    } finally {
      clear() // token lokal dihapus walau server tidak terjangkau
    }
  },
}
