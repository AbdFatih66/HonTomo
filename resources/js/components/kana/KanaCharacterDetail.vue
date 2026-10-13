<script setup>
import { useWindowSize } from '@vueuse/core'
import KanaStrokeAnimation from './KanaStrokeAnimation.vue'
import KanaWritingCanvas from './KanaWritingCanvas.vue'
import kanaStrokes from '@/data/kana-strokes.json'

const props = defineProps({
  character: { type: String, required: true },
  romaji: { type: String, required: true },
  strokeCount: { type: Number, default: null },
  usageNote: { type: String, default: null },
})

const emit = defineEmits(['complete'])

// The stroke-order example and the writing pad sit side by side. On wide
// screens each gets a comfortable square; on phones both shrink so they still
// fit next to each other instead of stacking (which would force scrolling).
const { width: windowWidth } = useWindowSize()
const PAIR_GAP = 16
const SIDE_PADDING = 32

const padSize = computed(() => {
  const fit = Math.floor((windowWidth.value - SIDE_PADDING - PAIR_GAP) / 2)

  return Math.max(140, Math.min(280, fit))
})

const { t } = useI18n()

// The chip must match what the writing practice actually asks for (tenten are
// two strokes there), so prefer the bundled stroke data over the DB value.
const displayStrokeCount = computed(() => kanaStrokes[props.character]?.strokes.length ?? props.strokeCount)
</script>

<template>
  <div class="kana-detail">
    <div class="kana-detail__head">
      <div class="kana-detail__character">
        {{ character }}
      </div>
      <div class="d-flex flex-column align-start gap-1">
        <div class="text-h5 text-medium-emphasis">
          {{ romaji }}
        </div>
        <VChip
          v-if="displayStrokeCount"
          size="small"
          variant="tonal"
        >
          {{ t('kana.stroke_count', { n: displayStrokeCount }) }}
        </VChip>
      </div>
    </div>

    <VAlert
      v-if="usageNote"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      {{ usageNote }}
    </VAlert>

    <!-- Example (left) and practice (right), side by side -->
    <div class="kana-detail__pair">
      <section class="kana-detail__col">
        <div class="text-subtitle-2 mb-2">
          {{ t('kana.stroke_order') }}
        </div>
        <KanaStrokeAnimation
          :key="`anim-${padSize}`"
          :character="character"
          :size="padSize"
        />
      </section>

      <section class="kana-detail__col">
        <div class="text-subtitle-2 mb-2">
          {{ t('kana.practice_writing') }}
        </div>
        <KanaWritingCanvas
          :key="`pad-${padSize}`"
          :character="character"
          :size="padSize"
          :show-outline="true"
          @complete="emit('complete', $event)"
        />
      </section>
    </div>
  </div>
</template>

<style scoped>
.kana-detail__head {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.25rem;
  margin-block-end: 1rem;
}

.kana-detail__character {
  font-family: 'Noto Sans JP', sans-serif;
  font-size: 4.5rem;
  line-height: 1;
}

.kana-detail__pair {
  display: flex;
  justify-content: center;
  gap: 16px;
}

.kana-detail__col {
  display: flex;
  flex-direction: column;
  align-items: center;
}
</style>
