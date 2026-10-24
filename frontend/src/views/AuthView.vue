<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../auth'

const router = useRouter()

const form = reactive({ email: '', password: '' })
const errors = ref({})
const formError = ref('')
const busy = ref(false)

async function submit() {
  busy.value = true
  errors.value = {}
  formError.value = ''
  try {
    await auth.login({ email: form.email, password: form.password })
    router.push('/')
  } catch (e) {
    const res = e.response
    if (res?.status === 422) errors.value = res.data.errors ?? {}
    else if (res?.status === 401) formError.value = res.data.message
    else formError.value = 'Server tidak bisa dihubungi. Coba lagi sebentar lagi.'
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <main class="auth">
    <section class="intro">
      <p class="brand">Buku Servis</p>
      <h1>Kapan terakhir ganti oli?</h1>
      <p class="lead">
        Catat setiap kali motor diservis, supaya jawabannya
        gak perlu ditebak-tebak.
      </p>
    </section>

    <section class="panel" aria-labelledby="judul-form">
      <h2 id="judul-form">Masuk</h2>

      <form novalidate @submit.prevent="submit">
        <p v-if="formError" class="alert" role="alert">{{ formError }}</p>

        <div class="field">
          <label for="email">Email</label>
          <input
            id="email"
            v-model.trim="form.email"
            type="email"
            autocomplete="email"
            :aria-invalid="Boolean(errors.email)"
          />
          <span v-if="errors.email" class="error">{{ errors.email[0] }}</span>
        </div>

        <div class="field">
          <label for="password">Kata sandi</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            :aria-invalid="Boolean(errors.password)"
          />
          <span v-if="errors.password" class="error">{{ errors.password[0] }}</span>
        </div>

        <button class="btn" type="submit" :disabled="busy">
          {{ busy ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>

      <p class="switch">Gunakan akun demo dari laporan untuk mencoba aplikasi.</p>
    </section>
  </main>
</template>

<style scoped>
.auth {
  min-height: 100vh;
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
}

.intro {
  padding: clamp(1.5rem, 5vw, 4rem);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 2rem;
}

.brand {
  font-stretch: 72%;
  font-weight: 800;
  font-size: 1.5rem;
}

h1 {
  font-size: clamp(3.2rem, 9vw, 7.5rem);
  max-width: 9ch;
}

.lead {
  max-width: 34ch;
  color: var(--muted);
  font-size: 1.1rem;
}

.panel {
  background: var(--surface);
  border-left: 1px solid var(--line);
  padding: clamp(1.5rem, 5vw, 4rem);
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 1.5rem;
}

.panel h2 {
  font-size: 2.2rem;
}

form {
  display: grid;
  gap: 1rem;
  max-width: 24rem;
}

form .btn {
  justify-self: start;
  min-width: 8rem;
}

.switch {
  color: var(--muted);
  font-size: 0.95rem;
}

@media (max-width: 760px) {
  .auth {
    grid-template-columns: 1fr;
  }
  .intro {
    padding-bottom: 1rem;
  }
  h1 {
    font-size: 3.4rem;
  }
  .panel {
    border-left: 0;
    border-top: 1px solid var(--line);
  }
}
</style>
