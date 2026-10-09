<script setup>
import { $api } from '@/utils/api'
import KanaWritingCanvas from '@/components/kana/KanaWritingCanvas.vue'
import { useAutoNext } from '@/composables/useAutoNext'

// Focus mode, same as the lesson player: no sidebar / navbar during the quiz.
definePage({ meta: { layout: 'blank' } })

// A written stroke is graded correct as long as the total mistakes made
// while tracing it stay at or below this — real handwriting on a mouse or
// touchscreen is rarely pixel-perfect, so a couple of corrected slips
// shouldn't fail the question.
const WRITING_MISTAKE_TOLERANCE = 2

const { t } = useI18n()
const router = useRouter()
const autoNext = useAutoNext()

const STAGE_SETUP = 'setup'
const STAGE_QUIZ = 'quiz'
const STAGE_RESULT = 'result'

const stage = ref(STAGE_SETUP)
const isLoading = ref(false)
const hasError = ref(false)

const form = reactive({
  script: 'hiragana',
  types: ['gojuon', 'dakuten', 'handakuten', 'yoon', 'yoon_dakuten'],
  count: 10,
  writeRatio: 30, // percent, shown as a slider
})

const SCRIPT_OPTIONS = [
  { value: 'hiragana', glyph: 'あ' },
  { value: 'katakana', glyph: 'ア' },
]

const TYPE_OPTIONS = ['gojuon', 'dakuten', 'handakuten', 'yoon', 'yoon_dakuten']

function toggleType(type) {
  const i = form.types.indexOf(type)

  if (i === -1)
    form.types.push(type)
  else
    form.types.splice(i, 1)
}

const questions = ref([])
const currentIndex = ref(0)
const score = ref(0)

// Per-question UI state
const selectedOption = ref(null)
const isAnswered = ref(false)

const currentQuestion = computed(() => questions.value[currentIndex.value])
const isLastQuestion = computed(() => currentIndex.value + 1 >= questions.value.length)

// The right answer for whichever direction the current choice question
// asks (character -> romaji, or romaji -> character).
const correctChoiceAnswer = computed(() => {
  const q = currentQuestion.value

  return q?.prompt_character ? q.answer_romaji : q?.answer_character
})

// Bottom verdict bar state, same shape as the lesson player's `feedback`
// (see pages/learn/[id].vue) so both quiz shells read the same way.
const feedback = computed(() => {
  if (currentQuestion.value?.mode === 'choice') {
    if (!isAnswered.value)
      return null

    const correct = selectedOption.value === correctChoiceAnswer.value

    return { correct, correctAnswer: correct ? null : correctChoiceAnswer.value }
  }

  if (!writingResult.value)
    return null

  return {
    correct: writingResult.value.correct,
    correctAnswer: writingResult.value.correct ? null : currentQuestion.value.answer_character,
  }
})

const progressPercent = computed(() => {
  if (!questions.value.length)
    return 0

  return Math.round((currentIndex.value / questions.value.length) * 100)
})

async function startQuiz() {
  isLoading.value = true
  hasError.value = false
  try {
    const data = await $api('/kana/quiz', {
      query: {
        script: form.script,
        types: form.types.join(','),
        count: form.count,
        write_ratio: (form.writeRatio / 100).toFixed(2),
      },
    })

    if (!data.questions.length) {
      hasError.value = true

      return
    }

    // A choice question without at least two options can't be answered —
    // turn it into a "write it yourself" question instead of showing an
    // empty card.
    questions.value = data.questions.map(q => (
      q.mode === 'choice' && (q.options?.length ?? 0) < 2
        ? { ...q, mode: 'write', prompt_romaji: q.answer_romaji, options: [] }
        : q
    ))
    currentIndex.value = 0
    score.value = 0
    resetQuestionState()
    stage.value = STAGE_QUIZ
  }
  catch {
    hasError.value = true
  }
  finally {
    isLoading.value = false
  }
}

// Every question resolves itself the same way as writing-practice mode:
// answer/finish -> a brief visual confirmation -> automatic advance. No
// separate "you were right/wrong, now click Next" step for either mode.
const ADVANCE_DELAY_CORRECT = 600
const ADVANCE_DELAY_INCORRECT = 1100 // a little longer so the correct answer is visible before it disappears

let advanceTimer = null

function clearAdvanceTimer() {
  if (advanceTimer) {
    clearTimeout(advanceTimer)
    advanceTimer = null
  }
}

function scheduleAdvance(correct) {
  if (!autoNext.value)
    return

  clearAdvanceTimer()
  advanceTimer = setTimeout(advance, correct ? ADVANCE_DELAY_CORRECT : ADVANCE_DELAY_INCORRECT)
}

watch(autoNext, on => {
  if (!on)
    clearAdvanceTimer()
})

function resetQuestionState() {
  selectedOption.value = null
  isAnswered.value = false
  writingResult.value = null
}

function chooseOption(option) {
  if (isAnswered.value)
    return

  selectedOption.value = option
  isAnswered.value = true

  const correctAnswer = currentQuestion.value.prompt_character
    ? currentQuestion.value.answer_romaji
    : currentQuestion.value.answer_character

  const correct = option === correctAnswer

  if (correct)
    score.value += 1

  scheduleAdvance(correct)
}

const writingResult = ref(null) // { correct, mistakes }

// Called once the user has traced every stroke of the character correctly
// (HanziWriter only fires this after the full character is done).
function onWritingComplete(summary) {
  if (isAnswered.value)
    return

  const correct = summary.totalMistakes <= WRITING_MISTAKE_TOLERANCE

  writingResult.value = { correct, mistakes: summary.totalMistakes }
  isAnswered.value = true

  if (correct)
    score.value += 1

  scheduleAdvance(correct)
}

// Escape hatch for a write question the learner cannot (or does not want to)
// finish: counts as wrong, briefly shows the answer, then advances.
function skipWriting() {
  if (isAnswered.value)
    return

  writingResult.value = { correct: false, mistakes: -1, skipped: true }
  isAnswered.value = true
  scheduleAdvance(false)
}

function advance() {
  clearAdvanceTimer()

  if (isLastQuestion.value) {
    stage.value = STAGE_RESULT

    return
  }

  currentIndex.value += 1
  resetQuestionState()
}

function retry() {
  clearAdvanceTimer()
  stage.value = STAGE_SETUP
}

onBeforeUnmount(clearAdvanceTimer)
</script>

<template>
  <div class="kana-fs kana-fs--page">
    <header class="kana-fs__header">
      <VBtn
        icon="tabler-x"
        variant="text"
        color="secondary"
        :aria-label="t('kana.quiz_back')"
        @click="router.push({ name: 'kana' })"
      />
      <div
        v-if="stage === STAGE_QUIZ"
        class="lp__bar"
        role="progressbar"
        :aria-valuenow="progressPercent"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <div
          class="lp__fill"
          :style="{ inlineSize: `${progressPercent}%` }"
        />
      </div>
      <h5
        v-else
        class="text-h6 mb-0"
      >
        {{ t('kana.quiz_title') }}
      </h5>

      <VSwitch
        v-if="stage === STAGE_QUIZ"
        v-model="autoNext"
        :label="t('kana.auto_next')"
        color="primary"
        density="compact"
        hide-details
        class="flex-shrink-0"
      />
    </header>

    <main class="kana-fs__body">
      <div class="kana-fs__content">
        <!-- SETUP -->
        <VCard
          v-if="stage === STAGE_SETUP"
          class="pa-6 pa-sm-8 mx-auto"
          max-width="520"
        >
          <VAlert
            v-if="hasError"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ t('common.error') }}
          </VAlert>

          <div class="mb-8">
            <div class="text-subtitle-2 mb-3">
              {{ t('kana.quiz_setup_script') }}
            </div>
            <div class="kana-quiz__tiles kana-quiz__tiles--2">
              <button
                v-for="sc in SCRIPT_OPTIONS"
                :key="sc.value"
                type="button"
                class="kana-quiz__tile"
                :class="{ 'kana-quiz__tile--active': form.script === sc.value }"
                :aria-pressed="form.script === sc.value"
                @click="form.script = sc.value"
              >
                <span class="kana-quiz__tile-glyph">{{ sc.glyph }}</span>
                <span class="kana-quiz__tile-label">{{ t(`kana.${sc.value}`) }}</span>
              </button>
            </div>
          </div>

          <div class="mb-8">
            <div class="text-subtitle-2 mb-3">
              {{ t('kana.quiz_setup_types') }}
            </div>
            <div class="kana-quiz__tiles kana-quiz__tiles--5">
              <button
                v-for="ty in TYPE_OPTIONS"
                :key="ty"
                type="button"
                class="kana-quiz__tile kana-quiz__tile--compact"
                :class="{ 'kana-quiz__tile--active': form.types.includes(ty) }"
                :aria-pressed="form.types.includes(ty)"
                @click="toggleType(ty)"
              >
                <span class="kana-quiz__tile-label">{{ t(`kana.${ty}`) }}</span>
              </button>
            </div>
          </div>

          <div class="mb-6">
            <div class="text-subtitle-2 mb-2">
              {{ t('kana.quiz_setup_count') }}: {{ form.count }}
            </div>
            <VSlider
              v-model="form.count"
              :min="5"
              :max="25"
              :step="1"
              thumb-label
            />
          </div>

          <div class="mb-6">
            <div class="text-subtitle-2 mb-2">
              {{ t('kana.quiz_setup_write_ratio') }}: {{ form.writeRatio }}%
            </div>
            <VSlider
              v-model="form.writeRatio"
              :min="0"
              :max="70"
              :step="10"
              thumb-label
            />
          </div>

          <VBtn
            block
            color="primary"
            :loading="isLoading"
            :disabled="!form.types.length"
            @click="startQuiz"
          >
            {{ t('kana.start_quiz') }}
          </VBtn>
        </VCard>

        <!-- QUIZ -->
        <div
          v-else-if="stage === STAGE_QUIZ && currentQuestion"
          class="mx-auto"
          style="max-width: 560px;"
        >
          <div class="text-caption text-medium-emphasis mb-4">
            {{ t('kana.question_of', { current: currentIndex + 1, total: questions.length }) }}
          </div>

          <VCard class="pa-6 text-center">
            <!-- Multiple choice -->
            <template v-if="currentQuestion.mode === 'choice'">
              <div
                v-if="currentQuestion.prompt_character"
                class="q-jp"
              >
                {{ currentQuestion.prompt_character }}
              </div>
              <div
                v-else
                class="text-h5 mb-4"
              >
                {{ t('kana.choice_prompt_romaji', { romaji: currentQuestion.prompt_romaji }) }}
              </div>
              <p
                v-if="currentQuestion.prompt_character"
                class="text-body-2 text-medium-emphasis mb-4"
              >
                {{ t('kana.choice_prompt_character') }}
              </p>

              <div class="option-grid">
                <button
                  v-for="(option, i) in currentQuestion.options"
                  :key="option"
                  type="button"
                  class="option-card"
                  :class="{
                    'is-selected': !isAnswered && option === selectedOption,
                    'is-correct': isAnswered && option === correctChoiceAnswer,
                    'is-wrong': isAnswered && option === selectedOption && option !== correctChoiceAnswer,
                  }"
                  :disabled="isAnswered"
                  @click="chooseOption(option)"
                >
                  <span class="option-card__key">{{ i + 1 }}</span>
                  <span
                    v-if="!currentQuestion.prompt_character"
                    class="option-card__jp"
                    lang="ja"
                  >{{ option }}</span>
                  <span v-else>{{ option }}</span>
                </button>
              </div>
            </template>

            <!--
              Write it yourself: real stroke-by-stroke tracing, graded live —
              same writing widget and layout language as the practice screen. 
            -->
            <template v-else>
              <div class="d-flex flex-column align-center gap-2 mb-4">
                <div class="text-h5">
                  {{ currentQuestion.prompt_romaji }}
                </div>
                <VChip
                  size="small"
                  variant="tonal"
                >
                  {{ t('kana.write_prompt') }}
                </VChip>
              </div>

              <div class="d-flex justify-center mb-2">
                <KanaWritingCanvas
                  :key="currentQuestion.id"
                  :character="currentQuestion.answer_character"
                  :size="200"
                  :show-outline="false"
                  @complete="onWritingComplete"
                />
              </div>

              <VBtn
                v-if="!writingResult"
                variant="text"
                size="small"
                append-icon="tabler-player-skip-forward"
                @click="skipWriting"
              >
                {{ t('kana.skip') }}
              </VBtn>
            </template>
          </VCard>
        </div>

        <!-- RESULT -->
        <VCard
          v-else-if="stage === STAGE_RESULT"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <div class="text-h3 mb-2">
            🎉
          </div>
          <h5 class="text-h5 mb-2">
            {{ t('kana.quiz_result_title') }}
          </h5>
          <p class="text-h6 mb-6">
            {{ t('kana.quiz_result_score', { correct: score, total: questions.length }) }}
          </p>
          <div class="d-flex gap-2 justify-center">
            <VBtn
              variant="tonal"
              @click="router.push({ name: 'kana' })"
            >
              {{ t('kana.quiz_back') }}
            </VBtn>
            <VBtn
              color="primary"
              @click="retry"
            >
              {{ t('kana.quiz_retry') }}
            </VBtn>
          </div>
        </VCard>
      </div>
    </main>

    <footer
      v-if="feedback"
      class="kana-fs__footer"
      :class="feedback.correct ? 'is-correct' : 'is-incorrect'"
    >
      <div class="kana-fs__footer-inner">
        <div
          class="lp__verdict"
          :style="{ color: feedback.correct ? 'rgb(var(--v-theme-success))' : 'rgb(var(--v-theme-error))' }"
        >
          <VIcon
            :icon="feedback.correct ? 'tabler-circle-check-filled' : 'tabler-circle-x-filled'"
            size="36"
          />
          <div>
            {{ feedback.correct ? t('kana.correct') : t('kana.incorrect') }}
            <small v-if="feedback.correctAnswer">
              {{ t('kana.the_answer_was', { answer: feedback.correctAnswer }) }}
            </small>
          </div>
        </div>

        <VBtn
          v-if="!autoNext"
          color="primary"
          append-icon="tabler-arrow-right"
          @click="advance"
        >
          {{ isLastQuestion ? t('kana.finish_quiz') : t('kana.next') }}
        </VBtn>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Setup-screen pickers only (script / type tiles) — the answer options and
   feedback bar now reuse the global .option-grid/.option-card/.lp__verdict
   classes from learning.scss, shared with the lesson player. */
.kana-quiz__tiles {
  display: grid;
  gap: 16px;
}

.kana-quiz__tiles--2 {
  grid-template-columns: repeat(2, 1fr);
}

.kana-quiz__tiles--3 {
  grid-template-columns: repeat(3, 1fr);
}

.kana-quiz__tiles--5 {
  grid-template-columns: repeat(5, 1fr);
}

@media (max-width: 599px) {
  .kana-quiz__tiles--5 {
    grid-template-columns: repeat(3, 1fr);
  }
}

.kana-quiz__tile {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 16px 12px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 14px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  transition: border-color 0.15s ease, background-color 0.15s ease;
}

.kana-quiz__tile:hover {
  border-color: rgba(var(--v-theme-primary), 0.6);
}

.kana-quiz__tile--active {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

.kana-quiz__tile--compact {
  padding: 14px 8px;
}

.kana-quiz__tile-glyph {
  font-family: 'Noto Sans JP', sans-serif;
  font-size: 2.5rem;
  line-height: 1.1;
}

.kana-quiz__tile-label {
  font-size: 0.95rem;
  font-weight: 600;
}
</style>
