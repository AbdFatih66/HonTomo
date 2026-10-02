<script setup>
import { $api } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const { t, locale, te } = useI18n()

const data = ref(null)
const isLoading = ref(true)
const hasError = ref(false)

// Tahap roadmap yang sedang dibuka di panel detail (default: tahap "current").
const selectedKey = ref(null)

// Hari yang diklik di grafik mingguan.
const selectedDay = ref(null)

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    data.value = await $api('/dashboard')
    selectedKey.value = data.value.roadmap.find(s => s.state === 'current')?.key
      ?? data.value.roadmap[0]?.key
    selectedDay.value = data.value.week.find(d => d.is_today)?.date ?? null
    animateNumbers()
    customExam.value = data.value.jlpt_exam_date ?? ''
    syncLegacyExam()
    checkNewBadges()
  }
  catch {
    hasError.value = true
  }
  finally {
    isLoading.value = false
  }

  loadPacks()
}

// Paket Tes JLPT bersifat pelengkap: kalau gagal dimuat, dasbor tetap tampil.
const packs = ref([])

async function loadPacks() {
  try {
    packs.value = (await $api('/jlpt-test/packs'))?.packs ?? []
  }
  catch {
    packs.value = []
  }
}

// ── Angka yang menghitung naik saat dasbor dibuka ─────────────────────────
const shown = reactive({ streak: 0, xp: 0, crowns: 0, goal: 0 })

function animateNumbers() {
  const d = data.value
  const target = { streak: d.streak, xp: d.xp, crowns: d.crowns, goal: goalPercent.value }
  const start = performance.now()
  const duration = 800

  if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
    Object.assign(shown, target)

    return
  }

  const tick = now => {
    const p = Math.min(1, (now - start) / duration)
    const ease = 1 - (1 - p) ** 3

    for (const k of Object.keys(target))
      shown[k] = Math.round(target[k] * ease)
    if (p < 1)
      requestAnimationFrame(tick)
  }

  requestAnimationFrame(tick)
}

// ── Turunan data ──────────────────────────────────────────────────────────
const goalPercent = computed(() => {
  if (!data.value?.daily_goal_target)
    return 0

  return Math.min(100, Math.round((data.value.xp_today / data.value.daily_goal_target) * 100))
})

const goalReached = computed(() => goalPercent.value >= 100)

const roadmap = computed(() => data.value?.roadmap ?? [])
const currentStage = computed(() => roadmap.value.find(s => s.state === 'current'))
const selectedStage = computed(() => roadmap.value.find(s => s.key === selectedKey.value))

// Progres keseluruhan = rata-rata tahap yang punya ukuran progres.
const overallPercent = computed(() => {
  const metered = roadmap.value.filter(s => s.percent !== null)
  if (!metered.length)
    return 0

  return Math.round(metered.reduce((sum, s) => sum + s.percent, 0) / metered.length)
})

const weekMax = computed(() => Math.max(10, ...(data.value?.week ?? []).map(d => d.xp)))
const weekTotal = computed(() => (data.value?.week ?? []).reduce((sum, d) => sum + d.xp, 0))
const activeDays = computed(() => (data.value?.week ?? []).filter(d => d.xp > 0).length)
const pickedDay = computed(() => data.value?.week.find(d => d.date === selectedDay.value))

function weekdayLabel(day) {
  // 2024-01-07 adalah hari Minggu → offset 0..6 memberi nama hari lokal.
  return new Date(2024, 0, 7 + day.weekday)
    .toLocaleDateString(locale.value === 'en' ? 'en-US' : 'id-ID', { weekday: 'short' })
}

function dayLong(day) {
  return new Date(`${day.date}T00:00:00`)
    .toLocaleDateString(locale.value === 'en' ? 'en-US' : 'id-ID', { weekday: 'long', day: 'numeric', month: 'long' })
}

const greeting = computed(() => {
  const h = new Date().getHours()
  const key = h < 11 ? 'morning' : h < 15 ? 'afternoon' : h < 19 ? 'evening' : 'night'

  return t(`dashboard.hello_${key}`, { name: data.value?.name || authStore.user?.name || '' })
})

const nextStep = computed(() => {
  const d = data.value
  if (!d)
    return null

  if (d.next_lesson) {
    return {
      label: d.next_lesson.status === 'in_progress' ? t('dashboard.continue_learning') : t('dashboard.start_learning'),
      title: d.next_lesson.title,
      action: () => router.push({ name: 'learn-id', params: { id: d.next_lesson.id } }),
    }
  }

  return null
})

function sourceLabel(source) {
  const key = `dashboard.source.${source}`

  return te(key) ? t(key) : t('dashboard.source.other')
}

function timeAgo(iso) {
  const diff = Math.max(0, Date.now() - new Date(iso).getTime())
  const min = Math.floor(diff / 60000)
  if (min < 1)
    return t('dashboard.just_now')
  if (min < 60)
    return t('dashboard.minutes_ago', { n: min })

  const hr = Math.floor(min / 60)
  if (hr < 24)
    return t('dashboard.hours_ago', { n: hr })

  return t('dashboard.days_ago', { n: Math.floor(hr / 24) })
}

function openStage(stage) {
  router.push({ name: stage.route })
}


// ── Saran hari ini: aksi cerdas berdasar kondisi belajar user ──────────────
const suggestions = computed(() => {
  const d = data.value
  if (!d)
    return []

  const list = []

  if (d.review_due > 0) {
    list.push({ key: 'review', icon: 'tabler-repeat', color: 'warning', to: { name: 'kosakata' },
      title: t('dashboard.sg_review_title', { n: d.review_due }), body: t('dashboard.sg_review_body') })
  }

  if (!d.streak_active_today && d.streak > 0) {
    list.push({ key: 'streak', icon: 'tabler-flame', color: 'error', to: nextStep.value ? { name: 'learn-id', params: { id: d.next_lesson.id } } : { name: 'learn' },
      title: t('dashboard.sg_streak_title'), body: t('dashboard.sg_streak_body', { n: d.streak }) })
  }

  if (!goalReached.value) {
    list.push({ key: 'goal', icon: 'tabler-target-arrow', color: 'success', to: { name: 'mondaishuu' },
      title: t('dashboard.sg_goal_title', { xp: Math.max(0, d.daily_goal_target - d.xp_today) }), body: t('dashboard.sg_goal_body') })
  }

  const cur = currentStage.value
  if (cur && !list.some(x => x.to.name === cur.route)) {
    list.push({ key: 'stage', icon: cur.icon, color: 'primary', to: { name: cur.route },
      title: t('dashboard.sg_stage_title', { stage: t(`nav.${cur.key}`) }), body: t('dashboard.sg_stage_body') })
  }

  // Sudah banyak tahap selesai → dorong ke simulasi ujian.
  if (overallPercent.value >= 60 && !list.some(x => x.to.name === 'jlpt-test')) {
    list.push({ key: 'exam', icon: 'tabler-certificate', color: 'info', to: { name: 'jlpt-test' },
      title: t('dashboard.sg_exam_title'), body: t('dashboard.sg_exam_body') })
  }

  return list.slice(0, 3)
})

// ── Tips belajar (carousel) & tips lulus JLPT (accordion) ─────────────────
// Teks ada di i18n: dashboard.tip_study.<key>.{title,body,cta}
const STUDY_TIPS = [
  { key: 'kana', icon: 'tabler-language-katakana', route: 'kana' },
  { key: 'short', icon: 'tabler-clock-hour-4', route: 'learn' },
  { key: 'spaced', icon: 'tabler-repeat', route: 'kosakata' },
  { key: 'kanji', icon: 'tabler-writing', route: 'kanji' },
  { key: 'write', icon: 'tabler-writing-sign', route: 'kaite-oboeru' },
  { key: 'listen', icon: 'tabler-headphones', route: 'chokai' },
  { key: 'quiz', icon: 'tabler-pencil-check', route: 'mondaishuu' },
]

const JLPT_TIPS = [
  { key: 'format', icon: 'tabler-list-details', route: 'jlpt-test' },
  { key: 'vocab', icon: 'tabler-notebook', route: 'kosakata' },
  { key: 'grammar', icon: 'tabler-clipboard-list', route: 'lampiran' },
  { key: 'listening', icon: 'tabler-headphones', route: 'chokai' },
  { key: 'time', icon: 'tabler-hourglass', route: 'jlpt-test' },
  { key: 'review', icon: 'tabler-checklist', route: 'jlpt-test' },
  { key: 'final_week', icon: 'tabler-moon-stars', route: 'lampiran' },
]

// Tip awal berganti setiap hari, jadi dasbor terasa segar tiap dibuka.
const dayOfYear = Math.floor((Date.now() - new Date(new Date().getFullYear(), 0, 0)) / 86400000)
const tipIndex = ref(dayOfYear % STUDY_TIPS.length)
const tip = computed(() => STUDY_TIPS[tipIndex.value])

function stepTip(delta) {
  tipIndex.value = (tipIndex.value + delta + STUDY_TIPS.length) % STUDY_TIPS.length
}

const openJlptTip = ref(0)

const readiness = computed(() => {
  const p = overallPercent.value
  const key = p >= 80 ? 'ready' : p >= 50 ? 'almost' : p >= 20 ? 'growing' : 'starting'
  const color = { ready: 'success', almost: 'info', growing: 'warning', starting: 'secondary' }[key]

  return { key, color }
})

// ── Hitung mundur JLPT ─────────────────────────────────────────────────────
// Jadwal resmi (jlpt.jp): 5 Jul 2026 & 6 Des 2026. Di luar tabel ini dipakai
// aturan JLPT: Minggu pertama bulan Juli dan Desember. Kota tertentu hanya
// menggelar salah satunya, jadi user boleh mengisi tanggal ujiannya sendiri.
const OFFICIAL_EXAMS = ['2026-07-05', '2026-12-06']

function firstSunday(year, month) {
  const d = new Date(year, month, 1)

  d.setDate(1 + ((7 - d.getDay()) % 7))

  return d
}

function toIso(d) {
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')

  return `${d.getFullYear()}-${mm}-${dd}`
}

function startOfToday() {
  const n = new Date()

  return new Date(n.getFullYear(), n.getMonth(), n.getDate())
}

function parseIso(iso) {
  const [y, m, d] = iso.split('-').map(Number)

  return new Date(y, m - 1, d)
}

const nextOfficialExam = computed(() => {
  const today = startOfToday()
  const known = OFFICIAL_EXAMS.map(parseIso)
  const y = today.getFullYear()
  const rule = [y, y + 1].flatMap(yy => [firstSunday(yy, 6), firstSunday(yy, 11)])

  return [...known, ...rule].filter(d => d >= today).sort((a, b) => a - b)[0]
})

const customExam = ref('')
const examDialog = ref(false)
const examDraft = ref('')
const savingExam = ref(false)

// Tanggal ujian dulu hanya disimpan di browser. Nilai lama dipindahkan ke
// database satu kali (lihat syncLegacy), lalu kuncinya dihapus.
const legacyExamKey = computed(() => `hontomo.exam_date.${authStore.user?.id ?? 'guest'}`)
const legacyBadgesKey = computed(() => `hontomo.badges_seen.${authStore.user?.id ?? 'guest'}`)

function readLegacy(key) {
  try { return localStorage.getItem(key) }
  catch { return null }
}

function clearLegacy(key) {
  try { localStorage.removeItem(key) }
  catch { /* abaikan */ }
}

async function savePreferences(payload) {
  return await $api('/dashboard/preferences', { method: 'PUT', body: payload })
}

const toast = reactive({ show: false, text: '', color: 'success', icon: 'tabler-award' })

function showToast(text, color = 'success', icon = 'tabler-award') {
  Object.assign(toast, { show: true, text, color, icon })
}

async function saveExam() {
  const v = examDraft.value
  if (!/^\d{4}-\d{2}-\d{2}$/.test(v) || savingExam.value)
    return

  savingExam.value = true
  try {
    const res = await savePreferences({ jlpt_exam_date: v })

    customExam.value = res.jlpt_exam_date ?? ''
    examDialog.value = false
  }
  catch {
    showToast(t('dashboard.cd_save_error'), 'error', 'tabler-alert-circle')
  }
  finally {
    savingExam.value = false
  }
}

async function resetExam() {
  if (savingExam.value)
    return

  savingExam.value = true
  try {
    await savePreferences({ jlpt_exam_date: null })
    customExam.value = ''
    examDialog.value = false
  }
  catch {
    showToast(t('dashboard.cd_save_error'), 'error', 'tabler-alert-circle')
  }
  finally {
    savingExam.value = false
  }
}

function openExamDialog() {
  examDraft.value = customExam.value || toIso(nextOfficialExam.value)
  examDialog.value = true
}

// Tanggal buatan user yang sudah lewat diabaikan → kembali ke jadwal resmi.
const examDate = computed(() => {
  if (customExam.value) {
    const d = parseIso(customExam.value)
    if (d >= startOfToday())
      return d
  }

  return nextOfficialExam.value
})

const isCustomExam = computed(() => !!customExam.value && examDate.value.getTime() === parseIso(customExam.value).getTime())

const daysLeft = computed(() => Math.round((examDate.value - startOfToday()) / 86400000))

const examDateLabel = computed(() => examDate.value.toLocaleDateString(
  locale.value === 'en' ? 'en-US' : 'id-ID',
  { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' },
))

const countdownMsg = computed(() => {
  const n = daysLeft.value
  const key = n === 0 ? 'today' : n <= 7 ? 'last' : n <= 30 ? 'near' : n <= 60 ? 'mid' : 'far'

  return t(`dashboard.cd_msg_${key}`)
})

// Cincin menghitung mundur dari 180 hari terakhir.
const countdownRing = computed(() => Math.max(0, Math.min(100, Math.round((1 - daysLeft.value / 180) * 100))))

// ── Lencana pencapaian ────────────────────────────────────────────────────
const stageOf = key => roadmap.value.find(s => s.key === key)

const badgeMetrics = computed(() => {
  const d = data.value
  if (!d)
    return {}

  return {
    lessons: d.progress.lessons_completed,
    streak: Math.max(d.streak, d.longest_streak),
    xp: d.xp,
    vocab: d.progress.vocabulary_mastered,
    kanji: stageOf('kanji')?.done ?? 0,
    crowns: d.crowns,
    jlpt: stageOf('jlpt_test')?.done ?? 0,
  }
})

// key → teks di i18n: dashboard.badge.<key>.{title,desc}
const BADGE_DEFS = [
  { key: 'first_step', icon: 'tabler-flag', color: 'primary', metric: 'lessons', target: 1, route: 'learn' },
  { key: 'lessons_5', icon: 'tabler-books', color: 'primary', metric: 'lessons', target: 5, route: 'learn' },
  { key: 'lessons_15', icon: 'tabler-school', color: 'primary', metric: 'lessons', target: 15, route: 'learn' },
  { key: 'streak_3', icon: 'tabler-flame', color: 'error', metric: 'streak', target: 3, route: 'learn' },
  { key: 'streak_7', icon: 'tabler-flame', color: 'error', metric: 'streak', target: 7, route: 'learn' },
  { key: 'streak_30', icon: 'tabler-flame', color: 'error', metric: 'streak', target: 30, route: 'learn' },
  { key: 'xp_100', icon: 'tabler-bolt', color: 'warning', metric: 'xp', target: 100, route: 'mondaishuu' },
  { key: 'xp_500', icon: 'tabler-bolt', color: 'warning', metric: 'xp', target: 500, route: 'mondaishuu' },
  { key: 'xp_1000', icon: 'tabler-bolt', color: 'warning', metric: 'xp', target: 1000, route: 'mondaishuu' },
  { key: 'vocab_10', icon: 'tabler-notebook', color: 'info', metric: 'vocab', target: 10, route: 'kosakata' },
  { key: 'vocab_50', icon: 'tabler-notebook', color: 'info', metric: 'vocab', target: 50, route: 'kosakata' },
  { key: 'kanji_10', icon: 'tabler-writing', color: 'secondary', metric: 'kanji', target: 10, route: 'kanji' },
  { key: 'crown_1', icon: 'tabler-crown', color: 'success', metric: 'crowns', target: 1, route: 'mondaishuu' },
  { key: 'crown_10', icon: 'tabler-crown', color: 'success', metric: 'crowns', target: 10, route: 'mondaishuu' },
  { key: 'jlpt_1', icon: 'tabler-certificate', color: 'info', metric: 'jlpt', target: 1, route: 'jlpt-test' },
]

const badges = computed(() => BADGE_DEFS.map(b => {
  const value = badgeMetrics.value[b.metric] ?? 0

  return { ...b, value: Math.min(value, b.target), raw: value, unlocked: value >= b.target, ratio: Math.min(1, value / b.target) }
}))

const unlockedCount = computed(() => badges.value.filter(b => b.unlocked).length)
const nextBadge = computed(() => badges.value.filter(b => !b.unlocked).sort((a, b) => b.ratio - a.ratio)[0] ?? null)

const badgeFilter = ref('all')
const shownBadges = computed(() => badges.value.filter(b => badgeFilter.value === 'all'
  || (badgeFilter.value === 'unlocked' ? b.unlocked : !b.unlocked)))

const pickedBadgeKey = ref(null)
const pickedBadge = computed(() => badges.value.find(b => b.key === pickedBadgeKey.value))

function badgeTitle(b) { return t(`dashboard.badge.${b.key}.title`) }

// Notifikasi saat lencana baru terbuka. `seen_badges` dari server bernilai null
// pada kunjungan pertama → hanya dicatat, tanpa notifikasi, supaya user lama
// tidak dibanjiri. Daftar lama di browser (versi sebelumnya) dipakai sebagai
// titik awal satu kali.
async function checkNewBadges() {
  const nowUnlocked = badges.value.filter(b => b.unlocked).map(b => b.key)
  let seen = data.value.seen_badges

  if (seen === null) {
    try {
      const raw = readLegacy(legacyBadgesKey.value)
      const parsed = raw === null ? null : JSON.parse(raw)

      seen = Array.isArray(parsed) ? parsed : null
    }
    catch { seen = null }
  }

  if (seen !== null) {
    const fresh = badges.value.filter(b => b.unlocked && !seen.includes(b.key))
    if (fresh.length)
      showToast(t('dashboard.badge_new', { name: fresh.map(badgeTitle).join(', ') }))
  }

  // Simpan hanya bila ada yang berubah (kunjungan pertama atau lencana baru).
  const changed = data.value.seen_badges === null || nowUnlocked.some(k => !data.value.seen_badges.includes(k))
  if (changed) {
    try {
      const res = await savePreferences({ seen_badges: nowUnlocked })

      data.value.seen_badges = res.seen_badges
      clearLegacy(legacyBadgesKey.value)
    }
    catch { /* dicoba lagi pada kunjungan berikutnya */ }
  }
}

// Pindahkan tanggal ujian lama dari browser ke database (sekali saja).
async function syncLegacyExam() {
  const v = readLegacy(legacyExamKey.value)
  if (v === null)
    return

  const valid = /^\d{4}-\d{2}-\d{2}$/.test(v) && parseIso(v) >= startOfToday()
  if (valid && !data.value.jlpt_exam_date) {
    try {
      const res = await savePreferences({ jlpt_exam_date: v })

      customExam.value = res.jlpt_exam_date ?? ''
    }
    catch { return } // biarkan di browser, coba lagi nanti
  }

  clearLegacy(legacyExamKey.value)
}

// ── Syarat lulus & hasil tes JLPT ─────────────────────────────────────────
// Selaras dengan config/jlpt.php (N5): total ≥ 80/180, bagian bahasa ≥ 38/120,
// menyimak ≥ 19/60. Level lain belum punya aturan di aplikasi → blok aturan disembunyikan.
const PASS_N5 = { total: 80, max: 180, language: { min: 38, max: 120 }, listening: { min: 19, max: 60 } }

const levelCode = computed(() => data.value?.current_level?.code ?? 'N5')
const passRules = computed(() => (levelCode.value === 'N5' ? PASS_N5 : null))

const bestPack = computed(() => {
  const withBest = packs.value.filter(p => p.best)
  if (!withBest.length)
    return null

  return withBest.reduce((a, b) => (b.best.total_score > a.best.total_score ? b : a)).best
})

const inProgressPack = computed(() => packs.value.find(p => p.in_progress) ?? null)
const jlptAttempts = computed(() => packs.value.reduce((a, p) => a + (p.attempts || 0), 0))

const jlptTestTo = computed(() => (inProgressPack.value
  ? { name: 'jlpt-test', query: { pack: inProgressPack.value.key } }
  : { name: 'jlpt-test' }))

// ── Kesiapan per bagian ujian (estimasi dari progres roadmap, BUKAN skor resmi) ──
const pctOf = key => stageOf(key)?.percent ?? 0
const avg = list => Math.round(list.reduce((a, b) => a + b, 0) / list.length)

const readyParts = computed(() => {
  if (!data.value)
    return []

  const parts = [
    { key: 'moji', icon: 'tabler-abc', color: 'warning', route: 'kanji', value: avg([pctOf('vocabulary'), pctOf('kanji'), pctOf('kaite_oboeru')]) },
    { key: 'bunpou', icon: 'tabler-book-2', color: 'primary', route: 'mondaishuu', value: avg([pctOf('learn'), pctOf('mondaishuu')]) },
    { key: 'chokai', icon: 'tabler-headphones', color: 'info', route: 'chokai', value: pctOf('chokai') },
  ]
  const weakest = [...parts].sort((a, b) => a.value - b.value)[0]

  return parts.map(x => ({ ...x, weakest: x.key === weakest.key && weakest.value < 100 }))
})

const weakestPart = computed(() => readyParts.value.find(x => x.weakest))

// ── Ritme belajar menuju tanggal ujian ────────────────────────────────────
const studyPlan = computed(() => {
  const n = daysLeft.value
  const learn = stageOf('learn')
  if (!learn?.total || n <= 0)
    return null

  const left = Math.max(0, learn.total - learn.done)
  if (!left)
    return t('dashboard.cd_plan_pace_done')

  const weeks = Math.max(1, Math.round(n / 7))

  return `${t('dashboard.cd_plan_weeks', { n: weeks })} ${t('dashboard.cd_plan_pace', { left, pace: Math.ceil(left / weeks) })}`
})

// ── Kanji hari ini (N5/N4 dari database, berganti tiap hari) ──────────────
const kanjiList = computed(() => data.value?.kanji_of_day ?? [])
const kanjiIndex = ref(0)
const kanjiToday = computed(() => kanjiList.value[kanjiIndex.value] ?? null)

function nextKanji() {
  if (kanjiList.value.length)
    kanjiIndex.value = (kanjiIndex.value + 1) % kanjiList.value.length
}

// "た.べる" → "た・べる"
const fmtReadings = list => (list ?? []).map(r => r.replace('.', '・')).join('、')

const statTiles = computed(() => {
  const d = data.value
  if (!d)
    return []

  return [
    { key: 'streak', icon: 'tabler-flame', color: 'error', value: shown.streak, label: t('dashboard.day_streak'),
      hint: t('dashboard.longest_streak', { n: d.longest_streak }), to: null },
    { key: 'xp', icon: 'tabler-bolt', color: 'warning', value: shown.xp.toLocaleString(), label: t('dashboard.total_xp'),
      hint: t('dashboard.level_progress', { xp: d.level_progress }), to: null },
    { key: 'level', icon: 'tabler-trophy', color: 'primary', value: `${t('common.level')} ${d.player_level}`, label: d.current_level?.code ?? '',
      hint: t('dashboard.xp_to_next', { xp: 100 - d.level_progress }), to: null },
    { key: 'crowns', icon: 'tabler-crown', color: 'success', value: shown.crowns, label: t('dashboard.crowns'),
      hint: t('dashboard.crowns_hint'), to: null },
  ]
})

onMounted(load)
</script>

<template>
  <div class="dash">
    <div
      v-if="isLoading"
      class="d-flex justify-center pa-10"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
    </div>

    <VAlert
      v-else-if="hasError"
      type="error"
      variant="tonal"
    >
      {{ t('common.error') }}
      <template #append>
        <VBtn
          size="small"
          variant="text"
          @click="load"
        >
          {{ t('common.retry') }}
        </VBtn>
      </template>
    </VAlert>

    <template v-else>
      <!-- ── Sapaan + langkah berikutnya ─────────────────────────────── -->
      <VRow>
        <VCol
          cols="12"
          md="8"
        >
          <VCard class="dash-hero h-100">
            <VCardText class="pa-6 d-flex flex-column gap-3 justify-center h-100">
              <div>
                <h4 class="text-h4 mb-1">
                  {{ greeting }}
                </h4>
                <p class="text-medium-emphasis mb-0">
                  {{ data.streak_active_today ? t('dashboard.streak_safe') : (data.streak > 0 ? t('dashboard.streak_at_risk', { n: data.streak }) : t('dashboard.greeting_sub')) }}
                </p>
              </div>

              <div
                v-if="nextStep"
                class="dash-next"
              >
                <div class="text-overline text-medium-emphasis">
                  {{ t('dashboard.next_lesson') }}
                </div>
                <div class="text-h5 mb-3">
                  {{ nextStep.title }}
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <VBtn
                    color="primary"
                    size="large"
                    prepend-icon="tabler-player-play-filled"
                    @click="nextStep.action"
                  >
                    {{ nextStep.label }}
                  </VBtn>
                  <VBtn
                    v-if="data.review_due > 0"
                    variant="tonal"
                    color="warning"
                    size="large"
                    prepend-icon="tabler-repeat"
                    :to="{ name: 'kosakata' }"
                  >
                    {{ t('dashboard.review_cta', { n: data.review_due }) }}
                  </VBtn>
                </div>
              </div>
              <div
                v-else
                class="text-h6"
              >
                {{ t('dashboard.all_done') }}
              </div>

              <!-- Kanji hari ini -->
              <div
                v-if="kanjiToday"
                class="kanji-day"
              >
                <div class="kanji-day__head">
                  <div class="text-overline text-medium-emphasis d-flex align-center gap-1">
                    <VIcon
                      icon="tabler-writing"
                      size="16"
                    />
                    {{ t('dashboard.kanji_day.title') }}
                    <VChip
                      size="x-small"
                      color="info"
                      class="ms-1"
                    >
                      {{ kanjiToday.jlpt_level }}
                    </VChip>
                  </div>
                  <div class="d-flex gap-1">
                    <VBtn
                      size="small"
                      variant="text"
                      prepend-icon="tabler-refresh"
                      @click="nextKanji"
                    >
                      {{ t('dashboard.kanji_day.another') }} ({{ kanjiIndex + 1 }}/{{ kanjiList.length }})
                    </VBtn>
                    <VBtn
                      size="small"
                      variant="tonal"
                      append-icon="tabler-arrow-right"
                      :to="{ name: 'kanji' }"
                    >
                      {{ t('dashboard.kanji_day.open') }}
                    </VBtn>
                  </div>
                </div>

                <Transition
                  name="fade"
                  mode="out-in"
                >
                  <div
                    :key="kanjiToday.id"
                    class="kanji-day__body"
                  >
                    <div class="kanji-day__char">
                      {{ kanjiToday.character }}
                    </div>
                    <div class="kanji-day__info">
                      <div class="font-weight-medium">
                        {{ kanjiToday.meaning }}
                        <span
                          v-if="kanjiToday.stroke_count"
                          class="text-caption text-medium-emphasis ms-1"
                        >· {{ t('dashboard.kanji_day.strokes', { n: kanjiToday.stroke_count }) }}</span>
                      </div>
                      <div
                        v-if="kanjiToday.onyomi.length"
                        class="text-body-2"
                      >
                        <span class="text-medium-emphasis">{{ t('dashboard.kanji_day.on') }}</span>
                        {{ fmtReadings(kanjiToday.onyomi) }}
                      </div>
                      <div
                        v-if="kanjiToday.kunyomi.length"
                        class="text-body-2"
                      >
                        <span class="text-medium-emphasis">{{ t('dashboard.kanji_day.kun') }}</span>
                        {{ fmtReadings(kanjiToday.kunyomi) }}
                      </div>
                    </div>
                  </div>
                </Transition>

                <div
                  v-if="kanjiToday.examples.length"
                  class="kanji-day__examples"
                >
                  <div
                    v-for="ex in kanjiToday.examples"
                    :key="ex.word + ex.reading"
                    class="kanji-day__ex"
                  >
                    <span class="kanji-day__word">{{ ex.word }}</span>
                    <span class="text-body-2">{{ ex.reading }}</span>
                    <span class="text-caption text-medium-emphasis">{{ ex.meaning }}</span>
                  </div>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Target harian: cincin progres -->
        <VCol
          cols="12"
          md="4"
        >
          <VCard class="h-100">
            <VCardText class="pa-6 d-flex flex-column align-center justify-center text-center h-100">
              <div class="text-subtitle-1 font-weight-medium mb-3">
                {{ t('dashboard.daily_goal') }}
              </div>
              <VProgressCircular
                :model-value="shown.goal"
                :size="132"
                :width="12"
                :color="goalReached ? 'success' : 'primary'"
              >
                <div>
                  <div class="text-h4">
                    {{ shown.goal }}%
                  </div>
                </div>
              </VProgressCircular>
              <div class="text-body-2 text-medium-emphasis mt-3">
                {{ t('dashboard.goal_progress', { done: data.xp_today, target: data.daily_goal_target }) }}
              </div>
              <VChip
                v-if="goalReached"
                color="success"
                size="small"
                class="mt-2"
                prepend-icon="tabler-confetti"
              >
                {{ t('dashboard.goal_reached') }}
              </VChip>
              <div
                v-else
                class="text-caption text-medium-emphasis mt-2"
              >
                {{ t('dashboard.goal_remaining', { xp: Math.max(0, data.daily_goal_target - data.xp_today) }) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Ubin statistik ───────────────────────────────────────── -->
        <VCol
          v-for="s in statTiles"
          :key="s.key"
          cols="6"
          md="3"
        >
          <VCard class="dash-tile h-100">
            <VCardText class="stat-tile">
              <div
                class="stat-tile__icon"
                :style="{ background: `rgba(var(--v-theme-${s.color}), 0.16)`, color: `rgb(var(--v-theme-${s.color}))` }"
              >
                <VIcon :icon="s.icon" />
              </div>
              <div class="min-w-0">
                <div class="stat-tile__value">
                  {{ s.value }}
                </div>
                <div class="stat-tile__label">
                  {{ s.label }}
                </div>
              </div>
            </VCardText>
            <div class="dash-tile__hint text-caption text-medium-emphasis px-5 pb-3">
              {{ s.hint }}
            </div>
          </VCard>
        </VCol>

        <!-- ── Tips belajar ────────────────────────────────────────── -->
        <VCol
          cols="12"
          md="6"
        >
          <VCard class="h-100 tip-card">
            <VCardItem>
              <VCardTitle>{{ t('dashboard.tip_study_title') }}</VCardTitle>
              <template #append>
                <VBtn
                  icon="tabler-chevron-left"
                  size="small"
                  variant="text"
                  :aria-label="t('common.back')"
                  @click="stepTip(-1)"
                />
                <VBtn
                  icon="tabler-chevron-right"
                  size="small"
                  variant="text"
                  :aria-label="t('dashboard.next')"
                  @click="stepTip(1)"
                />
              </template>
            </VCardItem>
            <VCardText>
              <Transition
                name="fade"
                mode="out-in"
              >
                <div
                  :key="tip.key"
                  class="d-flex flex-column gap-3"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                    size="52"
                    rounded="lg"
                  >
                    <VIcon
                      :icon="tip.icon"
                      size="28"
                    />
                  </VAvatar>
                  <div class="text-h6">
                    {{ t(`dashboard.tip_study.${tip.key}.title`) }}
                  </div>
                  <p class="text-body-2 mb-0">
                    {{ t(`dashboard.tip_study.${tip.key}.body`) }}
                  </p>
                  <div>
                    <VBtn
                      color="primary"
                      variant="tonal"
                      append-icon="tabler-arrow-right"
                      :to="{ name: tip.route }"
                    >
                      {{ t(`dashboard.tip_study.${tip.key}.cta`) }}
                    </VBtn>
                  </div>
                </div>
              </Transition>

              <div class="d-flex justify-center gap-2 mt-4">
                <button
                  v-for="(tp, i) in STUDY_TIPS"
                  :key="tp.key"
                  type="button"
                  class="tip-dot"
                  :class="{ 'is-active': i === tipIndex }"
                  :aria-label="`${i + 1} / ${STUDY_TIPS.length}`"
                  @click="tipIndex = i"
                />
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Tips lulus JLPT ─────────────────────────────────────── -->
        <VCol
          cols="12"
          md="6"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>{{ t('dashboard.tip_jlpt_title', { level: data.current_level?.code ?? 'JLPT' }) }}</VCardTitle>
              <VCardSubtitle>{{ t('dashboard.tip_jlpt_sub') }}</VCardSubtitle>
              <template #append>
                <VChip
                  :color="readiness.color"
                  size="small"
                  prepend-icon="tabler-gauge"
                >
                  {{ t(`dashboard.ready_${readiness.key}`) }} · {{ overallPercent }}%
                </VChip>
              </template>
            </VCardItem>
            <VCardText>
              <VExpansionPanels
                v-model="openJlptTip"
                variant="accordion"
                class="jlpt-tips"
              >
                <VExpansionPanel
                  v-for="(jt, i) in JLPT_TIPS"
                  :key="jt.key"
                  :value="i"
                  elevation="0"
                >
                  <VExpansionPanelTitle>
                    <VIcon
                      :icon="jt.icon"
                      color="primary"
                      class="me-3"
                    />
                    {{ t(`dashboard.tip_jlpt.${jt.key}.title`) }}
                  </VExpansionPanelTitle>
                  <VExpansionPanelText>
                    <p class="text-body-2 mb-3">
                      {{ t(`dashboard.tip_jlpt.${jt.key}.body`) }}
                    </p>
                    <VBtn
                      size="small"
                      color="primary"
                      variant="tonal"
                      append-icon="tabler-arrow-right"
                      :to="{ name: jt.route }"
                    >
                      {{ t(`dashboard.tip_jlpt.${jt.key}.cta`) }}
                    </VBtn>
                  </VExpansionPanelText>
                </VExpansionPanel>
              </VExpansionPanels>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Saran hari ini ──────────────────────────────────────── -->
        <VCol
          v-if="suggestions.length"
          cols="12"
        >
          <div class="text-subtitle-1 font-weight-medium mb-2">
            {{ t('dashboard.suggest_title') }}
          </div>
          <VRow>
            <VCol
              v-for="sg in suggestions"
              :key="sg.key"
              cols="12"
              md="4"
            >
              <VCard
                class="dash-tile h-100"
                :to="sg.to"
              >
                <VCardText class="d-flex align-center gap-3">
                  <VAvatar
                    :color="sg.color"
                    variant="tonal"
                    size="46"
                    rounded="lg"
                  >
                    <VIcon :icon="sg.icon" />
                  </VAvatar>
                  <div class="min-w-0 flex-grow-1">
                    <div class="font-weight-medium">
                      {{ sg.title }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      {{ sg.body }}
                    </div>
                  </div>
                  <VIcon
                    icon="tabler-chevron-right"
                    class="text-medium-emphasis"
                  />
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VCol>

        <!-- ── Roadmap belajar ─────────────────────────────────────── -->
        <VCol cols="12">
          <VCard>
            <VCardItem>
              <VCardTitle>{{ t('dashboard.roadmap_title') }}</VCardTitle>
              <VCardSubtitle>{{ t('dashboard.roadmap_sub') }}</VCardSubtitle>
              <template #append>
                <div class="text-end">
                  <div class="text-h5">
                    {{ overallPercent }}%
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ t('dashboard.overall') }}
                  </div>
                </div>
              </template>
            </VCardItem>

            <VCardText>
              <VProgressLinear
                :model-value="overallPercent"
                color="primary"
                height="8"
                rounded
                class="mb-5"
              />

              <div
                class="roadmap"
                role="tablist"
              >
                <button
                  v-for="(stage, i) in roadmap"
                  :key="stage.key"
                  type="button"
                  role="tab"
                  class="roadmap__step"
                  :class="[`is-${stage.state}`, { 'is-selected': stage.key === selectedKey }]"
                  :aria-selected="stage.key === selectedKey"
                  @click="selectedKey = stage.key"
                >
                  <span class="roadmap__node">
                    <VIcon
                      v-if="stage.state === 'done'"
                      icon="tabler-check"
                      size="20"
                    />
                    <VIcon
                      v-else
                      :icon="stage.icon"
                      size="20"
                    />
                  </span>
                  <span
                    v-if="i < roadmap.length - 1"
                    class="roadmap__line"
                  />
                  <span class="roadmap__name">{{ t(`nav.${stage.key}`) }}</span>
                  <span class="roadmap__meta">
                    <template v-if="stage.percent !== null">{{ stage.percent }}%</template>
                    <template v-else-if="stage.state === 'done'">{{ t('dashboard.state_done') }}</template>
                    <template v-else>&nbsp;</template>
                  </span>
                </button>
              </div>

              <!-- Detail tahap terpilih -->
              <Transition
                name="fade"
                mode="out-in"
              >
                <div
                  v-if="selectedStage"
                  :key="selectedStage.key"
                  class="roadmap-detail mt-4"
                >
                  <div class="d-flex flex-wrap align-center justify-space-between gap-3">
                    <div class="d-flex align-center gap-3">
                      <VAvatar
                        variant="tonal"
                        :color="selectedStage.state === 'done' ? 'success' : 'primary'"
                        size="44"
                      >
                        <VIcon :icon="selectedStage.icon" />
                      </VAvatar>
                      <div>
                        <div class="text-subtitle-1 font-weight-medium">
                          {{ t(`nav.${selectedStage.key}`) }}
                          <VChip
                            v-if="selectedStage.state === 'current'"
                            size="x-small"
                            color="primary"
                            class="ms-1"
                          >
                            {{ t('dashboard.state_current') }}
                          </VChip>
                        </div>
                        <div class="text-body-2 text-medium-emphasis">
                          {{ t(`dashboard.stage_desc.${selectedStage.key}`) }}
                        </div>
                      </div>
                    </div>
                    <VBtn
                      :variant="selectedStage.state === 'current' ? 'flat' : 'tonal'"
                      color="primary"
                      append-icon="tabler-arrow-right"
                      @click="openStage(selectedStage)"
                    >
                      {{ t('dashboard.open_stage') }}
                    </VBtn>
                  </div>

                  <div
                    v-if="selectedStage.percent !== null"
                    class="mt-3"
                  >
                    <VProgressLinear
                      :model-value="selectedStage.percent"
                      :color="selectedStage.state === 'done' ? 'success' : 'primary'"
                      height="10"
                      rounded
                    />
                    <div class="text-caption text-medium-emphasis mt-1">
                      {{ t(`dashboard.stage_unit.${selectedStage.key}`, { done: selectedStage.done, total: selectedStage.total }) }}
                    </div>
                  </div>
                  <div
                    v-else-if="selectedStage.key === 'jlpt_test'"
                    class="text-caption text-medium-emphasis mt-3"
                  >
                    {{ t('dashboard.stage_unit.jlpt_test', { done: selectedStage.done }) }}
                  </div>
                </div>
              </Transition>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Hitung mundur JLPT ──────────────────────────────────── -->
        <VCol
          cols="12"
          md="5"
        >
          <VCard class="h-100 countdown">
            <VCardText class="pa-6 d-flex flex-column gap-4 h-100">
              <div class="d-flex align-center justify-space-between">
                <div class="text-subtitle-1 font-weight-medium">
                  {{ t('dashboard.cd_title', { level: data.current_level?.code ?? 'JLPT' }) }}
                </div>
                <VChip
                  size="x-small"
                  :color="isCustomExam ? 'secondary' : 'primary'"
                >
                  {{ isCustomExam ? t('dashboard.cd_custom') : t('dashboard.cd_official') }}
                </VChip>
              </div>

              <div class="d-flex align-center gap-5">
                <VProgressCircular
                  :model-value="countdownRing"
                  :size="116"
                  :width="10"
                  color="primary"
                >
                  <div class="text-center">
                    <div class="text-h4 leading-none">
                      {{ daysLeft }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      {{ t('dashboard.cd_days') }}
                    </div>
                  </div>
                </VProgressCircular>
                <div class="min-w-0">
                  <div class="font-weight-medium">
                    {{ examDateLabel }}
                  </div>
                  <div
                    v-if="daysLeft >= 7"
                    class="text-body-2 text-medium-emphasis"
                  >
                    {{ t('dashboard.cd_weeks', { w: Math.floor(daysLeft / 7), d: daysLeft % 7 }) }}
                  </div>
                </div>
              </div>

              <p class="text-body-2 mb-0">
                {{ countdownMsg }}
              </p>
              <VAlert
                v-if="studyPlan"
                variant="tonal"
                color="primary"
                density="compact"
                icon="tabler-route"
              >
                {{ studyPlan }}
              </VAlert>

              <div class="d-flex flex-wrap gap-2 mt-auto">
                <VBtn
                  color="primary"
                  size="small"
                  prepend-icon="tabler-certificate"
                  :to="{ name: 'jlpt-test' }"
                >
                  {{ t('dashboard.cd_cta') }}
                </VBtn>
                <VBtn
                  variant="tonal"
                  size="small"
                  prepend-icon="tabler-calendar-event"
                  @click="openExamDialog"
                >
                  {{ t('dashboard.cd_set') }}
                </VBtn>
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ t('dashboard.cd_note') }}
                <a
                  href="https://www.jlpt.jp/e/"
                  target="_blank"
                  rel="noopener noreferrer"
                >{{ t('dashboard.cd_check') }}</a>
              </div>
            </VCardText>
          </VCard>

          <VDialog
            v-model="examDialog"
            max-width="380"
          >
            <VCard :title="t('dashboard.cd_set')">
              <VCardText>
                <VTextField
                  v-model="examDraft"
                  type="date"
                  :label="t('dashboard.cd_pick')"
                  :min="toIso(startOfToday())"
                />
                <p class="text-caption text-medium-emphasis mb-0">
                  {{ t('dashboard.cd_dialog_hint') }}
                </p>
              </VCardText>
              <VCardActions>
                <VBtn
                  v-if="customExam"
                  color="error"
                  variant="text"
                  :disabled="savingExam"
                  @click="resetExam"
                >
                  {{ t('dashboard.cd_reset') }}
                </VBtn>
                <VSpacer />
                <VBtn
                  variant="text"
                  :disabled="savingExam"
                  @click="examDialog = false"
                >
                  {{ t('common.cancel') }}
                </VBtn>
                <VBtn
                  color="primary"
                  :disabled="!examDraft"
                  :loading="savingExam"
                  @click="saveExam"
                >
                  {{ t('common.save') }}
                </VBtn>
              </VCardActions>
            </VCard>
          </VDialog>
        </VCol>

        <!-- ── Lencana pencapaian ──────────────────────────────────── -->
        <VCol
          cols="12"
          md="7"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>{{ t('dashboard.badges_title') }}</VCardTitle>
              <VCardSubtitle>
                {{ t('dashboard.badges_sub', { n: unlockedCount, total: badges.length }) }}
              </VCardSubtitle>
              <template #append>
                <VBtnToggle
                  v-model="badgeFilter"
                  mandatory
                  density="compact"
                  variant="outlined"
                  divided
                >
                  <VBtn value="all">
                    {{ t('dashboard.f_all') }}
                  </VBtn>
                  <VBtn value="unlocked">
                    {{ t('dashboard.f_unlocked') }}
                  </VBtn>
                  <VBtn value="locked">
                    {{ t('dashboard.f_locked') }}
                  </VBtn>
                </VBtnToggle>
              </template>
            </VCardItem>
            <VCardText>
              <VProgressLinear
                :model-value="(unlockedCount / badges.length) * 100"
                color="success"
                height="6"
                rounded
                class="mb-4"
              />

              <div
                v-if="nextBadge && badgeFilter !== 'unlocked'"
                class="text-body-2 mb-3"
              >
                <VIcon
                  icon="tabler-target"
                  size="18"
                  class="me-1"
                />
                {{ t('dashboard.badge_next') }}
                <strong>{{ badgeTitle(nextBadge) }}</strong>
                <span class="text-medium-emphasis"> — {{ nextBadge.raw }} / {{ nextBadge.target }}</span>
              </div>

              <div class="badges">
                <button
                  v-for="b in shownBadges"
                  :key="b.key"
                  type="button"
                  class="badge-tile"
                  :class="{ 'is-unlocked': b.unlocked, 'is-picked': b.key === pickedBadgeKey }"
                  :style="{ '--badge-color': `var(--v-theme-${b.color})` }"
                  :aria-label="badgeTitle(b)"
                  @click="pickedBadgeKey = pickedBadgeKey === b.key ? null : b.key"
                >
                  <span class="badge-tile__icon">
                    <VIcon
                      :icon="b.unlocked ? b.icon : 'tabler-lock'"
                      size="26"
                    />
                  </span>
                  <span class="badge-tile__name">{{ badgeTitle(b) }}</span>
                  <VProgressLinear
                    v-if="!b.unlocked"
                    :model-value="b.ratio * 100"
                    :color="b.color"
                    height="4"
                    rounded
                    class="w-100"
                  />
                </button>
              </div>

              <div
                v-if="!shownBadges.length"
                class="text-center text-medium-emphasis py-6"
              >
                {{ t('dashboard.badges_empty') }}
              </div>

              <Transition name="fade">
                <div
                  v-if="pickedBadge"
                  class="roadmap-detail mt-4 d-flex flex-wrap align-center justify-space-between gap-3"
                >
                  <div>
                    <div class="font-weight-medium">
                      {{ badgeTitle(pickedBadge) }}
                      <VChip
                        v-if="pickedBadge.unlocked"
                        size="x-small"
                        color="success"
                        class="ms-1"
                      >
                        {{ t('dashboard.badge_unlocked') }}
                      </VChip>
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      {{ t(`dashboard.badge.${pickedBadge.key}.desc`) }}
                      <template v-if="!pickedBadge.unlocked">
                        ({{ pickedBadge.raw }} / {{ pickedBadge.target }})
                      </template>
                    </div>
                  </div>
                  <VBtn
                    v-if="!pickedBadge.unlocked"
                    size="small"
                    color="primary"
                    variant="tonal"
                    append-icon="tabler-arrow-right"
                    :to="{ name: pickedBadge.route }"
                  >
                    {{ t('dashboard.badge_go') }}
                  </VBtn>
                </div>
              </Transition>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Syarat lulus & hasil tes JLPT ────────────────────────── -->
        <VCol
          cols="12"
          md="6"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>{{ t('dashboard.jlpt_pass.title', { level: levelCode }) }}</VCardTitle>
              <VCardSubtitle>{{ t('dashboard.jlpt_pass.sub') }}</VCardSubtitle>
              <template #prepend>
                <VIcon
                  icon="tabler-certificate"
                  color="error"
                />
              </template>
            </VCardItem>
            <VCardText class="d-flex flex-column gap-4">
              <template v-if="passRules">
                <div class="pass-rules">
                  <div class="pass-rule">
                    <div class="pass-rule__num">
                      {{ passRules.total }}<small>/{{ passRules.max }}</small>
                    </div>
                    <div class="text-caption">
                      {{ t('dashboard.jlpt_pass.total', { pass: passRules.total, max: passRules.max }) }}
                    </div>
                  </div>
                  <div class="pass-rule">
                    <div class="pass-rule__num">
                      {{ passRules.language.min }}<small>/{{ passRules.language.max }}</small>
                    </div>
                    <div class="text-caption">
                      {{ t('dashboard.jlpt_pass.language') }}
                    </div>
                  </div>
                  <div class="pass-rule">
                    <div class="pass-rule__num">
                      {{ passRules.listening.min }}<small>/{{ passRules.listening.max }}</small>
                    </div>
                    <div class="text-caption">
                      {{ t('dashboard.jlpt_pass.listening') }}
                    </div>
                  </div>
                </div>
                <p class="text-caption text-medium-emphasis mb-0">
                  {{ t('dashboard.jlpt_pass.note') }}
                </p>
              </template>

              <div
                v-if="packs.length"
                class="d-flex flex-wrap align-center gap-2 mt-auto"
              >
                <template v-if="bestPack">
                  <VChip :color="bestPack.passed ? 'success' : 'warning'">
                    {{ t('dashboard.jlpt_pass.best', { score: bestPack.total_score, max: bestPack.total_max }) }}
                  </VChip>
                  <VChip
                    :color="bestPack.passed ? 'success' : 'secondary'"
                    variant="tonal"
                  >
                    {{ bestPack.passed ? t('dashboard.jlpt_pass.passed') : t('dashboard.jlpt_pass.not_passed') }}
                  </VChip>
                </template>
                <VChip
                  v-else
                  variant="tonal"
                >
                  {{ t('dashboard.jlpt_pass.none') }}
                </VChip>
                <VChip
                  v-if="jlptAttempts"
                  variant="tonal"
                >
                  {{ t('dashboard.jlpt_pass.attempts', { n: jlptAttempts }) }}
                </VChip>
                <VSpacer />
                <VBtn
                  size="small"
                  variant="tonal"
                  color="error"
                  append-icon="tabler-arrow-right"
                  :to="jlptTestTo"
                >
                  {{ inProgressPack ? t('dashboard.jlpt_pass.resume') : t('nav.jlpt_test') }}
                </VBtn>
              </div>
              <p
                v-else
                class="text-body-2 text-medium-emphasis mb-0 mt-auto"
              >
                {{ t('dashboard.jlpt_pass.empty') }}
              </p>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Kesiapan per bagian ujian ────────────────────────────── -->
        <VCol
          cols="12"
          md="6"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>{{ t('dashboard.ready_parts.title') }}</VCardTitle>
              <VCardSubtitle>{{ t('dashboard.ready_parts.sub') }}</VCardSubtitle>
              <template #prepend>
                <VIcon
                  icon="tabler-gauge"
                  color="primary"
                />
              </template>
            </VCardItem>
            <VCardText class="d-flex flex-column gap-2">
              <VCard
                v-for="part in readyParts"
                :key="part.key"
                variant="tonal"
                :color="part.color"
                class="dash-tile ready-part"
                :class="{ 'is-weakest': part.weakest }"
                :to="{ name: part.route }"
              >
                <VCardText class="d-flex align-center gap-3 py-3">
                  <VIcon
                    :icon="part.icon"
                    size="26"
                  />
                  <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-space-between mb-1">
                      <span class="font-weight-medium">{{ t(`dashboard.ready_parts.${part.key}`) }}</span>
                      <span class="font-weight-bold">{{ part.value }}%</span>
                    </div>
                    <VProgressLinear
                      :model-value="part.value"
                      :color="part.color"
                      height="8"
                      rounded
                    />
                  </div>
                  <VIcon icon="tabler-chevron-right" />
                </VCardText>
              </VCard>
              <p
                v-if="weakestPart"
                class="text-caption text-medium-emphasis mb-0"
              >
                {{ t('dashboard.ready_parts.weakest', { part: t(`dashboard.ready_parts.${weakestPart.key}`) }) }}
              </p>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Aktivitas 7 hari ────────────────────────────────────── -->
        <VCol
          cols="12"
          md="7"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>{{ t('dashboard.week_title') }}</VCardTitle>
              <VCardSubtitle>
                {{ t('dashboard.week_sub', { xp: weekTotal, days: activeDays }) }}
              </VCardSubtitle>
            </VCardItem>
            <VCardText>
              <div class="week">
                <button
                  v-for="day in data.week"
                  :key="day.date"
                  type="button"
                  class="week__col"
                  :class="{ 'is-today': day.is_today, 'is-picked': day.date === selectedDay }"
                  :aria-label="`${dayLong(day)}: ${day.xp} XP`"
                  @click="selectedDay = day.date"
                >
                  <span class="week__xp">{{ day.xp > 0 ? day.xp : '' }}</span>
                  <span class="week__track">
                    <span
                      class="week__bar"
                      :style="{ height: `${day.xp > 0 ? Math.max(8, (day.xp / weekMax) * 100) : 0}%` }"
                    />
                  </span>
                  <span class="week__day">{{ weekdayLabel(day) }}</span>
                </button>
              </div>

              <div
                v-if="pickedDay"
                class="text-body-2 text-center mt-3"
              >
                <span class="text-medium-emphasis">{{ dayLong(pickedDay) }}:</span>
                <strong class="ms-1">{{ pickedDay.xp }} XP</strong>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- ── Aktivitas terbaru ───────────────────────────────────── -->
        <VCol
          cols="12"
          md="5"
        >
          <VCard class="h-100">
            <VCardItem>
              <VCardTitle>{{ t('dashboard.recent_title') }}</VCardTitle>
            </VCardItem>
            <VCardText v-if="data.recent.length">
              <VList class="pa-0">
                <VListItem
                  v-for="(r, i) in data.recent"
                  :key="i"
                  class="px-0"
                >
                  <template #prepend>
                    <VAvatar
                      color="warning"
                      variant="tonal"
                      size="36"
                      class="me-3"
                    >
                      <VIcon
                        icon="tabler-bolt"
                        size="18"
                      />
                    </VAvatar>
                  </template>
                  <VListItemTitle>{{ sourceLabel(r.source) }}</VListItemTitle>
                  <VListItemSubtitle>{{ timeAgo(r.at) }}</VListItemSubtitle>
                  <template #append>
                    <span class="text-success font-weight-medium">+{{ r.amount }} XP</span>
                  </template>
                </VListItem>
              </VList>
            </VCardText>
            <VCardText
              v-else
              class="text-center text-medium-emphasis py-8"
            >
              <VIcon
                icon="tabler-sparkles"
                size="32"
                class="mb-2"
              />
              <div>{{ t('dashboard.recent_empty') }}</div>
            </VCardText>
          </VCard>
        </VCol>

      </VRow>
    </template>

    <VSnackbar
      v-model="toast.show"
      :color="toast.color"
      location="bottom end"
      :timeout="6000"
    >
      <VIcon
        :icon="toast.icon"
        class="me-2"
      />
      {{ toast.text }}
    </VSnackbar>
  </div>
</template>

<style lang="scss" scoped>
.dash-hero {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.1), rgba(var(--v-theme-primary), 0.02));
}

.dash-tile {
  transition: transform 0.15s ease, box-shadow 0.15s ease;

  &:hover {
    box-shadow: 0 6px 18px rgba(var(--v-shadow-key-umbra-color), 0.18);
    transform: translateY(-2px);
  }
}

/* ── Roadmap ─────────────────────────────────────────────────────────── */
.roadmap {
  display: flex;
  overflow-x: auto;
  padding-block: 4px 8px;
  scroll-snap-type: x proximity;

  &__step {
    position: relative;
    display: flex;
    flex: 1 0 96px;
    flex-direction: column;
    align-items: center;
    padding: 6px 4px;
    border-radius: 12px;
    background: none;
    color: inherit;
    cursor: pointer;
    gap: 6px;
    scroll-snap-align: center;
    text-align: center;
    transition: background 0.15s ease;

    &:hover { background: rgba(var(--v-theme-on-surface), 0.05); }
    &:focus-visible { outline: 2px solid rgb(var(--v-theme-primary)); }
  }

  &__node {
    z-index: 1;
    display: grid;
    border: 2px solid rgba(var(--v-theme-on-surface), 0.25);
    border-radius: 50%;
    background: rgb(var(--v-theme-surface));
    block-size: 44px;
    color: rgba(var(--v-theme-on-surface), 0.6);
    inline-size: 44px;
    place-items: center;
    transition: transform 0.15s ease;
  }

  /* garis penghubung ke tahap berikutnya */
  &__line {
    position: absolute;
    background: rgba(var(--v-theme-on-surface), 0.18);
    block-size: 2px;
    inset-block-start: 28px;
    inset-inline: calc(50% + 24px) calc(-50% + 24px);
  }

  &__name {
    font-size: 0.8rem;
    font-weight: 500;
    line-height: 1.2;
  }

  &__meta {
    color: rgba(var(--v-theme-on-surface), 0.6);
    font-size: 0.72rem;
    min-block-size: 1em;
  }

  .is-done {
    .roadmap__node {
      border-color: rgb(var(--v-theme-success));
      background: rgb(var(--v-theme-success));
      color: #fff;
    }

    .roadmap__line { background: rgb(var(--v-theme-success)); }
  }

  .is-current .roadmap__node {
    border-color: rgb(var(--v-theme-primary));
    box-shadow: 0 0 0 5px rgba(var(--v-theme-primary), 0.18);
    color: rgb(var(--v-theme-primary));
  }

  .is-selected {
    background: rgba(var(--v-theme-primary), 0.08);

    .roadmap__node { transform: scale(1.08); }
  }
}

.roadmap-detail {
  padding: 16px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
}

/* ── Grafik 7 hari ───────────────────────────────────────────────────── */
.week {
  display: flex;
  align-items: stretch;
  block-size: 190px;
  gap: 8px;

  &__col {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: center;
    padding: 2px;
    border-radius: 10px;
    background: none;
    color: inherit;
    cursor: pointer;
    gap: 4px;
    transition: background 0.15s ease;

    &:hover,
    &.is-picked { background: rgba(var(--v-theme-primary), 0.08); }
  }

  &__xp {
    font-size: 0.7rem;
    min-block-size: 1em;
    opacity: 0.7;
  }

  &__track {
    display: flex;
    flex: 1;
    align-items: flex-end;
    justify-content: center;
    inline-size: 100%;
  }

  &__bar {
    border-radius: 6px 6px 2px 2px;
    background: rgba(var(--v-theme-primary), 0.45);
    inline-size: 70%;
    max-inline-size: 36px;
    transition: height 0.5s ease;
  }

  &__col.is-today &__bar { background: rgb(var(--v-theme-primary)); }

  &__day {
    font-size: 0.75rem;
    opacity: 0.75;
  }

  &__col.is-today &__day {
    font-weight: 700;
    opacity: 1;
  }
}

/* ── Tips ────────────────────────────────────────────────────────────── */
.tip-dot {
  border-radius: 99px;
  background: rgba(var(--v-theme-on-surface), 0.2);
  block-size: 8px;
  cursor: pointer;
  inline-size: 8px;
  transition: inline-size 0.2s ease, background 0.2s ease;

  &.is-active {
    background: rgb(var(--v-theme-primary));
    inline-size: 22px;
  }
}

.jlpt-tips :deep(.v-expansion-panel) {
  background: transparent;
}

/* ── Hitung mundur & lencana ─────────────────────────────────────────── */
.countdown {
  background: linear-gradient(160deg, rgba(var(--v-theme-info), 0.1), transparent 60%);
}

/* ── Syarat lulus, kesiapan, kata hari ini ──────────────────────────── */
.pass-rules {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
}

.pass-rule {
  padding: 12px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  text-align: center;

  &__num {
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1.1;

    small {
      font-size: 0.8rem;
      font-weight: 500;
      opacity: 0.6;
    }
  }
}

.ready-part.is-weakest { outline: 2px solid currentcolor; outline-offset: -2px; }

.kanji-day {
  display: flex;
  flex-direction: column;
  padding: 12px 16px;
  border-radius: 12px;
  background: rgba(var(--v-theme-info), 0.08);
  gap: 8px;

  &__head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
  }

  &__body {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  &__char {
    display: grid;
    flex: none;
    border-radius: 14px;
    background: rgb(var(--v-theme-surface));
    block-size: 76px;
    font-size: 2.8rem;
    font-weight: 700;
    inline-size: 76px;
    line-height: 1;
    place-items: center;
  }

  &__examples {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  &__ex {
    display: flex;
    flex-direction: column;
    padding: 6px 12px;
    border-radius: 10px;
    background: rgb(var(--v-theme-surface));
    min-inline-size: 110px;
  }

  &__word {
    font-size: 1.15rem;
    font-weight: 700;
  }
}

.leading-none { line-height: 1; }

.badges {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(auto-fill, minmax(104px, 1fr));
}

.badge-tile {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px 8px 10px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 14px;
  background: none;
  color: inherit;
  cursor: pointer;
  gap: 6px;
  text-align: center;
  transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;

  &:hover { transform: translateY(-2px); }
  &:focus-visible { outline: 2px solid rgb(var(--v-theme-primary)); }
  &.is-picked { border-color: rgb(var(--v-theme-primary)); }

  &__icon {
    display: grid;
    border-radius: 50%;
    background: rgba(var(--v-theme-on-surface), 0.08);
    block-size: 48px;
    color: rgba(var(--v-theme-on-surface), 0.45);
    inline-size: 48px;
    place-items: center;
  }

  &__name {
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1.2;
    min-block-size: 2.4em;
  }

  &:not(.is-unlocked) { opacity: 0.8; }

  &.is-unlocked {
    background: rgba(var(--badge-color), 0.08);
    border-color: rgba(var(--badge-color), 0.45);

    .badge-tile__icon {
      background: rgba(var(--badge-color), 0.2);
      color: rgb(var(--badge-color));
    }
  }
}

.fade-enter-active,
.fade-leave-active { transition: opacity 0.15s ease; }

.fade-enter-from,
.fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
  .dash-tile,
  .week__bar,
  .roadmap__node,
  .badge-tile { transition: none; }
}
</style>
