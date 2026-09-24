<script setup>
import { $api } from '@/utils/api'
import { useWindowSize } from '@vueuse/core'
import KanjiStrokeAnimation from './KanjiStrokeAnimation.vue'
import KanjiWritingCanvas from './KanjiWritingCanvas.vue'

const props = defineProps({
  kanjiId: { type: [Number, String], required: true },
  character: { type: String, required: true },
  onyomi: { type: Array, default: () => [] },
  kunyomi: { type: Array, default: () => [] },
  meaning: { type: String, default: '' },
  strokeCount: { type: Number, default: null },
  jlptLevel: { type: String, default: null },
  grade: { type: Number, default: null },
  vocabulary: { type: Array, default: () => [] },
  curriculumWords: { type: Array, default: () => [] },
  progress: { type: Object, default: () => ({ status: 'new', correct_count: 0, incorrect_count: 0 }) },
})

const emit = defineEmits(['complete'])

// Same tolerance as the quiz's writing questions (kanji/quiz.vue) — a
// couple of corrected slips while tracing shouldn't count against mastery.
const WRITING_MISTAKE_TOLERANCE = 2

const localProgress = ref({ ...props.progress })

watch(() => props.progress, val => {
  localProgress.value = { ...val }
})

function onWritingComplete(summary) {
  const correct = summary.totalMistakes <= WRITING_MISTAKE_TOLERANCE

  $api(`/kanji/${props.kanjiId}/progress`, {
    method: 'POST',
    body: { correct },
  }).then(updated => {
    localProgress.value = { ...localProgress.value, ...updated }
  }).catch(() => {
    // Best-effort — practicing from the detail screen still "counts" for
    // the learner even if this particular save didn't land.
  })

  emit('complete', summary)
}

// Same side-by-side sizing approach as KanaCharacterDetail.
const { width: windowWidth } = useWindowSize()
const PAIR_GAP = 16
const SIDE_PADDING = 32

const padSize = computed(() => {
  const fit = Math.floor((windowWidth.value - SIDE_PADDING - PAIR_GAP) / 2)

  return Math.max(140, Math.min(280, fit))
})

const { t } = useI18n()
</script>

<template>
  <div class="kanji-detail">
    <div class="kanji-detail__head">
      <div class="kanji-detail__character">
        {{ character }}
      </div>
      <div class="d-flex flex-column align-start gap-1">
        <div class="text-h6 text-medium-emphasis">
          {{ meaning }}
        </div>
        <div class="d-flex flex-wrap gap-1">
          <VChip
            v-if="jlptLevel"
            size="small"
            color="primary"
            variant="tonal"
          >
            {{ jlptLevel }}
          </VChip>
          <VChip
            v-if="grade"
            size="small"
            variant="tonal"
          >
            {{ t('kanji.grade', { n: grade }) }}
          </VChip>
          <VChip
            v-if="strokeCount"
            size="small"
            variant="tonal"
          >
            {{ t('kana.stroke_count', { n: strokeCount }) }}
          </VChip>
          <VChip
            size="small"
            :color="localProgress.status === 'mastered' ? 'success' : (localProgress.status === 'learning' ? 'warning' : undefined)"
            :variant="localProgress.status === 'new' ? 'outlined' : 'flat'"
            :prepend-icon="localProgress.status === 'mastered' ? 'tabler-circle-check-filled' : undefined"
          >
            {{ t(`kanji.progress_status_${localProgress.status}`) }}
          </VChip>
        </div>
      </div>
    </div>

    <div class="kanji-detail__readings mb-4">
      <div
        v-if="onyomi.length"
        class="kanji-detail__reading-row"
      >
        <span class="text-caption text-medium-emphasis kanji-detail__reading-label">{{ t('kanji.onyomi') }}</span>
        <span class="kanji-detail__reading-value">{{ onyomi.join('、') }}</span>
      </div>
      <div
        v-if="kunyomi.length"
        class="kanji-detail__reading-row"
      >
        <span class="text-caption text-medium-emphasis kanji-detail__reading-label">{{ t('kanji.kunyomi') }}</span>
        <span class="kanji-detail__reading-value">{{ kunyomi.join('、') }}</span>
      </div>
    </div>

    <!-- Example (left) and practice (right), side by side -->
    <div class="kanji-detail__pair">
      <section class="kanji-detail__col">
        <div class="text-subtitle-2 mb-2">
          {{ t('kana.stroke_order') }}
        </div>
        <KanjiStrokeAnimation
          :key="`anim-${padSize}-${kanjiId}`"
          :character="character"
          :kanji-id="kanjiId"
          :size="padSize"
        />
      </section>

      <section class="kanji-detail__col">
        <div class="text-subtitle-2 mb-2">
          {{ t('kana.practice_writing') }}
        </div>
        <KanjiWritingCanvas
          :key="`pad-${padSize}-${kanjiId}`"
          :character="character"
          :kanji-id="kanjiId"
          :size="padSize"
          :show-outline="true"
          @complete="onWritingComplete"
        />
      </section>
    </div>

    <div
      v-if="curriculumWords.length"
      class="kanji-detail__vocab mb-4"
    >
      <div class="text-subtitle-2 mb-2">
        {{ t('kanji.used_in_lessons') }}
      </div>
      <VList density="compact">
        <VListItem
          v-for="word in curriculumWords"
          :key="word.id"
        >
          <div class="d-flex align-center flex-wrap gap-2">
            <span class="kanji-detail__vocab-word">{{ word.word }}</span>
            <span class="text-caption text-medium-emphasis">{{ word.reading }}</span>
            <span class="text-body-2 ms-auto">{{ word.meaning }}</span>
          </div>
        </VListItem>
      </VList>
    </div>

    <div
      v-if="vocabulary.length"
      class="kanji-detail__vocab"
    >
      <div class="text-subtitle-2 mb-2">
        {{ t('kanji.related_vocabulary') }}
      </div>
      <VList density="compact">
        <VListItem
          v-for="word in vocabulary"
          :key="word.id"
        >
          <div class="d-flex align-center gap-3">
            <span class="kanji-detail__vocab-word">{{ word.word }}</span>
            <span class="text-caption text-medium-emphasis">{{ word.reading }}</span>
            <span class="text-body-2 ms-auto">{{ word.meaning }}</span>
          </div>
        </VListItem>
      </VList>
    </div>
  </div>
</template>

<style scoped>
.kanji-detail__head {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.25rem;
  margin-block-end: 1rem;
}

.kanji-detail__character {
  font-family: 'Noto Sans JP', sans-serif;
  font-size: 4.5rem;
  line-height: 1;
}

.kanji-detail__readings {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  align-items: center;
}

.kanji-detail__reading-row {
  display: flex;
  gap: 0.5rem;
  align-items: baseline;
}

.kanji-detail__reading-label {
  min-inline-size: 3.5rem;
  text-align: end;
}

.kanji-detail__reading-value {
  font-family: 'Noto Sans JP', sans-serif;
}

.kanji-detail__pair {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-block-end: 1.5rem;
}

.kanji-detail__col {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.kanji-detail__vocab {
  max-inline-size: 480px;
  margin-inline: auto;
}

.kanji-detail__vocab-word {
  font-family: 'Noto Sans JP', sans-serif;
  font-size: 1.1rem;
  min-inline-size: 3.5rem;
}
</style>
