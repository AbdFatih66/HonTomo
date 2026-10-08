<script setup>
// Chokai Mondai — latihan menyimak (聴解) per pelajaran. Sama seperti
// Mondaishuu: kartu pemilihan pelajaran ala Kanji, lalu kuis full-layar
// dengan bar verdict di bawah (bukan popup). Bedanya, tiap soal di sini
// punya audio yang harus didengarkan dulu, bukan teks kalimat.
//
// PENTING soal isi soal: audio yang diunggah baru untuk Pelajaran 1
// (Kaiwa + Mondai 1/2/3, dari CD Minna no Nihongo I). Karena tidak bisa
// "mendengarkan" filenya sendiri, pertanyaan/pilihan/kunci jawaban untuk
// ketiga mondai itu masih PLACEHOLDER (ditandai jelas di choices/translate
// masing-masing) — tinggal diisi begitu transkrip/kunci jawabannya ada.
import RubyText from '@/components/learning/RubyText.vue'
import { useAutoNext } from '@/composables/useAutoNext'
import { useAuthStore } from '@/stores/auth'
import { $api } from '@/utils/api'

const { t, locale } = useI18n()
const autoNext = useAutoNext()
const authStore = useAuthStore()

// Some fields are plain Indonesian strings and some are { id, en } — same
// convention as Mondaishuu, so this can be filled in gradually.
function tr(value) {
  if (value == null)
    return value

  return typeof value === 'object' ? (locale.value === 'en' ? value.en : value.id) : value
}

// ---------------------------------------------------------------------
// Data — skrip tiap pelajaran ada di public/data/chokai/lesson-{n}.json
// (sumber tunggal, dipakai bareng oleh `php artisan chokai:generate-audio`
// untuk membuat audio lewat Google TTS, dan oleh halaman ini untuk
// menampilkan soal). Audio dibuat dari skrip ORIGINAL kita sendiri, bukan
// rekaman buku/CD siapa pun. Pelajaran yang JSON-nya belum ada = "segera hadir".
// ---------------------------------------------------------------------
const AVAILABLE_LESSON_IDS = Array.from({ length: 25 }, (_, i) => i + 1) // 1-25 semua sudah ada; tambah id di sini kalau ada pelajaran baru
const LESSONS = Array.from({ length: 25 }, (_, i) => ({ id: i + 1 }))
const loadedLessons = reactive({})
const loadingLesson = ref(false)
const loadError = ref(false)

async function fetchLesson(id) {
  if (loadedLessons[id])
    return loadedLessons[id]

  const res = await fetch(`/data/chokai/lesson-${id}.json`, { cache: 'no-cache' })
  if (!res.ok)
    throw new Error(`lesson ${id}: HTTP ${res.status}`)

  const json = await res.json()

  loadedLessons[id] = json

  return json
}

// ---------------------------------------------------------------------
// Progres per pelajaran — server-backed lewat GET/POST
// /api/chokai/progress, pola sama persis dengan Mondaishuu.
// ---------------------------------------------------------------------
const progress = reactive({})
const progressLoaded = ref(false)

async function loadProgress() {
  try {
    const res = await $api('/chokai/progress')

    Object.assign(progress, res.progress ?? {})
  }
  catch {
    // Gagal (mis. offline) -> lanjut dengan progress kosong.
  }
  finally {
    progressLoaded.value = true
  }
}

loadProgress()

function markProgress(key, perfect) {
  const prev = progress[key]

  progress[key] = { done: true, crown: perfect || !!prev?.crown }

  $api(`/chokai/progress/${encodeURIComponent(key)}`, {
    method: 'POST',
    body: { perfect: !!perfect },
  }).catch(() => {
    // Best-effort — gagal kirim tidak boleh mengganggu layar hasil.
  })
}

// Semua pelajaran terbuka untuk semua akun — tidak ada penguncian.
function isLocked() {
  return false
}

// Pemberitahuan saat kartu terkunci diketuk — snackbar supaya tetap
// terlihat walau kartunya ada jauh di bawah.
const lockToast = reactive({ show: false, text: '' })

function openLesson(lesson) {
  if (!lesson.ready)
    return

  if (lesson.status === 'locked') {
    lockToast.text = t('chokai.locked_hint', { n: lesson.n - 1, next: lesson.n })
    lockToast.show = true

    return
  }

  startTab(lesson.id)
}

function statusFor(key) {
  if (isLocked(key))
    return 'locked'

  const p = progress[key]
  if (p?.crown)
    return 'mastered'
  if (p?.done)
    return 'completed'

  return 'available'
}

const pathLessons = computed(() => LESSONS.map(l => ({
  id: `l${l.id}`,
  n: l.id,
  ready: AVAILABLE_LESSON_IDS.includes(l.id),
  status: statusFor(`l${l.id}`),
})))

const activeKey = ref(null)
const active = computed(() => {
  const m = /^l(\d+)$/.exec(activeKey.value ?? '')

  return m ? loadedLessons[Number(m[1])] ?? null : null
})

// Pelajaran berikutnya — untuk tombol "lanjut ke Pelajaran N+1" di layar
// hasil, sama seperti Mondaishuu. `null` kalau pelajaran ini yang
// terakhir atau pelajaran berikutnya belum tersedia.
const nextLessonId = computed(() => {
  const m = /^l(\d+)$/.exec(activeKey.value ?? '')
  if (!m)
    return null

  const n = Number(m[1]) + 1

  return AVAILABLE_LESSON_IDS.includes(n) ? n : null
})

// ---------------------------------------------------------------------
// Stage machine — sama seperti Mondaishuu (setup -> quiz -> result),
// plus `hearts_out`. `showingIntro` menahan alur di slide Kaiwa (kalau
// ada) sebelum soal pertama tampil — tidak dinilai, tidak masuk hitungan
// progress bar / hearts, cuma pemanasan.
// ---------------------------------------------------------------------
const STAGE_SETUP = 'setup'
const STAGE_QUIZ = 'quiz'
const STAGE_RESULT = 'result'
const STAGE_HEARTS_OUT = 'hearts_out'

const stage = ref(STAGE_SETUP)
const showingIntro = ref(false)

// ---------------------------------------------------------------------
// Kaiwa comprehension check — 1–2 soal RINGAN berdasarkan percakapan
// pembuka, tampil setelah kaiwa diputar. Tidak dinilai (tidak masuk
// hearts/score/progress) — cuma supaya kaiwa "dipakai", bukan sekadar
// audio yang lewat. Kalau kaiwa tidak punya `questions`, tombol
// "Mulai Mondai" langsung tampil seperti sebelumnya.
// ---------------------------------------------------------------------
const kaiwaQIndex = ref(0)
const kaiwaAnswered = ref(false)
const kaiwaSelected = ref(null)
const kaiwaShowTranscript = ref(false)

const kaiwaQuestions = computed(() => active.value?.kaiwa?.questions ?? [])
const kaiwaCurrentQuestion = computed(() => kaiwaQuestions.value[kaiwaQIndex.value] ?? null)
const kaiwaDone = computed(() => kaiwaQIndex.value >= kaiwaQuestions.value.length)

function resetKaiwaCheck() {
  kaiwaQIndex.value = 0
  kaiwaAnswered.value = false
  kaiwaSelected.value = null
  kaiwaShowTranscript.value = false
}

function chooseKaiwaAnswer(i) {
  if (kaiwaAnswered.value)
    return
  kaiwaSelected.value = i
  kaiwaAnswered.value = true
}

function nextKaiwaQuestion() {
  kaiwaQIndex.value++
  kaiwaAnswered.value = false
  kaiwaSelected.value = null
}

const STARTING_HEARTS = 5
const hearts = ref(STARTING_HEARTS)
const wrongCount = ref(0)

const qIndex = ref(0)
const score = ref(0)
const answered = ref(false)
const selectedChoice = ref(null)

// Soal diacak setiap pelajaran dibuka / diulang (sama seperti Mondaishuu).
// Pilihan jawaban juga diacak, kecuali soal ○/× (2 pilihan) supaya urutan
// ○ lalu × tetap.
const shuffledQuestions = ref([])
const currentQuestion = computed(() => shuffledQuestions.value?.[qIndex.value] ?? null)
const totalQuestions = computed(() => shuffledQuestions.value?.length ?? 0)

function shuffle(arr) {
  const a = [...arr]
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[a[i], a[j]] = [a[j], a[i]]
  }

  return a
}

function shuffleChoices(q) {
  if (!q?.choices || q.choices.length <= 2)
    return q

  const order = shuffle(q.choices.map((_, i) => i))

  return { ...q, choices: order.map(i => q.choices[i]), answer: order.indexOf(q.answer) }
}

function buildQuestionSet() {
  shuffledQuestions.value = shuffle(active.value?.questions ?? []).map(shuffleChoices)
}

const progressPercent = computed(() => {
  if (!totalQuestions.value)
    return 0

  return Math.round((qIndex.value / totalQuestions.value) * 100)
})

const feedback = computed(() => {
  if (!answered.value || !currentQuestion.value)
    return null

  const q = currentQuestion.value

  return {
    correct: selectedChoice.value === q.answer,
    correctAnswer: q.choices[q.answer],
    translate: tr(q.translate),
  }
})

// ---------------------------------------------------------------------
// Audio playback — satu elemen <audio> dipakai bergantian (cuma satu
// soal tampil sekaligus), lewat template ref `audioEl`.
// ---------------------------------------------------------------------
const audioEl = ref(null)
const isPlaying = ref(false)
const hasPlayedOnce = ref(false)

function playAudio(src) {
  const el = audioEl.value
  if (!el || !src)
    return

  if (el.src !== window.location.origin + src && !el.src.endsWith(src))
    el.src = src

  el.currentTime = 0
  el.play()
  isPlaying.value = true
  hasPlayedOnce.value = true
}

function toggleAudio(src) {
  const el = audioEl.value
  if (!el || !src)
    return

  if (isPlaying.value) {
    el.pause()
    isPlaying.value = false

    return
  }

  playAudio(src)
}

function onAudioEnded() {
  isPlaying.value = false
}

// Every question resolves the same way as Mondaishuu: answer -> brief
// verdict -> automatic advance kalau auto-next aktif.
const ADVANCE_DELAY_CORRECT = 900
const ADVANCE_DELAY_INCORRECT = 1600

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

function stopAudioPlayback() {
  const el = audioEl.value
  if (el) {
    el.pause()
    el.currentTime = 0
  }
  isPlaying.value = false
}

function setupQuestion() {
  answered.value = false
  selectedChoice.value = null
  hasPlayedOnce.value = false
  stopAudioPlayback()
}

async function startTab(key) {
  clearAdvanceTimer()
  loadError.value = false
  loadingLesson.value = true

  try {
    await fetchLesson(Number(key.slice(1)))
  }
  catch {
    loadError.value = true

    return
  }
  finally {
    loadingLesson.value = false
  }

  activeKey.value = key
  qIndex.value = 0
  score.value = 0
  hearts.value = STARTING_HEARTS
  wrongCount.value = 0
  buildQuestionSet()
  showingIntro.value = !!active.value?.kaiwa?.audio
  resetKaiwaCheck()
  setupQuestion()
  stage.value = STAGE_QUIZ
}

function registerWrongAnswer() {
  wrongCount.value++
  hearts.value = Math.max(0, hearts.value - 1)
}

function chooseAnswer(i) {
  if (answered.value)
    return
  selectedChoice.value = i
  answered.value = true

  const correct = i === currentQuestion.value.answer
  if (correct)
    score.value++
  else
    registerWrongAnswer()
  scheduleAdvance(correct)
}

function beginQuestions() {
  clearAdvanceTimer()
  stopAudioPlayback()
  showingIntro.value = false
  setupQuestion()
}

function nextQuestion() {
  clearAdvanceTimer()

  if (hearts.value <= 0) {
    stage.value = STAGE_HEARTS_OUT

    return
  }

  if (qIndex.value + 1 >= totalQuestions.value) {
    markProgress(activeKey.value, wrongCount.value === 0)
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
  hearts.value = STARTING_HEARTS
  wrongCount.value = 0
  buildQuestionSet()
  showingIntro.value = !!active.value?.kaiwa?.audio
  resetKaiwaCheck()
  setupQuestion()
  stage.value = STAGE_QUIZ
}

// Dari layar hasil langsung ke kuis pelajaran berikutnya — tidak perlu
// balik ke kartu pemilihan pelajaran dulu.
function goToNextLesson() {
  if (nextLessonId.value)
    startTab(`l${nextLessonId.value}`)
}

function backToSetup() {
  clearAdvanceTimer()
  stopAudioPlayback()
  stage.value = STAGE_SETUP
}

onBeforeUnmount(clearAdvanceTimer)
</script>

<template>
  <audio
    ref="audioEl"
    @ended="onAudioEnded"
  />

  <!-- SETUP: kartu pemilihan pelajaran, gaya sama dengan pemilihan Kanji. -->
  <div v-if="stage === STAGE_SETUP">
    <div class="d-flex align-center justify-space-between mb-1 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('chokai.title') }}
      </h4>
    </div>
    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ t('chokai.subtitle') }}
    </p>

    <div
      v-if="!progressLoaded"
      class="d-flex justify-center pa-10"
    >
      <VProgressCircular indeterminate color="primary" />
    </div>

    <VAlert
      v-else-if="loadError"
      type="error"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      {{ t('chokai.load_error') }}
    </VAlert>

    <div v-if="progressLoaded" class="chokai-grid" :class="{ 'chokai-grid--busy': loadingLesson }">
      <button
        v-for="lesson in pathLessons"
        :key="lesson.id"
        type="button"
        class="chokai-card"
        :class="[`chokai-card--${lesson.status}`, { 'chokai-card--disabled': !lesson.ready }]"
        :disabled="!lesson.ready"
        :aria-disabled="lesson.status === 'locked'"
        @click="openLesson(lesson)"
      >
        <VIcon
          v-if="lesson.status === 'locked'"
          icon="tabler-lock"
          size="16"
          class="chokai-card__badge chokai-card__badge--lock"
        />
        <VIcon
          v-else-if="lesson.status === 'mastered'"
          icon="tabler-crown"
          size="18"
          class="chokai-card__badge chokai-card__badge--crown"
        />
        <VIcon
          v-else-if="lesson.status === 'completed'"
          icon="tabler-circle-check-filled"
          size="16"
          color="success"
          class="chokai-card__badge"
        />
        <span class="chokai-card__num">{{ lesson.n }}</span>
        <span class="chokai-card__focus">
          {{ lesson.ready ? t('chokai.lesson_label', { n: lesson.n }) : t('chokai.coming_soon') }}
        </span>
      </button>
    </div>

    <VSnackbar
      v-model="lockToast.show"
      color="warning"
      location="bottom"
      :timeout="4000"
    >
      <VIcon
        icon="tabler-lock"
        class="me-2"
      />
      {{ lockToast.text }}
    </VSnackbar>
  </div>

  <!-- QUIZ / RESULT: shell full-layar sama dengan Mondaishuu/Kana/Kanji. -->
  <div v-else class="kana-fs kana-fs--page">
    <header class="kana-fs__header">
      <VBtn
        icon="tabler-x"
        variant="text"
        color="secondary"
        :aria-label="t('chokai.quiz_back')"
        @click="backToSetup"
      />

      <div
        v-if="stage === STAGE_QUIZ && !showingIntro"
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
        {{ t('chokai.title') }}
      </h5>

      <div
        v-if="stage === STAGE_QUIZ && !showingIntro"
        class="lp__hearts"
        :aria-label="t('chokai.hearts')"
      >
        <VIcon icon="tabler-heart-filled" />
        {{ hearts }}
      </div>

      <VSwitch
        v-if="stage === STAGE_QUIZ && !showingIntro"
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
        <!-- INTRO: percakapan pembuka, tidak dinilai. -->
        <VCard
          v-if="stage === STAGE_QUIZ && showingIntro"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <VIcon icon="tabler-message-circle-2" size="40" color="primary" class="mb-3" />
          <h5 class="text-h5 mb-2">
            {{ t('chokai.kaiwa_title') }}
          </h5>
          <p class="text-body-2 text-medium-emphasis mb-5">
            {{ t('chokai.kaiwa_hint') }}
          </p>

          <VBtn
            :icon="isPlaying ? 'tabler-player-pause-filled' : 'tabler-player-play-filled'"
            size="x-large"
            color="primary"
            class="mb-5"
            @click="toggleAudio(active.kaiwa.audio)"
          />

          <!-- Cek pemahaman kaiwa: 1-2 soal ringan, tidak dinilai. -->
          <div v-if="kaiwaQuestions.length && !kaiwaDone" class="text-start mt-2">
            <div class="text-caption text-medium-emphasis mb-2 text-center">
              {{ t('chokai.kaiwa_question_of', { n: kaiwaQIndex + 1, total: kaiwaQuestions.length }) }}
            </div>

            <p class="text-body-2 mb-3 text-center">
              <RubyText :text="tr(kaiwaCurrentQuestion.prompt)" />
            </p>

            <div class="d-flex flex-column ga-2 mb-3">
              <button
                v-for="(choice, ci) in kaiwaCurrentQuestion.choices"
                :key="ci"
                type="button"
                class="chokai-choice"
                :class="{
                  'chokai-choice--selected': kaiwaSelected === ci && !kaiwaAnswered,
                  'chokai-choice--correct': kaiwaAnswered && ci === kaiwaCurrentQuestion.answer,
                  'chokai-choice--wrong': kaiwaAnswered && kaiwaSelected === ci && ci !== kaiwaCurrentQuestion.answer,
                }"
                :disabled="kaiwaAnswered"
                @click="chooseKaiwaAnswer(ci)"
              >
                <RubyText :text="choice" />
              </button>
            </div>

            <p v-if="kaiwaAnswered" class="text-caption text-medium-emphasis text-center mb-3">
              {{ t('chokai.meaning') }}: {{ tr(kaiwaCurrentQuestion.translate) }}
            </p>

            <div class="d-flex justify-center">
              <VBtn
                v-if="kaiwaAnswered"
                variant="tonal"
                append-icon="tabler-arrow-right"
                @click="nextKaiwaQuestion"
              >
                {{ t('chokai.kaiwa_continue') }}
              </VBtn>
              <VBtn
                v-else
                variant="text"
                size="small"
                @click="nextKaiwaQuestion"
              >
                {{ t('chokai.kaiwa_skip') }}
              </VBtn>
            </div>
          </div>

          <div v-else>
            <VBtn
              v-if="!kaiwaShowTranscript && active.kaiwa.audio_text"
              variant="text"
              size="small"
              class="mb-3"
              @click="kaiwaShowTranscript = true"
            >
              {{ t('chokai.kaiwa_transcript') }}
            </VBtn>

            <VCard
              v-if="kaiwaShowTranscript && active.kaiwa.audio_text"
              variant="tonal"
              class="pa-3 mb-4 text-start"
            >
              <p
                v-for="(turn, ti) in (Array.isArray(active.kaiwa.audio_text) ? active.kaiwa.audio_text : [{ text: active.kaiwa.audio_text }])"
                :key="ti"
                class="text-body-2 mb-1"
              >
                <RubyText :text="turn.text" />
              </p>
            </VCard>

            <VBtn
              variant="tonal"
              append-icon="tabler-arrow-right"
              @click="beginQuestions"
            >
              {{ t('chokai.start_mondai') }}
            </VBtn>
          </div>
        </VCard>

        <!-- QUIZ -->
        <div
          v-else-if="stage === STAGE_QUIZ && currentQuestion"
          class="mx-auto"
          style="max-inline-size: 560px;"
        >
          <div class="d-flex align-center justify-space-between mb-2">
            <div class="text-caption text-medium-emphasis">
              {{ t('chokai.lesson_label', { n: active.id }) }}
            </div>
            <VChip size="small" variant="tonal" color="primary">
              {{ t('chokai.score', { score, total: totalQuestions }) }}
            </VChip>
          </div>

          <div class="text-caption text-medium-emphasis mb-4">
            {{ t('chokai.question_of', { n: qIndex + 1, total: totalQuestions }) }}
          </div>

          <VCard class="pa-6">
            <p class="text-body-1 mb-4 text-center">
              <RubyText :text="tr(currentQuestion.prompt)" />
            </p>

            <VAlert
              v-if="!currentQuestion.audio"
              type="warning"
              variant="tonal"
              density="compact"
              class="mb-4"
            >
              {{ t('chokai.audio_missing') }}
            </VAlert>

            <div class="chokai-player mb-5">
              <VBtn
                :icon="isPlaying ? 'tabler-player-pause-filled' : 'tabler-player-play-filled'"
                size="x-large"
                color="primary"
                :disabled="!currentQuestion.audio"
                @click="toggleAudio(currentQuestion.audio)"
              />
              <span class="text-caption text-medium-emphasis mt-2">
                {{ isPlaying ? t('chokai.playing') : (hasPlayedOnce ? t('chokai.replay') : t('chokai.play')) }}
              </span>
            </div>

            <div class="d-flex flex-column ga-2">
              <button
                v-for="(choice, ci) in currentQuestion.choices"
                :key="ci"
                type="button"
                class="chokai-choice"
                :class="{
                  'chokai-choice--selected': selectedChoice === ci && !answered,
                  'chokai-choice--correct': answered && ci === currentQuestion.answer,
                  'chokai-choice--wrong': answered && selectedChoice === ci && ci !== currentQuestion.answer,
                }"
                :disabled="answered"
                @click="chooseAnswer(ci)"
              >
                <RubyText :text="choice" />
              </button>
            </div>
          </VCard>
        </div>

        <!-- RESULT -->
        <VCard
          v-else-if="stage === STAGE_RESULT"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <div class="text-h3 mb-2">
            {{ wrongCount === 0 ? '👑' : '🎉' }}
          </div>
          <h5 class="text-h5 mb-1">
            {{ t('chokai.done_title') }}
          </h5>
          <p class="text-h6 mb-2">
            {{ t('chokai.score', { score, total: totalQuestions }) }}
          </p>

          <div
            v-if="wrongCount === 0"
            class="chokai-crown-earned mb-6"
          >
            <VIcon icon="tabler-crown" size="22" />
            {{ t('chokai.crown_earned') }}
          </div>
          <p
            v-else
            class="text-body-2 text-medium-emphasis mb-6"
          >
            {{ t('chokai.crown_hint') }}
          </p>

          <div class="d-flex flex-wrap gap-2 justify-center">
            <VBtn
              variant="tonal"
              @click="backToSetup"
            >
              {{ t('chokai.quiz_back') }}
            </VBtn>
            <VBtn
              variant="tonal"
              prepend-icon="tabler-refresh"
              @click="restartQuiz"
            >
              {{ t('chokai.retry') }}
            </VBtn>
            <VBtn
              v-if="nextLessonId"
              color="primary"
              append-icon="tabler-arrow-right"
              @click="goToNextLesson"
            >
              {{ t('chokai.next_lesson', { n: nextLessonId }) }}
            </VBtn>
          </div>
        </VCard>

        <!-- HEARTS OUT -->
        <VCard
          v-else-if="stage === STAGE_HEARTS_OUT"
          class="pa-6 text-center mx-auto"
          max-width="480"
        >
          <div class="text-h3 mb-2">
            💔
          </div>
          <h5 class="text-h5 mb-1">
            {{ t('chokai.out_of_hearts') }}
          </h5>
          <p class="text-body-2 text-medium-emphasis mb-6">
            {{ t('chokai.out_of_hearts_hint') }}
          </p>
          <div class="d-flex flex-wrap gap-2 justify-center">
            <VBtn
              variant="tonal"
              @click="backToSetup"
            >
              {{ t('chokai.quiz_back') }}
            </VBtn>
            <VBtn
              color="primary"
              prepend-icon="tabler-refresh"
              @click="restartQuiz"
            >
              {{ t('chokai.retry') }}
            </VBtn>
          </div>
        </VCard>
      </div>
    </main>

    <!-- Notifikasi benar/salah — bar di bawah, sama seperti Mondaishuu /
         jalur belajar, bukan popup. -->
    <footer
      v-if="feedback && stage === STAGE_QUIZ && !showingIntro"
      class="lp__footer"
      :class="feedback.correct ? 'is-correct' : 'is-incorrect'"
    >
      <div class="lp__footer-inner">
        <div
          class="lp__verdict"
          :style="{ color: feedback.correct ? 'rgb(var(--v-theme-success))' : 'rgb(var(--v-theme-error))' }"
        >
          <VIcon
            :icon="feedback.correct ? 'tabler-circle-check-filled' : 'tabler-circle-x-filled'"
            size="36"
          />
          <div>
            {{ feedback.correct ? t('chokai.correct') : t('chokai.incorrect') }}
            <small v-if="!feedback.correct">
              {{ t('chokai.correct_answer') }} <strong>{{ feedback.correctAnswer }}</strong>
            </small>
            <small v-if="feedback.translate" class="d-block" :lang="locale">
              {{ t('chokai.meaning') }}: {{ feedback.translate }}
            </small>
          </div>
        </div>

        <VBtn
          v-if="!autoNext"
          color="primary"
          append-icon="tabler-arrow-right"
          class="lp__cta"
          @click="nextQuestion"
        >
          {{ qIndex + 1 >= totalQuestions ? t('chokai.finish') : t('chokai.next') }}
        </VBtn>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ---------- lesson grid (setup stage) — sama persis dengan gaya
   pemilihan Kanji/Mondaishuu ---------- */
.chokai-grid {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
}

.chokai-card {
  position: relative;
  display: flex;
  overflow: hidden;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 14px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  gap: 6px;
  min-block-size: 104px;
  padding-block: 22px 12px;
  padding-inline: 10px;
  text-align: center;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.chokai-card::before {
  position: absolute;
  background: transparent;
  block-size: 3px;
  content: "";
  inline-size: 100%;
  inset-block-start: 0;
  inset-inline-start: 0;
}

.chokai-card--completed::before { background: rgb(var(--v-theme-success)); }
.chokai-card--completed { background: rgba(var(--v-theme-success), 0.06); }
.chokai-card--locked { opacity: 0.55; }
.chokai-card.chokai-card--locked:hover {
  border-color: rgba(var(--v-theme-on-surface), 0.12);
  box-shadow: none;
  transform: none;
}
.chokai-card__badge--lock { color: rgba(var(--v-theme-on-surface), 0.6); }
.chokai-card--mastered::before { background: rgb(var(--v-theme-warning)); }
.chokai-card--mastered { background: rgba(var(--v-theme-warning), 0.08); }

.chokai-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.6);
  box-shadow: 0 10px 20px -10px rgba(var(--v-theme-primary), 0.5);
  transform: translateY(-2px);
}

.chokai-card:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

.chokai-grid--busy {
  opacity: 0.6;
  pointer-events: none;
}

.chokai-card--disabled {
  cursor: not-allowed;
  opacity: 0.45;
}

.chokai-card--disabled:hover {
  border-color: rgba(var(--v-theme-on-surface), 0.12);
  box-shadow: none;
  transform: none;
}

.chokai-card__num {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1.15;
}

.chokai-card__focus {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.68rem;
  -webkit-line-clamp: 2;
  line-height: 1.25;
}

.chokai-card__badge {
  position: absolute;
  inset-block-start: 7px;
  inset-inline-end: 7px;
}

.chokai-card__badge--crown {
  color: rgb(var(--v-theme-warning));
}

@media (prefers-reduced-motion: reduce) {
  .chokai-card { transition: none; }
  .chokai-card:hover { transform: none; }
}

/* ---------- audio player ---------- */
.chokai-player {
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ---------- answer choices — sama gayanya dengan Mondaishuu ---------- */
.chokai-choice {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 10px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  font-size: 1rem;
  padding-block: 12px;
  padding-inline: 16px;
  text-align: start;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.chokai-choice:hover:not(:disabled) {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.06);
}

.chokai-choice--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.1);
}

.chokai-choice--correct {
  border-color: rgb(var(--v-theme-success));
  background: rgba(var(--v-theme-success), 0.14);
}

.chokai-choice--wrong {
  border-color: rgb(var(--v-theme-error));
  background: rgba(var(--v-theme-error), 0.14);
}

.chokai-choice:disabled {
  cursor: default;
}

/* ---------- crown badge on the result screen ---------- */
.chokai-crown-earned {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  background: rgba(var(--v-theme-warning), 0.14);
  color: rgb(var(--v-theme-warning));
  font-weight: 600;
  gap: 8px;
  padding-block: 8px;
  padding-inline: 18px;
}
</style>
