import { ref } from 'vue'

// Level materi yang sedang dipilih (selektor N5 / N4). Dipakai bersama oleh
// Tata Bahasa, Kosakata dan Referensi Tata Bahasa, dan diingat di browser
// supaya pilihan tetap sama saat pindah halaman atau membuka ulang aplikasi.
export const LEVEL_CODES = ['N5', 'N4']

const STORAGE_KEY = 'hontomo.level'

function readStored() {
  try {
    const value = localStorage.getItem(STORAGE_KEY)

    return LEVEL_CODES.includes(value) ? value : 'N5'
  }
  catch {
    return 'N5'
  }
}

const level = ref(readStored())

export function useLevelChoice() {
  function setLevel(code) {
    if (!LEVEL_CODES.includes(code))
      return

    level.value = code

    try {
      localStorage.setItem(STORAGE_KEY, code)
    }
    catch {
      // penyimpanan tidak tersedia (mode privat) — pilihan tetap berlaku selama sesi
    }
  }

  return { level, setLevel, LEVEL_CODES }
}
