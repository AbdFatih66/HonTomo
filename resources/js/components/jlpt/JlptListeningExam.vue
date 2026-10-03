<script setup>
// Pemutar ujian menyimak (聴解) Tes JLPT.
//
// Meniru rekaman ujian asli: audio diputar OTOMATIS dan berurutan
// (penjelasan umum → petunjuk もんだい + れい → soal 1 → soal 2 → … → istirahat
// → … → penutup), hanya SEKALI, tanpa tombol
// ulang/mundur/jeda, dan tidak bisa kembali ke soal sebelumnya. Jawaban boleh
// dipilih saat audio diputar maupun sesudahnya; begitu audio selesai ada jeda
// menjawab (hitung mundur) lalu pindah sendiri ke soal berikutnya.
//
// Yang dikendalikan server tetap timer 30 menit + jawaban (autosave & submit
// ada di halaman induk); pemutar ini hanya mengurus urutan audio & tampilan.
// Posisi terakhir disimpan di sessionStorage agar muat ulang halaman tidak
// mengulang dari awal (soal yang sedang berjalan diputar lagi dari awal).
import JlptListeningItem from '@/components/jlpt/JlptListeningItem.vue'
import JlptText from '@/components/jlpt/JlptText.vue'
import { playChime, unlockChime } from '@/utils/jlptChime'
import { defineEmits, defineProps, } from 'vue'

const props = defineProps({
  test: { type: Object, required: true }, // { mondai, questions } dari server
  answers: { type: Object, required: true },
  stopped: { type: Boolean, default: false }, // waktu habis
  storageKey: { type: String, default: '' },
})

const emit = defineEmits(['choose', 'done'])

const { t } = useI18n()

const DEFAULT_WAIT = 8 // detik jeda menjawab bila bank soal tidak menyebutnya
const INTRO_GAP = 2 // detik jeda setelah petunjuk/れい selesai

// Urutan langkah mengikuti nomor trek rekaman: penjelasan umum (`start`), lalu
// tiap もんだい = [istirahat (`rest`, bila ada)] + petunjuk & れい (`intro`) +
// tiap soal, dan terakhir penutup (`end`).
const steps = computed(() => {
  const out = []
  const nos = Object.keys(props.test.mondai ?? {}).map(Number).sort((a, b) => a - b)

  if (props.test.audio?.intro)
    out.push({ kind: 'start', audio: [props.test.audio.intro] })

  for (const no of nos) {
    const m = props.test.mondai[no]

    if (m.audio_before)
      out.push({ kind: 'rest', mondai: no, m, audio: [m.audio_before] })
    out.push({ kind: 'intro', mondai: no, m, audio: [m.audio, m.example?.audio].filter(Boolean) })
    for (const q of props.test.questions.filter(x => Number(x.mondai) === no))
      out.push({ kind: 'question', mondai: no, m, q, audio: q.audio ? [q.audio] : [], wait: m.answer_seconds ?? DEFAULT_WAIT })
  }

  if (props.test.audio?.outro)
    out.push({ kind: 'end', audio: [props.test.audio.outro] })

  return out
})

const CARD_STEPS = { start: 'tabler-headphones', rest: 'tabler-coffee', end: 'tabler-flag-check' }

const total = computed(() => props.test.questions.length)

const idx = ref(0)
const phase = ref('gate') // gate | playing | waiting | finished
const resumed = ref(false)
const audioError = ref('') // berkas yang gagal dimuat
const blocked = ref(false) // browser menahan autoplay
const manual = ref(false) // tidak pindah otomatis (audio hilang / tanpa audio)
const progress = ref(0)
const countdown = ref(0)
const memo = ref('')
const belling = ref(false) // bel awal soal sedang berbunyi

const step = computed(() => steps.value[idx.value] ?? null)
const answeredIndex = computed(() => props.test.questions.findIndex(q => q.id === step.value?.q?.id) + 1)

let audioEl = null
let bellDone = false // bel れい sudah dibunyikan untuk langkah ini
let bellToken = 0 // membatalkan bel yang tertunda bila langkah berganti / dihentikan
let queue = []
let timer = null

function clearTimer() {
  if (timer)
    clearInterval(timer)
  timer = null
}

function cleanupAudio() {
  if (audioEl) {
    audioEl.onended = null
    audioEl.onerror = null
    audioEl.ontimeupdate = null
    audioEl.pause()
    audioEl.removeAttribute('src')
    audioEl.load()
  }
  audioEl = null
}

function cleanup() {
  bellToken++
  belling.value = false
  clearTimer()
  cleanupAudio()
}

function startStep() {
  cleanup()
  bellDone = false
  audioError.value = ''
  blocked.value = false
  manual.value = false
  progress.value = 0
  queue = [...(step.value?.audio ?? [])]
  phase.value = 'playing'

  if (!queue.length) {
    manual.value = true
    phase.value = 'waiting'

    return
  }

  // Bel di awal tiap soal (kind 'question'), baru rekaman moderator.
  if (step.value?.kind === 'question') {
    const token = ++bellToken

    belling.value = true
    playChime().then(() => {
      if (gone || token !== bellToken)
        return
      belling.value = false
      playNext()
    })

    return
  }
  playNext()
}

function playNext() {
  const src = queue.shift()

  // Bel sebelum moderator mengucapkan れい (audio contoh di langkah petunjuk).
  const ex = step.value?.kind === 'intro' ? step.value.m?.example?.audio : null

  if (ex && src === ex && !bellDone) {
    const token = ++bellToken

    queue.unshift(src)
    bellDone = true
    belling.value = true
    playChime().then(() => {
      if (gone || token !== bellToken)
        return
      belling.value = false
      playNext()
    })

    return
  }

  cleanupAudio()
  audioEl = new Audio(src)
  audioEl.preload = 'auto'
  audioEl.ontimeupdate = () => {
    if (audioEl?.duration)
      progress.value = audioEl.currentTime / audioEl.duration
  }
  audioEl.onended = () => (queue.length ? playNext() : audioFinished())
  audioEl.onerror = () => {
    audioError.value = src
    manual.value = true
    cleanup()
    phase.value = 'waiting'
  }

  audioEl.play().catch((e) => {
    if (e?.name === 'NotAllowedError')
      blocked.value = true
  })
}

function replayBlocked() {
  blocked.value = false
  audioEl?.play().catch(() => {
    blocked.value = true
  })
}

function audioFinished() {
  progress.value = 1
  phase.value = 'waiting'

  // penutup: rekaman selesai, langsung akhiri
  if (step.value?.kind === 'end') {
    next()

    return
  }
  countdown.value = step.value?.kind === 'question' ? step.value.wait ?? DEFAULT_WAIT : INTRO_GAP
  clearTimer()
  timer = setInterval(() => {
    countdown.value -= 1
    if (countdown.value <= 0)
      next()
  }, 1000)
}

let gone = false

function next() {
  cleanup()
  if (idx.value + 1 >= steps.value.length) {
    idx.value = steps.value.length
    phase.value = 'finished'
    emit('done')

    return
  }
  idx.value += 1
  startStep()
}

function begin() {
  unlockChime() // dari klik pengguna, supaya browser mengizinkan bel
  startStep()
}

function pick(q, n) {
  emit('choose', q.id, n)
}

// ── simpan/pulihkan posisi ──────────────────────────────────────────
watch(idx, (v) => {
  try {
    if (props.storageKey)
      sessionStorage.setItem(props.storageKey, String(v))
  }
  catch { /* penyimpanan tidak tersedia: abaikan */ }
})

watch(() => props.stopped, (v) => {
  if (v) {
    cleanup()
    phase.value = 'finished'
  }
})

onMounted(() => {
  try {
    const saved = Number(sessionStorage.getItem(props.storageKey))

    if (props.storageKey && Number.isInteger(saved) && saved > 0 && saved <= steps.value.length) {
      idx.value = saved
      resumed.value = true
    }
  }
  catch { /* abaikan */ }

  if (idx.value >= steps.value.length) {
    phase.value = 'finished'
    emit('done')
  }
})

onBeforeUnmount(() => {
  gone = true
  cleanup()
})
</script>

<template>
  <div class="jl">
    <!-- Sebelum mulai: perlu satu ketukan agar browser mengizinkan audio -->
    <div
      v-if="phase === 'gate'"
      class="jl-card text-center"
    >
      <VIcon
        icon="tabler-headphones"
        size="56"
        color="primary"
      />
      <h5 class="text-h5 my-3">
        {{ resumed ? t('jlptTest.listen.gate_resume_title') : t('jlptTest.listen.gate_title') }}
      </h5>
      <p class="text-body-1 mb-4">
        {{ t('jlptTest.listen.gate_body') }}
      </p>
      <ul class="text-body-2 text-start jl-rules mb-6">
        <li>{{ t('jlptTest.listen.rule_once') }}</li>
        <li>{{ t('jlptTest.listen.rule_forward') }}</li>
        <li>{{ t('jlptTest.listen.rule_answer') }}</li>
        <li>{{ t('jlptTest.listen.rule_timer') }}</li>
      </ul>
      <VBtn
        size="large"
        color="primary"
        prepend-icon="tabler-player-play"
        :disabled="belling"
        :loading="belling"
        @click="begin"
      >
        {{ resumed ? t('jlptTest.listen.resume') : t('jlptTest.listen.start') }}
      </VBtn>
    </div>

    <div
      v-else-if="phase === 'finished'"
      class="jl-card text-center"
    >
      <VIcon
        :icon="stopped ? 'tabler-clock-off' : 'tabler-circle-check'"
        size="56"
        :color="stopped ? 'warning' : 'success'"
      />
      <h5 class="text-h5 my-3">
        {{ stopped ? t('jlptTest.listen.stopped') : t('jlptTest.listen.finished_title') }}
      </h5>
      <p
        v-if="!stopped"
        class="text-body-1 mb-0"
      >
        {{ t('jlptTest.listen.finished_body') }}
      </p>
    </div>

    <template v-else-if="step">
      <!-- Status: soal ke-n, indikator audio, hitung mundur -->
      <div class="jl-status">
        <div class="jl-status__left">
          <span
            class="jl-eq"
            :class="{ 'jl-eq--on': phase === 'playing' && !blocked && !audioError }"
            aria-hidden="true"
          ><i /><i /><i /><i /></span>
          <span
            class="font-weight-medium"
            lang="ja"
          >
            <template v-if="step.kind === 'start'">ちょうかい</template>
            <template v-else-if="step.kind === 'rest'">きゅうけい</template>
            <template v-else-if="step.kind === 'end'">おわり</template>
            <template v-else>
              もんだい {{ step.mondai }}<template v-if="step.kind === 'question'"> · {{ step.q.no }}ばん</template>
            </template>
          </span>
          <span
            v-if="step.kind === 'question'"
            class="text-caption text-medium-emphasis"
          >
            {{ t('jlptTest.listen.question_of', { n: answeredIndex, total }) }}
          </span>
        </div>
        <div class="text-caption text-medium-emphasis">
          <template v-if="phase === 'playing' && !audioError">
            {{ t('jlptTest.listen.playing') }}
          </template>
          <template v-else-if="phase === 'waiting' && !manual">
            {{ t('jlptTest.listen.next_in', { n: countdown }) }}
          </template>
        </div>
      </div>
      <VProgressLinear
        :model-value="progress * 100"
        color="primary"
        height="4"
        rounded
        class="mb-4"
      />

      <VAlert
        v-if="audioError"
        type="error"
        variant="tonal"
        class="mb-4"
      >
        {{ t('jlptTest.listen.audio_missing', { file: audioError }) }}
      </VAlert>

      <VAlert
        v-if="blocked"
        type="info"
        variant="tonal"
        class="mb-4"
      >
        {{ t('jlptTest.listen.tap_to_play') }}
        <template #append>
          <VBtn
            size="small"
            color="primary"
            prepend-icon="tabler-player-play"
            @click="replayBlocked"
          >
            {{ t('jlptTest.listen.start') }}
          </VBtn>
        </template>
      </VAlert>

      <!-- Penjelasan umum / istirahat / penutup: kartu sederhana selama audio berjalan -->
      <div
        v-if="CARD_STEPS[step.kind]"
        class="jl-card text-center"
      >
        <VIcon
          :icon="CARD_STEPS[step.kind]"
          size="48"
          color="primary"
        />
        <h5 class="text-h5 my-3">
          {{ t(`jlptTest.listen.${step.kind}_title`) }}
        </h5>
        <p class="text-body-1 mb-0">
          {{ t(`jlptTest.listen.${step.kind}_body`) }}
        </p>
      </div>

      <!-- Langkah petunjuk: teks もんだい + れい (contoh) -->
      <div
        v-else-if="step.kind === 'intro'"
        class="jl-paper"
      >
        <div class="jl-mondai">
          <span
            class="jl-mondai__tag"
            lang="ja"
          >もんだい {{ step.mondai }}</span>
          <span class="jl-mondai__text"><JlptText :text="step.m.instruction" /></span>
        </div>
        <div class="text-caption text-medium-emphasis mt-2 mb-4">
          {{ t(`jlptTest.mondai_hint.chokai_${step.mondai}`) }}
        </div>

        <div
          v-if="step.m.example"
          class="jl-example"
        >
          <div
            class="font-weight-bold mb-2"
            lang="ja"
          >
            れい
          </div>
          <JlptListeningItem
            :item="step.m.example"
            :selected="step.m.example.answer"
            readonly
          />
          <div class="text-caption text-medium-emphasis mt-2">
            {{ t('jlptTest.listen.example_answer', { n: step.m.example.answer }) }}
          </div>
        </div>
      </div>

      <!-- Langkah soal -->
      <div
        v-else
        class="jl-paper"
      >
        <div
          class="jl-qno"
          lang="ja"
        >
          {{ step.q.no }}ばん
        </div>
        <JlptListeningItem
          :key="step.q.id"
          :item="step.q"
          :selected="answers[step.q.id] ?? null"
          @pick="n => pick(step.q, n)"
        />
      </div>

      <!-- メモ: もんだい 4 (tanpa gambar) menyediakan ruang catatan -->
      <div
        v-if="step.m?.memo"
        class="jl-memo mt-4"
      >
        <div
          class="text-center text-medium-emphasis"
          lang="ja"
        >
          − メモ −
        </div>
        <VTextarea
          v-model="memo"
          rows="4"
          auto-grow
          variant="outlined"
          hide-details
          :placeholder="t('jlptTest.listen.memo_hint')"
        />
      </div>

      <div
        v-if="phase === 'waiting'"
        class="text-center mt-5"
      >
        <VBtn
          variant="tonal"
          color="primary"
          append-icon="tabler-chevron-right"
          @click="next"
        >
          {{ t('jlptTest.listen.next') }}
        </VBtn>
      </div>
    </template>
  </div>
</template>

<style scoped>
.jl-card {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 12px;
  margin-inline: auto;
  max-inline-size: 560px;
  padding-block: 28px;
  padding-inline: 24px;
}

.jl-rules { padding-inline-start: 1.4em; }

.jl-status {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-block-end: 8px;
}

.jl-status__left { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }

.jl-eq { display: inline-flex; align-items: flex-end; block-size: 18px; gap: 2px; }
.jl-eq i { display: block; border-radius: 1px; background: rgb(var(--v-theme-primary)); block-size: 5px; inline-size: 3px; opacity: 0.45; }
.jl-eq--on i { animation: jl-bounce 0.9s ease-in-out infinite; opacity: 1; }
.jl-eq--on i:nth-child(2) { animation-delay: 0.15s; }
.jl-eq--on i:nth-child(3) { animation-delay: 0.3s; }
.jl-eq--on i:nth-child(4) { animation-delay: 0.45s; }

@keyframes jl-bounce {
  0%,
 100% { block-size: 4px; }
  50% { block-size: 18px; }
}

.jl-paper {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 10px;
  background: rgb(var(--v-theme-surface));
  padding-block: 20px 24px;
  padding-inline: 24px;
}

.jl-mondai { display: flex; align-items: baseline; font-weight: 700; gap: 12px; line-height: 1.9; }
.jl-mondai__tag { flex: none; }
.jl-mondai__text { min-inline-size: 0; }

.jl-example {
  border-block-start: 1px solid rgba(var(--v-theme-on-surface), 0.25);
  padding-block-start: 12px;
}

.jl-qno {
  display: inline-block;
  border: 1.5px solid currentcolor;
  border-radius: 4px;
  font-size: 1.2rem;
  font-weight: 700;
  margin-block-end: 12px;
  padding-block: 2px;
  padding-inline: 12px;
}

@media (prefers-reduced-motion: reduce) {
  .jl-eq--on i { animation: none; block-size: 12px; }
}

@media (max-width: 599px) {
  .jl-paper { padding-block: 14px 18px; padding-inline: 12px; }
  .jl-card { padding-block: 20px; padding-inline: 14px; }
}
</style>
