<script setup>
// Freehand writing pad — the user draws their answer by hand with mouse,
// stylus or finger, same idea as writing on paper. There is no handwriting
// recognition here: after writing, the learner reveals the printed answer
// themselves and self-judges (like checking your own kaite-oboeru workbook),
// same pattern as e.g. flashcards.
//
// Usage: <HandwritingPad ref="pad" :key="resetKey" />, call pad.clear() to
// wipe the canvas (also happens automatically whenever :key changes, since
// that remounts the component).
const props = defineProps({
  height: { type: Number, default: 220 },
})

const canvasRef = ref(null)
const wrapRef = ref(null)
let ctx = null
let drawing = false
let hasStrokes = false
const strokes = ref([]) // stack of {x,y,start}[] paths, for simple undo

function setupCanvas() {
  const canvas = canvasRef.value
  const wrap = wrapRef.value
  if (!canvas || !wrap)
    return

  const ratio = window.devicePixelRatio || 1
  const width = wrap.clientWidth

  canvas.width = width * ratio
  canvas.height = props.height * ratio
  canvas.style.width = `${width}px`
  canvas.style.height = `${props.height}px`

  ctx = canvas.getContext('2d')
  ctx.scale(ratio, ratio)
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  ctx.lineWidth = 4
  ctx.strokeStyle = '#2b2b2b'
}

function pointFromEvent(e) {
  const canvas = canvasRef.value
  const rect = canvas.getBoundingClientRect()
  const src = e.touches ? e.touches[0] : e

  return { x: src.clientX - rect.left, y: src.clientY - rect.top }
}

let currentStroke = null

function startStroke(e) {
  e.preventDefault()
  drawing = true
  hasStrokes = true
  const p = pointFromEvent(e)

  currentStroke = [p]
  ctx.beginPath()
  ctx.moveTo(p.x, p.y)
}

function moveStroke(e) {
  if (!drawing)
    return
  e.preventDefault()
  const p = pointFromEvent(e)

  ctx.lineTo(p.x, p.y)
  ctx.stroke()
  currentStroke?.push(p)
}

function endStroke() {
  if (!drawing)
    return
  drawing = false
  if (currentStroke?.length)
    strokes.value.push(currentStroke)
  currentStroke = null
}

function clear() {
  const canvas = canvasRef.value
  if (!canvas || !ctx)
    return
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  strokes.value = []
  hasStrokes = false
}

function undo() {
  strokes.value.pop()
  const canvas = canvasRef.value
  if (!canvas || !ctx)
    return
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  for (const stroke of strokes.value) {
    ctx.beginPath()
    stroke.forEach((p, i) => (i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y)))
    ctx.stroke()
  }
  hasStrokes = strokes.value.length > 0
}

function isEmpty() {
  return !hasStrokes
}

onMounted(() => {
  setupCanvas()
  window.addEventListener('resize', setupCanvas)
})
onBeforeUnmount(() => window.removeEventListener('resize', setupCanvas))

defineExpose({ clear, undo, isEmpty })
</script>

<template>
  <div ref="wrapRef" class="hw-pad">
    <canvas
      ref="canvasRef"
      class="hw-pad__canvas"
      @mousedown="startStroke"
      @mousemove="moveStroke"
      @mouseup="endStroke"
      @mouseleave="endStroke"
      @touchstart="startStroke"
      @touchmove="moveStroke"
      @touchend="endStroke"
    />
  </div>
</template>

<style scoped>
.hw-pad {
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.24);
  border-radius: 12px;
  background:
    repeating-linear-gradient(
      rgba(var(--v-theme-on-surface), 0.06) 0,
      rgba(var(--v-theme-on-surface), 0.06) 1px,
      transparent 1px,
      transparent 36px
    );
  overflow: hidden;
  touch-action: none;
}

.hw-pad__canvas {
  display: block;
  cursor: crosshair;
  touch-action: none;
}
</style>
