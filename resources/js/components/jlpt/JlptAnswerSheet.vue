<script setup>
// Lembar Jawaban ala OMR, dipakai kedua format soal Tes JLPT.
//  - tekan nomor  → lompat ke soal,
//  - tekan bulatan → isi / batalkan jawaban langsung dari lembar,
//  - nomor berbingkai kuning = belum dijawab, bendera = ditandai.
// Jawaban 1-based (1..4), sama dengan kontrak API Tes JLPT.
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  // [{ key, label, items: [{ id, no, count }] }] — dikelompokkan per もんだい
  groups: { type: Array, required: true },
  answers: { type: Object, required: true },
  flags: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue', 'answer', 'jump', 'finish'])

const { t } = useI18n()

const open = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v),
})

function bubbles(count) {
  return Array.from({ length: Math.min(Math.max(count || 4, 2), 4) }, (_, i) => i + 1)
}
</script>

<template>
  <VDialog
    v-model="open"
    max-width="760"
    scrollable
  >
    <VCard>
      <VCardItem :title="t('jlptMock.answer_sheet')">
        <template #append>
          <VBtn
            icon="tabler-x"
            variant="text"
            size="small"
            :aria-label="t('common.cancel')"
            @click="open = false"
          />
        </template>
      </VCardItem>

      <VCardText>
        <div class="text-caption text-medium-emphasis mb-3">
          {{ t('jlptMock.sheet_hint') }}
        </div>

        <div
          v-for="g in groups"
          :key="g.key"
          class="mb-4"
        >
          <div class="text-subtitle-2 mb-1">
            {{ g.label }}
          </div>

          <div
            v-for="item in g.items"
            :key="item.id"
            class="jlpt-sheet__row"
            :class="{ 'jlpt-sheet__row--empty': !answers[item.id] }"
          >
            <button
              type="button"
              class="jlpt-sheet__no"
              @click="emit('jump', item)"
            >
              {{ item.no }}
            </button>
            <button
              v-for="b in bubbles(item.count)"
              :key="b"
              type="button"
              class="jlpt-bubble"
              :class="{ 'jlpt-bubble--on': answers[item.id] === b }"
              :aria-label="`${item.no}-${b}`"
              :aria-pressed="answers[item.id] === b"
              @click="emit('answer', item, b)"
            >
              {{ b }}
            </button>
            <VIcon
              v-if="flags[item.id]"
              icon="tabler-flag-filled"
              color="warning"
              size="18"
              class="ms-2"
            />
          </div>
        </div>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn @click="open = false">
          {{ t('jlptMock.back_to_test') }}
        </VBtn>
        <VBtn
          color="success"
          variant="flat"
          @click="emit('finish')"
        >
          {{ t('jlptMock.finish_section') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style scoped>
.jlpt-sheet__row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-block: 3px;
}

.jlpt-sheet__row--empty .jlpt-sheet__no {
  border-color: rgb(var(--v-theme-warning));
}

.jlpt-sheet__no {
  border: 1.5px solid rgba(var(--v-theme-on-surface), 0.3);
  border-radius: 6px;
  background: transparent;
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  font-weight: 700;
  inline-size: 2.6em;
  min-block-size: 2em;
  text-align: center;
}

/* Gelembung jawaban seperti lembar OMR: bulat kosong → terisi penuh. */
.jlpt-bubble {
  border: 1.5px solid rgba(var(--v-theme-on-surface), 0.55);
  border-radius: 50%;
  background: transparent;
  block-size: 2em;
  color: rgba(var(--v-theme-on-surface), 0.75);
  cursor: pointer;
  font-size: 0.85rem;
  inline-size: 2em;
}

.jlpt-bubble--on {
  border-color: rgb(var(--v-theme-on-surface));
  background: rgb(var(--v-theme-on-surface));
  color: rgb(var(--v-theme-surface));
}
</style>
