<script setup>
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  value: { type: Number, default: 0 },
  digits: { type: Number, default: 6 },
})

// Mulai dari 000000, lalu roda berputar ke angka sebenarnya saat halaman dibuka.
const current = ref(Array(props.digits).fill(0))

const target = computed(() => {
  const max = 10 ** props.digits - 1
  const clamped = Math.min(Math.max(Math.round(props.value), 0), max)
  return String(clamped).padStart(props.digits, '0').split('').map(Number)
})

const label = computed(() => `Odometer ${props.value.toLocaleString('id-ID')} kilometer`)

function roll() {
  current.value = target.value
}

onMounted(() => requestAnimationFrame(() => requestAnimationFrame(roll)))
watch(target, roll)
</script>

<template>
  <div class="odometer" role="img" :aria-label="label">
    <span
      v-for="(digit, i) in current"
      :key="i"
      class="drum"
      :class="{ last: i === digits - 1, gap: (digits - 1 - i) % 3 === 0 && i !== digits - 1 }"
      aria-hidden="true"
    >
      <span
        class="reel"
        :style="{
          transform: `translateY(calc(${-digit} * var(--h)))`,
          transitionDelay: `${i * 90}ms`,
        }"
      >
        <span v-for="n in 10" :key="n">{{ n - 1 }}</span>
      </span>
    </span>
    <span class="unit" aria-hidden="true">km</span>
  </div>
</template>

<style scoped>
.odometer {
  display: inline-flex;
  align-items: flex-end;
  font-size: clamp(2.4rem, 6vw, 3.8rem);
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.drum {
  --h: 1.25em;
  display: block;
  width: 0.78em;
  height: var(--h);
  overflow: hidden;
  background: var(--ink);
  color: var(--surface);
  border-radius: 3px;
  margin-right: 2px;
}

.drum.gap {
  margin-right: 0.3em;
}

.drum.last {
  background: var(--amber);
  color: var(--ink);
  margin-right: 0;
}

.reel {
  display: flex;
  flex-direction: column;
  transition: transform 1.5s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.reel span {
  height: var(--h);
  line-height: var(--h);
  text-align: center;
}

.unit {
  font-size: 0.4em;
  font-weight: 600;
  margin: 0 0 0.35em 0.5em;
  color: var(--muted);
}
</style>
