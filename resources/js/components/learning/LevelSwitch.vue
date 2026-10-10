<script setup>
// Selektor level materi (N5 / N4). Gaya pil sama dengan pemilih bab di
// Kosakata dan Kana/Kanji. Nilai disimpan lewat useLevelChoice.
//
// Mode terkontrol (opsional): bila `modelValue` diberikan, pilihan tidak
// menyentuh level bersama; halaman yang memakainya (mis. Kosakata dengan N3)
// menentukan sendiri apa yang dilakukan saat pilihan berubah.
import { LEVEL_CODES, useLevelChoice } from '@/composables/useLevelChoice'

const props = defineProps({
  levels: { type: Array, default: () => LEVEL_CODES },
  modelValue: { type: String, default: undefined },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()
const { level, setLevel } = useLevelChoice()

const current = computed(() => props.modelValue ?? level.value)

function choose(code) {
  if (props.modelValue !== undefined)
    emit('update:modelValue', code)
  else
    setLevel(code)
}
</script>

<template>
  <div
    class="level-switch"
    role="tablist"
    :aria-label="t('levels.switch_label')"
  >
    <button
      v-for="code in levels"
      :key="code"
      type="button"
      role="tab"
      class="level-switch__btn"
      :class="{ 'level-switch__btn--active': current === code }"
      :aria-selected="current === code"
      @click="choose(code)"
    >
      {{ code }}
    </button>
  </div>
</template>

<style scoped>
.level-switch {
  display: inline-flex;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.level-switch__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-inline-size: 52px;
  padding-block: 8px;
  padding-inline: 16px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: background 0.15s ease, color 0.15s ease;
}

.level-switch__btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.level-switch__btn--active,
.level-switch__btn--active:hover {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.level-switch__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}
</style>
