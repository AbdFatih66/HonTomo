<script setup>
// Kaite Oboeru (書いて覚える) — "write it to remember it" practice, based on
// the Minna no Nihongo I Sentence Pattern Workbook (Kaite Oboeru). Content
// below is transcribed from the workbook's own pages (word banks / picture
// prompts), lesson by lesson — see the comment above each LESSONS entry for
// which page it comes from. Only a first couple of lessons are included so
// far; add more objects to LESSONS, following the same shape, to extend it
// to the rest of the book.
//
// Grading is automatic and stroke-based, exactly like the Kana/Kanji writing
// quiz: the user writes each kana character of the answer by hand and
// HanziWriter checks every stroke's shape/direction in real time (see
// KanaWritingCanvas.vue, which already powers Kana practice). A multi-kana
// answer (e.g. おはようございます) is written one character at a time; the
// question is graded once every character in the word is done. There is no
// self-report step — same as Kana/Kanji, not like Mondaishuu's tap-to-check.
import KaiteOboekuWritingCanvas from '@/components/learning/KaiteOboekuWritingCanvas.vue'
import RubyText from '@/components/learning/RubyText.vue'
import kanaStrokes from '@/data/kana-strokes.json'
import { useAutoNext } from '@/composables/useAutoNext'

const { t } = useI18n()
const autoNext = useAutoNext()

// Greedy-match against the known kana glyphs (see kana-strokes.json), trying
// two-character yoon combos (しゃ, きゅ, …) first since those are stored and
// written as one glyph, then falling back to single characters.
function segmentAnswer(answer) {
  const units = []
  let i = 0
  while (i < answer.length) {
    const two = answer.slice(i, i + 2)
    if (kanaStrokes[two]) {
      units.push(two)
      i += 2
      continue
    }
    const one = answer[i]
    if (kanaStrokes[one]) {
      units.push(one)
      i += 1
      continue
    }
    // No stroke data for this glyph (shouldn't happen with the curated
    // word list below) — still show it, but it'll need `skipWriting()`.
    units.push(one)
    i += 1
  }

  return units
}

// ---------------------------------------------------------------------
// Bank soal. Setiap soal: { type: 'write', prompt, cue, answer }
//  - prompt: instruksi/konteks soal (apa yang digambarkan/diminta)
//  - cue:    kata kunci dalam bahasa Indonesia (dipakai sebagai pengganti
//            gambar pada halaman asli)
//  - answer: jawaban dalam hiragana — ditulis tangan huruf demi huruf,
//            dinilai otomatis lewat pengecekan goresan (sama seperti Kana)
// ---------------------------------------------------------------------
const LESSONS = [
  // p.1 "あいさつ" (greetings) — the workbook's very first page: 7 little
  // comic panels (sunrise, midday sun, night sky, waving, bedtime, knocking
  // on a door, family at the table) each with an empty speech bubble.
  {
    id: 1,
    title: 'あいさつ',
    subtitle: 'Salam sehari-hari (hal. 1)',
    questions: [
      { type: 'write', prompt: 'Gambar: matahari mulai terbit, dua orang berpapasan pagi-pagi.', cue: 'Selamat pagi', answer: 'おはようございます' },
      { type: 'write', prompt: 'Gambar: matahari di atas kepala, siang hari.', cue: 'Selamat siang', answer: 'こんにちは' },
      { type: 'write', prompt: 'Gambar: langit malam berbintang dan berbulan.', cue: 'Selamat malam (saat bertemu)', answer: 'こんばんは' },
      { type: 'write', prompt: 'Gambar: dua orang melambaikan tangan berpisah di jalan.', cue: 'Selamat tinggal / sampai jumpa', answer: 'さようなら' },
      { type: 'write', prompt: 'Gambar: seseorang hendak tidur di kasur.', cue: 'Selamat tidur', answer: 'おやすみなさい' },
      { type: 'write', prompt: 'Gambar: mengetuk pintu sambil membawa buku, hendak masuk ruangan.', cue: 'Permisi (masuk/lewat)', answer: 'しつれいします' },
      { type: 'write', prompt: 'Gambar: keluarga duduk di meja makan, makanan baru disajikan.', cue: 'Sebelum makan', answer: 'いただきます' },
    ],
  },
  // p.2 "職業名" (occupation names) — word bank at the top of section I.
  {
    id: 2,
    title: '職業名',
    subtitle: 'Nama profesi (hal. 2)',
    questions: [
      { type: 'write', prompt: 'Tuliskan kata bahasa Jepang untuk profesi ini.', cue: 'karyawan perusahaan', answer: 'かいしゃいん' },
      { type: 'write', prompt: 'Tuliskan kata bahasa Jepang untuk profesi ini.', cue: 'guru', answer: 'せんせい' },
      { type: 'write', prompt: 'Tuliskan kata bahasa Jepang untuk profesi ini.', cue: 'murid / mahasiswa', answer: 'がくせい' },
      { type: 'write', prompt: 'Tuliskan kata bahasa Jepang untuk profesi ini.', cue: 'dokter', answer: 'いしゃ' },
      { type: 'write', prompt: 'Tuliskan kata bahasa Jepang untuk profesi ini.', cue: 'pegawai bank', answer: 'ぎんこういん' },
    ],
  },
]

const activeLesson = ref(null)

const STAGE_SETUP = 'setup'
const STAGE_QUIZ = 'quiz'
const STAGE_RESULT = 'result'

const stage = ref(STAGE_SETUP)

const qIndex = ref(0)
const score = ref(0)
const answered = ref(false)
const isCorrect = ref(false)

const shuffledQuestions = ref([])
const currentQuestion = computed(() => shuffledQuestions.value?.[qIndex.value] ?? null)
const totalQuestions = computed(() => shuffledQuestions.value?.length ?? 0)

// Per-character progress through the current word. `revealed` only ever
// grows with characters the learner has actually finished writing
// correctly — nothing about the answer is shown ahead of time.
const units = ref([])
const unitIndex = ref(0)
const revealed = ref([])
const canvasRef = ref(null)

const progressPercent = computed(() => {
  if (!totalQuestions.value)
    return 0

  return Math.round((qIndex.value / totalQuestions.value) * 100)
})

const feedback = computed(() => {
  if (!answered.value)
    return null

  return { correct: isCorrect.value }
})

function shuffle(arr) {
  const a = [...arr]
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[a[i], a[j]] = [a[j], a[i]]
  }
  return a
}

function setupQuestion() {
  answered.value = false
  isCorrect.value = false
  unitIndex.value = 0
  revealed.value = []
  units.value = segmentAnswer(currentQuestion.value?.answer ?? '')
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
  if (!autoNext.value)
    return
  clearAdvanceTimer()
  advanceTimer = setTimeout(nextQuestion, correct ? ADVANCE_DELAY_CORRECT : ADVANCE_DELAY_INCORRECT)
}

watch(autoNext, on => {
  if (!on)
    clearAdvanceTimer()
})

function startLesson(lesson) {
  clearAdvanceTimer()
  activeLesson.value = lesson
  qIndex.value = 0
  score.value = 0
  shuffledQuestions.value = shuffle(lesson.questions)
  setupQuestion()
  stage.value = STAGE_QUIZ
}

// Fires once per character, and only once it has actually been written
// correctly (KaiteOboekuWritingCanvas never force-passes a wrong stroke).
// So reaching the last character always means the whole word was written
// correctly — the question is simply graded "correct" at that point.
function onCharComplete() {
  if (answered.value)
    return

  revealed.value = [...revealed.value, units.value[unitIndex.value]]

  if (unitIndex.value + 1 < units.value.length) {
    unitIndex.value += 1

    return
  }

  answered.value = true
  isCorrect.value = true
  score.value++
  scheduleAdvance(true)
}

// Escape hatch for a word the learner cannot (or does not want to) finish.
function skipWriting() {
  if (answered.value)
    return
  answered.value = true
  isCorrect.value = false
  scheduleAdvance(false)
}

function nextQuestion() {
  clearAdvanceTimer()
  if (qIndex.value + 1 >= totalQuestions.value) {
    stage.value = STAGE_RESULT

    return
  }
  qIndex.value++
  setupQuestion()
}

function restartQuiz() {
  clearAdvanceTimer()
  qIndex.value = 0
  score.value = 0
  shuffledQuestions.value = shuffle(activeLesson.value?.questions ?? [])
  setupQuestion()
  stage.value = STAGE_QUIZ
}

function backToSetup() {
  clearAdvanceTimer()
  stage.value = STAGE_SETUP
}

onBeforeUnmount(clearAdvanceTimer)
</script>

<template>
  <!-- SETUP: lesson picker, normal layout -->
  <div v-if="stage === STAGE_SETUP">
    <div class="d-flex align-center justify-space-between mb-1 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('kaite_oboeru.title') }}
      </h4>
    </div>
    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ t('kaite_oboeru.subtitle') }}
    </p>

    <div class="ko-grid">
      <button
        v-for="lesson in LESSONS"
        :key="lesson.id"
        type="button"
        class="ko-card"
        @click="startLesson(lesson)"
      >
        <span class="ko-card__num">{{ lesson.id }}</span>
        <span class="ko-card__title"><RubyText :text="lesson.title" /></span>
        <span class="ko-card__subtitle">{{ lesson.subtitle }}</span>
        <span class="ko-card__count">{{ t('kaite_oboeru.question_count', { n: lesson.questions.length }) }}</span>
      </button>
    </div>
  </div>

  <!-- QUIZ / RESULT: distraction-free full-screen shell, same as Kana/Kanji/Mondaishuu -->
  <div v-else class="kana-fs kana-fs--page">
    <header class="kana-fs__header">
      <VBtn
        icon="tabler-x"
        variant="text"
        color="secondary"
        :aria-label="t('kaite_oboeru.quiz_back')"
        @click="backToSetup"
      />

      <div
        v-if="stage === STAGE_QUIZ"
        class="lp__bar"
        role="progressbar"
        :aria-valuenow="progressPercent"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <div class="lp__fill" :style="{ inlineSize: `${progressPercent}%` }" />
      </div>
      <h5 v-else class="text-h6 mb-0">
        {{ t('kaite_oboeru.title') }}
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
        <!-- QUIZ -->
        <div
          v-if="stage === STAGE_QUIZ && currentQuestion"
          class="mx-auto"
          style="max-inline-size: 560px;"
        >
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-caption text-medium-emphasis">
              <RubyText :text="activeLesson.title" />
            </div>
            <VChip size="small" variant="tonal" color="primary">
              {{ t('kaite_oboeru.score', { score, total: totalQuestions }) }}
            </VChip>
          </div>

          <div class="text-caption text-medium-emphasis mb-4">
            {{ t('kaite_oboeru.question_of', { n: qIndex + 1, total: totalQuestions }) }}
          </div>

          <VCard class="pa-6 text-center">
            <p class="text-body-2 text-medium-emphasis mb-1">
              {{ currentQuestion.prompt }}
            </p>
            <p class="text-h6 mb-4">
              {{ currentQuestion.cue }}
            </p>

            <!-- Only characters already written correctly are shown — the
                 rest of the word is just dots, so nothing here hints at the
                 answer ahead of time. -->
            <div class="ko-word mb-4">
              <span
                v-for="(u, ui) in units"
                :key="ui"
                class="ko-word__char"
                :class="{ 'ko-word__char--active': ui === unitIndex && !answered }"
              >
                <template v-if="ui < revealed.length">{{ revealed[ui] }}</template>
                <template v-else>・</template>
              </span>
            </div>

            <div class="d-flex justify-center">
              <KaiteOboekuWritingCanvas
                :key="`${qIndex}-${unitIndex}`"
                ref="canvasRef"
                :character="units[unitIndex]"
                :size="200"
                @complete="onCharComplete"
              />
            </div>

            <VBtn
              v-if="!answered"
              variant="text"
              size="small"
              class="mt-2"
              @click="skipWriting"
            >
              {{ t('kaite_oboeru.skip') }}
            </VBtn>
          </VCard>
        </div>

        <!-- RESULT -->
        <VCard
          v-else-if="stage === STAGE_RESULT"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <div class="text-h3 mb-2">
            ✍️
          </div>
          <h5 class="text-h5 mb-1">
            {{ t('kaite_oboeru.done_title') }}
          </h5>
          <p class="text-h6 mb-6">
            {{ t('kaite_oboeru.score', { score, total: totalQuestions }) }}
          </p>
          <div class="d-flex flex-wrap gap-2 justify-center">
            <VBtn variant="tonal" @click="backToSetup">
              {{ t('kaite_oboeru.quiz_back') }}
            </VBtn>
            <VBtn variant="tonal" prepend-icon="tabler-refresh" @click="restartQuiz">
              {{ t('kaite_oboeru.retry') }}
            </VBtn>
          </div>
        </VCard>
      </div>
    </main>

    <footer v-if="feedback" class="kana-fs__footer" :class="feedback.correct ? 'is-correct' : 'is-incorrect'">
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
            {{ feedback.correct ? t('kaite_oboeru.correct') : t('kaite_oboeru.incorrect') }}
          </div>
        </div>

        <VBtn v-if="!autoNext" color="primary" append-icon="tabler-arrow-right" @click="nextQuestion">
          {{ qIndex + 1 >= totalQuestions ? t('kaite_oboeru.finish') : t('kaite_oboeru.next') }}
        </VBtn>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.ko-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
}

.ko-card {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  gap: 4px;
  padding: 16px;
  text-align: start;
  transition: border-color 0.15s ease, transform 0.15s ease;
}

.ko-card:hover {
  border-color: rgb(var(--v-theme-primary));
  transform: translateY(-2px);
}

.ko-card__num {
  border-radius: 999px;
  background: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary));
  font-size: 12px;
  font-weight: 600;
  padding-block: 2px;
  padding-inline: 10px;
}

.ko-card__title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-block-start: 4px;
}

.ko-card__subtitle {
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.8rem;
}

.ko-card__count {
  color: rgba(var(--v-theme-on-surface), 0.5);
  font-size: 0.75rem;
  margin-block-start: 6px;
}

.ko-word {
  display: flex;
  gap: 4px;
  justify-content: center;
}

.ko-word__char {
  border-radius: 6px;
  color: rgba(var(--v-theme-on-surface), 0.35);
  font-size: 1.1rem;
  padding-block: 2px;
  padding-inline: 4px;
}

.ko-word__char--active {
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
  font-weight: 700;
}

.ko-word__char--done {
  color: rgba(var(--v-theme-on-surface), 0.7);
}
</style>
