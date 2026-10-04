<script setup>
// Kaiwa (会話) — latihan percakapan bergaya chat.
//
// Alur: lawan bicara "berbicara" (audio Google TTS, MP3 dibuat oleh
// `php artisan kaiwa:generate-audio`), lalu giliran user: kalimat target tampil di
// gelembung kanan, mikrofon AKTIF OTOMATIS (bisa dimatikan lewat "Mic otomatis")
// dan user mengucapkannya. Peringatan (suara kurang keras, tidak terdengar, dst.)
// DIUCAPKAN dalam bahasa Jepang lewat public/data/kaiwa/alerts.json. Penilaian LONGGAR
// (kemiripan teks, lihat utils/kaiwaMatch.js) dan TIDAK mengunci kemajuan:
//  - lolos (skor >= PASS_SCORE) -> lanjut otomatis;
//  - gagal -> boleh coba lagi; setelah 2 kali gagal tombol "Lanjut" terbuka;
//  - browser tanpa pengenalan suara (mis. banyak iPhone) -> mode "nilai sendiri".
//
// Data: public/data/kaiwa/index.json (daftar pelajaran yang tersedia) dan
// public/data/kaiwa/lesson-{n}.json. Teks dengan penanda 漢字《かんじ》
// ditampilkan sebagai furigana lewat RubyText.
//
// Progres/XP: tiap skenario yang ditamatkan dikirim ke /api/kaiwa/progress/{id skenario}
// (10 XP pertama kali selesai, +5 bila semua giliran lolos pada percobaan pertama).
//
// Tukar peran WAJIB: satu skenario selalu terdiri dari 2 putaran.
// Putaran 1 = alur biasa (user menjawab). Putaran 2 = peran dibalik: user jadi penanya dan
// bicara duluan (baris `partner` di data diucapkan user), sedangkan baris `you` diputar
// sebagai suara lawan bicara. Skenario baru dianggap tamat setelah putaran 2.
import RubyText from '@/components/learning/RubyText.vue'
import { useAuthStore } from '@/stores/auth'
import { $api } from '@/utils/api'
import { PASS_SCORE, registerReadings, scoreSpeech, stripRuby } from '@/utils/kaiwaMatch'

const MAX_TRIES_BEFORE_SKIP = 2
// Dipakai bila index.json belum ada (pemasangan lama): hanya Pelajaran 1.
const FALLBACK_MANIFEST = [{ id: 1, file: 'lesson-1.json', level: 'N5' }]

const { t, locale } = useI18n()
const authStore = useAuthStore()

// Field data dwibahasa: `x_en` bila locale en dan tersedia, selain itu `x_id`/`x`.
const tr = (o, key) => (locale.value === 'en' && o[`${key}_en`]) || o[`${key}_id`] || o[key] || ''

const manifest = ref([]) // pelajaran yang tersedia (index.json)
const lessonId = ref(null) // pelajaran yang sedang dipilih
const lesson = ref(null)
const loading = ref(true)
const loadError = ref(false)

// Progres server-backed: { [id skenario]: { done, crown } }
const progress = reactive({})

// Kunci pelajaran: Pelajaran n+1 terbuka hanya bila SEMUA skenario Pelajaran n (dan sebelumnya)
// sudah selesai (progress.done). Daftar id skenario tiap pelajaran dimuat dari berkas materi.
const lessonScenarioIds = reactive({}) // { [id pelajaran]: [id skenario] }
const gateReady = ref(false) // false sampai materi + progres termuat → semua pelajaran selain pertama terkunci
const earnedXp = ref(0)

const scenario = ref(null) // skenario yang sedang dimainkan
const messages = ref([]) // gelembung chat yang sudah muncul
const turnIdx = ref(0)
const TOTAL_ROUNDS = 2 // wajib: skenario baru tamat setelah kedua peran dimainkan
const round = ref(1) // putaran: 1 = user menjawab, 2 = peran dibalik (user jadi penanya)
const phase = ref('idle') // idle | partner | you | listening | result | swap | finished
const tries = ref(0)
const heardText = ref('')
const lastScore = ref(0)
const micError = ref('')
const stats = reactive({ passedFirst: 0, passed: 0, skipped: 0, total: 0 })

const showFurigana = ref(true)
const showTranslation = ref(true)
const listenMode = ref(false) // teks lawan bicara disembunyikan sampai diketuk "lihat teks"
const autoMic = ref(true) // mic menyala sendiri saat giliran user
const alerts = ref({}) // peringatan suara (alerts.json)
const alertKey = ref('') // peringatan yang sedang ditampilkan
const level = ref(0) // 0..1, level suara mic langsung (hanya desktop)

// Pengukur level mic memakai getUserMedia berdampingan dengan SpeechRecognition.
// Di ponsel keduanya bisa saling berebut mikrofon, jadi pengukur hanya di desktop.
const USE_METER = typeof navigator !== 'undefined' && !!navigator.mediaDevices?.getUserMedia
  && !/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)
const QUIET_LEVEL = 0.03 // RMS puncak di bawah ini dianggap "suara kurang keras"

const chatEl = ref(null)

const SR = typeof window !== 'undefined' ? (window.SpeechRecognition || window.webkitSpeechRecognition) : null
const canRecognize = !!SR

const turn = computed(() => scenario.value?.turns[turnIdx.value] ?? null)

// Peran efektif satu giliran. Putaran 2: peran dibalik.
const roleOf = t => (round.value === 2 ? (t.who === 'partner' ? 'you' : 'partner') : t.who)
const partnerName = computed(() => scenario.value?.partner?.name ?? '')
const youName = computed(() => scenario.value?.you?.name ?? '')

// Tokoh yang diperankan user / diputar aplikasi pada putaran sekarang.
const userName = computed(() => (round.value === 2 ? partnerName.value : youName.value))
const appName = computed(() => (round.value === 2 ? youName.value : partnerName.value))
const passPercent = Math.round(PASS_SCORE * 100)

/** Id pelajaran pertama (urutan manifest) yang belum tuntas dan menghalangi `id`; null bila tidak terkunci. */
function blockerOf(id) {
  // Admin boleh membuka semua pelajaran (untuk uji materi).
  if (authStore.isAdmin)
    return null

  const idx = manifest.value.findIndex(x => x.id === id)

  if (idx <= 0)
    return null
  if (!gateReady.value)
    return manifest.value[0].id

  for (const prev of manifest.value.slice(0, idx)) {
    const ids = lessonScenarioIds[prev.id]

    if (ids?.length && !ids.every(k => progress[k]?.done))
      return prev.id
  }

  return null
}

const isLocked = id => blockerOf(id) !== null

// Petunjuk di bawah chip: pelajaran terkunci pertama + pelajaran yang harus diselesaikan.
const lockHint = computed(() => {
  for (const m of manifest.value) {
    const b = blockerOf(m.id)

    if (b !== null)
      return t('kaiwa.locked_hint', { n: b, next: m.id })
  }

  return ''
})

async function loadLessonIds() {
  await Promise.all(manifest.value.map(async (m) => {
    try {
      const res = await fetch(`/data/kaiwa/${m.file}`, { cache: 'no-cache' })

      if (res.ok)
        lessonScenarioIds[m.id] = ((await res.json()).scenarios ?? []).map(sc => sc.id)
    }
    catch { /* gagal muat: pelajaran ini tidak dianggap menghalangi */ }
  }))
}

async function selectLesson(id) {
  if (isLocked(id))
    return

  const entry = manifest.value.find(x => x.id === id) ?? manifest.value[0]

  loading.value = true
  loadError.value = false
  try {
    if (!entry)
      throw new Error('no lesson')
    const res = await fetch(`/data/kaiwa/${entry.file}`, { cache: 'no-cache' })

    if (!res.ok)
      throw new Error(`HTTP ${res.status}`)
    lesson.value = await res.json()
    // bacaan kanji dari furigana materi → dipakai penilaian (pengenal suara sering menulis kanji)
    for (const sc of lesson.value.scenarios ?? []) {
      for (const tn of sc.turns ?? [])
        registerReadings(tn.ja)
    }
    lessonId.value = entry.id
  }
  catch {
    lessonId.value = entry?.id ?? null
    loadError.value = true
  }
  loading.value = false
}

async function loadProgress() {
  try {
    const res = await $api('/kaiwa/progress')

    Object.assign(progress, res.progress ?? {})
  }
  catch { /* offline: lanjut dengan progres kosong */ }
}

function statusOf(id) {
  const p = progress[id]

  return p?.crown ? 'mastered' : p?.done ? 'completed' : 'available'
}

/** Skenario tamat: simpan progres + XP (best-effort, tidak boleh mengganggu layar hasil). */
function finishScenario() {
  const key = scenario.value.id
  const perfect = stats.total > 0 && stats.passedFirst === stats.total && stats.skipped === 0
  const prev = progress[key]

  progress[key] = { done: true, crown: perfect || !!prev?.crown }
  $api(`/kaiwa/progress/${encodeURIComponent(key)}`, {
    method: 'POST',
    body: { perfect },
  }).then((res) => {
    earnedXp.value = res?.xp ?? 0
  }).catch(() => {})
}

onMounted(async () => {
  try {
    const res = await fetch('/data/kaiwa/index.json', { cache: 'no-cache' })

    if (!res.ok)
      throw new Error(`HTTP ${res.status}`)
    manifest.value = (await res.json()).lessons ?? FALLBACK_MANIFEST
  }
  catch {
    manifest.value = FALLBACK_MANIFEST
  }
  await selectLesson(manifest.value[0]?.id)
  await Promise.all([loadLessonIds(), loadProgress()])
  gateReady.value = true

  // Peringatan suara: opsional. Bila gagal dimuat, peringatan hanya berupa teks.
  try {
    const r = await fetch('/data/kaiwa/alerts.json', { cache: 'no-cache' })

    if (r.ok)
      alerts.value = await r.json()
  }
  catch { /* abaikan */ }
})

onBeforeUnmount(() => {
  chatObserver?.disconnect()
  stopAll()
})

// ── Audio ────────────────────────────────────────────────────────────
let audioEl = null
let recog = null
let token = 0 // membatalkan callback lama saat langkah berganti

function stopAudio() {
  if (audioEl) {
    audioEl.onended = null
    audioEl.onerror = null
    audioEl.pause()
    audioEl = null
  }
  window.speechSynthesis?.cancel()
}

function stopAll() {
  token++
  stopMeter()
  stopAudio()
  try { recog?.abort() }
  catch { /* abaikan */ }
  recog = null
}

/** Putar audio satu giliran; bila MP3 belum ada, pakai suara browser (ja-JP). */
function speak(t, onDone) {
  stopAudio()
  const mine = token
  const done = () => mine === token && onDone?.()
  let usedFallback = false
  const fallback = () => {
    if (usedFallback)
      return
    usedFallback = true
    const synth = window.speechSynthesis

    if (!synth) {
      setTimeout(done, 1200)

      return
    }
    const u = new SpeechSynthesisUtterance(t.speak)

    u.lang = 'ja-JP'
    u.rate = 0.9
    u.onend = done
    u.onerror = done
    synth.speak(u)
  }

  if (!t.audio) {
    fallback()

    return
  }
  audioEl = new Audio(t.audio)
  audioEl.onended = done
  audioEl.onerror = fallback
  audioEl.play().catch(fallback)
}

// ── Level mic (desktop) ──────────────────────────────────────────────
let meter = null
let peakLevel = 0
let meterOk = false // pengukur benar-benar berjalan (izin mic diberikan)
const hardError = ref(false) // mic/jaringan bermasalah -> pindah ke mode nilai sendiri

function stopMeter() {
  if (meter) {
    cancelAnimationFrame(meter.raf)
    meter.stream.getTracks().forEach(trk => trk.stop())
    meter.ctx.close().catch(() => {})
    meter = null
  }
  level.value = 0
}

async function startMeter(mine) {
  stopMeter()
  peakLevel = 0
  meterOk = false
  if (!USE_METER)
    return
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })

    if (mine !== token || phase.value !== 'listening') {
      stream.getTracks().forEach(trk => trk.stop())

      return
    }
    const ctx = new (window.AudioContext || window.webkitAudioContext)()
    const an = ctx.createAnalyser()

    an.fftSize = 1024
    ctx.createMediaStreamSource(stream).connect(an)
    const buf = new Float32Array(an.fftSize)
    const m = { stream, ctx, raf: 0 }
    const tick = () => {
      an.getFloatTimeDomainData(buf)
      let sum = 0

      for (let i = 0; i < buf.length; i++)
        sum += buf[i] * buf[i]
      const rms = Math.sqrt(sum / buf.length)

      level.value = Math.min(1, rms * 6)
      peakLevel = Math.max(peakLevel, rms)
      m.raf = requestAnimationFrame(tick)
    }

    meter = m
    meterOk = true
    tick()
  }
  catch { meter = null }
}

// ── Peringatan suara berbahasa Jepang ────────────────────────────────
const alertInfo = computed(() => alerts.value[alertKey.value] ?? null)

/** Tampilkan peringatan (teks) dan ucapkan; lalu panggil onDone. */
function raiseAlert(key, onDone) {
  alertKey.value = key
  const a = alerts.value[key]

  if (!a) {
    onDone?.()

    return
  }
  speak({ audio: a.audio, speak: a.speak }, onDone)
}

/** Mic menyala sendiri bila diizinkan dan masih ada kesempatan. */
function autoListen(delay = 400) {
  if (!autoMic.value || !canRecognize)
    return
  const mine = token

  setTimeout(() => {
    if (mine === token && phase.value === 'you' && tries.value < MAX_TRIES_BEFORE_SKIP)
      startListening()
  }, delay)
}

/** Gagal menangkap/mencocokkan: peringatan suara, lalu (bila boleh) coba lagi otomatis. */
function failTurn(key, { retry = true } = {}) {
  phase.value = 'you'
  raiseAlert(key, () => retry && autoListen(200))
}

// ── Alur percakapan ──────────────────────────────────────────────────
// Obrolan mengikuti gelembung terbaru selama user tidak sedang menggulung ke atas.
// Panel suara di bawah obrolan berubah tinggi (teks "Terdengar", peringatan, bar level) → kotak
// obrolan ikut menyusut; tanpa ini gelembung terbaru terpotong di dasar kotak.
let stickBottom = true

function onChatScroll() {
  const el = chatEl.value

  if (el)
    stickBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 80
}

function scrollDown() {
  stickBottom = true
  nextTick(() => {
    const el = chatEl.value

    if (el)
      el.scrollTop = el.scrollHeight
  })
}

let chatObserver = null

watch(chatEl, (el) => {
  chatObserver?.disconnect()
  chatObserver = null
  if (!el || typeof ResizeObserver === 'undefined')
    return
  chatObserver = new ResizeObserver(() => {
    if (stickBottom)
      el.scrollTop = el.scrollHeight
  })
  chatObserver.observe(el)
})

function start(sc) {
  stopAll()
  scenario.value = sc
  messages.value = []
  turnIdx.value = 0
  round.value = 1
  earnedXp.value = 0

  // SEMUA giliran dibicarakan user satu kali: putaran 1 = baris `you`, putaran 2 = baris `partner`.
  Object.assign(stats, { passedFirst: 0, passed: 0, skipped: 0, total: sc.turns.length })
  runTurn()
}

/** Putaran 2: peran dibalik, user jadi penanya dan bicara duluan. */
function startSecondRound() {
  stopAll()
  round.value = 2
  messages.value = []
  turnIdx.value = 0
  runTurn()
}

function backToList() {
  stopAll()
  scenario.value = null
  phase.value = 'idle'
}

function runTurn() {
  const t = turn.value

  if (!t) {
    if (round.value < TOTAL_ROUNDS) {
      // putaran 1 selesai -> tunggu user siap untuk gantian peran
      phase.value = 'swap'
      scrollDown()

      return
    }
    phase.value = 'finished'
    finishScenario()
    scrollDown()

    return
  }
  tries.value = 0
  heardText.value = ''
  micError.value = ''
  alertKey.value = ''
  hardError.value = false
  lastScore.value = 0

  if (roleOf(t) === 'partner') {
    phase.value = 'partner'
    messages.value.push({ key: t.id, who: 'partner', name: appName.value, turn: t, revealed: false })
    scrollDown()
    speak(t, advance)
  }
  else {
    phase.value = 'you'
    messages.value.push({ key: t.id, who: 'you', name: userName.value, turn: t, state: 'pending' })
    scrollDown()
    autoListen(600)
  }
}

function advance() {
  const mine = token

  turnIdx.value++
  setTimeout(() => mine === token && runTurn(), 500)
}

function playExample() {
  if (!turn.value)
    return
  // hentikan mic dulu supaya suara contoh tidak terekam, lalu nyalakan lagi
  token++
  stopMeter()
  try { recog?.abort() }
  catch { /* abaikan */ }
  phase.value = 'you'
  speak(turn.value, () => autoListen(300))
}

function pass(first) {
  stopMeter()
  const m = messages.value.find(x => x.key === turn.value.id)

  if (m)
    m.state = 'ok'
  stats.passed++
  if (first)
    stats.passedFirst++
  phase.value = 'result'
  setTimeout(advance, 900)
}

function skip() {
  const m = messages.value.find(x => x.key === turn.value.id)

  if (m)
    m.state = 'skipped'
  stats.skipped++
  advance()
}

// ── Pengenalan suara ─────────────────────────────────────────────────
// Mode kontinu: browser TIDAK lagi memutus pengenalan pada jeda pertama. Hasil
// (sementara + final) dikumpulkan; ucapan dianggap selesai bila user diam selama
// SILENCE_MS, menekan tombol berhenti, atau teks sudah sangat cocok (EARLY_PASS).
const SILENCE_MS = 1500 // diam setelah ucapan terakhir -> dianggap selesai (naikkan bila masih terpotong)
const MAX_LISTEN_MS = 20000 // batas keras satu kali merekam
const EARLY_PASS = 0.9 // sudah semirip ini -> berhenti sendiri tanpa menunggu diam

function startListening() {
  if (!SR || phase.value === 'listening')
    return
  stopAudio()
  micError.value = ''
  alertKey.value = ''
  heardText.value = ''
  const mine = ++token
  const r = new SR()
  let segments = [] // [{ alts: [...], final }] — semua hasil sesi ini, berurutan
  let evaluated = false
  let errored = false
  let silenceTimer = null
  let maxTimer = null

  const clearTimers = () => {
    clearTimeout(silenceTimer)
    clearTimeout(maxTimer)
  }
  const stopSoon = () => {
    try { r.stop() }
    catch { /* abaikan */ }
  }

  // Gabungkan potongan menjadi beberapa varian kalimat (alternatif ke-1, ke-2, ke-3 tiap potongan).
  // Chrome Android kadang mengirim potongan kumulatif (potongan berikutnya memuat yang sebelumnya):
  // potongan yang menjadi awalan potongan sesudahnya dibuang agar tidak terhitung ganda.
  const variants = () => {
    const segs = segments.filter((sg, i) => {
      const nxt = segments[i + 1]

      return !(nxt && sg.alts[0] && nxt.alts[0]?.startsWith(sg.alts[0]))
    })
    const out = []

    for (let k = 0; k < 3; k++)
      out.push(segs.map(sg => sg.alts[k] ?? sg.alts[0] ?? '').join(''))

    return [...new Set(out.filter(Boolean))]
  }

  r.lang = 'ja-JP'
  r.continuous = true
  r.interimResults = true
  r.maxAlternatives = 5
  r.onresult = (e) => {
    if (mine !== token)
      return
    segments = Array.from(e.results).map(res => ({
      alts: Array.from(res).map(a => a.transcript),
      final: res.isFinal,
    }))
    const v = variants()

    heardText.value = v[0] ?? '' // tampil langsung selagi user bicara
    clearTimeout(silenceTimer)
    if (v.length && scoreSpeech(v, turn.value) >= EARLY_PASS) {
      stopSoon()

      return
    }
    silenceTimer = setTimeout(stopSoon, SILENCE_MS)
  }
  r.onerror = (e) => {
    if (mine !== token)
      return
    // "aborted" = kita sendiri yang menghentikan; "no-speech" setelah ada ucapan = biarkan onend menilai.
    if (e.error === 'aborted' || (e.error === 'no-speech' && segments.length))
      return
    errored = true // error sudah ditangani di sini (onend tidak menilai lagi)
    clearTimers()
    const quiet = meterOk && peakLevel < QUIET_LEVEL

    stopMeter()
    const map = {
      'not-allowed': ['err_not_allowed', 'mic_denied', false],
      'service-not-allowed': ['err_service', 'mic_denied', false],
      'audio-capture': ['err_capture', 'mic_denied', false],
      'network': ['err_network', 'network', false],
      // tanpa suara terdeteksi: dianggap suara kurang keras (atau mic tidak menangkap)
      'no-speech': ['err_no_speech', quiet || !meterOk ? 'low_volume' : 'no_speech', true],
    }
    const [msg, key, retry] = map[e.error] ?? [null, 'not_match', false]

    micError.value = msg ? t(`kaiwa.${msg}`) : t('kaiwa.err_generic', { code: e.error })
    if (e.error === 'no-speech')
      tries.value++
    else if (!retry)
      hardError.value = true
    failTurn(key, { retry: retry && tries.value < MAX_TRIES_BEFORE_SKIP })
  }
  r.onend = () => {
    if (mine !== token)
      return
    clearTimers()
    if (evaluated || errored)
      return
    evaluated = true
    const quiet = meterOk && peakLevel < QUIET_LEVEL

    stopMeter()
    const v = variants()

    if (!v.length) {
      micError.value = t('kaiwa.err_no_speech')
      tries.value++
      failTurn(meterOk && peakLevel >= QUIET_LEVEL ? 'no_speech' : 'low_volume', { retry: tries.value < MAX_TRIES_BEFORE_SKIP })

      return
    }
    heardText.value = v[0]
    lastScore.value = scoreSpeech(v, turn.value)
    tries.value++
    if (lastScore.value >= PASS_SCORE)
      pass(tries.value === 1)
    else
      failTurn(quiet ? 'low_volume' : 'not_match', { retry: tries.value < MAX_TRIES_BEFORE_SKIP })
  }
  recog = r
  phase.value = 'listening'
  try {
    r.start()
    maxTimer = setTimeout(stopSoon, MAX_LISTEN_MS)
    startMeter(mine)
  }
  catch {
    phase.value = 'you'
    micError.value = t('kaiwa.err_start')
  }
}

function stopListening() {
  try { recog?.stop() }
  catch { /* abaikan */ }
}

/** Mode tanpa pengenalan suara: user mengucapkan sendiri lalu menilai diri. */
function selfRated() {
  tries.value++
  pass(tries.value === 1)
}

const manualOnly = computed(() => !canRecognize || hardError.value)
const canSkip = computed(() => tries.value >= MAX_TRIES_BEFORE_SKIP)
const stars = computed(() => {
  const r = stats.total ? stats.passedFirst / stats.total : 0

  return r >= 0.8 ? 3 : r >= 0.5 ? 2 : 1
})
</script>

<template>
  <div
    class="kaiwa"
    :class="scenario && 'kaiwa--chat'"
  >
    <template v-if="!scenario">
      <h4 class="text-h4 mb-3">
        {{ t('kaiwa.title') }}
      </h4>
      <div
        v-if="manifest.length > 1"
        class="d-flex flex-wrap gap-2 mb-4"
      >
        <VChip
          v-for="m in manifest"
          :key="m.id"
          :color="m.id === lessonId ? 'primary' : undefined"
          :variant="m.id === lessonId ? 'flat' : 'tonal'"
          :disabled="isLocked(m.id)"
          :prepend-icon="isLocked(m.id) ? 'tabler-lock' : undefined"
          @click="selectLesson(m.id)"
        >
          {{ t('kaiwa.lesson_n', { n: m.id }) }}
        </VChip>
      </div>
      <p
        v-if="manifest.length > 1 && lockHint"
        class="text-caption text-medium-emphasis mb-4"
      >
        <VIcon
          icon="tabler-lock"
          size="14"
          class="me-1"
        />
        {{ lockHint }}
      </p>
    </template>

    <div
      v-if="loading"
      class="text-center pa-10"
    >
      <VProgressCircular indeterminate />
    </div>

    <VAlert
      v-else-if="loadError"
      type="error"
      variant="tonal"
    >
      {{ t('kaiwa.load_error') }}
    </VAlert>

    <!-- ── Daftar skenario ─────────────────────────────────────────── -->
    <template v-else-if="!scenario">
      <p class="text-body-1 text-medium-emphasis mb-6">
        {{ t('kaiwa.subtitle', { lesson: tr(lesson, 'title'), level: lesson.level }) }}
      </p>

      <VAlert
        v-if="!canRecognize"
        type="info"
        variant="tonal"
        class="mb-6"
      >
        {{ t('kaiwa.no_recognition') }}
      </VAlert>

      <VRow>
        <VCol
          v-for="sc in lesson.scenarios"
          :key="sc.id"
          cols="12"
          md="6"
        >
          <VCard
            class="h-100"
            hover
            @click="start(sc)"
          >
            <VCardText>
              <div class="d-flex align-center gap-2 mb-1">
                <div class="text-h6 flex-grow-1">
                  {{ tr(sc, 'title') }}
                </div>
                <VIcon
                  v-if="statusOf(sc.id) === 'mastered'"
                  icon="tabler-crown"
                  color="warning"
                  size="20"
                />
                <VIcon
                  v-else-if="statusOf(sc.id) === 'completed'"
                  icon="tabler-circle-check-filled"
                  color="success"
                  size="20"
                />
              </div>
              <p class="text-body-2 mb-3">
                {{ tr(sc, 'setting') }}
              </p>
              <div class="d-flex flex-wrap gap-2 mb-2">
                <VChip
                  v-for="p in sc.patterns"
                  :key="p"
                  size="small"
                  variant="tonal"
                >
                  {{ p }}
                </VChip>
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ tr(sc, 'goal') }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>

    <!-- ── Chat ────────────────────────────────────────────────────── -->
    <template v-else>
      <div class="d-flex align-center gap-2 mb-3">
        <VBtn
          icon="tabler-arrow-left"
          variant="text"
          @click="backToList"
        />
        <div class="flex-grow-1">
          <div class="text-h6">
            {{ tr(scenario, 'title') }}
          </div>
          <div class="text-caption text-medium-emphasis">
            {{ t('kaiwa.you_play', { name: userName }) }} · {{ scenario.patterns.join(' / ') }}
          </div>
        </div>
        <VChip
          size="small"
          color="primary"
          variant="tonal"
        >
          {{ t('kaiwa.round_n', { n: round, total: TOTAL_ROUNDS }) }}
        </VChip>
      </div>

      <VAlert
        variant="tonal"
        color="primary"
        density="compact"
        class="mb-3"
      >
        {{ tr(scenario, 'setting') }}
      </VAlert>

      <div class="d-flex flex-wrap gap-4 mb-3">
        <VSwitch
          v-model="showFurigana"
          :label="t('kaiwa.furigana')"
          density="compact"
          hide-details
        />
        <VSwitch
          v-model="showTranslation"
          :label="t('kaiwa.translation')"
          density="compact"
          hide-details
        />
        <VSwitch
          v-model="listenMode"
          :label="t('kaiwa.listen_mode')"
          density="compact"
          hide-details
        />
        <VSwitch
          v-if="canRecognize"
          v-model="autoMic"
          :label="t('kaiwa.auto_mic')"
          density="compact"
          hide-details
        />
      </div>

      <div
        ref="chatEl"
        class="kaiwa-chat"
        @scroll="onChatScroll"
      >
        <div
          v-for="m in messages"
          :key="m.key"
          class="kaiwa-row"
          :class="m.who === 'you' ? 'kaiwa-row--you' : 'kaiwa-row--partner'"
        >
          <div
            v-if="m.who === 'partner'"
            class="kaiwa-name"
          >
            {{ m.name }}
          </div>

          <div
            class="kaiwa-bubble"
            :class="[
              m.who === 'you' ? 'kaiwa-bubble--you' : 'kaiwa-bubble--partner',
              m.state === 'pending' && 'kaiwa-bubble--pending',
              m.state === 'ok' && 'kaiwa-bubble--ok',
              m.state === 'skipped' && 'kaiwa-bubble--skipped',
            ]"
          >
            <template v-if="m.who === 'partner' && listenMode && !m.revealed">
              <VIcon
                icon="tabler-volume"
                size="20"
              />
              <span class="text-body-2"> {{ t('kaiwa.hidden_text') }} </span>
              <VBtn
                size="x-small"
                variant="text"
                @click="m.revealed = true"
              >
                {{ t('kaiwa.show_text') }}
              </VBtn>
            </template>
            <template v-else>
              <div class="kaiwa-ja">
                <RubyText
                  :text="m.turn.ja"
                  :show-furigana="showFurigana"
                />
              </div>
              <div
                v-if="showTranslation"
                class="kaiwa-id"
              >
                {{ locale === 'en' && m.turn.en_text ? m.turn.en_text : m.turn.id_text }}
              </div>
              <VBtn
                v-if="m.who === 'partner'"
                size="x-small"
                variant="text"
                prepend-icon="tabler-rotate"
                @click="speak(m.turn)"
              >
                {{ t('kaiwa.replay') }}
              </VBtn>
            </template>
          </div>
        </div>

        <div
          v-if="phase === 'swap'"
          class="kaiwa-done"
        >
          <VIcon
            icon="tabler-arrows-exchange"
            size="48"
            color="primary"
          />
          <div class="text-h6 my-2">
            {{ t('kaiwa.swap_title') }}
          </div>
          <div class="text-body-2 mb-3">
            {{ t('kaiwa.swap_text', { name: partnerName }) }}
          </div>
          <VBtn
            color="primary"
            prepend-icon="tabler-player-play"
            @click="startSecondRound"
          >
            {{ t('kaiwa.swap_start') }}
          </VBtn>
        </div>

        <div
          v-if="phase === 'finished'"
          class="kaiwa-done"
        >
          <VIcon
            icon="tabler-circle-check"
            size="48"
            color="success"
          />
          <div class="text-h6 my-2">
            {{ t('kaiwa.finished') }}
          </div>
          <div class="mb-1">
            <VIcon
              v-for="n in 3"
              :key="n"
              :icon="n <= stars ? 'tabler-star-filled' : 'tabler-star'"
              color="warning"
            />
          </div>
          <div class="text-body-2 mb-3">
            {{ t('kaiwa.summary', { first: stats.passedFirst, total: stats.total, skipped: stats.skipped }) }}
          </div>
          <VChip
            v-if="earnedXp > 0"
            color="success"
            size="small"
            class="mb-3"
          >
            {{ t('kaiwa.xp_earned', { xp: earnedXp }) }}
          </VChip>
          <VBtn
            variant="tonal"
            class="me-2"
            @click="start(scenario)"
          >
            {{ t('kaiwa.again') }}
          </VBtn>
          <VBtn
            color="primary"
            @click="backToList"
          >
            {{ t('kaiwa.other') }}
          </VBtn>
        </div>
      </div>

      <!-- Panel giliran user -->
      <VCard
        v-if="phase === 'you' || phase === 'listening' || phase === 'result'"
        class="kaiwa-panel mt-3"
        variant="tonal"
      >
        <VCardText>
          <div class="text-caption mb-1">
            {{ t('kaiwa.your_turn') }}
          </div>

          <div
            v-if="heardText"
            class="mb-2"
          >
            <span class="text-caption text-medium-emphasis">{{ t('kaiwa.heard') }} </span>
            <span class="font-weight-medium">{{ heardText }}</span>
            <VChip
              v-if="phase !== 'listening'"
              size="x-small"
              class="ms-2"
              :color="lastScore >= PASS_SCORE ? 'success' : 'warning'"
            >
              {{ t('kaiwa.score', { n: Math.round(lastScore * 100), pass: passPercent }) }}
            </VChip>
          </div>
          <VAlert
            v-if="alertInfo || micError"
            type="warning"
            variant="tonal"
            density="compact"
            class="mb-2"
          >
            <div v-if="alertInfo">
              <div class="kaiwa-ja">
                <RubyText :text="alertInfo.ja" />
              </div>
              <div class="text-caption">
                {{ locale === 'en' && alertInfo.en ? alertInfo.en : alertInfo.id }}
              </div>
            </div>
            <div v-else>
              {{ micError }}
            </div>
          </VAlert>
          <VProgressLinear
            v-if="phase === 'listening' && level > 0"
            :model-value="level * 100"
            color="primary"
            height="6"
            rounded
            class="mb-2"
          />

          <div class="d-flex flex-wrap gap-2">
            <VBtn
              variant="tonal"
              prepend-icon="tabler-volume"
              :disabled="phase === 'result'"
              @click="playExample"
            >
              {{ t('kaiwa.play_example') }}
            </VBtn>

            <template v-if="!manualOnly">
              <VBtn
                v-if="phase !== 'listening'"
                color="primary"
                prepend-icon="tabler-microphone"
                :disabled="phase !== 'you'"
                @click="startListening"
              >
                {{ tries ? t('kaiwa.retry') : t('kaiwa.speak') }}
              </VBtn>
              <VBtn
                v-else
                color="error"
                prepend-icon="tabler-player-stop"
                @click="stopListening"
              >
                {{ t('kaiwa.stop_listening') }}
              </VBtn>
            </template>
            <VBtn
              v-else
              color="primary"
              prepend-icon="tabler-check"
              :disabled="phase !== 'you'"
              @click="selfRated"
            >
              {{ t('kaiwa.self_rated') }}
            </VBtn>

            <VBtn
              v-if="canSkip && !manualOnly"
              variant="text"
              :disabled="phase !== 'you'"
              @click="skip"
            >
              {{ t('kaiwa.skip') }}
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </template>
  </div>
</template>

<style scoped>
.kaiwa-chat {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-block-size: 55vh;
  min-block-size: 200px;
  overflow-y: auto;
  padding: 12px;
  border-radius: 12px;
  background: rgba(var(--v-theme-on-surface), 0.04);
}

.kaiwa-row {
  display: flex;
  flex-direction: column;
  max-inline-size: 85%;
}

.kaiwa-row--partner {
  align-items: flex-start;
}

.kaiwa-row--you {
  align-items: flex-end;
  align-self: flex-end;
}

.kaiwa-name {
  font-size: 0.75rem;
  margin-block-end: 2px;
  opacity: 0.7;
}

.kaiwa-bubble {
  padding: 10px 14px;
  border-radius: 16px;
  overflow-wrap: anywhere;
}

.kaiwa-bubble--partner {
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-end-start-radius: 4px;
}

.kaiwa-bubble--you {
  background: rgb(var(--v-theme-primary));
  border-end-end-radius: 4px;
  color: rgb(var(--v-theme-on-primary));
}

.kaiwa-bubble--pending {
  background: transparent;
  border: 2px dashed rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-primary));
}

.kaiwa-bubble--ok {
  background: rgb(var(--v-theme-success));
  color: rgb(var(--v-theme-on-success));
}

.kaiwa-bubble--skipped {
  opacity: 0.6;
}

.kaiwa-ja {
  font-size: 1.1rem;
  line-height: 1.9;
}

.kaiwa-id {
  font-size: 0.8rem;
  opacity: 0.85;
}

/* Layar percakapan: satu kolom setinggi layar. Kotak obrolan mengisi sisa tinggi dan
   scroll sendiri; panel suara di bawahnya TIDAK menutupi obrolan dan selalu terlihat.
   Sesuaikan --kaiwa-offset (tinggi navbar + padding + footer) bila terlalu pendek/panjang. */
.kaiwa--chat {
  --kaiwa-offset: 150px;

  display: flex;
  flex-direction: column;
  block-size: calc(100dvh - var(--kaiwa-offset));
  min-block-size: 460px;
}

.kaiwa--chat > * {
  flex: none;
}

.kaiwa--chat > .kaiwa-chat {
  flex: 1 1 auto;
  max-block-size: none;
  min-block-size: 120px;
}

.kaiwa-done {
  align-self: center;
  padding: 16px;
  text-align: center;
}
</style>
