<script setup>
// Same idea as KanaWritingCanvas (HanziWriter quiz mode), but used where the
// result has to be a trustworthy pass/fail — Kaite Oboeru grades a question
// "correct" simply because `complete` fired, so unlike the free-practice
// Kana canvas this one does NOT auto-forgive a stroke after a few misses.
// A wrong stroke just flashes and resets, same as writing on paper and
// crossing it out — the learner must actually draw the right shape/order to
// move on. No outline, no character shown beforehand: nothing here hints at
// what the glyph looks like.
import HanziWriter from 'hanzi-writer'
import kanaStrokes from '@/data/kana-strokes.json'

const props = defineProps({
  character: { type: String, default: '' },
  size: { type: Number, default: 220 },
})

const emit = defineEmits(['complete', 'mistake'])

const hostRef = ref(null)
let writer = null

function charDataLoader(char) {
  return kanaStrokes[char] ?? null
}

function startQuiz() {
  if (!writer)
    return

  writer.quiz({
    // No `markStrokeCorrectAfterMisses` — a wrong stroke is never silently
    // accepted, however many times it's retried, so completion always means
    // the character was actually written correctly.
    // `showHintAfterMisses: false` — never draw a faint reference stroke,
    // even after repeated misses: any hint outline would just show the
    // learner the answer they're supposed to recall from memory.
    showHintAfterMisses: false,
    highlightOnComplete: true,
    onMistake: () => emit('mistake'),
    onComplete: summary => emit('complete', summary),
  })
}

function createWriter() {
  if (!hostRef.value || !props.character)
    return

  const padding = Math.round(props.size * 0.06)
  const effectiveSize = props.size - 2 * padding
  const boldStrokeWidth = Math.round((7 * 1024) / effectiveSize)

  writer = HanziWriter.create(hostRef.value, props.character, {
    width: props.size,
    height: props.size,
    padding,
    showOutline: false,
    showCharacter: false,
    strokeWidth: boldStrokeWidth,
    outlineWidth: boldStrokeWidth,
    drawingWidth: boldStrokeWidth,
    strokeColor: '#3d3d3d',
    outlineColor: '#dddddd',
    drawingColor: '#6750a4',
    highlightColor: '#ffca28',
    charDataLoader,

    // A little slack for rough touchscreen strokes (same reasoning as the
    // Kana canvas), but nowhere near lenient enough to accept a wrong shape.
    leniency: 1.2,
    averageDistanceThreshold: 380,
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

watch(() => props.character, () => {
  destroyWriter()
  createWriter()
})

onMounted(createWriter)
onBeforeUnmount(destroyWriter)
</script>

<template>
  <div
    ref="hostRef"
    class="ko-writing-surface"
    :style="{ width: `${size}px`, height: `${size}px` }"
  />
</template>

<style scoped>
.ko-writing-surface {
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  border-radius: 8px;
  background: rgba(var(--v-theme-on-surface), 0.02);
  touch-action: none;
}
</style>
