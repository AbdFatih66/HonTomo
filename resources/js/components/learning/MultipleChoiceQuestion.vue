<script setup>
// Also used for `listening` questions (the audio button appears when audio_url is set).
const props = defineProps({
  question: { type: Object, required: true },
  locked: { type: Boolean, default: false },
})

const emit = defineEmits(['change', 'submit'])

const selectedId = ref(null)
const result = ref(null) // { isCorrect, correctOptionId }

function select(id) {
  if (props.locked)
    return
  selectedId.value = id
  emit('change', id)
}

// Called by the lesson player once the server has graded the answer.
function markResult(payload) {
  result.value = payload
}

// The reading printed under a letter would give the answer away when one of
// the options IS that reading (e.g. あ / "a" with the options "za, i, gu, a").
const showRomaji = computed(() => {
  const romaji = props.question.romaji?.trim().toLowerCase()
  if (!romaji)
    return false

  return !props.question.options?.some(o => o.label?.trim().toLowerCase() === romaji)
})

function optionState(option) {
  if (!result.value)
    return { 'is-selected': selectedId.value === option.id }

  const isCorrectOption = result.value.correctOptionId === option.id
  const isChosen = selectedId.value === option.id

  return {
    'is-correct': isCorrectOption || (isChosen && result.value.isCorrect),
    'is-wrong': isChosen && !result.value.isCorrect,
  }
}

// 1–9 keys pick an option on desktop
function onKey(e) {
  if (props.locked || e.target?.tagName === 'INPUT')
    return
  const n = Number.parseInt(e.key, 10)
  const option = props.question.options?.[n - 1]
  if (option)
    select(option.id)
}

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

defineExpose({ markResult })
</script>

<template>
  <div>
    <p class="q-prompt">
      {{ question.prompt }}
    </p>

    <div
      v-if="question.japanese_text"
      class="q-jp"
      lang="ja"
    >
      {{ question.japanese_text }}
    </div>
    <div
      v-if="showRomaji"
      class="q-romaji"
    >
      {{ question.romaji }}
    </div>

    <div
      v-if="question.audio_url"
      class="q-audio"
    >
      <AudioButton :src="question.audio_url" />
    </div>

    <div class="option-grid">
      <button
        v-for="(option, i) in question.options"
        :key="option.id"
        type="button"
        class="option-card"
        :class="optionState(option)"
        :disabled="locked"
        @click="select(option.id)"
      >
        <span class="option-card__key">{{ i + 1 }}</span>
        <span
          v-if="option.japanese_text"
          class="option-card__jp"
          lang="ja"
        >{{ option.japanese_text }}</span>
        <span>{{ option.label }}</span>
      </button>
    </div>
  </div>
</template>
