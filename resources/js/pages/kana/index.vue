<script setup>
import { $api } from '@/utils/api'
import KanaCharacterDetail from '@/components/kana/KanaCharacterDetail.vue'

const { t } = useI18n()
const router = useRouter()

const script = ref('hiragana')
const chart = ref(null)
const isLoading = ref(true)
const hasError = ref(false)
const selected = ref(null)
const showDetail = ref(false)
const currentId = ref(null)
const justCompleted = ref(false)
let autoAdvanceTimer = null

// Gojuon rows in the traditional order, with the 5 columns (a/i/u/e/o) they
// each occupy — used purely for chart layout, since や/わ rows skip columns.
const ROW_ORDER = ['a', 'ka', 'sa', 'ta', 'na', 'ha', 'ma', 'ya', 'ra', 'wa', 'n']
const DAKUTEN_ROW_ORDER = ['ga', 'za', 'da', 'ba']
const YOON_ROW_ORDER = ['ka', 'sa', 'ta', 'na', 'ha', 'ma', 'ra']
const YOON_DAKUTEN_ROW_ORDER = ['ga', 'za', 'da', 'ba', 'pa']

const SCRIPT_OPTIONS = [
  { value: 'hiragana', glyph: 'あ' },
  { value: 'katakana', glyph: 'ア' },
]

// Which single section is shown below the selector — avoids stacking every
// section (gojuon..yoon) and forcing a long scroll to reach the later ones.
const section = ref('gojuon')
const SECTION_OPTIONS = [
  { value: 'gojuon', label: 'kana.gojuon' },
  { value: 'dakuten', label: 'kana.dakuten' },
  { value: 'handakuten', label: 'kana.handakuten' },
  { value: 'yoon', label: 'kana.yoon' },
  { value: 'yoon_dakuten', label: 'kana.yoon_dakuten' },
]

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    chart.value = await $api('/kana', { query: { script: script.value } })
  }
  catch {
    hasError.value = true
  }
  finally {
    isLoading.value = false
  }
}

function rowsFor(list, order) {
  const byRow = {}

  for (const k of list) {
    if (!byRow[k.row])
      byRow[k.row] = []
    byRow[k.row].push(k)
  }

  return order
    .filter(r => byRow[r])
    .map(r => byRow[r].sort((a, b) => a.column - b.column))
}

const gojuonRows = computed(() => chart.value ? rowsFor(chart.value.gojuon, ROW_ORDER) : [])
const dakutenRows = computed(() => chart.value ? rowsFor(chart.value.dakuten, DAKUTEN_ROW_ORDER) : [])
const handakutenRow = computed(() => chart.value ? rowsFor(chart.value.handakuten, ['pa'])[0] || [] : [])
const yoonRows = computed(() => chart.value ? rowsFor(chart.value.yoon ?? [], YOON_ROW_ORDER) : [])
const yoonDakutenRows = computed(() => chart.value ? rowsFor(chart.value.yoon_dakuten ?? [], YOON_DAKUTEN_ROW_ORDER) : [])

// Every character in chart order (basic -> dakuten -> handakuten -> yoon),
// so the dialog can step through them with Previous / Next.
const flatKana = computed(() => [
  ...gojuonRows.value.flat(),
  ...dakutenRows.value.flat(),
  ...handakutenRow.value,
  ...yoonRows.value.flat(),
  ...yoonDakutenRows.value.flat(),
])

const currentIndex = computed(() => flatKana.value.findIndex(k => k.id === currentId.value))
const hasPrev = computed(() => currentIndex.value > 0)
const hasNext = computed(() => currentIndex.value >= 0 && currentIndex.value < flatKana.value.length - 1)

async function openCharacter(k) {
  clearAutoAdvance()
  showDetail.value = true
  justCompleted.value = false
  currentId.value = k.id
  selected.value = null

  const data = await $api(`/kana/${k.id}`)

  // ignore a slow response if the user already moved on
  if (currentId.value === k.id)
    selected.value = data
}

function clearAutoAdvance() {
  if (autoAdvanceTimer) {
    clearTimeout(autoAdvanceTimer)
    autoAdvanceTimer = null
  }
}

// Writing the character correctly is itself the "answer" — advance straight
// to the next one instead of waiting for a manual click, same as getting a
// multiple-choice question right in the quiz. A brief pause first lets the
// learner see HanziWriter's own success highlight before the view changes.
function onWritingComplete() {
  justCompleted.value = true
  clearAutoAdvance()

  if (hasNext.value)
    autoAdvanceTimer = setTimeout(() => step(1), 700)
}

function step(delta) {
  clearAutoAdvance()

  const target = flatKana.value[currentIndex.value + delta]

  if (target)
    openCharacter(target)
}

onBeforeUnmount(clearAutoAdvance)

// Progress through the whole chart, shown in the full-screen header.
const detailProgressPercent = computed(() => {
  if (!flatKana.value.length)
    return 0

  return Math.round(((currentIndex.value + 1) / flatKana.value.length) * 100)
})

watch(script, load)
watch(showDetail, open => {
  if (!open)
    clearAutoAdvance()
})
onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('kana.title') }}
      </h4>
      <VBtn
        color="primary"
        prepend-icon="tabler-pencil"
        @click="router.push({ name: 'kana-quiz' })"
      >
        {{ t('kana.start_quiz') }}
      </VBtn>
    </div>

    <div
      class="kana-script mb-6"
      role="tablist"
      :aria-label="t('kana.title')"
    >
      <button
        v-for="sc in SCRIPT_OPTIONS"
        :key="sc.value"
        type="button"
        role="tab"
        class="kana-script__btn"
        :class="{ 'kana-script__btn--active': script === sc.value }"
        :aria-selected="script === sc.value"
        @click="script = sc.value"
      >
        <span class="kana-script__glyph">{{ sc.glyph }}</span>
        <span class="kana-script__text">
          <span class="kana-script__label">{{ t(`kana.${sc.value}`) }}</span>
          <span class="kana-script__caption">{{ t('kana.script_caption') }}</span>
        </span>
      </button>
    </div>

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
      <!-- Section selector: only one group (gojuon/dakuten/.../yoon) is
           rendered below at a time, so switching sections is a click
           instead of scrolling past everything above it. -->
      <div
        class="kana-section-tabs mb-4"
        role="tablist"
        :aria-label="t('kana.title')"
      >
        <button
          v-for="opt in SECTION_OPTIONS"
          :key="opt.value"
          type="button"
          role="tab"
          class="kana-section-tabs__btn"
          :class="{ 'kana-section-tabs__btn--active': section === opt.value }"
          :aria-selected="section === opt.value"
          @click="section = opt.value"
        >
          {{ t(opt.label) }}
        </button>
      </div>

      <VCard
        v-if="section === 'gojuon'"
        class="pa-4"
      >
        <div class="kana-chart">
          <div
            v-for="(row, i) in gojuonRows"
            :key="i"
            class="kana-chart__row"
          >
            <button
              v-for="k in row"
              :key="k.id"
              class="kana-card"
              type="button"
              @click="openCharacter(k)"
            >
              <span class="kana-card__char">{{ k.character }}</span>
              <span class="kana-card__romaji">{{ k.romaji }}</span>
            </button>
          </div>
        </div>
      </VCard>

      <VCard
        v-else-if="section === 'dakuten'"
        class="pa-4"
      >
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ t('kana.dakuten_intro') }}
        </p>
        <div class="kana-chart">
          <div
            v-for="(row, i) in dakutenRows"
            :key="i"
            class="kana-chart__row"
          >
            <button
              v-for="k in row"
              :key="k.id"
              class="kana-card kana-card--dakuten"
              type="button"
              @click="openCharacter(k)"
            >
              <span class="kana-card__char">{{ k.character }}</span>
              <span class="kana-card__romaji">{{ k.romaji }}</span>
            </button>
          </div>
        </div>
      </VCard>

      <VCard
        v-else-if="section === 'handakuten'"
        class="pa-4"
      >
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ t('kana.handakuten_intro') }}
        </p>
        <div class="kana-chart">
          <div class="kana-chart__row">
            <button
              v-for="k in handakutenRow"
              :key="k.id"
              class="kana-card kana-card--handakuten"
              type="button"
              @click="openCharacter(k)"
            >
              <span class="kana-card__char">{{ k.character }}</span>
              <span class="kana-card__romaji">{{ k.romaji }}</span>
            </button>
          </div>
        </div>
      </VCard>

      <VCard
        v-else-if="section === 'yoon'"
        class="pa-4"
      >
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ t('kana.yoon_intro') }}
        </p>
        <div class="kana-chart">
          <div
            v-for="(row, i) in yoonRows"
            :key="i"
            class="kana-chart__row"
          >
            <button
              v-for="k in row"
              :key="k.id"
              class="kana-card kana-card--yoon"
              type="button"
              @click="openCharacter(k)"
            >
              <span class="kana-card__char kana-card__char--yoon">{{ k.character }}</span>
              <span class="kana-card__romaji">{{ k.romaji }}</span>
            </button>
          </div>
        </div>
      </VCard>

      <VCard
        v-else
        class="pa-4"
      >
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ t('kana.yoon_dakuten_intro') }}
        </p>
        <div class="kana-chart">
          <div
            v-for="(row, i) in yoonDakutenRows"
            :key="i"
            class="kana-chart__row"
          >
            <button
              v-for="k in row"
              :key="k.id"
              class="kana-card kana-card--yoon kana-card--yoon-dakuten"
              type="button"
              @click="openCharacter(k)"
            >
              <span class="kana-card__char kana-card__char--yoon">{{ k.character }}</span>
              <span class="kana-card__romaji">{{ k.romaji }}</span>
            </button>
          </div>
        </div>
      </VCard>
    </template>

    <!-- Writing mode: full screen, same focus layout as the lesson player
         (no page chrome, progress bar on top, navigation pinned at the bottom). -->
    <VDialog
      v-model="showDetail"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <div class="kana-fs">
        <header class="kana-fs__header">
          <VBtn
            icon="tabler-x"
            variant="text"
            color="secondary"
            :aria-label="t('common.back')"
            @click="showDetail = false"
          />
          <div
            class="lp__bar"
            role="progressbar"
            :aria-valuenow="detailProgressPercent"
            aria-valuemin="0"
            aria-valuemax="100"
          >
            <div
              class="lp__fill"
              :style="{ inlineSize: `${detailProgressPercent}%` }"
            />
          </div>
        </header>

        <main class="kana-fs__body">
          <div class="kana-fs__content">
            <div
              v-if="!selected"
              class="d-flex justify-center pa-10"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
            </div>
            <KanaCharacterDetail
              v-else
              :key="selected.character"
              :character="selected.character"
              :romaji="selected.romaji"
              :stroke-count="selected.stroke_count"
              :usage-note="selected.usage_note"
              @complete="onWritingComplete"
            />
          </div>
        </main>

        <footer class="kana-fs__footer">
          <div class="kana-fs__footer-inner">
            <VBtn
              variant="tonal"
              prepend-icon="tabler-chevron-left"
              :disabled="!hasPrev"
              @click="step(-1)"
            >
              {{ t('kana.prev_character') }}
            </VBtn>
            <span class="text-caption text-medium-emphasis">
              {{ t('kana.character_of', { current: currentIndex + 1, total: flatKana.length }) }}
            </span>
            <VBtn
              :variant="justCompleted ? 'flat' : 'tonal'"
              color="primary"
              append-icon="tabler-chevron-right"
              :disabled="!hasNext"
              @click="step(1)"
            >
              {{ t('kana.next_character') }}
            </VBtn>
          </div>
        </footer>
      </div>
    </VDialog>
  </div>
</template>

<style scoped>
/* ---------- hiragana / katakana selector ---------- */
.kana-script {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  max-inline-size: 480px;
}

.kana-script__btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 14px;
  padding: 14px 20px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 16px;
  background: rgb(var(--v-theme-surface));
  cursor: pointer;
  text-align: start;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.kana-script__btn:hover {
  border-color: rgba(var(--v-theme-primary), 0.5);
  transform: translateY(-2px);
}

.kana-script__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

.kana-script__btn--active {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.08);
  box-shadow: 0 8px 18px -8px rgba(var(--v-theme-primary), 0.55);
}

.kana-script__glyph {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(var(--v-theme-on-surface), 0.06);
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-family: 'Noto Sans JP', sans-serif;
  font-size: 1.75rem;
  line-height: 1;
  transition: background 0.15s ease, color 0.15s ease;
}

.kana-script__btn--active .kana-script__glyph {
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.kana-script__text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.kana-script__label {
  color: rgba(var(--v-theme-on-surface), 0.9);
  font-size: 1.05rem;
  font-weight: 600;
  transition: color 0.15s ease;
}

.kana-script__btn--active .kana-script__label {
  color: rgb(var(--v-theme-primary));
}

.kana-script__caption {
  color: rgba(var(--v-theme-on-surface), 0.55);
  font-size: 0.75rem;
}

@media (max-width: 599px) {
  .kana-script {
    max-inline-size: none;
  }

  .kana-script__btn {
    padding: 12px 14px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .kana-script__btn { transition: none; }
  .kana-script__btn:hover { transform: none; }
}

/* ---------- section selector (gojuon / dakuten / handakuten / yoon...) ---------- */
.kana-section-tabs {
  display: inline-flex;
  flex-wrap: wrap;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.kana-section-tabs__btn {
  padding: 8px 16px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  transition: background 0.15s ease, color 0.15s ease;
}

.kana-section-tabs__btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.kana-section-tabs__btn--active,
.kana-section-tabs__btn--active:hover {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.kana-section-tabs__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

@media (max-width: 599px) {
  .kana-section-tabs {
    display: flex;
    inline-size: 100%;
  }

  .kana-section-tabs__btn {
    flex: 1 1 auto;
    padding-inline: 10px;
    text-align: center;
  }
}

.kana-chart {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.kana-chart__row {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

/* ---------- kana cards (same visual language as .kanji-card) ---------- */
.kana-card {
  --kc: var(--v-theme-primary);

  position: relative;
  display: flex;
  overflow: hidden;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 68px;
  height: 68px;
  padding: 6px 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 14px;
  background: rgb(var(--v-theme-surface));
  cursor: pointer;
  gap: 2px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

.kana-card--dakuten { --kc: var(--v-theme-info); }
.kana-card--handakuten { --kc: var(--v-theme-warning); }
.kana-card--yoon { --kc: var(--v-theme-success); }
.kana-card--yoon-dakuten { --kc: var(--v-theme-error); }

.kana-card:hover {
  border-color: rgba(var(--kc), 0.7);
  box-shadow: 0 10px 20px -10px rgba(var(--kc), 0.5);
  transform: translateY(-3px);
}

.kana-card:focus-visible {
  outline: 2px solid rgb(var(--kc));
  outline-offset: 2px;
}

.kana-card__char {
  color: rgba(var(--v-theme-on-surface), 0.92);
  font-family: 'Noto Sans JP', sans-serif;
  font-size: 1.6rem;
  line-height: 1.15;
  transition: color 0.15s ease;
}

.kana-card__char--yoon {
  font-size: 1.35rem;
}

.kana-card:hover .kana-card__char {
  color: rgb(var(--kc));
}

.kana-card__romaji {
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.65rem;
}

@media (max-width: 599px) {
  .kana-card {
    width: 58px;
    height: 58px;
  }

  .kana-card__char {
    font-size: 1.4rem;
  }

  .kana-card__char--yoon {
    font-size: 1.15rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .kana-card { transition: none; }
  .kana-card:hover { transform: none; }
}
</style>
