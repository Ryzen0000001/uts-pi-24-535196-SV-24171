<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { auth } from '../auth'
import { kilometer, rupiah, tanggal } from '../format'
import Odometer from '../components/Odometer.vue'
import ServiceForm from '../components/ServiceForm.vue'

const router = useRouter()

const logs = ref([])
const loading = ref(true)
const loadError = ref('')
const editing = ref(null)
const confirmingId = ref(null)
const notice = ref('')
let noticeTimer

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await api.get('/service-logs')
    logs.value = data.data
  } catch (e) {
    if (e.response?.status !== 401) {
      loadError.value = 'Data tidak bisa dimuat. Periksa koneksi atau alamat API, lalu muat ulang.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)
onBeforeUnmount(() => clearTimeout(noticeTimer))

function showNotice(text) {
  notice.value = text
  clearTimeout(noticeTimer)
  noticeTimer = setTimeout(() => (notice.value = ''), 4000)
}

async function onSaved(kind) {
  editing.value = null
  await load()
  showNotice(kind === 'updated' ? 'Perubahan disimpan.' : 'Catatan disimpan.')
}

function edit(log) {
  confirmingId.value = null
  editing.value = log
  document.getElementById('judul-catatan')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

async function remove(log) {
  try {
    await api.delete(`/service-logs/${log.id}`)
    logs.value = logs.value.filter((l) => l.id !== log.id)
    if (editing.value?.id === log.id) editing.value = null
    showNotice('Catatan dihapus.')
  } catch (e) {
    if (e.response?.status !== 401) showNotice('Catatan belum terhapus. Coba lagi.')
  } finally {
    confirmingId.value = null
  }
}

async function logout() {
  await auth.logout()
  router.push('/masuk')
}

const latestKm = computed(() => Math.max(0, ...logs.value.map((l) => l.odometer_km)))
const totalCost = computed(() => logs.value.reduce((sum, l) => sum + l.cost, 0))
const lastMotor = computed(() => logs.value[0]?.motor ?? '')
const summary = computed(() => {
  if (!logs.value.length) return 'Belum ada catatan servis.'
  return `Servis terakhir ${tanggal(logs.value[0].serviced_at)}. Total biaya ${rupiah(totalCost.value)} dari ${logs.value.length} catatan.`
})
</script>

<template>
  <div class="page">
    <header class="top">
      <p class="brand">Buku Servis</p>
      <div class="who">
        <span>{{ auth.state.user?.name }}</span>
        <button class="link" type="button" @click="logout">Keluar</button>
      </div>
    </header>

    <main class="grid">
      <section class="hero" aria-label="Ringkasan">
        <Odometer :value="latestKm" />
        <p class="summary">{{ summary }}</p>
      </section>

      <div class="form-slot">
        <ServiceForm
          :editing="editing"
          :default-motor="lastMotor"
          @saved="onSaved"
          @cancel="editing = null"
        />
      </div>

      <section class="ledger" aria-labelledby="judul-riwayat">
        <div class="ledger-head">
          <h2 id="judul-riwayat">Riwayat servis</h2>
          <p class="notice" role="status">{{ notice }}</p>
        </div>

        <p v-if="loading && !logs.length">Memuat catatan...</p>

        <div v-else-if="loadError" class="alert" role="alert">
          {{ loadError }}
          <button class="link" type="button" @click="load">Muat ulang</button>
        </div>

        <p v-else-if="!logs.length" class="empty">
          Belum ada catatan servis. Isi formulir untuk mencatat yang pertama.
        </p>

        <ul v-else class="rows">
          <li class="cols" aria-hidden="true">
            <span>Tanggal</span>
            <span>Servis</span>
            <span class="num">Kilometer</span>
            <span class="num">Biaya</span>
            <span></span>
          </li>

          <li v-for="log in logs" :key="log.id" class="row" :class="{ active: editing?.id === log.id }">
            <span class="when">{{ tanggal(log.serviced_at) }}</span>

            <span class="what">
              <strong>{{ log.service_type }}</strong>
              <span class="sub">{{ log.motor }}<template v-if="log.workshop">, {{ log.workshop }}</template></span>
              <span v-if="log.notes" class="note">{{ log.notes }}</span>
            </span>

            <span class="num km">{{ kilometer(log.odometer_km) }}</span>
            <span class="num cost">{{ rupiah(log.cost) }}</span>

            <span class="act">
              <template v-if="confirmingId === log.id">
                <span class="ask">Hapus catatan ini? Tidak bisa dibatalkan.</span>
                <button class="link danger" type="button" @click="remove(log)">Ya, hapus</button>
                <button class="link" type="button" @click="confirmingId = null">Batal</button>
              </template>
              <template v-else>
                <button class="link" type="button" @click="edit(log)">Ubah</button>
                <button class="link danger" type="button" @click="confirmingId = log.id">Hapus</button>
              </template>
            </span>
          </li>
        </ul>
      </section>
    </main>
  </div>
</template>

<style scoped>
.page {
  max-width: 78rem;
  margin: 0 auto;
  padding: 0 clamp(1rem, 4vw, 2.5rem) 4rem;
}

.top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem 0;
  border-bottom: 2px solid var(--ink);
}

.brand {
  font-stretch: 72%;
  font-weight: 800;
  font-size: 1.5rem;
}

.who {
  display: flex;
  gap: 1rem;
  align-items: baseline;
}

.grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 21rem;
  grid-template-areas:
    'hero form'
    'ledger form';
  column-gap: clamp(1.5rem, 4vw, 3.5rem);
  row-gap: 2rem;
  padding-top: 2rem;
}

.hero {
  grid-area: hero;
  display: grid;
  gap: 0.9rem;
  justify-items: start;
}

.summary {
  color: var(--muted);
  font-size: 1.05rem;
}

.form-slot {
  grid-area: form;
  align-self: start;
  position: sticky;
  top: 1rem;
  background: var(--surface);
  border-left: 4px solid var(--ink);
  padding: 1.4rem 1.2rem 1.6rem;
}

.ledger {
  grid-area: ledger;
}

.ledger-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.8rem;
}

.ledger-head h2 {
  font-size: 1.9rem;
}

.notice {
  font-weight: 700;
  min-height: 1.5em;
}

.rows {
  list-style: none;
  margin: 0;
  padding: 0;
}

.cols,
.row {
  display: grid;
  grid-template-columns: 7.5rem minmax(0, 1fr) 7rem 8rem 9.5rem;
  column-gap: 1rem;
  align-items: start;
}

.cols {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--muted);
  padding: 0.4rem 0.5rem;
  border-bottom: 2px solid var(--ink);
}

.row {
  padding: 0.85rem 0.5rem;
  border-bottom: 1px solid var(--line);
}

.row:hover {
  background: rgba(243, 244, 239, 0.7);
}

.row.active {
  background: var(--surface);
  box-shadow: inset 4px 0 0 var(--amber);
}

.num {
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.what {
  display: grid;
  gap: 0.1rem;
}

.sub {
  color: var(--muted);
  font-size: 0.95rem;
}

.note {
  font-size: 0.95rem;
  font-style: italic;
}

.act {
  display: flex;
  flex-wrap: wrap;
  gap: 0.2rem 0.9rem;
  justify-content: flex-end;
}

.ask {
  width: 100%;
  text-align: right;
  font-size: 0.9rem;
  font-weight: 600;
}

.empty {
  padding: 1.5rem 0;
  max-width: 34ch;
}

@media (max-width: 980px) {
  .grid {
    grid-template-columns: minmax(0, 1fr);
    grid-template-areas:
      'hero'
      'form'
      'ledger';
  }
  .form-slot {
    position: static;
  }
}

@media (max-width: 640px) {
  .cols {
    display: none;
  }
  .row {
    grid-template-columns: 1fr auto;
    row-gap: 0.25rem;
  }
  .when {
    grid-column: 1;
    color: var(--muted);
    font-size: 0.9rem;
  }
  .what {
    grid-column: 1 / -1;
    grid-row: 2;
  }
  .row > .km {
    grid-column: 1;
    grid-row: 3;
    text-align: left;
  }
  .row > .cost {
    grid-column: 2;
    grid-row: 3;
  }
  .act {
    grid-column: 1 / -1;
    grid-row: 4;
    justify-content: flex-start;
  }
  .ask {
    text-align: left;
  }
}
</style>
