<script setup>
// Umpan balik satu soal pada MODE LATIHAN (hasil POST …/check): benar/salah,
// kunci, dan pembahasan. Tidak pernah dipakai di mode ujian.
defineProps({
  // { correct: boolean, answer: number (1-based), exp: string|null }
  fb: { type: Object, required: true },
})

const { t } = useI18n()
</script>

<template>
  <VAlert
    :type="fb.correct ? 'success' : 'error'"
    variant="tonal"
    density="compact"
    class="jlpt-feedback mt-3"
    role="status"
  >
    <div class="font-weight-medium">
      {{ fb.correct ? t('jlptTest.practice.correct') : t('jlptTest.practice.wrong', { n: fb.answer }) }}
    </div>
    <div
      v-if="fb.exp"
      class="text-body-2 mt-1"
    >
      <strong>{{ t('jlptMock.explanation') }}:</strong> {{ fb.exp }}
    </div>
  </VAlert>
</template>
