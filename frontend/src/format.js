export const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID')

export const kilometer = (n) => Number(n).toLocaleString('id-ID') + ' km'

export const tanggal = (iso) =>
  new Date(iso + 'T00:00:00').toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })

// Tanggal hari ini menurut zona waktu perangkat, format yyyy-mm-dd untuk <input type="date">.
export function hariIni() {
  const d = new Date()
  const bulan = String(d.getMonth() + 1).padStart(2, '0')
  const hari = String(d.getDate()).padStart(2, '0')
  return `${d.getFullYear()}-${bulan}-${hari}`
}
