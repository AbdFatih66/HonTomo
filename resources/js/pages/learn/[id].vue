<script setup>
import { $api } from '@/utils/api'
import MultipleChoiceQuestion from '@/components/learning/MultipleChoiceQuestion.vue'
import FlashcardQuestion from '@/components/learning/FlashcardQuestion.vue'
import TypingQuestion from '@/components/learning/TypingQuestion.vue'
import GrammarQuestion from '@/components/learning/GrammarQuestion.vue'

// Focus mode: no sidebar / navbar while answering questions.
definePage({ meta: { layout: 'blank' } })

// ListeningQuestion / MatchingQuestion / SentenceOrderingQuestion /
// WritingQuestion / SpeakingQuestion plug in here as they are built (phase 2+).
// "grammar" is deliberately NOT here — grammar cards are shown in their own
// separate step before the quiz starts, never interleaved with quiz questions.
const componentMap = {
  multiple_choice: MultipleChoiceQuestion,
  listening: MultipleChoiceQuestion, // shares UI; audio_url drives playback
  flashcard: FlashcardQuestion,
  typing: TypingQuestion,
  translation: TypingQuestion,
}

const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const STARTING_HEARTS = 5

const isLoading = ref(true)
const loadError = ref(false)
const grammarCards = ref([])
const questions = ref([])
const currentIndex = ref(0)
const userLessonId = ref(null)
const hearts = ref(STARTING_HEARTS)

// Grammar is taught as its own separate step before the scored quiz starts —
// its own header/progress, no hearts, never mixed into the flashcard/quiz flow.
const grammarIndex = ref(0)
const showingGrammar = ref(false)
const isLastGrammarCard = computed(() => grammarIndex.value + 1 >= grammarCards.value.length)

// A grammar-only lesson (e.g. "Tata Bahasa (Bunpou)") has no quiz: the last
// card finishes the lesson instead of starting practice.
const isStudyOnly = computed(() => grammarCards.value.length > 0 && !questions.value.length)
const isFinishingStudy = ref(false)
const currentGrammarCard = computed(() => grammarCards.value[grammarIndex.value])

const grammarProgressPercent = computed(() => {
  if (!grammarCards.value.length)
    return 0

  return Math.round(((grammarIndex.value + 1) / grammarCards.value.length) * 100)
})

// Grammar cards can be long; moving to the next one must put the reader back
// at the top of the card instead of leaving them mid-scroll.
function scrollToTop() {
  nextTick(() => {
    const scroller = document.querySelector('.lp__body') || document.scrollingElement || document.documentElement

    scroller.scrollTo?.({ top: 0, behavior: 'smooth' })
    window.scrollTo?.({ top: 0, behavior: 'smooth' })
  })
}

async function advanceGrammar() {
  if (isFinishingStudy.value)
    return

  if (!isLastGrammarCard.value) {
    grammarIndex.value += 1
    scrollToTop()

    return
  }

  if (!isStudyOnly.value) {
    showingGrammar.value = false
    scrollToTop()

    return
  }

  isFinishingStudy.value = true
  try {
    result.value = await $api(`/user-lessons/${userLessonId.value}/finish`, { method: 'POST' })
    showingGrammar.value = false
  }
  catch {
    loadError.value = true
  }
  finally {
    isFinishingStudy.value = false
  }
}

const answer = ref(null)
const isChecking = ref(false)
const feedback = ref(null) // { correct, correctAnswer }
const result = ref(null) // set when the lesson is finished
const outOfHearts = ref(false)
const questionRef = ref(null)

const currentQuestion = computed(() => questions.value[currentIndex.value])
const isLastQuestion = computed(() => currentIndex.value + 1 >= questions.value.length)

// The bar fills as questions are answered (the current one counts once checked).
const progressPercent = computed(() => {
  if (!questions.value.length)
    return 0
  const done = currentIndex.value + (feedback.value ? 1 : 0)

  return Math.round((done / questions.value.length) * 100)
})

const questionComponent = computed(() =>
  componentMap[currentQuestion.value?.question_type] ?? MultipleChoiceQuestion)

// Question types that grade themselves the instant an option is picked —
// no separate "check" tap needed, unlike typing/translation which need a submit signal.
const AUTO_CHECK_TYPES = ['multiple_choice', 'listening']

// Flashcards grade themselves via their own buttons, and choice-based questions
// now grade the instant an option is tapped — so the "Periksa" button only
// remains for free-typed answers (typing/translation).
const showCheckButton = computed(() =>
  currentQuestion.value?.question_type !== 'flashcard'
  && !AUTO_CHECK_TYPES.includes(currentQuestion.value?.question_type))


// A choice question that reached the player with no answer options can't be
// answered; offer a way past it instead of leaving the learner stuck.
const hasNoOptions = computed(() =>
  AUTO_CHECK_TYPES.includes(currentQuestion.value?.question_type)
  && !(currentQuestion.value?.options?.length))

const canCheck = computed(() => answer.value !== null && answer.value !== '' && !isChecking.value)
const autoAdvanceTimer = ref(null)
const AUTO_ADVANCE_CORRECT_MS = 1100
const AUTO_ADVANCE_WRONG_MS = 2000

function clearAutoAdvance() {
  if (autoAdvanceTimer.value) {
    clearTimeout(autoAdvanceTimer.value)
    autoAdvanceTimer.value = null
  }
}

async function load() {
  clearAutoAdvance()
  isLoading.value = true
  loadError.value = false
  result.value = null
  outOfHearts.value = false
  feedback.value = null
  answer.value = null
  currentIndex.value = 0
  grammarIndex.value = 0
  showingGrammar.value = false
  isFinishingStudy.value = false
  hearts.value = STARTING_HEARTS

  try {
    const lessonId = route.params.id
    const res = await $api(`/lessons/${lessonId}`)

    lessonCategory.value = res.lesson?.category ?? null
    unitOrder.value = res.lesson?.unit_order ?? null

    // Grammar cards are pulled out and taught as their own step first —
    // they never sit inside the scored quiz's question list/progress/hearts.
    grammarCards.value = res.questions.filter(q => q.question_type === 'grammar')
    questions.value = res.questions.filter(q => q.question_type !== 'grammar')
    if (!questions.value.length && !grammarCards.value.length)
      throw new Error('empty')

    showingGrammar.value = grammarCards.value.length > 0

    const start = await $api(`/lessons/${lessonId}/start`, { method: 'POST' })

    userLessonId.value = start.user_lesson_id
  }
  catch {
    loadError.value = true
  }
  finally {
    isLoading.value = false
  }
}

function onAnswerChange(value) {
  answer.value = value

  // Choice-based questions are unambiguous the moment an option is tapped —
  // grade immediately instead of waiting for a separate "Periksa" tap.
  if (AUTO_CHECK_TYPES.includes(currentQuestion.value?.question_type))
    check()
}

async function check() {
  if (!canCheck.value && currentQuestion.value?.question_type !== 'flashcard')
    return
  if (isChecking.value || feedback.value)
    return

  isChecking.value = true
  try {
    const res = await $api(`/user-lessons/${userLessonId.value}/answer`, {
      method: 'POST',
      body: { question_id: currentQuestion.value.id, answer: answer.value },
    })

    if (!res.is_correct)
      hearts.value = Math.max(0, hearts.value - 1)

    questionRef.value?.markResult?.({
      isCorrect: res.is_correct,
      correctOptionId: res.correct_option_id,
    })

    feedback.value = { correct: res.is_correct, correctAnswer: res.correct_answer }

    // Auto-advance: the popup only shows the verdict (no "Lanjut" button) and
    // moves on by itself after a short pause.
    clearAutoAdvance()
    autoAdvanceTimer.value = setTimeout(next, res.is_correct ? AUTO_ADVANCE_CORRECT_MS : AUTO_ADVANCE_WRONG_MS)
  }
  catch {
    loadError.value = true
  }
  finally {
    isChecking.value = false
  }
}

async function next() {
  clearAutoAdvance()
  feedback.value = null
  answer.value = null

  if (hearts.value === 0) {
    outOfHearts.value = true

    return
  }

  if (isLastQuestion.value) {
    try {
      result.value = await $api(`/user-lessons/${userLessonId.value}/finish`, { method: 'POST' })
    }
    catch {
      loadError.value = true
    }

    return
  }

  currentIndex.value += 1
}

const passed = computed(() => ['completed', 'mastered'].includes(result.value?.status))

const accuracy = computed(() => {
  if (!result.value?.total_questions)
    return 0

  return Math.round((result.value.correct_answers / result.value.total_questions) * 100)
})

// Two different exits — they used to be one function, which is why the close
// button and "Lanjut" misbehaved:
//
//  * goToPath()        the X button and every "back to path" button: always
//                      return to the learning path, focused on this lesson.
//  * continueLearning() "Lanjut" after finishing: open the next lesson that
//                      can still be done.
const isLeaving = ref(false)

// Vocabulary quizzes are started from the Kosakata page, so they return there
// (to the chapter they belong to) instead of the Bunpou learning path.
const lessonCategory = ref(null)
const unitOrder = ref(null)

const isVocabularyQuiz = computed(() => lessonCategory.value === 'vocabulary')

function goToPath() {
  clearAutoAdvance()

  if (isVocabularyQuiz.value) {
    router.push({ name: 'kosakata', query: unitOrder.value ? { chapter: unitOrder.value } : {} })

    return
  }

  router.push({ name: 'learn', query: { focus: route.params.id } })
}

async function continueLearning() {
  if (isLeaving.value)
    return

  if (isVocabularyQuiz.value) {
    goToPath()

    return
  }

  isLeaving.value = true

  const currentId = String(route.params.id)

  try {
    const path = await $api('/learning-path')
    const lessons = (path?.units ?? []).flatMap(unit => unit.lessons ?? [])

    const isOpenable = lesson =>
      ['available', 'in_progress'].includes(lesson.status) && String(lesson.id) !== currentId

    // First unfinished lesson AFTER this one in path order; if there is none
    // after it, the earliest unfinished one anywhere. Locked lessons never
    // qualify (the server only reports a lesson as available once its
    // prerequisite is done), so this can never jump ahead of e.g. a Bunpou
    // lesson that has not been completed yet.
    const position = lessons.findIndex(lesson => String(lesson.id) === currentId)
    const target = lessons.slice(position + 1).find(isOpenable) ?? lessons.find(isOpenable)

    if (target) {
      await router.push({ name: 'learn-id', params: { id: target.id } })

      return
    }
  }
  catch {
    // fall through to the path below
  }
  finally {
    isLeaving.value = false
  }

  goToPath()
}

// The route component is reused when only :id changes (learn/5 -> learn/6),
// so onMounted does not run again — reload the lesson ourselves. Without this
// "Lanjut" changed the URL but left the old result screen up, and it took a
// second click to get anywhere.
watch(() => route.params.id, (id, previousId) => {
  if (id && id !== previousId)
    load()
})

// Enter = advance grammar card / Check / Continue on desktop
function onKey(e) {
  // A focused button already reacts to Enter by itself; handling it here
  // as well would advance twice (skipping a grammar card).
  if (e.key !== 'Enter' || ['INPUT', 'BUTTON'].includes(e.target?.tagName))
    return
  if (showingGrammar.value)
    advanceGrammar()
  else if (feedback.value)
    next()
  else if (showCheckButton.value)
    check()
}

onMounted(() => {
  load()
  window.addEventListener('keydown', onKey)
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKey)
  clearAutoAdvance()
})
</script>

<template>
  <div class="lp">
    <!-- Loading -->
    <div
      v-if="isLoading"
      class="lp__center"
    >
      <VProgressCircular
        indeterminate
        color="primary"
        size="48"
      />
      <span>{{ t('common.loading') }}</span>
    </div>

    <!-- Error -->
    <div
      v-else-if="loadError"
      class="lp__center"
    >
      <VIcon
        icon="tabler-mood-sad"
        size="56"
        color="error"
      />
      <p class="text-h6 mb-0">
        {{ t('lesson.not_found') }}
      </p>
      <div class="d-flex gap-3">
        <VBtn
          variant="tonal"
          @click="goToPath"
        >
          {{ t('lesson.back_to_path') }}
        </VBtn>
        <VBtn @click="load">
          {{ t('common.retry') }}
        </VBtn>
      </div>
    </div>

    <!-- Out of hearts -->
    <div
      v-else-if="outOfHearts"
      class="lp__center"
    >
      <div style="font-size: 4rem;">
        💔
      </div>
      <h2 class="mb-0">
        {{ t('lesson.out_of_hearts') }}
      </h2>
      <p class="mb-0 text-medium-emphasis">
        {{ t('lesson.out_of_hearts_hint') }}
      </p>
      <div class="d-flex gap-3">
        <VBtn
          variant="tonal"
          @click="goToPath"
        >
          {{ t('lesson.back_to_path') }}
        </VBtn>
        <VBtn @click="load">
          {{ t('lesson.try_again') }}
        </VBtn>
      </div>
    </div>

    <!-- Finished -->
    <div
      v-else-if="result"
      class="lp__center"
    >
      <div style="font-size: 4.5rem;">
        {{ passed ? '🎉' : '💪' }}
      </div>
      <h2 class="mb-0">
        {{ passed ? t('lesson.lesson_complete') : t('lesson.lesson_failed') }}
      </h2>
      <p
        v-if="!passed"
        class="mb-0 text-medium-emphasis"
      >
        {{ t('lesson.lesson_failed_hint') }}
      </p>

      <div class="result-stats">
        <div class="result-stat">
          <div
            class="result-stat__value"
            style="color: rgb(var(--v-theme-warning));"
          >
            +{{ result.xp_earned }}
          </div>
          <div class="result-stat__label">
            {{ t('lesson.xp_earned') }}
          </div>
        </div>
        <div
          v-if="!isStudyOnly"
          class="result-stat"
        >
          <div class="result-stat__value">
            {{ accuracy }}%
          </div>
          <div class="result-stat__label">
            {{ t('lesson.accuracy') }}
          </div>
        </div>
        <div
          v-if="!isStudyOnly"
          class="result-stat"
        >
          <div class="result-stat__value">
            {{ result.correct_answers }}/{{ result.total_questions }}
          </div>
          <div class="result-stat__label">
            {{ t('lesson.correct_count') }}
          </div>
        </div>
      </div>

      <div class="d-flex flex-wrap justify-center gap-3 mt-2">
        <VBtn
          v-if="!passed"
          variant="tonal"
          size="large"
          @click="load"
        >
          {{ t('lesson.try_again') }}
        </VBtn>
        <VBtn
          variant="tonal"
          color="secondary"
          size="large"
          prepend-icon="tabler-x"
          @click="goToPath"
        >
          {{ t('lesson.close') }}
        </VBtn>
        <VBtn
          size="large"
          :loading="isLeaving"
          @click="continueLearning"
        >
          {{ t('lesson.continue') }}
        </VBtn>
      </div>
    </div>

    <!-- Grammar intro: fully separate step, own header, no hearts/quiz progress -->
    <template v-else-if="showingGrammar">
      <header class="lp__header">
        <VBtn
          icon="tabler-x"
          variant="text"
          color="secondary"
          :aria-label="t('lesson.exit')"
          @click="goToPath"
        />
        <div
          class="lp__bar"
          role="progressbar"
          :aria-valuenow="grammarProgressPercent"
          aria-valuemin="0"
          aria-valuemax="100"
        >
          <div
            class="lp__fill"
            :style="{ inlineSize: `${grammarProgressPercent}%` }"
          />
        </div>
      </header>

      <main class="lp__body">
        <div class="lp__content">
          <div class="text-caption text-medium-emphasis">
            {{ t('lesson.grammar_of', { current: grammarIndex + 1, total: grammarCards.length }) }}
          </div>

          <GrammarQuestion
            :key="currentGrammarCard.id"
            :question="currentGrammarCard"
            :locked="isFinishingStudy"
            :is-last="isLastGrammarCard"
            :study-only="isStudyOnly"
            @submit="advanceGrammar"
          />
        </div>
      </main>
    </template>

    <!-- No quiz questions after grammar (grammar-only lesson) -->
    <div
      v-else-if="!questions.length"
      class="lp__center"
    >
      <VIcon
        icon="tabler-books"
        size="56"
        color="primary"
      />
      <p class="text-h6 mb-0">
        {{ t('lesson.no_questions') }}
      </p>
      <VBtn @click="goToPath">
        {{ t('lesson.back_to_path') }}
      </VBtn>
    </div>

    <!-- Question -->
    <template v-else-if="currentQuestion">
      <header class="lp__header">
        <VBtn
          icon="tabler-x"
          variant="text"
          color="secondary"
          :aria-label="t('lesson.exit')"
          @click="goToPath"
        />
        <div
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
        <div
          class="lp__hearts"
          :aria-label="t('lesson.hearts')"
        >
          <VIcon icon="tabler-heart-filled" />
          {{ hearts }}
        </div>
      </header>

      <main class="lp__body">
        <div class="lp__content">
          <div class="text-caption text-medium-emphasis">
            {{ t('lesson.question_of', { current: currentIndex + 1, total: questions.length }) }}
          </div>

          <VAlert
            v-if="hasNoOptions"
            type="warning"
            variant="tonal"
            class="mt-4"
          >
            {{ t('lesson.no_options') }}
          </VAlert>

          <Component
            :is="questionComponent"
            ref="questionRef"
            :key="currentQuestion.id"
            :question="currentQuestion"
            :locked="!!feedback || isChecking"
            @change="onAnswerChange"
            @submit="check"
          />
        </div>
      </main>

      <footer
        v-if="feedback || showCheckButton || hasNoOptions"
        class="lp__footer"
        :class="feedback ? (feedback.correct ? 'is-correct' : 'is-incorrect') : ''"
      >
        <div class="lp__footer-inner">
          <div
            v-if="feedback"
            class="lp__verdict"
            :style="{ color: feedback.correct ? 'rgb(var(--v-theme-success))' : 'rgb(var(--v-theme-error))' }"
          >
            <VIcon
              :icon="feedback.correct ? 'tabler-circle-check-filled' : 'tabler-circle-x-filled'"
              size="36"
            />
            <div>
              {{ feedback.correct ? t('lesson.correct') : t('lesson.incorrect') }}
              <small v-if="!feedback.correct && feedback.correctAnswer">
                {{ t('lesson.correct_answer') }}: <strong lang="ja">{{ feedback.correctAnswer }}</strong>
              </small>
              <small v-else-if="feedback.correct && feedback.correctAnswer">
                <span lang="ja">{{ feedback.correctAnswer }}</span>
              </small>
            </div>
          </div>
          <span v-else />

          <VBtn
            v-if="hasNoOptions && !feedback"
            size="large"
            variant="tonal"
            class="lp__cta"
            @click="next"
          >
            {{ t('lesson.skip') }}
          </VBtn>

          <VBtn
            v-else-if="!feedback"
            size="large"
            color="primary"
            class="lp__cta"
            :disabled="!canCheck"
            :loading="isChecking"
            @click="check"
          >
            {{ t('lesson.check') }}
          </VBtn>
        </div>
      </footer>
    </template>
  </div>
</template>
