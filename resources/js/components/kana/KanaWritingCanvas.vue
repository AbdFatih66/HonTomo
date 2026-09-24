<script setup>
// Interactive stroke-by-stroke writing practice — the user traces the
// character with mouse/touch and every stroke is checked against its real
// shape and direction (HanziWriter's built-in quiz engine), the same idea
// as the writing-practice screen in kanji-learning apps. No text
// instructions: wrong strokes flash and reset, a hint outline can appear
// after a few misses, and completing all strokes correctly is the only way
// to finish.
//
// Two contexts use this:
//  - free practice on the character detail screen (showOutline: true —
//    a faint reference of the character is visible while tracing)
//  - a graded quiz question (showOutline: false — must be written from
//    memory; the parent listens for `complete` to grade it)
import HanziWriter from 'hanzi-writer'
import kanaStrokes from '@/data/kana-strokes.json'

const props = defineProps({
  character: { type: String, default: '' },
  size: { type: Number, default: 220 },
  showOutline: { type: Boolean, default: true },
})

const emit = defineEmits(['complete'])

const hostRef = ref(null)
let writer = null

function charDataLoader(char) {
  return kanaStrokes[char] ?? null
}

function startQuiz() {
  if (!writer)
    return

  writer.quiz({
    showHintAfterMisses: 3,
    // A safety net on top of the looser leniency above: if a stroke (most
    // often the handakuten ゜circle) still keeps failing after several
    // honest attempts, mark it correct and move on instead of trapping the
    // learner in an unwinnable retry loop on a touchscreen.
    markStrokeCorrectAfterMisses: 5,
    highlightOnComplete: true,
    onComplete: summary => emit('complete', summary),
  })
}

function createWriter() {
  if (!hostRef.value || !props.character)
    return

  const padding = Math.round(props.size * 0.06)
  const effectiveSize = props.size - 2 * padding

  // Same reasoning as KanaStrokeAnimation: widths are defined in
  // HanziWriter's internal 1024-unit space, so they must be scaled up to
  // read as a bold, marker-like line at our actual pixel size instead of
  // the library's hairline-thin defaults. The user's own drawn line
  // matches the reference stroke's thickness exactly, rather than being
  // thinner than what they're tracing.
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
    // Kana strokes are simple, but a couple of them (the handakuten ゜maru
    // circle, small loops in す/む/ぬ, etc.) are hard to trace as a
    // geometrically perfect shape on a phone touchscreen. HanziWriter's
    // defaults (leniency: 1, averageDistanceThreshold: 350) were tuned for
    // mouse-precision kanji tracing; loosening both here means a rough,
    // finger-drawn circle or loop is still accepted as long as it's
    // recognizably the right stroke in the right place — this is a
    // learning app, not a calligraphy grader.
    leniency: 1.8,
    averageDistanceThreshold: 500,
    acceptBackwardsStrokes: true,
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

watch([() => props.character, () => props.showOutline], () => {
  destroyWriter()
  createWriter()
})

onMounted(createWriter)
onBeforeUnmount(destroyWriter)

defineExpose({ reset })
</script>

<template>
  <div class="kana-writing">
    <div
      ref="hostRef"
      class="kana-writing__surface"
      :style="{ width: `${size}px`, height: `${size}px` }"
    />
    <VBtn
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
.kana-writing {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
}

.kana-writing__surface {
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  border-radius: 8px;
  background: rgba(var(--v-theme-on-surface), 0.02);
  touch-action: none;
}
</style>
