<script setup>
// Membaca (読解) — latihan membaca per pelajaran untuk N5 / N4 / N3.
//
// Alur: pilih level + pelajaran → baca teks (furigana & terjemahan bisa
// dinyalakan/dimatikan, kata penting ada di samping) → jawab soal pemahaman
// (pilihan ganda dan ○×) → hasil + progres. Materi ada di
// `@/data/practice/membaca` (statis, tanpa tabel pelajaran); progres per
// pelajaran disimpan lewat /api/membaca/progress dengan kunci `n{level}l{n}`.
import RubyText from '@/components/learning/RubyText.vue'
import LevelSwitch from '@/components/learning/LevelSwitch.vue'
import { useLevelChoice } from '@/composables/useLevelChoice'
import {
  MEMBACA,
  MEMBACA_LESSON_COUNT,
  MEMBACA_LEVELS,
  MEMBACA_SOURCE_LESSONS,
} from '@/data/practice/membaca'
import { $api } from '@/utils/api'

const { t, locale } = useI18n()
const { level: sharedLevel } = useLevelChoice()

function tr(value) {
  if (value == null)
    return value

  return typeof value === 'object' ? (locale.value === 'en' ? value.en : value.id) : value
}

// Level halaman ini (N5 / N4 / N3); awalnya mengikuti level bersama bila cocok.
const level = ref(MEMBACA_LEVELS.includes(sharedLevel.value) ? sharedLevel.value : 'N5')

const STAGE_LIST = 'list'
const STAGE_READ = 'read'
const STAGE_QUIZ = 'quiz'
const STAGE_RESULT = 'result'

const stage = ref(STAGE_LIST)
const activeId = ref(null)

const lessonsOfLevel = computed(() => MEMBACA[level.value] ?? {})
const lesson = computed(() => (activeId.value == null ? null : lessonsOfLevel.value[activeId.value] ?? null))

// ---------------------------------------------------------------------
// Progres
// ---------------------------------------------------------------------
const progress = reactive({})
const progressLoaded = ref(false)

const keyOf = id => `${level.value.toLowerCase()}l${id}`

async function loadProgress() {
  try {
    const res = await $api('/membaca/progress')

    Object.assign(progress, res.progress ?? {})
  }
  catch {
    // Offline / gagal: lanjut dengan progres kosong, jangan memblokir halaman.
  }
  finally {
    progressLoaded.value = true
  }
}

loadProgress()

function markProgress(key, perfect) {
  const prev = progress[key]

  progress[key] = { done: true, crown: perfect || !!prev?.crown }

  $api(`/membaca/progress/${encodeURIComponent(key)}`, {
    method: 'POST',
    body: { perfect: !!perfect },
  }).catch(() => {
    // Best-effort — gagal kirim tidak boleh mengganggu layar hasil.
  })
}

const cards = computed(() => Array.from({ length: MEMBACA_LESSON_COUNT }, (_, i) => {
  const id = i + 1
  const entry = lessonsOfLevel.value[id]
  const p = progress[keyOf(id)]
  const sources = MEMBACA_SOURCE_LESSONS[level.value]?.[id] ?? [id]

  return {
    id,
    available: !!entry,
    status: p?.crown ? 'mastered' : p?.done ? 'completed' : 'available',
    title: entry ? tr(entry.title) : null,
    sources: sources.length > 1 ? `${sources[0]}–${sources[sources.length - 1]}` : String(sources[0]),
    merged: sources.length > 1,
  }
}))

// ---------------------------------------------------------------------
// Membaca
// ---------------------------------------------------------------------
const showFurigana = ref(true)
const showTranslation = ref(false)
const openParagraphs = reactive({})

function isTranslated(i) {
  return showTranslation.value || !!openParagraphs[i]
}

function toggleParagraph(i) {
  openParagraphs[i] = !openParagraphs[i]
}

function openLesson(card) {
  if (!card.available)
    return

  activeId.value = card.id
  showTranslation.value = false
  Object.keys(openParagraphs).forEach(k => delete openParagraphs[k])
  stage.value = STAGE_READ
  scrollTop()
}

function backToList() {
  stage.value = STAGE_LIST
  activeId.value = null
  scrollTop()
}

function scrollTop() {
  if (typeof window !== 'undefined')
    window.scrollTo({ top: 0 })
}

watch(level, () => {
  backToList()
})

// ---------------------------------------------------------------------
// Soal
// ---------------------------------------------------------------------
const qIndex = ref(0)
const picked = ref(null) // mc: indeks pilihan; tf: true / false
const answered = ref(false)
const correctCount = ref(0)
const wrongCount = ref(0)

const questions = computed(() => lesson.value?.questions ?? [])
const current = computed(() => questions.value[qIndex.value] ?? null)
const progressPercent = computed(() => (questions.value.length
  ? Math.round((qIndex.value / questions.value.length) * 100)
  : 0))

const isCorrect = computed(() => {
  if (!answered.value || !current.value)
    return false

  return picked.value === current.value.answer
})

function startQuiz() {
  qIndex.value = 0
  picked.value = null
  answered.value = false
  correctCount.value = 0
  wrongCount.value = 0
  stage.value = STAGE_QUIZ
  scrollTop()
}

function choose(value) {
  if (answered.value || !current.value)
    return

  picked.value = value
  answered.value = true

  if (value === current.value.answer)
    correctCount.value++
  else
    wrongCount.value++
}

function nextQuestion() {
  if (qIndex.value + 1 >= questions.value.length) {
    finish()

    return
  }

  qIndex.value++
  picked.value = null
  answered.value = false
}

function finish() {
  stage.value = STAGE_RESULT
  markProgress(keyOf(activeId.value), wrongCount.value === 0)
  scrollTop()
}

function choiceState(i) {
  if (!answered.value)
    return ''

  if (i === current.value.answer)
    return 'correct'

  return i === picked.value ? 'wrong' : 'dim'
}

function tfState(value) {
  if (!answered.value)
    return ''

  if (value === current.value.answer)
    return 'correct'

  return value === picked.value ? 'wrong' : 'dim'
}

const perfect = computed(() => wrongCount.value === 0)
</script>

<template>
  <!-- DAFTAR PELAJARAN -->
  <div v-if="stage === STAGE_LIST">
    <div class="d-flex align-center justify-space-between mb-1 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('membaca.title') }}
      </h4>
      <LevelSwitch
        v-model="level"
        :levels="MEMBACA_LEVELS"
      />
    </div>
    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ t('membaca.subtitle') }}
    </p>

    <div
      v-if="!progressLoaded"
      class="d-flex justify-center pa-10"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
    </div>

    <div
      v-else
      class="membaca-grid"
    >
      <button
        v-for="card in cards"
        :key="card.id"
        type="button"
        class="membaca-card"
        :class="[`membaca-card--${card.status}`, { 'membaca-card--soon': !card.available }]"
        :aria-disabled="!card.available"
        @click="openLesson(card)"
      >
        <VIcon
          v-if="card.status === 'mastered'"
          icon="tabler-crown"
          size="18"
          class="membaca-card__badge membaca-card__badge--crown"
        />
        <VIcon
          v-else-if="card.status === 'completed'"
          icon="tabler-circle-check-filled"
          size="16"
          color="success"
          class="membaca-card__badge"
        />
        <span class="membaca-card__title">
          {{ t('membaca.lesson_label', { n: card.id }) }}
        </span>
        <span
          v-if="card.available"
          class="membaca-card__focus"
        >
          <RubyText :text="card.title" />
        </span>
        <span
          v-else
          class="membaca-card__focus"
        >
          {{ t('membaca.coming_soon') }}
        </span>
        <span
          v-if="card.merged"
          class="membaca-card__merged"
        >
          {{ t('membaca.covers', { range: card.sources }) }}
        </span>
      </button>
    </div>
  </div>

  <!-- BACA -->
  <div
    v-else-if="stage === STAGE_READ && lesson"
    class="membaca-page"
  >
    <div class="d-flex align-center gap-2 mb-4 flex-wrap">
      <VBtn
        icon="tabler-arrow-left"
        variant="text"
        color="secondary"
        :aria-label="t('membaca.back_to_list')"
        @click="backToList"
      />
      <div>
        <div class="text-caption text-medium-emphasis">
          {{ level }} · {{ t('membaca.lesson_label', { n: activeId }) }}
        </div>
        <h5 class="text-h5 mb-0">
          {{ tr(lesson.title) }}
        </h5>
      </div>
    </div>

    <VCard
      variant="tonal"
      color="info"
      class="mb-4"
    >
      <VCardText>
        <div class="text-subtitle-2 mb-1">
          {{ t('membaca.before_reading') }}
        </div>
        <div class="text-body-2">
          {{ tr(lesson.before) }}
        </div>
        <div class="text-caption mt-2 text-medium-emphasis">
          {{ t('membaca.focus_label') }}: <RubyText :text="tr(lesson.focus)" />
        </div>
      </VCardText>
    </VCard>

    <div class="d-flex flex-wrap gap-2 mb-3">
      <VBtn
        size="small"
        :variant="showFurigana ? 'tonal' : 'outlined'"
        color="primary"
        prepend-icon="tabler-language-hiragana"
        @click="showFurigana = !showFurigana"
      >
        {{ t('membaca.furigana') }}
      </VBtn>
      <VBtn
        size="small"
        :variant="showTranslation ? 'tonal' : 'outlined'"
        color="primary"
        prepend-icon="tabler-language"
        @click="showTranslation = !showTranslation"
      >
        {{ t('membaca.translation') }}
      </VBtn>
    </div>

    <VCard class="mb-4">
      <VCardText>
        <h6 class="membaca-passage__title">
          <RubyText
            :text="lesson.passage.title"
            :show-furigana="showFurigana"
          />
        </h6>

        <div
          v-for="(p, i) in lesson.passage.paragraphs"
          :key="i"
          class="membaca-paragraph"
        >
          <button
            type="button"
            class="membaca-paragraph__ja"
            :aria-expanded="isTranslated(i)"
            @click="toggleParagraph(i)"
          >
            <RubyText
              :text="p.ja"
              :show-furigana="showFurigana"
            />
          </button>
          <p
            v-if="isTranslated(i)"
            class="membaca-paragraph__tr"
          >
            {{ tr({ id: p.id, en: p.en }) }}
          </p>
        </div>
        <div class="text-caption text-medium-emphasis mt-2">
          {{ t('membaca.tap_hint') }}
        </div>
      </VCardText>
    </VCard>

    <VCard class="mb-6">
      <VCardText>
        <div class="text-subtitle-1 mb-2">
          {{ t('membaca.vocab_title') }}
        </div>
        <div class="membaca-vocab">
          <div
            v-for="w in lesson.vocab"
            :key="w.ja"
            class="membaca-vocab__item"
          >
            <div class="membaca-vocab__ja">
              <RubyText :text="w.ja === w.reading ? w.ja : `${w.ja}《${w.reading}》`" />
            </div>
            <div class="text-body-2 text-medium-emphasis">
              {{ tr({ id: w.id, en: w.en }) }}
            </div>
          </div>
        </div>
      </VCardText>
    </VCard>

    <VBtn
      color="primary"
      size="large"
      block
      append-icon="tabler-arrow-right"
      @click="startQuiz"
    >
      {{ t('membaca.start_questions') }}
    </VBtn>
  </div>

  <!-- SOAL -->
  <div
    v-else-if="stage === STAGE_QUIZ && lesson && current"
    class="membaca-page"
  >
    <div class="d-flex align-center gap-2 mb-3">
      <VBtn
        icon="tabler-x"
        variant="text"
        color="secondary"
        :aria-label="t('membaca.back_to_list')"
        @click="backToList"
      />
      <VProgressLinear
        :model-value="progressPercent"
        color="primary"
        rounded
        height="10"
      />
    </div>

    <div class="text-caption text-medium-emphasis mb-2">
      {{ t('membaca.question_of', { n: qIndex + 1, total: questions.length }) }}
      · {{ current.type === 'tf' ? t('membaca.type_tf') : t('membaca.type_mc') }}
    </div>

    <VExpansionPanels class="mb-4">
      <VExpansionPanel>
        <VExpansionPanelTitle>
          <VIcon
            icon="tabler-book-2"
            class="me-2"
          />
          {{ t('membaca.show_passage') }}
        </VExpansionPanelTitle>
        <VExpansionPanelText>
          <p
            v-for="(p, i) in lesson.passage.paragraphs"
            :key="i"
            class="membaca-paragraph__ja membaca-paragraph__ja--static"
          >
            <RubyText :text="p.ja" />
          </p>
        </VExpansionPanelText>
      </VExpansionPanel>
    </VExpansionPanels>

    <VCard class="mb-4">
      <VCardText class="membaca-question">
        <RubyText :text="current.q" />
      </VCardText>
    </VCard>

    <div
      v-if="current.type === 'mc'"
      class="membaca-choices"
    >
      <button
        v-for="(c, i) in current.choices"
        :key="i"
        type="button"
        class="membaca-choice"
        :class="`membaca-choice--${choiceState(i)}`"
        :disabled="answered"
        @click="choose(i)"
      >
        <RubyText :text="c" />
      </button>
    </div>

    <div
      v-else
      class="membaca-tf"
    >
      <button
        type="button"
        class="membaca-choice membaca-choice--tf"
        :class="`membaca-choice--${tfState(true)}`"
        :disabled="answered"
        :aria-label="t('membaca.true_label')"
        @click="choose(true)"
      >
        ○
      </button>
      <button
        type="button"
        class="membaca-choice membaca-choice--tf"
        :class="`membaca-choice--${tfState(false)}`"
        :disabled="answered"
        :aria-label="t('membaca.false_label')"
        @click="choose(false)"
      >
        ×
      </button>
    </div>

    <VAlert
      v-if="answered"
      :type="isCorrect ? 'success' : 'error'"
      variant="tonal"
      class="mt-4"
    >
      <div class="font-weight-medium">
        {{ isCorrect ? t('membaca.correct') : t('membaca.incorrect') }}
      </div>
      <div class="text-body-2 mt-1">
        {{ tr(current.explain) }}
      </div>
    </VAlert>

    <VBtn
      v-if="answered"
      color="primary"
      size="large"
      block
      class="mt-4"
      @click="nextQuestion"
    >
      {{ qIndex + 1 >= questions.length ? t('membaca.finish') : t('membaca.next') }}
    </VBtn>
  </div>

  <!-- HASIL -->
  <div
    v-else-if="stage === STAGE_RESULT && lesson"
    class="membaca-page text-center"
  >
    <VIcon
      :icon="perfect ? 'tabler-crown' : 'tabler-circle-check-filled'"
      :color="perfect ? 'warning' : 'success'"
      size="72"
      class="mb-3"
    />
    <h4 class="text-h4 mb-1">
      {{ t('membaca.done_title') }}
    </h4>
    <p class="text-h6 mb-1">
      {{ t('membaca.score', { score: correctCount, total: questions.length }) }}
    </p>
    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ perfect ? t('membaca.crown_earned') : t('membaca.crown_hint') }}
    </p>

    <VCard
      v-if="lesson.challenge"
      variant="tonal"
      color="primary"
      class="text-start mb-6"
    >
      <VCardText>
        <div class="text-subtitle-2 mb-1">
          {{ t('membaca.challenge') }}
        </div>
        <div class="text-body-2">
          {{ tr(lesson.challenge) }}
        </div>
      </VCardText>
    </VCard>

    <div class="d-flex flex-column gap-2">
      <VBtn
        color="primary"
        size="large"
        @click="startQuiz"
      >
        {{ t('membaca.retry') }}
      </VBtn>
      <VBtn
        variant="outlined"
        @click="stage = STAGE_READ"
      >
        {{ t('membaca.read_again') }}
      </VBtn>
      <VBtn
        variant="text"
        @click="backToList"
      >
        {{ t('membaca.back_to_list') }}
      </VBtn>
    </div>
  </div>
</template>

<style scoped>
.membaca-page {
  margin-inline: auto;
  max-inline-size: 760px;
}

.membaca-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
}

.membaca-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 14px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 14px;
  background: rgb(var(--v-theme-surface));
  cursor: pointer;
  gap: 4px;
  min-block-size: 112px;
  text-align: start;
  transition: border-color 0.15s ease, transform 0.15s ease;
}

.membaca-card:hover:not(.membaca-card--soon) {
  border-color: rgb(var(--v-theme-primary));
  transform: translateY(-2px);
}

.membaca-card--mastered {
  border-color: rgba(var(--v-theme-warning), 0.6);
}

.membaca-card--completed {
  border-color: rgba(var(--v-theme-success), 0.5);
}

.membaca-card--soon {
  cursor: not-allowed;
  opacity: 0.55;
}

.membaca-card__badge {
  position: absolute;
  inset-block-start: 10px;
  inset-inline-end: 10px;
}

.membaca-card__badge--crown {
  color: rgb(var(--v-theme-warning));
}

.membaca-card__title {
  font-size: 0.95rem;
  font-weight: 600;
}

.membaca-card__focus {
  color: rgba(var(--v-theme-on-surface), 0.68);
  font-size: 0.8rem;
  line-height: 1.7;
}

.membaca-card__merged {
  margin-block-start: auto;
  color: rgba(var(--v-theme-on-surface), 0.5);
  font-size: 0.7rem;
}

.membaca-passage__title {
  margin-block-end: 12px;
  font-size: 1.25rem;
  font-weight: 600;
  line-height: 2;
}

.membaca-paragraph {
  margin-block-end: 8px;
}

.membaca-paragraph__ja {
  display: block;
  padding: 6px 8px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font-size: 1.1rem;
  inline-size: 100%;
  line-height: 2.4;
  text-align: start;
}

.membaca-paragraph__ja:hover {
  background: rgba(var(--v-theme-primary), 0.06);
}

.membaca-paragraph__ja--static {
  cursor: default;
  font-size: 1rem;
  margin-block: 0 4px;
}

.membaca-paragraph__ja--static:hover {
  background: transparent;
}

.membaca-paragraph__tr {
  margin: 0;
  padding: 4px 8px 8px;
  color: rgba(var(--v-theme-on-surface), 0.72);
  font-size: 0.875rem;
}

.membaca-vocab {
  display: grid;
  gap: 10px 16px;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
}

.membaca-vocab__ja {
  font-size: 1.05rem;
  line-height: 2;
}

.membaca-question {
  font-size: 1.15rem;
  line-height: 2.4;
}

.membaca-choices {
  display: grid;
  gap: 10px;
}

.membaca-tf {
  display: grid;
  gap: 12px;
  grid-template-columns: 1fr 1fr;
}

.membaca-choice {
  padding: 14px 16px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.14);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  color: inherit;
  cursor: pointer;
  font-size: 1.05rem;
  line-height: 2;
  text-align: start;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.membaca-choice--tf {
  font-size: 2rem;
  line-height: 1.4;
  text-align: center;
}

.membaca-choice:hover:not(:disabled) {
  border-color: rgb(var(--v-theme-primary));
}

.membaca-choice:disabled {
  cursor: default;
}

.membaca-choice:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

.membaca-choice--correct {
  border-color: rgb(var(--v-theme-success));
  background: rgba(var(--v-theme-success), 0.12);
}

.membaca-choice--wrong {
  border-color: rgb(var(--v-theme-error));
  background: rgba(var(--v-theme-error), 0.1);
}

.membaca-choice--dim {
  opacity: 0.5;
}
</style>
