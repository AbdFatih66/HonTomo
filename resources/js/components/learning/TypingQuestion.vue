<script setup>
// Also used for `translation` questions.
const props = defineProps({
  question: { type: Object, required: true },
  locked: { type: Boolean, default: false },
})

const emit = defineEmits(['change', 'submit'])
const { t } = useI18n()

const answer = ref('')
const result = ref(null)
const inputRef = ref(null)

watch(answer, v => emit('change', v.trim() ? v.trim() : null))

function markResult(payload) {
  result.value = payload
}

onMounted(() => inputRef.value?.focus())

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
      v-if="question.romaji"
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

    <input
      ref="inputRef"
      v-model="answer"
      type="text"
      class="q-input"
      :class="result ? (result.isCorrect ? 'is-correct' : 'is-wrong') : ''"
      :placeholder="t('lesson.type_answer')"
      :disabled="locked"
      autocomplete="off"
      autocapitalize="off"
      spellcheck="false"
      @keyup.enter="emit('submit')"
    >
  </div>
</template>
