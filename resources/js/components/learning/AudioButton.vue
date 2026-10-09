<script setup>
// Play / replay button for a vocabulary or lesson audio clip.
// Never autoplays — the learner always taps it.
const props = defineProps({
  src: { type: String, default: null },
})

const { t } = useI18n()
const isPlaying = ref(false)
const hasPlayed = ref(false)
let audio = null

function play() {
  if (!props.src)
    return
  audio?.pause()
  audio = new Audio(props.src)
  audio.addEventListener('ended', () => { isPlaying.value = false })
  audio.addEventListener('error', () => { isPlaying.value = false })
  isPlaying.value = true
  hasPlayed.value = true
  audio.play().catch(() => { isPlaying.value = false })
}

onBeforeUnmount(() => audio?.pause())
</script>

<template>
  <VBtn
    v-if="src"
    color="primary"
    variant="tonal"
    size="large"
    rounded="pill"
    :prepend-icon="isPlaying ? 'tabler-volume' : 'tabler-volume-2'"
    @click="play"
  >
    {{ hasPlayed ? t('lesson.replay') : t('lesson.listen') }}
  </VBtn>
</template>
