<script setup>
import { reactive, ref, watch } from 'vue'
import api from '../api'
import { hariIni } from '../format'

const props = defineProps({
  editing: { type: Object, default: null },
  defaultMotor: { type: String, default: '' },
})
const emit = defineEmits(['saved', 'cancel'])

const jenisUmum = [
  'Ganti oli mesin',
  'Ganti oli gardan',
  'Servis rutin',
  'Ganti kampas rem',
  'Ganti ban',
  'Ganti aki',
  'Ganti busi',
  'Ganti rantai dan gir',
]

function kosong() {
  return {
    motor: props.defaultMotor,
    serviced_at: hariIni(),
    odometer_km: '',
    service_type: '',
    cost: '',
    workshop: '',
    notes: '',
  }
}

const form = reactive(kosong())
const errors = ref({})
const formError = ref('')
const busy = ref(false)

// Saat tombol "Ubah" ditekan, isi formulir dengan data catatan; saat batal, kosongkan lagi.
watch(
  () => props.editing,
  (log) => {
    errors.value = {}
    formError.value = ''
    Object.assign(
      form,
      log
        ? {
            motor: log.motor,
            serviced_at: log.serviced_at,
            odometer_km: log.odometer_km,
            service_type: log.service_type,
            cost: log.cost,
            workshop: log.workshop ?? '',
            notes: log.notes ?? '',
          }
        : kosong(),
    )
  },
)

// Log baru selesai dimuat: isi motor otomatis dengan motor terakhir yang dicatat.
watch(
  () => props.defaultMotor,
  (motor) => {
    if (!form.motor && !props.editing) form.motor = motor
  },
)

const angka = (v) => (v === '' || v === null ? null : Number(v))

async function submit() {
  busy.value = true
  errors.value = {}
  formError.value = ''

  const payload = {
    motor: form.motor.trim(),
    serviced_at: form.serviced_at,
    odometer_km: angka(form.odometer_km),
    service_type: form.service_type.trim(),
    cost: angka(form.cost),
    workshop: form.workshop.trim() || null,
    notes: form.notes.trim() || null,
  }

  try {
    const wasEditing = Boolean(props.editing)
    if (wasEditing) await api.put(`/service-logs/${props.editing.id}`, payload)
    else await api.post('/service-logs', payload)

    Object.assign(form, kosong(), { motor: payload.motor })
    emit('saved', wasEditing ? 'updated' : 'created')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
    else if (e.response?.status !== 401) formError.value = 'Catatan belum tersimpan. Periksa koneksi, lalu coba lagi.'
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <section class="form-panel" aria-labelledby="judul-catatan">
    <h2 id="judul-catatan">{{ editing ? 'Ubah catatan' : 'Catat servis' }}</h2>

    <form novalidate @submit.prevent="submit">
      <p v-if="formError" class="alert" role="alert">{{ formError }}</p>

      <div class="field">
        <label for="motor">Motor</label>
        <input id="motor" v-model="form.motor" placeholder="Contoh: Vario 125" :aria-invalid="Boolean(errors.motor)" />
        <span v-if="errors.motor" class="error">{{ errors.motor[0] }}</span>
      </div>

      <div class="pair">
        <div class="field">
          <label for="serviced_at">Tanggal servis</label>
          <input id="serviced_at" v-model="form.serviced_at" type="date" :aria-invalid="Boolean(errors.serviced_at)" />
          <span v-if="errors.serviced_at" class="error">{{ errors.serviced_at[0] }}</span>
        </div>
        <div class="field">
          <label for="odometer_km">Kilometer</label>
          <input
            id="odometer_km"
            v-model="form.odometer_km"
            type="number"
            min="0"
            inputmode="numeric"
            :aria-invalid="Boolean(errors.odometer_km)"
          />
          <span v-if="errors.odometer_km" class="error">{{ errors.odometer_km[0] }}</span>
        </div>
      </div>

      <div class="field">
        <label for="service_type">Jenis servis</label>
        <input
          id="service_type"
          v-model="form.service_type"
          list="jenis-servis"
          placeholder="Contoh: Ganti oli mesin"
          :aria-invalid="Boolean(errors.service_type)"
        />
        <datalist id="jenis-servis">
          <option v-for="j in jenisUmum" :key="j" :value="j" />
        </datalist>
        <span v-if="errors.service_type" class="error">{{ errors.service_type[0] }}</span>
      </div>

      <div class="pair">
        <div class="field">
          <label for="cost">Biaya (Rp)</label>
          <input
            id="cost"
            v-model="form.cost"
            type="number"
            min="0"
            inputmode="numeric"
            :aria-invalid="Boolean(errors.cost)"
          />
          <span v-if="errors.cost" class="error">{{ errors.cost[0] }}</span>
        </div>
        <div class="field">
          <label for="workshop">Bengkel</label>
          <input id="workshop" v-model="form.workshop" :aria-invalid="Boolean(errors.workshop)" />
          <span v-if="errors.workshop" class="error">{{ errors.workshop[0] }}</span>
        </div>
      </div>

      <div class="field">
        <label for="notes">Catatan</label>
        <textarea id="notes" v-model="form.notes" :aria-invalid="Boolean(errors.notes)"></textarea>
        <span v-if="errors.notes" class="error">{{ errors.notes[0] }}</span>
      </div>

      <div class="actions">
        <button class="btn" type="submit" :disabled="busy">
          {{ busy ? 'Menyimpan...' : editing ? 'Simpan perubahan' : 'Simpan catatan' }}
        </button>
        <button v-if="editing" class="btn plain" type="button" @click="emit('cancel')">Batal</button>
      </div>
    </form>
  </section>
</template>

<style scoped>
.form-panel {
  display: grid;
  gap: 1.1rem;
  align-content: start;
}

h2 {
  font-size: 1.9rem;
}

form {
  display: grid;
  gap: 0.9rem;
}

.pair {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.8rem;
}

.actions {
  display: flex;
  gap: 0.6rem;
  flex-wrap: wrap;
}

@media (max-width: 420px) {
  .pair {
    grid-template-columns: 1fr;
  }
}
</style>
