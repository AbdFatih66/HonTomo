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

// Level untuk empat halaman latihan (Latihan Soal, Menyimak, Percakapan, Menulis): N5 / N4 / N3.
// Berdiri sendiri dari level bersama di atas, sehingga N3 hanya muncul di halaman latihan
// (Tata Bahasa, Kosakata dan Referensi tetap mengatur N3 lewat selektor terkontrol masing-masing).
// Pilihan diingat di browser; nilai awal mengikuti level bersama bila belum pernah memilih.
export const PRACTICE_LEVEL_CODES = ['N5', 'N4', 'N3']

const PRACTICE_STORAGE_KEY = 'hontomo.practice.level'

function readPracticeStored() {
  try {
    const value = localStorage.getItem(PRACTICE_STORAGE_KEY)

    return PRACTICE_LEVEL_CODES.includes(value) ? value : level.value
  }
  catch {
    return level.value
  }
}

const practiceLevel = ref(readPracticeStored())

export function usePracticeLevel() {
  function setLevel(code) {
    if (!PRACTICE_LEVEL_CODES.includes(code))
      return

    practiceLevel.value = code

    try {
      localStorage.setItem(PRACTICE_STORAGE_KEY, code)
    }
    catch {
      // penyimpanan tidak tersedia (mode privat) — pilihan tetap berlaku selama sesi
    }
  }

  return { level: practiceLevel, setLevel, PRACTICE_LEVEL_CODES }
}
