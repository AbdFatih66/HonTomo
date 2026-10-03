<script setup>
// Halaman ujian satu sesi Tes JLPT. Meniru kondisi ujian sungguhan:
//  - timer mundur dari server (refresh/tutup tab tidak mengulang waktu),
//  - TIDAK ada umpan balik benar/salah per soal (mode ujian),
//  - jawaban bisa diubah bebas sampai waktu habis / dikirim,
//  - waktu habis → jawaban otomatis dikirim.
// Mode LATIHAN: tanpa timer, umpan balik per soal (POST …/check), tanpa XP.
// Soal datang dari server setelah sesi dimulai (tanpa kunci jawaban), bukan
// dari bundle JS. Skor & keputusan lulus hanya ada di rekap akhir (halaman recap).
//
// Satu tata letak untuk SEMUA paket soal (soal asli maupun paket HonTomo): halaman
// panjang + daftar soal (navigator); chokai lewat pemutar rekaman berurutan.
import { useDebounceFn, useStorage } from '@vueuse/core'
import JlptQuestionFeedback from '@/components/jlpt/JlptQuestionFeedback.vue'
import JlptIllustration from '@/components/jlpt/JlptIllustration.vue'
import JlptMockImage from '@/components/jlpt-mock/JlptMockImage.vue'
import JlptListeningExam from '@/components/jlpt/JlptListeningExam.vue'
import JlptPassage from '@/components/jlpt/JlptPassage.vue'
import JlptRoomScene from '@/components/jlpt/JlptRoomScene.vue'
import JlptText from '@/components/jlpt/JlptText.vue'
import { plainLength } from '@/data/jlpt'
import { $api } from '@/utils/api'

definePage({ meta: { layout: 'blank' } })

const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const attemptId = computed(() => route.params.attempt)
const sectionKey = computed(() => route.params.section)
// { title, mondai: { [no]: { instruction, lead?, example? } }, passages?: { [id]: {...} },
//   questions: [...] } dari server
const section = ref(null)
const mode = ref('strict')
const level = ref('N5')
const packKey = ref(null)
const homeRoute = computed(() => ({ name: 'jlpt-test', query: packKey.value ? { pack: packKey.value } : {} }))
const isPractice = computed(() => mode.value === 'practice')

// Toggle furigana (disimpan di perangkat); dibaca semua JlptText lewat provide/inject.
const furigana = useStorage('jlptTest:furigana', false)
provide('jlptFurigana', furigana)

const isLoading = ref(true)
const loadError = ref(false)
const answers = reactive({})
const remaining = ref(0)
const isSubmitting = ref(false)
const submitError = ref(false)
const confirmSubmit = ref(false)
const confirmExit = ref(false)
const showNavigator = ref(false)
const timeUp = ref(false)
const feedback = reactive({}) // hasil /check per soal (hanya mode latihan)

// Sesi menyimak (聴解) punya tampilan sendiri: audio diputar berurutan oleh
// JlptListeningExam; tombol kirim baru muncul setelah rekaman selesai.
const isListening = computed(() => sectionKey.value === 'chokai')
const listeningDone = ref(false)

const questions = computed(() => section.value?.questions ?? [])
// Per もんだい: daftar { q, passage }. `passage` terisi hanya pada soal pertama
// yang memakai bacaan itu, jadi bacaan tampil sekali di atas soal-soalnya
// (mis. bacaan (1) untuk no. 22–23, bacaan (2) untuk no. 24–26).
const groups = computed(() => {
  const byMondai = new Map()
  const passages = section.value?.passages ?? {}
  let lastPassage = null

  for (const q of questions.value) {
    if (!byMondai.has(q.mondai))
      byMondai.set(q.mondai, [])

    const showPassage = q.passage && q.passage !== lastPassage ? (passages[q.passage] ?? null) : null

    lastPassage = q.passage ?? null
    byMondai.get(q.mondai).push({ q, passage: showPassage })
  }

  return [...byMondai.entries()].map(([mondai, items]) => ({ mondai, items }))
})

const sectionTitle = computed(() => section.value?.title ?? '')

const answeredCount = computed(() => questions.value.filter(q => answers[q.id]).length)
const unansweredCount = computed(() => questions.value.length - answeredCount.value)

// ── Timer ────────────────────────────────────────────────────────────
// Sisa waktu dari server dipakai sebagai patokan; jam lokal hanya untuk
// menghitung selisih (performance.now tahan terhadap ubah jam perangkat).
let anchorMs = 0
let anchorRemaining = 0
let tickHandle = null

function syncTimer(seconds) {
  anchorRemaining = seconds
  anchorMs = performance.now()
  remaining.value = seconds
}

function tick() {
  if (isPractice.value)
    return
  const left = Math.max(0, Math.ceil(anchorRemaining - (performance.now() - anchorMs) / 1000))
  remaining.value = left
  if (left <= 0 && !timeUp.value) {
    timeUp.value = true
    submit(true)
  }
}

const timerText = computed(() => {
  const m = Math.floor(remaining.value / 60)
  const s = remaining.value % 60

  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})
const timerColor = computed(() => (remaining.value <= 60 ? 'error' : remaining.value <= 300 ? 'warning' : 'primary'))

// ── Load ─────────────────────────────────────────────────────────────
async function load() {
  isLoading.value = true
  loadError.value = false
  try {
    const data = await $api(`/jlpt-test/attempts/${attemptId.value}/sections/${sectionKey.value}`)
    if (data.submitted) {
      router.replace(homeRoute.value)

      return
    }
    if (!data.test) {
      router.replace(homeRoute.value)

      return
    }
    section.value = data.test
    mode.value = data.mode ?? 'strict'
    packKey.value = data.pack ?? null
    level.value = data.level ?? level.value
    Object.assign(answers, data.answers ?? {})
    if (!isPractice.value) {
      syncTimer(data.remaining_seconds)
      tickHandle = setInterval(tick, 500)
      tick()
    }
  }
  catch (e) {
    // 404/409/422: sesi belum dimulai / sudah selesai / tes tidak aktif.
    if ([404, 409, 422].includes(e?.response?.status))
      router.replace(homeRoute.value)
    else
      loadError.value = true
  }
  finally {
    isLoading.value = false
  }
}

// ── Jawaban ──────────────────────────────────────────────────────────
const saveNow = async () => {
  if (isSubmitting.value || timeUp.value)
    return
  try {
    const res = await $api(`/jlpt-test/attempts/${attemptId.value}/sections/${sectionKey.value}/answers`, {
      method: 'PUT',
      body: { answers: { ...answers } },
    })
    // koreksi kecil bila jam klien melenceng dari server
    if (res.remaining_seconds != null && Math.abs(res.remaining_seconds - remaining.value) > 3)
      syncTimer(res.remaining_seconds)
  }
  catch { /* autosave best-effort; jawaban tetap ikut terkirim saat submit */ }
}
const saveDebounced = useDebounceFn(saveNow, 1500)

function choose(qid, n) {
  if (timeUp.value)
    return
  // klik pilihan yang sama = batalkan (seperti menghapus tanda di lembar jawaban)
  if (answers[qid] === n) {
    delete answers[qid]
    delete feedback[qid]
  }
  else {
    answers[qid] = n
    if (isPractice.value)
      checkAnswer(qid, n)
  }
  saveDebounced()
}

// Mode latihan: minta umpan balik SATU soal ke server (kunci tidak ada di klien).
async function checkAnswer(qid, n) {
  try {
    feedback[qid] = await $api(`/jlpt-test/attempts/${attemptId.value}/sections/${sectionKey.value}/check`, {
      method: 'POST',
      body: { question: String(qid), choice: n },
    })
  }
  catch { delete feedback[qid] /* umpan balik hanya bonus; jawaban tetap tersimpan */ }
}

async function submit(auto = false) {
  if (isSubmitting.value)
    return
  isSubmitting.value = true
  submitError.value = false
  confirmSubmit.value = false
  try {
    const res = await $api(`/jlpt-test/attempts/${attemptId.value}/sections/${sectionKey.value}/submit`, {
      method: 'POST',
      body: { answers: { ...answers } },
    })
    clearInterval(tickHandle)
    window.removeEventListener('beforeunload', onBeforeUnload)
    router.replace(res.test_completed
      ? { name: 'jlpt-test-recap-attempt', params: { attempt: res.attempt_id } }
      : { name: 'jlpt-test', query: { pack: packKey.value, done: sectionKey.value } })
  }
  catch (e) {
    if ([409, 422].includes(e?.response?.status)) {
      router.replace(homeRoute.value)

      return
    }
    // Gagal jaringan: tetap di halaman; bila waktu sudah habis, coba lagi otomatis.
    submitError.value = true
    isSubmitting.value = false
    if (auto)
      setTimeout(() => submit(true), 3000)
  }
}

// Keluar tanpa mengirim: jawaban terakhir disimpan dulu (best-effort), lalu kembali.
// Mode ujian: timer tetap berjalan di server. Mode latihan: bisa dilanjutkan kapan saja.
async function exitExam() {
  confirmExit.value = false
  window.removeEventListener('beforeunload', onBeforeUnload)
  try {
    await saveNow()
  }
  finally {
    router.replace(homeRoute.value)
  }
}

function scrollToQuestion(id) {
  showNavigator.value = false
  nextTick(() => document.getElementById(`jlpt-q-${id}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' }))
}

function onBeforeUnload(e) {
  e.preventDefault()
  e.returnValue = ''
}
function onVisibility() {
  if (document.visibilityState === 'hidden')
    saveNow()
}

// Pilihan bisa berupa teks atau gambar { image, alt } (paket HonTomo, mis. soal 28).
function isImageChoice(c) {
  return typeof c === 'object' && c !== null && !!c.image
}

function hasImageChoices(q) {
  return q.choices.some(isImageChoice)
}

function isLongChoices(q) {
  return !hasImageChoices(q) && q.choices.some(c => typeof c === 'string' && plainLength(c) > 14)
}

onMounted(() => {
  window.addEventListener('beforeunload', onBeforeUnload)
  document.addEventListener('visibilitychange', onVisibility)
  load()
})
onBeforeUnmount(() => {
  clearInterval(tickHandle)
  window.removeEventListener('beforeunload', onBeforeUnload)
  document.removeEventListener('visibilitychange', onVisibility)
})
</script>

<template>
  <div class="kana-fs kana-fs--page jlpt-exam">
    <header class="kana-fs__header">
      <VBtn
        icon="tabler-x"
        variant="text"
        color="secondary"
        :aria-label="t('jlptTest.exam.exit')"
        @click="confirmExit = true"
      />

      <div class="flex-grow-1 min-w-0">
        <div class="text-body-1 font-weight-medium text-truncate">
          <span class="text-primary">{{ level }}</span>
          {{ sectionTitle }}
          <VChip
            v-if="isPractice"
            size="x-small"
            variant="tonal"
            class="ms-1"
          >
            {{ t('jlptMock.mode_practice_short') }}
          </VChip>
        </div>
        <div class="text-caption text-medium-emphasis">
          {{ t('jlptTest.exam.answered', { n: answeredCount, total: questions.length }) }}
        </div>
      </div>

      <VChip
        v-if="!isPractice"
        :color="timerColor"
        variant="flat"
        size="large"
        class="jlpt-timer flex-shrink-0"
        role="timer"
        :aria-label="t('jlptTest.exam.time_left')"
      >
        <VIcon
          start
          icon="tabler-clock"
        />
        {{ timerText }}
      </VChip>
      <VChip
        v-else
        variant="tonal"
        size="small"
        class="flex-shrink-0"
      >
        {{ t('jlptMock.no_time_limit') }}
      </VChip>

      <VSwitch
        v-model="furigana"
        :label="t('jlptMock.furigana_short')"
        color="primary"
        density="compact"
        hide-details
        class="flex-shrink-0"
      />

      <VBtn
        v-if="!isListening"
        icon="tabler-layout-grid"
        variant="tonal"
        color="secondary"
        :aria-label="t('jlptTest.exam.question_list')"
        @click="showNavigator = true"
      />
    </header>

    <main class="kana-fs__body">
      <div class="kana-fs__content">
        <div
          v-if="isLoading"
          class="d-flex justify-center pa-10"
        >
          <VProgressCircular
            indeterminate
            color="primary"
          />
        </div>

        <div
          v-else-if="loadError"
          class="text-center pa-10"
        >
          <p class="mb-4">
            {{ t('common.error') }}
          </p>
          <VBtn @click="load">
            {{ t('common.retry') }}
          </VBtn>
        </div>

        <div
          v-else
          class="mx-auto jlpt-paper"
        >
          <VAlert
            v-if="timeUp"
            type="warning"
            variant="tonal"
            class="mb-6"
          >
            {{ t('jlptTest.exam.time_up') }}
          </VAlert>

          <JlptListeningExam
            v-if="isListening"
            :test="section"
            :answers="answers"
            :stopped="timeUp"
            :storage-key="`jlpt-chokai-${attemptId}`"
            class="mb-8"
            @choose="choose"
            @done="listeningDone = true"
          />

          <template v-else>
            <section
              v-for="g in groups"
              :key="g.mondai"
              class="mb-10"
            >
              <!-- Petunjuk もんだい, format sama dengan lembar soal -->
              <div class="jlpt-mondai">
                <div class="jlpt-mondai__head">
                  <span class="jlpt-mondai__tag">もんだい {{ g.mondai }}</span>
                  <span class="jlpt-mondai__text">
                    <JlptText :text="section.mondai[g.mondai].instruction" />
                  </span>
                </div>
                <div class="text-caption text-medium-emphasis mt-2">
                  {{ t(`jlptTest.mondai_hint.${sectionKey}_${g.mondai}`) }}
                </div>

                <!-- Contoh (れい) — hanya untuk もんだい yang punya contoh -->
                <div
                  v-if="section.mondai[g.mondai].example"
                  class="jlpt-example"
                  lang="ja"
                >
                  <span class="jlpt-example__label">{{ section.mondai[g.mondai].example.label ?? '（れい）' }}</span>
                  <span class="jlpt-example__stem">
                    <JlptText :text="section.mondai[g.mondai].example.stem" />
                  </span>
                  <span class="jlpt-example__choices">
                    <span
                      v-for="(c, ci) in section.mondai[g.mondai].example.choices"
                      :key="ci"
                    >{{ ci + 1 }} <JlptText :text="c" /></span>
                  </span>

                  <!-- cara menjawab (こたえかた), mis. soal ★ -->
                  <div
                    v-if="section.mondai[g.mondai].example.howto"
                    class="jlpt-howto"
                  >
                    <div class="font-weight-medium">
                      {{ section.mondai[g.mondai].example.howto.title }}
                    </div>
                    <div><JlptText :text="section.mondai[g.mondai].example.howto.step1" /></div>
                    <div class="jlpt-howto__figure">
                      <div><JlptText :text="section.mondai[g.mondai].example.howto.figure.stem" /></div>
                      <div class="jlpt-howto__order">
                        <JlptText :text="section.mondai[g.mondai].example.howto.figure.order" />
                      </div>
                      <div v-if="section.mondai[g.mondai].example.howto.figure.reply">
                        <JlptText :text="section.mondai[g.mondai].example.howto.figure.reply" />
                      </div>
                    </div>
                    <div><JlptText :text="section.mondai[g.mondai].example.howto.step2" /></div>
                  </div>

                  <!-- contoh lembar jawaban: bulatan jawaban contoh terisi -->
                  <div class="jlpt-example__sheet">
                    <span>（かいとうようし）</span>
                    <span class="jlpt-example__sheet-box">
                      <b>（れい）</b>
                      <i
                        v-for="n in 4"
                        :key="n"
                        class="jlpt-choice__bubble jlpt-choice__bubble--static"
                        :class="{ 'is-filled': n === section.mondai[g.mondai].example.answer }"
                      >{{ n }}</i>
                    </span>
                  </div>
                </div>
              </div>

              <p
                v-if="section.mondai[g.mondai].lead"
                class="jlpt-lead"
              >
                <JlptText :text="section.mondai[g.mondai].lead" />
              </p>

              <template
                v-for="{ q, passage } in g.items"
                :key="q.id"
              >
                <JlptPassage
                  v-if="passage"
                  :passage="passage"
                />

                <div
                  :id="`jlpt-q-${q.id}`"
                  class="jlpt-q"
                  :class="{ 'jlpt-q--answered': answers[q.id] }"
                >
                  <div class="jlpt-q__stem">
                    <span class="jlpt-q__no">{{ q.no ?? q.id }}</span>
                    <span
                      v-if="q.stem"
                      class="jlpt-q__text"
                    >
                      <JlptText :text="q.stem" />
                    </span>
                  </div>

                  <JlptIllustration
                    v-if="q.image"
                    :name="q.image"
                    class="my-3"
                  />

                  <div
                    class="jlpt-choices"
                    :class="{
                      'jlpt-choices--long': isLongChoices(q),
                      'jlpt-choices--art': q.choice_art || hasImageChoices(q),
                    }"
                    role="radiogroup"
                    :aria-label="`${t('jlptTest.exam.question')} ${q.id}`"
                  >
                    <button
                      v-for="(c, ci) in q.choices"
                      :key="ci"
                      type="button"
                      class="jlpt-choice"
                      :class="{ 'is-selected': answers[q.id] === ci + 1, 'jlpt-choice--art': q.choice_art || isImageChoice(c) }"
                      role="radio"
                      :aria-checked="answers[q.id] === ci + 1"
                      :aria-label="q.choice_art || isImageChoice(c) ? String(ci + 1) : undefined"
                      @click="choose(q.id, ci + 1)"
                    >
                      <span class="jlpt-choice__bubble">{{ ci + 1 }}</span>
                      <JlptMockImage
                        v-if="isImageChoice(c)"
                        :src="c.image"
                        :alt="c.alt"
                      />
                      <JlptRoomScene
                        v-else-if="q.choice_art === 'room'"
                        :variant="ci + 1"
                      />
                      <span
                        v-else
                        class="jlpt-choice__text"
                      ><JlptText :text="c" /></span>
                    </button>
                  </div>

                  <JlptQuestionFeedback
                    v-if="feedback[q.id]"
                    :fb="feedback[q.id]"
                  />
                </div>
              </template>
            </section>
          </template>

          <div
            v-if="!isListening || listeningDone"
            class="text-center pb-8"
          >
            <VAlert
              v-if="submitError"
              type="error"
              variant="tonal"
              class="mb-4 text-start"
            >
              {{ t('jlptTest.exam.submit_error') }}
            </VAlert>
            <VBtn
              size="large"
              color="primary"
              :loading="isSubmitting"
              @click="confirmSubmit = true"
            >
              {{ t('jlptTest.exam.finish') }}
            </VBtn>
          </div>
        </div>
      </div>
    </main>

    <!-- Daftar soal -->
    <VNavigationDrawer
      v-model="showNavigator"
      location="end"
      temporary
      width="300"
    >
      <div class="pa-4">
        <h6 class="text-h6 mb-1">
          {{ t('jlptTest.exam.question_list') }}
        </h6>
        <p class="text-caption text-medium-emphasis mb-4">
          {{ t('jlptTest.exam.navigator_hint') }}
        </p>
        <div class="jlpt-nav-grid">
          <button
            v-for="q in questions"
            :key="q.id"
            type="button"
            class="jlpt-nav-cell"
            :class="{ 'is-answered': answers[q.id] }"
            @click="scrollToQuestion(q.id)"
          >
            {{ q.no ?? q.id }}
          </button>
        </div>
      </div>
    </VNavigationDrawer>

    <!-- Konfirmasi kirim -->
    <VDialog
      v-model="confirmSubmit"
      max-width="420"
    >
      <VCard class="pa-2">
        <VCardTitle>{{ t('jlptTest.exam.confirm_title') }}</VCardTitle>
        <VCardText>
          <p v-if="unansweredCount > 0">
            {{ t('jlptTest.exam.confirm_unanswered', { n: unansweredCount }) }}
          </p>
          <p v-else>
            {{ t('jlptTest.exam.confirm_all_answered') }}
          </p>
          <p class="text-medium-emphasis mb-0">
            {{ t('jlptTest.exam.confirm_final') }}
          </p>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            color="secondary"
            @click="confirmSubmit = false"
          >
            {{ t('jlptTest.exam.keep_working') }}
          </VBtn>
          <VBtn @click="submit(false)">
            {{ t('jlptTest.exam.submit') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Konfirmasi keluar: timer TETAP berjalan -->
    <VDialog
      v-model="confirmExit"
      max-width="420"
    >
      <VCard class="pa-2">
        <VCardTitle>{{ t('jlptTest.exam.exit_title') }}</VCardTitle>
        <VCardText>{{ t(isPractice ? 'jlptTest.practice.exit_body' : 'jlptTest.exam.exit_body') }}</VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            color="secondary"
            @click="confirmExit = false"
          >
            {{ t('jlptTest.exam.keep_working') }}
          </VBtn>
          <VBtn
            color="error"
            @click="exitExam"
          >
            {{ t('jlptTest.exam.exit_confirm') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.jlpt-paper {
  max-inline-size: 860px;
}

.jlpt-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.jlpt-timer {
  font-variant-numeric: tabular-nums;
  font-weight: 600;
}

.jlpt-mondai {
  padding: 16px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.6);
  border-radius: 8px;
  margin-block-end: 20px;
}

.jlpt-mondai__head {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.jlpt-mondai__tag {
  flex: none;
  font-weight: 700;
  white-space: nowrap;
}

.jlpt-mondai__text {
  font-weight: 600;
  white-space: pre-line;
}

.jlpt-example {
  padding: 10px 12px;
  border-radius: 6px;
  background: rgba(var(--v-theme-on-surface), 0.05);
  margin-block-start: 12px;
}

.jlpt-example__label { font-weight: 600; margin-inline-end: 8px; }
.jlpt-example__choices { display: flex; flex-wrap: wrap; gap: 4px 20px; margin-block-start: 6px; }

.jlpt-example__stem { white-space: pre-line; }

.jlpt-howto {
  padding-inline-start: 4px;
  line-height: 1.9;
  margin-block-start: 12px;
}

.jlpt-howto__figure {
  padding: 10px 14px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  margin-block: 6px;
}

.jlpt-howto__order { padding-inline-start: 2.5em; }

.jlpt-example__sheet {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
  margin-block-start: 10px;
}

.jlpt-example__sheet-box {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  gap: 6px;
}

.jlpt-lead {
  font-size: 1.15rem;
  line-height: 2;
  margin-block: 0 12px;
}

.jlpt-q {
  padding: 16px;
  border-radius: 8px;
  margin-block-end: 8px;
  scroll-margin-block: 80px;
}

.jlpt-q--answered { background: rgba(var(--v-theme-primary), 0.05); }

.jlpt-q__stem {
  display: flex;
  align-items: baseline;
  font-size: 1.15rem;
  gap: 12px;
  line-height: 1.9;
}

.jlpt-q__text { min-inline-size: 0; }

.jlpt-q__no {
  display: inline-flex;
  flex: none;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.7);
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 700;
  min-inline-size: 34px;
  padding-inline: 4px;
}

.jlpt-choices {
  display: grid;
  gap: 8px 16px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  margin-block-start: 8px;
  padding-inline-start: 46px;
}

.jlpt-choices--long { grid-template-columns: 1fr; }

/* pilihan berupa gambar (mis. 4 ruangan pada もんだい 4 no. 28) */
.jlpt-choices--art { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.jlpt-choice--art { align-items: flex-start; }

.jlpt-choice {
  display: flex;
  align-items: center;
  padding: 8px 10px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  color: inherit;
  cursor: pointer;
  gap: 10px;
  text-align: start;
}

.jlpt-choice:hover { border-color: rgba(var(--v-theme-primary), 0.6); }
.jlpt-choice:focus-visible { outline: 2px solid rgb(var(--v-theme-primary)); outline-offset: 2px; }
.jlpt-choice.is-selected { border-color: rgb(var(--v-theme-primary)); background: rgba(var(--v-theme-primary), 0.1); }

/* Bulatan ala lembar jawaban (mark sheet): terisi penuh saat dipilih */
.jlpt-choice__bubble {
  display: inline-flex;
  flex: none;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.6);
  border-radius: 50%;
  block-size: 26px;
  font-size: 0.8rem;
  inline-size: 26px;
}

.jlpt-choice__bubble--static { cursor: default; font-style: normal; }

.jlpt-choice.is-selected .jlpt-choice__bubble,
.jlpt-choice__bubble.is-filled {
  border-color: rgb(var(--v-theme-primary));
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.jlpt-choice__text { font-size: 1.05rem; line-height: 1.5; }

.jlpt-nav-grid { display: grid; gap: 8px; grid-template-columns: repeat(5, 1fr); }

.jlpt-nav-cell {
  border: 2px solid rgba(var(--v-theme-on-surface), 0.25);
  border-radius: 6px;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font-variant-numeric: tabular-nums;
  padding-block: 8px;
}

.jlpt-nav-cell.is-answered {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.15);
}

@media (max-width: 599px) {
  .jlpt-choices { padding-inline-start: 0; }
}
</style>
