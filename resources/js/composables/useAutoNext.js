import { useStorage } from '@vueuse/core'

// Whether the writing-practice screens (kana & kanji) jump to the next
// character on their own once the current one has been written correctly.
// Persisted in localStorage and shared by both screens, so the learner's
// choice sticks between characters, pages and visits.
export function useAutoNext() {
  return useStorage('writing-practice:auto-next', true)
}
