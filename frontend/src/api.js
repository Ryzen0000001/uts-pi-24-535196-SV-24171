import axios from 'axios'

// Alamat API diatur lewat variabel lingkungan (lihat .env.example), bukan ditulis di komponen.
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: 'application/json' },
})

// Setiap permintaan membawa token yang disimpan saat login.
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Token ditolak server (kedaluwarsa/dicabut): beri tahu aplikasi supaya kembali ke halaman masuk.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const url = error.config?.url ?? ''
    const isAuthCall = url === '/login' || url === '/register'
    if (error.response?.status === 401 && !isAuthCall) {
      window.dispatchEvent(new Event('auth:expired'))
    }
    return Promise.reject(error)
  },
)

export default api
