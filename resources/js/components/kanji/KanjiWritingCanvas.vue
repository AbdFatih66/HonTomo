<script setup>
// Interactive stroke-by-stroke writing practice for a kanji — same
// HanziWriter quiz engine and behaviour as KanaWritingCanvas.vue, just
// backed by the lazily-fetched API stroke data instead of a bundled
// import (see KanjiStrokeAnimation.vue for why this is a separate
// component rather than a shared one).
import HanziWriter from 'hanzi-writer'
import { $api } from '@/utils/api'

const props = defineProps({
  character: { type: String, default: '' },
  kanjiId: { type: [Number, String], default: null },
  size: { type: Number, default: 220 },
  showOutline: { type: Boolean, default: true },
})

const emit = defineEmits(['complete', 'load-error'])

const hostRef = ref(null)
const loadError = ref(false)
let writer = null

function charDataLoader(char, onLoad, onError) {
  $api(`/kanji/${props.kanjiId}/strokes`)
    .then(onLoad)
    .catch(err => {
      loadError.value = true
      emit('load-error', err)
      onError(err)
    })
}

function startQuiz() {
  if (!writer)
    return

  writer.quiz({
    showHintAfterMisses: 3,
    highlightOnComplete: true,
    onComplete: summary => emit('complete', summary),
  })
}

function createWriter() {
  if (!hostRef.value || !props.character || !props.kanjiId)
    return

  loadError.value = false

  const padding = Math.round(props.size * 0.06)
  const effectiveSize = props.size - 2 * padding

  // Same reasoning as KanaWritingCanvas: widths are defined in HanziWriter's
  // internal 1024-unit space, so they must be scaled up to read as a bold,
  // marker-like line at our actual pixel size instead of the library's
  // hairline-thin defaults. strokeWidth/outlineWidth were missing here
  // before — only drawingWidth was set — so the faint reference outline
  // stayed hairline-thin while the user's own traced line was bold; setting
  // all three to the same boldStrokeWidth keeps the reference outline, the
  // stroke hints, and the user's drawn line at one consistent thickness.
  const boldStrokeWidth = Math.round((7 * 1024) / effectiveSize)

  writer = HanziWriter.create(hostRef.value, props.character, {
    width: props.size,
    height: props.size,
    padding,
    showOutline: props.showOutline,
    showCharacter: false,
    strokeWidth: boldStrokeWidth,
    outlineWidth: boldStrokeWidth,
    drawingWidth: boldStrokeWidth,
    strokeColor: '#3d3d3d',
    outlineColor: '#dddddd',
    drawingColor: '#6750a4',
    highlightColor: '#ffca28',
    charDataLoader,
  })

  startQuiz()
}

function destroyWriter() {
  writer?.cancelQuiz?.()
  writer?.target?.node?.remove?.()
  writer = null
  if (hostRef.value)
    hostRef.value.innerHTML = ''
}

/** Cancel the current attempt and start this character over from stroke 1. */
function reset() {
  writer?.cancelQuiz()
  startQuiz()
}

watch([() => props.character, () => props.kanjiId, () => props.showOutline], () => {
  destroyWriter()
  createWriter()
})

onMounted(createWriter)
onBeforeUnmount(destroyWriter)

defineExpose({ reset })
</script>

<template>
  <div class="kanji-writing">
    <div
      v-show="!loadError"
      ref="hostRef"
      class="kanji-writing__surface"
      :style="{ width: `${size}px`, height: `${size}px` }"
    />
    <div
      v-if="loadError"
      class="kanji-writing__error"
      :style="{ width: `${size}px`, height: `${size}px` }"
    >
      <VIcon
        icon="tabler-writing-off"
        size="28"
        class="mb-1"
      />
      <span class="text-caption text-center px-2">
        {{ $t('kanji.strokes_unavailable') }}
      </span>
    </div>
    <VBtn
      v-if="!loadError"
      icon
      size="small"
      variant="text"
      class="mt-2"
      :aria-label="$t('kana.clear')"
      @click="reset"
    >
      <VIcon icon="tabler-refresh" />
    </VBtn>
  </div>
</template>

<style scoped>
.kanji-writing {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
}

.kanji-writing__surface {
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  border-radius: 8px;
  background: rgba(var(--v-theme-on-surface), 0.02);
  touch-action: none;
}

.kanji-writing__error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: rgba(var(--v-theme-on-surface), 0.5);
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  border-radius: 8px;
}
</style>
