<script setup>
import KanjiWritingCanvas from '@/components/kanji/KanjiWritingCanvas.vue'
import { $api } from '@/utils/api'

// Focus mode, same as the lesson player and the Kana quiz: no sidebar/navbar.
definePage({ meta: { layout: 'blank' } })

// Same reasoning as Kana's quiz: a couple of corrected slips while tracing
// shouldn't fail an otherwise-correct answer.
const WRITING_MISTAKE_TOLERANCE = 2

const { t } = useI18n()
const router = useRouter()

const STAGE_SETUP = 'setup'
const STAGE_QUIZ = 'quiz'
const STAGE_RESULT = 'result'

const stage = ref(STAGE_SETUP)
const isLoading = ref(false)
const hasError = ref(false)

const LEVEL_OPTIONS = ['N5', 'N4', 'N3', 'N2', 'N1', 'CURRICULUM']

function levelLabel(level) {
  return level === 'CURRICULUM' ? t('kanji.quiz_scope_curriculum') : level
}
const TYPE_OPTIONS = [
  { value: 'meaning', label: 'kanji.quiz_type_meaning' },
  { value: 'reading', label: 'kanji.quiz_type_reading' },
  { value: 'kanji_from_reading', label: 'kanji.quiz_type_kanji_from_reading' },
  { value: 'vocabulary', label: 'kanji.quiz_type_vocabulary' },
]

const form = reactive({
  jlptLevel: 'N5',
  mode: 'practice', // 'practice' | 'review'
  types: ['meaning', 'reading', 'kanji_from_reading', 'vocabulary'],
  count: 10,
  writeRatio: 20, // percent, shown as a slider
})

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
const reviewPoolTooSmall = ref(false)

const selectedOption = ref(null)
const isAnswered = ref(false)
const writingResult = ref(null) // { correct, mistakes }

const currentQuestion = computed(() => questions.value[currentIndex.value])
const isLastQuestion = computed(() => currentIndex.value + 1 >= questions.value.length)

const progressPercent = computed(() => {
  if (!questions.value.length)
    return 0

  return Math.round((currentIndex.value / questions.value.length) * 100)
})

async function startQuiz() {
  isLoading.value = true
  hasError.value = false
  reviewPoolTooSmall.value = false
  try {
    // "write" is a fifth, separate toggle on the write-ratio slider
    // (0% = never), not one of the `types` checkboxes — asking for it
    // explicitly here keeps the request symmetric with what the API
    // actually needs to know.
    const requestedTypes = form.writeRatio > 0 ? [...form.types, 'write'] : form.types

    const isCurriculum = form.jlptLevel === 'CURRICULUM'

    const data = await $api('/kanji/quiz', {
      query: {
        jlpt_level: isCurriculum ? undefined : form.jlptLevel,
        scope: isCurriculum ? 'curriculum' : 'jlpt',
        mode: form.mode,
        types: requestedTypes.join(','),
        count: form.count,
        write_ratio: (form.writeRatio / 100).toFixed(2),
      },
    })

    if (!data.questions.length) {
      // In review mode, an empty pool most likely means "nothing left to
      // review" (everything already mastered) rather than a real error —
      // worth telling apart in the UI.
      if (form.mode === 'review' && data.pool_size === 0)
        reviewPoolTooSmall.value = true
      else
        hasError.value = true

      return
    }

    questions.value = data.questions
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

const ADVANCE_DELAY_CORRECT = 700
const ADVANCE_DELAY_INCORRECT = 1300

let advanceTimer = null

function clearAdvanceTimer() {
  if (advanceTimer) {
    clearTimeout(advanceTimer)
    advanceTimer = null
  }
}

function scheduleAdvance(correct) {
  clearAdvanceTimer()
  advanceTimer = setTimeout(advance, correct ? ADVANCE_DELAY_CORRECT : ADVANCE_DELAY_INCORRECT)
}

function resetQuestionState() {
  selectedOption.value = null
  isAnswered.value = false
  writingResult.value = null
}

// The four choice-based question types each keep their answer under a
// different option shape (a plain string, or {word, reading} for the
// vocabulary type) — this resolves the right comparator/display value
// once per question instead of repeating a switch in the template.
function optionValue(option) {
  return typeof option === 'object' ? option.word : option
}

// Fire-and-forget: records this answer's outcome against the kanji's
// persistent mastery tracking (Tahap 6). Never awaited/blocking — a slow
// or failed request must not delay the auto-advance the learner is
// already watching for.
function recordProgress(kanjiId, correct) {
  $api(`/kanji/${kanjiId}/progress`, {
    method: 'POST',
    body: { correct },
  }).catch(() => {
    // Best-effort only. A missed progress update for one question isn't
    // worth surfacing an error over mid-quiz.
  })
}

function chooseOption(option) {
  if (isAnswered.value)
    return

  selectedOption.value = option
  isAnswered.value = true

  const correct = optionValue(option) === currentQuestion.value.answer

  if (correct)
    score.value += 1

  recordProgress(currentQuestion.value.id, correct)
  scheduleAdvance(correct)
}

function onWritingComplete(summary) {
  if (isAnswered.value)
    return

  const correct = summary.totalMistakes <= WRITING_MISTAKE_TOLERANCE

  writingResult.value = { correct, mistakes: summary.totalMistakes }
  isAnswered.value = true

  if (correct)
    score.value += 1

  recordProgress(currentQuestion.value.id, correct)
  scheduleAdvance(correct)
}

function skipWriting() {
  if (isAnswered.value)
    return

  writingResult.value = { correct: false, mistakes: -1, skipped: true }
  isAnswered.value = true
  recordProgress(currentQuestion.value.id, false)
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
        @click="router.push({ name: 'kanji' })"
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
        {{ t('kanji.quiz_title') }}
      </h5>
    </header>

    <main class="kana-fs__body">
      <div class="kana-fs__content">
        <!-- SETUP -->
        <VCard
          v-if="stage === STAGE_SETUP"
          class="pa-6 pa-sm-8 mx-auto"
          max-width="560"
        >
          <VAlert
            v-if="hasError"
            type="error"
            variant="tonal"
            class="mb-4"
          >
            {{ t('kanji.quiz_no_data') }}
          </VAlert>

          <VAlert
            v-if="reviewPoolTooSmall"
            type="success"
            variant="tonal"
            class="mb-4"
          >
            {{ t('kanji.quiz_review_all_mastered') }}
          </VAlert>

          <div class="mb-8">
            <div class="text-subtitle-2 mb-3">
              {{ t('kanji.quiz_setup_mode') }}
            </div>
            <div class="kana-quiz__tiles kana-quiz__tiles--2">
              <button
                type="button"
                class="kana-quiz__tile kana-quiz__tile--compact"
                :class="{ 'kana-quiz__tile--active': form.mode === 'practice' }"
                :aria-pressed="form.mode === 'practice'"
                @click="form.mode = 'practice'"
              >
                <VIcon icon="tabler-cards" />
                <span class="kana-quiz__tile-label">{{ t('kanji.quiz_mode_practice') }}</span>
              </button>
              <button
                type="button"
                class="kana-quiz__tile kana-quiz__tile--compact"
                :class="{ 'kana-quiz__tile--active': form.mode === 'review' }"
                :aria-pressed="form.mode === 'review'"
                @click="form.mode = 'review'"
              >
                <VIcon icon="tabler-repeat" />
                <span class="kana-quiz__tile-label">{{ t('kanji.quiz_mode_review') }}</span>
              </button>
            </div>
            <p class="text-caption text-medium-emphasis mt-2 mb-0">
              {{ form.mode === 'review' ? t('kanji.quiz_mode_review_hint') : t('kanji.quiz_mode_practice_hint') }}
            </p>
          </div>

          <div class="mb-8">
            <div class="text-subtitle-2 mb-3">
              {{ t('kanji.quiz_setup_level') }}
            </div>
            <div class="kana-quiz__tiles kana-quiz__tiles--5">
              <button
                v-for="level in LEVEL_OPTIONS"
                :key="level"
                type="button"
                class="kana-quiz__tile kana-quiz__tile--compact"
                :class="{ 'kana-quiz__tile--active': form.jlptLevel === level }"
                :aria-pressed="form.jlptLevel === level"
                @click="form.jlptLevel = level"
              >
                <span class="kana-quiz__tile-label">{{ levelLabel(level) }}</span>
              </button>
            </div>
          </div>

          <div class="mb-8">
            <div class="text-subtitle-2 mb-3">
              {{ t('kanji.quiz_setup_types') }}
            </div>
            <VCheckbox
              v-for="ty in TYPE_OPTIONS"
              :key="ty.value"
              :model-value="form.types.includes(ty.value)"
              :label="t(ty.label)"
              density="compact"
              hide-details
              @update:model-value="toggleType(ty.value)"
            />
          </div>

          <div class="mb-8">
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
              {{ t('kanji.quiz_setup_write_ratio') }}: {{ form.writeRatio }}%
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
            :disabled="!form.types.length && form.writeRatio === 0"
            @click="startQuiz"
          >
            {{ t('kana.start_quiz') }}
          </VBtn>
        </VCard>

        <!-- QUIZ -->
        <div
          v-else-if="stage === STAGE_QUIZ && currentQuestion"
          class="mx-auto"
          style="max-inline-size: 560px;"
        >
          <div class="text-caption text-medium-emphasis mb-4">
            {{ t('kana.question_of', { current: currentIndex + 1, total: questions.length }) }}
          </div>

          <VCard class="pa-6 text-center">
            <!-- A) Kanji -> Arti -->
            <template v-if="currentQuestion.question_type === 'meaning'">
              <div class="kanji-quiz__character mb-2">
                {{ currentQuestion.prompt_character }}
              </div>
              <p class="text-body-2 text-medium-emphasis mb-4">
                {{ t('kanji.choice_prompt_meaning') }}
              </p>
            </template>

            <!-- B) Kanji -> Cara Baca (context: a whole word, never a bare kanji) -->
            <template v-else-if="currentQuestion.question_type === 'reading'">
              <div class="kanji-quiz__character mb-1">
                {{ currentQuestion.prompt_word }}
              </div>
              <p class="text-body-2 text-medium-emphasis mb-4">
                {{ currentQuestion.prompt_meaning }} — {{ t('kanji.choice_prompt_reading') }}
              </p>
            </template>

            <!-- C) Hiragana -> Kanji -->
            <template v-else-if="currentQuestion.question_type === 'kanji_from_reading'">
              <div class="text-h5 mb-1">
                {{ currentQuestion.prompt_reading }}
              </div>
              <p class="text-body-2 text-medium-emphasis mb-4">
                {{ currentQuestion.prompt_meaning }} — {{ t('kanji.choice_prompt_kanji') }}
              </p>
            </template>

            <!-- D) Kanji -> Kosakata -->
            <template v-else-if="currentQuestion.question_type === 'vocabulary'">
              <div class="kanji-quiz__character mb-1">
                {{ currentQuestion.prompt_character }}
              </div>
              <p class="text-body-2 text-medium-emphasis mb-4">
                {{ currentQuestion.prompt_meaning }} — {{ t('kanji.choice_prompt_vocabulary') }}
              </p>
            </template>

            <!-- Choice options, shared by all 4 non-writing types above -->
            <div
              v-if="currentQuestion.mode === 'choice'"
              class="kana-quiz__options"
            >
              <VBtn
                v-for="(option, i) in currentQuestion.options"
                :key="i"
                size="large"
                class="kana-quiz__option"
                :class="[typeof option === 'object' || currentQuestion.question_type === 'meaning' ? '' : 'kana-quiz__option--char']"
                :color="
                  isAnswered
                    ? optionValue(option) === currentQuestion.answer
                      ? 'success'
                      : (option === selectedOption ? 'error' : undefined)
                    : undefined
                "
                :variant="isAnswered ? 'flat' : 'tonal'"
                :disabled="isAnswered"
                @click="chooseOption(option)"
              >
                <template v-if="typeof option === 'object'">
                  <div class="d-flex flex-column">
                    <span>{{ option.word }}</span>
                    <span class="text-caption text-medium-emphasis">{{ option.reading }}</span>
                  </div>
                </template>
                <template v-else>
                  {{ option }}
                </template>
              </VBtn>
            </div>

            <div
              v-if="currentQuestion.mode === 'choice'"
              class="kana-quiz__feedback"
              :class="{ 'kana-quiz__feedback--visible': isAnswered }"
            >
              <VIcon
                :icon="optionValue(selectedOption) === currentQuestion.answer ? 'tabler-circle-check-filled' : 'tabler-circle-x-filled'"
                :color="optionValue(selectedOption) === currentQuestion.answer ? 'success' : 'error'"
              />
              {{ optionValue(selectedOption) === currentQuestion.answer ? t('kana.correct') : t('kana.the_answer_was', { answer: currentQuestion.answer }) }}
            </div>

            <!-- E) Writing quiz -->
            <template v-else-if="currentQuestion.mode === 'write'">
              <div class="d-flex flex-column align-center gap-2 mb-4">
                <div class="text-h5">
                  {{ currentQuestion.prompt_meaning }}
                </div>
                <VChip
                  size="small"
                  variant="tonal"
                >
                  {{ t('kanji.write_prompt') }}
                </VChip>
              </div>

              <div class="d-flex justify-center mb-2">
                <KanjiWritingCanvas
                  :key="currentQuestion.id"
                  :character="currentQuestion.answer_character"
                  :kanji-id="currentQuestion.id"
                  :size="200"
                  :show-outline="false"
                  @complete="onWritingComplete"
                />
              </div>

              <div
                class="kana-quiz__feedback"
                :class="{ 'kana-quiz__feedback--visible': !!writingResult }"
              >
                <template v-if="writingResult">
                  <VIcon
                    :icon="writingResult.correct ? 'tabler-circle-check-filled' : 'tabler-circle-x-filled'"
                    :color="writingResult.correct ? 'success' : 'error'"
                  />
                  {{ writingResult.correct ? t('kana.correct') : t('kana.the_answer_was', { answer: currentQuestion.answer_character }) }}
                </template>
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
              @click="router.push({ name: 'kanji' })"
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
  </div>
</template>

<style scoped>
/* Copied from resources/js/pages/kana/quiz.vue's own scoped styles — these
   classes are NOT global (unlike .kana-fs* / .lp__* in learning.scss), so
   they must be duplicated here rather than just reused by class name. If
   you tweak the look of one quiz's tiles/options/feedback, check whether
   the other should match. */
.kana-quiz__tiles {
  display: grid;
  gap: 16px;
}

.kana-quiz__tiles--5 {
  grid-template-columns: repeat(5, 1fr);
}

.kana-quiz__tiles--2 {
  grid-template-columns: repeat(2, 1fr);
}

.kana-quiz__tile {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 14px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  gap: 4px;
  padding-block: 16px;
  padding-inline: 12px;
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
  padding-block: 14px;
  padding-inline: 8px;
}

.kana-quiz__tile-label {
  font-size: 0.95rem;
  font-weight: 600;
}

.kana-quiz__feedback {
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(var(--v-theme-on-surface), 0.8);
  font-size: 0.9rem;
  gap: 0.4rem;
  margin-block-start: 1rem;
  min-block-size: 2rem;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.kana-quiz__feedback--visible {
  opacity: 1;
}

.kana-quiz__options {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(2, 1fr);
}

.kana-quiz__option {
  block-size: auto;
  min-block-size: 64px;
  padding-block: 8px;
}

.kana-quiz__option--char {
  font-family: "Noto Sans JP", sans-serif;
  font-size: 1.75rem;
}

.kanji-quiz__character {
  font-family: "Noto Sans JP", sans-serif;
  font-size: 4rem;
  line-height: 1;
}
</style>
