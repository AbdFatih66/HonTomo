<script setup>
// Sesi menyimak (聴解) untuk pack bergaya "mock": langkah berurutan
// petunjuk → contoh (例) → soal 1..n untuk tiap もんだい, dengan MP3 per langkah.
//
//  - strict   : audio otomatis & sekali putar, jeda menjawab (`gap`) lalu lanjut sendiri,
//               tanpa putar ulang. Posisi langkah disimpan di sessionStorage supaya
//               refresh tidak mengulang dari awal.
//  - practice : audio bisa diputar ulang, lanjut manual, ada umpan balik per soal.
//               Bila MP3 belum dibuat, dipakai suara browser dari `audio_text`
//               (server hanya mengirim `audio_text` pada mode latihan).
//
// Jawaban yang masuk/keluar 1-based (kontrak API); JlptMockQuestion 0-based.
import JlptMockQuestion from '@/components/jlpt-mock/JlptMockQuestion.vue'
import JlptMockText from '@/components/jlpt-mock/JlptMockText.vue'
import JlptQuestionFeedback from '@/components/jlpt/JlptQuestionFeedback.vue'
import { buildListeningSteps } from '@/utils/jlptMock'

const props = defineProps({
  section: { type: Object, required: true },
  answers: { type: Object, required: true },
  feedback: { type: Object, default: () => ({}) },
  practice: { type: Boolean, default: false },
  furigana: { type: Boolean, default: false },
  // true saat waktu sesi habis → audio dihentikan
  stopped: { type: Boolean, default: false },
  storageKey: { type: String, default: '' },
})

const emit = defineEmits(['choose', 'done'])

const { t } = useI18n()

const audioEl = ref(null)
const steps = computed(() => buildListeningSteps(props.section))
const stepIdx = ref(0)
const started = ref(false)
const stepStatus = ref('playing') // playing | waiting | idle
const waitLeft = ref(0)
const audioProblem = ref(false)
const needsTap = ref(false)
const memo = ref('')
let waitTimer = null

const step = computed(() => steps.value[stepIdx.value] ?? null)
const scoredTotal = computed(() => steps.value.filter(s => s.kind === 'item' && !s.example).length)
const scoredDone = computed(() => steps.value.slice(0, stepIdx.value).filter(s => s.kind === 'item' && !s.example).length)
const resumed = computed(() => stepIdx.value > 0)

// ── Posisi langkah (hanya mode ujian) ────────────────────────────────
function loadProgress() {
  if (props.practice || !props.storageKey)
    return
  try {
    const saved = Number(sessionStorage.getItem(props.storageKey))
    if (Number.isInteger(saved) && saved > 0 && saved < steps.value.length)
      stepIdx.value = saved
  }
  catch { /* sessionStorage bisa diblokir; abaikan */ }
}

function saveProgress() {
  if (props.practice || !props.storageKey)
    return
  try {
    sessionStorage.setItem(props.storageKey, String(stepIdx.value))
  }
  catch { /* abaikan */ }
}

// ── Audio ────────────────────────────────────────────────────────────
function clearWait() {
  if (waitTimer)
    clearInterval(waitTimer)
  waitTimer = null
}

// Cadangan bila MP3 belum dibuat / gagal dimuat: suara browser (ja-JP).
// Hanya mungkin di mode latihan, karena hanya di sana `audio_text` dikirim server.
let fallbackToken = 0

function cancelFallback() {
  fallbackToken++
  window.speechSynthesis?.cancel()
}

function speakFallback(turns, done) {
  const synth = window.speechSynthesis
  const token = ++fallbackToken
  const lines = (turns ?? []).map(x => x.text).filter(Boolean)

  if (!synth || !lines.length) {
    setTimeout(() => token === fallbackToken && done(), 1200)

    return
  }

  synth.cancel()
  const voice = synth.getVoices().find(v => v.lang?.startsWith('ja'))

  lines.forEach((txt, i) => {
    const u = new SpeechSynthesisUtterance(txt)

    u.lang = 'ja-JP'
    u.rate = 0.9
    if (voice)
      u.voice = voice
    if (i === lines.length - 1)
      u.onend = () => token === fallbackToken && done()
    synth.speak(u)
  })
}

function playFallback(s) {
  audioProblem.value = true
  speakFallback(s.script, () => step.value === s && onAudioDone())
}

function stopAudio() {
  clearWait()
  cancelFallback()
  if (audioEl.value) {
    audioEl.value.onended = null
    audioEl.value.onerror = null
    audioEl.value.pause()
  }
}

function startStep() {
  clearWait()
  cancelFallback()
  audioProblem.value = false
  needsTap.value = false

  const s = step.value
  if (!s) {
    finish()

    return
  }

  stepStatus.value = 'playing'

  if (!s.audio) {
    playFallback(s)

    return
  }

  const el = audioEl.value

  el.onended = () => onAudioDone()
  el.onerror = () => playFallback(s)
  el.src = s.audio
  el.currentTime = 0
  el.play().catch(() => {
    needsTap.value = true
    stepStatus.value = 'idle'
  })
}

function tapToPlay() {
  needsTap.value = false
  stepStatus.value = 'playing'
  audioEl.value?.play().catch(() => { needsTap.value = true; stepStatus.value = 'idle' })
}

function onAudioDone() {
  const s = step.value
  if (!s)
    return

  // Latihan: tunggu tombol "Lanjut". Ujian: hitung mundur lalu lanjut sendiri.
  if (props.practice) {
    stepStatus.value = 'idle'

    return
  }

  stepStatus.value = 'waiting'
  waitLeft.value = s.kind === 'intro' ? 2 : (s.mondai.gap ?? 5)
  clearWait()
  waitTimer = setInterval(() => {
    waitLeft.value--
    if (waitLeft.value <= 0)
      nextStep()
  }, 1000)
}

function nextStep() {
  clearWait()
  stepIdx.value++
  saveProgress()
  if (stepIdx.value >= steps.value.length) {
    finish()

    return
  }
  startStep()
}

function replay() {
  startStep()
}

function begin() {
  started.value = true
  nextTick(startStep)
}

function finish() {
  stopAudio()
  try {
    if (props.storageKey)
      sessionStorage.removeItem(props.storageKey)
  }
  catch { /* abaikan */ }
  emit('done')
}

function choose(q, n) {
  emit('choose', q, n)
}

watch(() => props.stopped, v => {
  if (v)
    stopAudio()
})

onMounted(loadProgress)
onBeforeUnmount(stopAudio)
</script>

<template>
  <div>
    <!-- Gerbang: butuh sentuhan pengguna supaya browser mengizinkan audio otomatis -->
    <VCard
      v-if="!started"
      class="pa-2"
    >
      <VCardText class="text-center">
        <VIcon
          icon="tabler-headphones"
          size="48"
          class="mb-2"
        />
        <div class="text-h5 mb-2">
          {{ resumed ? t('jlptTest.listen.gate_resume_title') : t('jlptTest.listen.gate_title') }}
        </div>
        <p class="text-body-2 text-medium-emphasis">
          {{ t('jlptTest.listen.gate_body') }}
        </p>

        <ul class="text-start jlpt-rules my-4">
          <li>{{ t('jlptMock.rule_listening_1') }}</li>
          <li>{{ t('jlptMock.rule_listening_2') }}</li>
          <li>{{ t(practice ? 'jlptMock.rule_listening_3_practice' : 'jlptMock.rule_listening_3_strict') }}</li>
        </ul>

        <VBtn
          color="primary"
          size="large"
          prepend-icon="tabler-headphones"
          @click="begin"
        >
          {{ resumed ? t('jlptTest.listen.resume') : t('jlptMock.start_listening') }}
        </VBtn>
      </VCardText>
    </VCard>

    <template v-else-if="step">
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="jlpt-mondai mb-0">
          <span class="jlpt-mondai__label">{{ step.mondai.label }}</span>
        </div>
        <VChip
          size="small"
          variant="tonal"
        >
          {{ t('jlptMock.listening_progress', { n: Math.min(scoredDone + (step.kind === 'item' && !step.example ? 1 : 0), scoredTotal), total: scoredTotal }) }}
        </VChip>
      </div>

      <p class="mb-4">
        <JlptMockText
          :text="step.mondai.instruction"
          :show-furigana="furigana"
        />
      </p>

      <VCard
        variant="tonal"
        :color="audioProblem ? 'warning' : 'primary'"
        class="mb-4"
      >
        <VCardText class="d-flex align-center gap-3">
          <VIcon
            :icon="stepStatus === 'playing' ? 'tabler-volume' : 'tabler-hourglass'"
            size="28"
          />
          <div class="flex-grow-1">
            <div class="font-weight-medium">
              <template v-if="audioProblem">
                {{ t('jlptMock.audio_missing') }}
              </template>
              <template v-else-if="needsTap">
                {{ t('jlptMock.audio_tap') }}
              </template>
              <template v-else-if="stepStatus === 'playing'">
                {{ step.kind === 'intro' ? t('jlptMock.listening_instruction') : t('jlptMock.listening_now') }}
              </template>
              <template v-else-if="stepStatus === 'waiting'">
                {{ t('jlptMock.listening_answer_now', { n: waitLeft }) }}
              </template>
              <template v-else>
                {{ t('jlptMock.listening_done') }}
              </template>
            </div>
            <div
              v-if="step.kind === 'item' && step.example"
              class="text-caption"
            >
              {{ t('jlptMock.example_hint') }}
            </div>
          </div>

          <VBtn
            v-if="needsTap"
            color="primary"
            @click="tapToPlay"
          >
            {{ t('jlptMock.play') }}
          </VBtn>
          <template v-else-if="practice">
            <VBtn
              variant="text"
              prepend-icon="tabler-rotate"
              @click="replay"
            >
              {{ t('jlptMock.replay') }}
            </VBtn>
            <VBtn
              color="primary"
              :disabled="stepStatus === 'playing'"
              @click="nextStep"
            >
              {{ t('jlptMock.next') }}
            </VBtn>
          </template>
        </VCardText>
      </VCard>

      <JlptMockQuestion
        v-if="step.kind === 'item'"
        :key="step.key"
        :q="step.q"
        :model-value="step.example ? step.q.answer - 1 : (answers[step.q.id] ? answers[step.q.id] - 1 : null)"
        :show-furigana="furigana"
        :reveal="step.example"
        :disabled="step.example"
        @update:model-value="choose(step.q, $event + 1)"
      />

      <JlptQuestionFeedback
        v-if="step.kind === 'item' && !step.example && feedback[step.q.id]"
        :fb="feedback[step.q.id]"
      />

      <VTextarea
        v-if="step.mondai.id === 'l4' && step.kind === 'item'"
        v-model="memo"
        :label="t('jlptMock.memo')"
        rows="3"
        auto-grow
        class="mt-6"
        hide-details
      />
    </template>

    <audio
      ref="audioEl"
      preload="auto"
    />
  </div>
</template>

<style scoped>
.jlpt-mondai {
  display: flex;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 12px;
}

.jlpt-mondai__label {
  font-size: 1.15rem;
  font-weight: 700;
}

.jlpt-rules {
  color: rgba(var(--v-theme-on-surface), 0.85);
  line-height: 1.9;
  padding-inline-start: 22px;
}
</style>
