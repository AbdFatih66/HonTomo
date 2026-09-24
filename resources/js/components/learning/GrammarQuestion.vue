<script setup>
// A grammar explanation "card" shown in the separate grammar step of a lesson
// (before the scored quiz, or on its own in a grammar-only lesson such as
// "Tata Bahasa (Bunpou)"). Nothing to answer — tapping the button moves on.
//
// payload shape (all optional except the explanation):
//   explanation_id / explanation_en
//   notes_id / notes_en         : string[]
//   examples                    : [{ ja, reading, id, en }]
//   dialogue                    : { title_id, title_en, lines: [{ speaker, ja, reading, id, en }] }
//   example_japanese / _reading / _translation_*  (legacy single example)
//
// Any Japanese string above may carry furigana markers (漢字《かんじ》); they
// are rendered as <ruby> by RubyText and ignored when absent.
import RubyText from '@/components/learning/RubyText.vue'

const props = defineProps({
  question: { type: Object, required: true },
  locked: { type: Boolean, default: false },
  isLast: { type: Boolean, default: false },
  // true when this card is the last step of the whole lesson (no quiz follows)
  studyOnly: { type: Boolean, default: false },
})

const emit = defineEmits(['change', 'submit'])
const { t, locale } = useI18n()

const isEn = computed(() => locale.value === 'en')
const payload = computed(() => props.question.payload || {})

const explanation = computed(() => (isEn.value ? payload.value.explanation_en : payload.value.explanation_id))
const notes = computed(() => (isEn.value ? payload.value.notes_en : payload.value.notes_id) || [])

// Legacy cards only have one example — show it through the same list UI.
const examples = computed(() => {
  const p = payload.value
  if (p.examples?.length)
    return p.examples

  if (p.example_japanese) {
    return [{
      ja: p.example_japanese,
      reading: p.example_reading,
      id: p.example_translation_id,
      en: p.example_translation_en,
    }]
  }

  return []
})

const dialogue = computed(() => {
  const d = payload.value.dialogue
  if (!d?.lines?.length)
    return null

  return { title: isEn.value ? d.title_en : d.title_id, lines: d.lines }
})

const buttonLabel = computed(() => {
  if (!props.isLast)
    return t('lesson.got_it')

  return props.studyOnly ? t('lesson.finish_study') : t('lesson.start_practice')
})

function translation(item) {
  return isEn.value ? item.en : item.id
}

function acknowledge() {
  if (props.locked)
    return
  emit('change', 'seen')
  emit('submit')
}

// Grammar cards are self-graded, nothing to show after the server responds.
function markResult() {}

defineExpose({ markResult })
</script>

<template>
  <div class="grammar-card">
    <div class="grammar-card__badge">
      {{ t('lesson.grammar_point') }}
    </div>

    <p class="q-prompt mb-1">
      <RubyText :text="question.prompt" />
    </p>

    <div
      v-if="question.japanese_text"
      class="grammar-card__pattern"
      lang="ja"
    >
      <RubyText :text="question.japanese_text" />
    </div>

    <p
      v-if="explanation"
      class="grammar-card__explanation"
    >
      <RubyText :text="explanation" />
    </p>

    <ul
      v-if="notes.length"
      class="grammar-card__notes"
    >
      <li
        v-for="(note, i) in notes"
        :key="i"
      >
        <RubyText :text="note" />
      </li>
    </ul>

    <!-- Contoh kalimat -->
    <section
      v-if="examples.length"
      class="grammar-card__section"
    >
      <h6 class="grammar-card__heading">
        <VIcon
          icon="tabler-pencil"
          size="18"
        />
        {{ t('lesson.examples') }}
      </h6>

      <div
        v-for="(ex, i) in examples"
        :key="i"
        class="grammar-card__example"
      >
        <div
          lang="ja"
          class="grammar-card__ja"
        >
          <RubyText :text="ex.ja" />
        </div>
        <div
          v-if="ex.reading"
          class="text-caption text-medium-emphasis"
        >
          {{ ex.reading }}
        </div>
        <div class="text-body-2">
          {{ translation(ex) }}
        </div>
      </div>
    </section>

    <!-- Percakapan -->
    <section
      v-if="dialogue"
      class="grammar-card__section"
    >
      <h6 class="grammar-card__heading">
        <VIcon
          icon="tabler-messages"
          size="18"
        />
        {{ t('lesson.dialogue') }}<template v-if="dialogue.title">
          · {{ dialogue.title }}
        </template>
      </h6>

      <div
        v-for="(line, i) in dialogue.lines"
        :key="i"
        class="grammar-card__line"
      >
        <div
          class="grammar-card__speaker"
          lang="ja"
        >
          {{ line.speaker }}
        </div>
        <div class="grammar-card__line-body">
          <div
            lang="ja"
            class="grammar-card__ja"
          >
            <RubyText :text="line.ja" />
          </div>
          <div
            v-if="line.reading"
            class="text-caption text-medium-emphasis"
          >
            {{ line.reading }}
          </div>
          <div class="text-body-2">
            {{ translation(line) }}
          </div>
        </div>
      </div>
    </section>

    <div
      v-if="question.audio_url"
      class="q-audio mt-3"
    >
      <AudioButton :src="question.audio_url" />
    </div>

    <VBtn
      color="primary"
      size="large"
      class="mt-5"
      block
      :disabled="locked"
      @click="acknowledge"
    >
      {{ buttonLabel }}
    </VBtn>
  </div>
</template>

<style scoped>
.grammar-card {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 16px;
  padding: 1.5rem;
  background: rgba(var(--v-theme-primary), 0.06);
}

.grammar-card__badge {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
  border-radius: 999px;
  padding: 0.15rem 0.75rem;
  margin-block-end: 0.75rem;
}

.grammar-card__pattern {
  font-size: 1.5rem;
  font-weight: 600;
  margin-block-end: 0.75rem;
}

.grammar-card__explanation {
  line-height: 1.6;
  margin-block-end: 0.75rem;
}

.grammar-card__notes {
  margin-block-end: 0.5rem;
  padding-inline-start: 1.1rem;
  font-size: 0.9rem;
  line-height: 1.5;
  opacity: 0.85;
}

.grammar-card__section {
  margin-block-start: 1.25rem;
}

.grammar-card__heading {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.95rem;
  font-weight: 700;
  margin-block-end: 0.6rem;
  color: rgb(var(--v-theme-primary));
}

.grammar-card__ja {
  font-family: "Noto Sans JP", "Hiragino Sans", "Yu Gothic", "Meiryo", sans-serif;
  font-size: 1.2rem;
  line-height: 1.5;
}

.grammar-card__example {
  border-inline-start: 3px solid rgb(var(--v-theme-primary));
  padding-inline-start: 0.9rem;
  padding-block: 0.25rem;
  margin-block-end: 0.75rem;
}

.grammar-card__line {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-block-end: 0.75rem;
}

.grammar-card__speaker {
  flex: 0 0 4.5rem;
  font-size: 0.8rem;
  font-weight: 700;
  text-align: center;
  color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
  border-radius: 999px;
  padding: 0.2rem 0.4rem;
  margin-block-start: 0.15rem;
}

.grammar-card__line-body {
  flex: 1;
  min-inline-size: 0;
}
</style>
