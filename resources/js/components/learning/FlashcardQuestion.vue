<script setup>
const props = defineProps({
  question: { type: Object, required: true },
  locked: { type: Boolean, default: false },
})

const emit = defineEmits(['change', 'submit'])
const { t } = useI18n()

const flipped = ref(false)

function answer(known) {
  if (props.locked)
    return
  emit('change', known ? 'known' : 'unknown')
  emit('submit')
}

// Flashcards are self-graded, nothing to show after the server responds.
function markResult() {}

defineExpose({ markResult })
</script>

<template>
  <div>
    <p class="q-prompt">
      {{ question.prompt }}
    </p>

    <div class="flashcard">
      <div
        class="flashcard__inner"
        :class="{ 'is-flipped': flipped }"
        role="button"
        tabindex="0"
        @click="flipped = !flipped"
        @keyup.enter="flipped = !flipped"
        @keyup.space.prevent="flipped = !flipped"
      >
        <div class="flashcard__face">
          <div
            class="q-jp"
            lang="ja"
          >
            {{ question.japanese_text }}
          </div>
          <span class="flashcard__hint">{{ t('lesson.flip_card') }}</span>
        </div>
        <div class="flashcard__face flashcard__face--back">
          <div class="text-h4">
            {{ question.romaji || question.options?.[0]?.label }}
          </div>
        </div>
      </div>

      <div
        v-if="question.audio_url"
        class="q-audio mt-4"
      >
        <AudioButton :src="question.audio_url" />
      </div>

      <div
        v-if="flipped"
        class="flashcard__actions"
      >
        <VBtn
          color="error"
          variant="tonal"
          size="large"
          :disabled="locked"
          @click="answer(false)"
        >
          {{ t('lesson.i_didnt_know') }}
        </VBtn>
        <VBtn
          color="success"
          size="large"
          :disabled="locked"
          @click="answer(true)"
        >
          {{ t('lesson.i_knew_it') }}
        </VBtn>
      </div>
    </div>
  </div>
</template>
