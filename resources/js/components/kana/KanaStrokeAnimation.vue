<script setup>
// Animated, correct-order stroke demonstration for a single kana character —
// no text instructions, just the character drawing itself stroke by stroke
// (the same idea as the stroke-order animation in kanji-writing apps).
import HanziWriter from 'hanzi-writer'
import kanaStrokes from '@/data/kana-strokes.json'

const props = defineProps({
  character: { type: String, required: true },
  size: { type: Number, default: 200 },
  autoplay: { type: Boolean, default: true },
})

const hostRef = ref(null)
let writer = null

// Synchronous loader: every kana this app teaches is bundled locally, so
// there is never a network round-trip and never a missing-character case
// mid-lesson.
function charDataLoader(char) {
  return kanaStrokes[char] ?? null
}

function createWriter() {
  if (!hostRef.value)
    return

  const padding = Math.round(props.size * 0.06)
  const effectiveSize = props.size - 2 * padding

  // strokeWidth/outlineWidth are defined in HanziWriter's internal
  // 1024-unit character space and get scaled down to fit our much smaller
  // pixel-sized widget — the library's own defaults (1–2 units) end up as
  // a barely visible hairline at this size, so we scale a bold, marker-like
  // on-screen width (~7px) back up into that internal unit space.
  const boldStrokeWidth = Math.round((7 * 1024) / effectiveSize)

  writer = HanziWriter.create(hostRef.value, props.character, {
    width: props.size,
    height: props.size,
    padding,
    showOutline: true,
    showCharacter: false,
    strokeAnimationSpeed: 1,
    strokeFadeDuration: 300,
    strokeHighlightSpeed: 2,
    delayBetweenStrokes: 400,
    strokeWidth: boldStrokeWidth,
    outlineWidth: boldStrokeWidth,
    strokeColor: '#3d3d3d',
    outlineColor: '#dddddd',
    charDataLoader,
  })

  if (props.autoplay)
    writer.animateCharacter()
}

function destroyWriter() {
  writer?.target?.node?.remove?.()
  writer = null
  if (hostRef.value)
    hostRef.value.innerHTML = ''
}

function replay() {
  writer?.animateCharacter()
}

watch(() => props.character, () => {
  destroyWriter()
  createWriter()
})

onMounted(createWriter)
onBeforeUnmount(destroyWriter)

defineExpose({ replay })
</script>

<template>
  <div class="kana-stroke-animation">
    <div
      ref="hostRef"
      class="kana-stroke-animation__canvas"
      :style="{ width: `${size}px`, height: `${size}px` }"
    />
    <VBtn
      icon
      size="small"
      variant="tonal"
      class="kana-stroke-animation__replay"
      :aria-label="$t('kana.replay_animation')"
      @click="replay"
    >
      <VIcon icon="tabler-player-play-filled" />
    </VBtn>
  </div>
</template>

<style scoped>
.kana-stroke-animation {
  position: relative;
  display: inline-flex;
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 12px;
  background: rgba(var(--v-theme-on-surface), 0.02);
}

.kana-stroke-animation__canvas {
  display: block;
}

.kana-stroke-animation__replay {
  position: absolute;
  inset-block-end: 6px;
  inset-inline-end: 6px;
}
</style>
