<script setup>
// Animated, correct-order stroke demonstration for a single kanji.
// Mirrors KanaStrokeAnimation.vue exactly in behaviour/props; the only
// real difference is charDataLoader — kana bundles ~103 characters
// locally, but kanji (2000+ across N5-N1) are fetched lazily per
// character from the API instead (see docs/kanji-module.md, "Skala data").
// Kept as a separate component rather than a shared one with a
// charDataLoader prop so the already-working Kana components stay
// untouched (see original brief: "jangan mengubah fitur Kana yang sudah
// berjalan baik jika tidak diperlukan").
import HanziWriter from 'hanzi-writer'
import { $api } from '@/utils/api'

const props = defineProps({
  character: { type: String, required: true },
  kanjiId: { type: [Number, String], required: true },
  size: { type: Number, default: 200 },
  autoplay: { type: Boolean, default: true },
})

const hostRef = ref(null)
const loadError = ref(false)
let writer = null

// HanziWriter's charDataLoader supports the async (onLoad/onError) form —
// used here instead of the synchronous return-value form KanaStrokeAnimation
// uses, since this data comes over the network rather than a bundled import.
function charDataLoader(char, onLoad, onError) {
  $api(`/kanji/${props.kanjiId}/strokes`)
    .then(onLoad)
    .catch(err => {
      loadError.value = true
      onError(err)
    })
}

function createWriter() {
  if (!hostRef.value)
    return

  loadError.value = false

  const padding = Math.round(props.size * 0.06)
  const effectiveSize = props.size - 2 * padding

  // Same reasoning as KanaStrokeAnimation: strokeWidth/outlineWidth are
  // defined in HanziWriter's internal 1024-unit character space, so the
  // library's own defaults (1–2 units) end up as a barely visible hairline
  // at our actual widget size. Scaling a bold, marker-like on-screen width
  // (~7px) back up into that internal unit space keeps every kanji — no
  // matter its actual stroke count or the widget's rendered size — at the
  // same, consistently bold line weight that Kana already uses. Without
  // this the same character can render thin at one size and thick at
  // another, or thin/thick/uneven next to each other, because the
  // un-scaled default is relative to the 1024-unit space, not to pixels.
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

watch(() => [props.character, props.kanjiId], () => {
  destroyWriter()
  createWriter()
})

onMounted(createWriter)
onBeforeUnmount(destroyWriter)

defineExpose({ replay })
</script>

<template>
  <div class="kanji-stroke-animation">
    <div
      v-show="!loadError"
      ref="hostRef"
      class="kanji-stroke-animation__canvas"
      :style="{ width: `${size}px`, height: `${size}px` }"
    />
    <div
      v-if="loadError"
      class="kanji-stroke-animation__error"
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
      variant="tonal"
      class="kanji-stroke-animation__replay"
      :aria-label="$t('kana.replay_animation')"
      @click="replay"
    >
      <VIcon icon="tabler-player-play-filled" />
    </VBtn>
  </div>
</template>

<style scoped>
.kanji-stroke-animation {
  position: relative;
  display: inline-flex;
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 12px;
  background: rgba(var(--v-theme-on-surface), 0.02);
}

.kanji-stroke-animation__canvas {
  display: block;
}

.kanji-stroke-animation__error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: rgba(var(--v-theme-on-surface), 0.5);
}

.kanji-stroke-animation__replay {
  position: absolute;
  inset-block-end: 6px;
  inset-inline-end: 6px;
}
</style>
