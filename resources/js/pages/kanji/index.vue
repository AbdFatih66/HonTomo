<script setup>
import { $api } from '@/utils/api'
import { useDebounceFn } from '@vueuse/core'
import KanjiCharacterDetail from '@/components/kanji/KanjiCharacterDetail.vue'

const { t } = useI18n()
const router = useRouter()

const jlptLevel = ref('N5')
const search = ref('')
const page = ref(1)
// 'level' = urutan bawaan per level JLPT (order column). 'curriculum' =
// urutan sesuai kemunculan pertama di Pelajaran (earliest_lesson_order) —
// lihat KanjiController::index() docblock.
const sortMode = ref('level')
const onlyLearned = ref(false)

const items = ref([])
const availableLevels = ref([])
const total = ref(0)
const learnedMeta = ref(null)
const lastPage = ref(1)
const isLoading = ref(true)
const hasError = ref(false)

// Level selector always reads left -> right: N5 (easiest) ... N1 (hardest).
// The API used to return these alphabetically (N1 first), which put the
// default N5 tab at the far right — sort here too so the UI never depends
// on backend ordering.
const LEVEL_ORDER = ['N5', 'N4', 'N3', 'N2', 'N1']
const sortedLevels = computed(() => [...availableLevels.value].sort((a, b) => {
  const ia = LEVEL_ORDER.indexOf(a)
  const ib = LEVEL_ORDER.indexOf(b)

  return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib)
}))

const sortOptions = [
  { value: 'level', label: 'kanji.sort_level', icon: 'tabler-sort-ascending-numbers' },
  { value: 'curriculum', label: 'kanji.sort_curriculum', icon: 'tabler-book-2' },
]

// "Only learned" can be empty for three different reasons — say which one,
// instead of a generic "no kanji match" that looks like the filter is broken.
const emptyMessage = computed(() => {
  if (onlyLearned.value && learnedMeta.value) {
    if (!learnedMeta.value.linked_kanji)
      return t('kanji.empty_learned_unlinked')
    if (!learnedMeta.value.has_progress)
      return t('kanji.empty_learned_no_progress')

    return t('kanji.empty_learned_level')
  }

  return t('kanji.empty_state')
})

const selected = ref(null)
const showDetail = ref(false)
const currentId = ref(null)
const justCompleted = ref(false)
let autoAdvanceTimer = null

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    const res = await $api('/kanji', {
      query: {
        jlpt_level: jlptLevel.value || undefined,
        search: search.value || undefined,
        page: page.value,
        sort: sortMode.value === 'curriculum' ? 'curriculum' : undefined,
        scope: onlyLearned.value ? 'learned' : undefined,
      },
    })

    items.value = res.data
    lastPage.value = res.last_page
    total.value = res.total ?? res.data.length
    learnedMeta.value = res.learned_meta ?? null
    availableLevels.value = res.available_jlpt_levels
  }
  catch {
    hasError.value = true
  }
  finally {
    isLoading.value = false
  }
}

const debouncedLoad = useDebounceFn(() => {
  page.value = 1
  load()
}, 300)

watch(jlptLevel, () => {
  page.value = 1
  load()
})
watch(search, debouncedLoad)
watch(page, load)
watch([sortMode, onlyLearned], () => {
  page.value = 1
  load()
})
onMounted(load)

const currentIndex = computed(() => items.value.findIndex(k => k.id === currentId.value))
const hasPrev = computed(() => currentIndex.value > 0)
const hasNext = computed(() => currentIndex.value >= 0 && currentIndex.value < items.value.length - 1)

async function openCharacter(k) {
  clearAutoAdvance()
  showDetail.value = true
  justCompleted.value = false
  currentId.value = k.id
  selected.value = null

  const data = await $api(`/kanji/${k.id}`)

  if (currentId.value === k.id)
    selected.value = data
}

function clearAutoAdvance() {
  if (autoAdvanceTimer) {
    clearTimeout(autoAdvanceTimer)
    autoAdvanceTimer = null
  }
}

// Same as Kana: writing the character correctly is itself the "answer",
// so advance straight to the next one instead of waiting for a manual
// click. Brief pause first so the learner sees the success highlight.
function onWritingComplete() {
  justCompleted.value = true
  clearAutoAdvance()

  if (hasNext.value)
    autoAdvanceTimer = setTimeout(() => step(1), 700)
}

function step(delta) {
  clearAutoAdvance()

  const target = items.value[currentIndex.value + delta]

  if (target)
    openCharacter(target)
}

onBeforeUnmount(clearAutoAdvance)

const detailProgressPercent = computed(() => {
  if (!items.value.length)
    return 0

  return Math.round(((currentIndex.value + 1) / items.value.length) * 100)
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('kanji.title') }}
      </h4>
      <VBtn
        color="primary"
        prepend-icon="tabler-pencil"
        @click="router.push({ name: 'kanji-quiz' })"
      >
        {{ t('kana.start_quiz') }}
      </VBtn>
    </div>

    <!-- Level picker: N5 (left) -> N1 (right) -->
    <div
      class="kanji-levels mb-5"
      role="tablist"
      :aria-label="t('kanji.title')"
    >
      <button
        v-for="level in sortedLevels"
        :key="level"
        type="button"
        role="tab"
        class="kanji-level"
        :class="[`kanji-level--${level.toLowerCase()}`, { 'kanji-level--active': jlptLevel === level }]"
        :aria-selected="jlptLevel === level"
        @click="jlptLevel = level"
      >
        <span class="kanji-level__code">{{ level }}</span>
        <span class="kanji-level__caption">{{ t(`kanji.level_caption.${level}`) }}</span>
      </button>
    </div>

    <div class="kanji-toolbar mb-5">
      <VTextField
        v-model="search"
        :placeholder="t('kanji.search_placeholder')"
        prepend-inner-icon="tabler-search"
        density="comfortable"
        variant="outlined"
        rounded="lg"
        hide-details
        class="kanji-search"
        clearable
      />

      <div
        class="kanji-seg"
        role="group"
      >
        <button
          v-for="opt in sortOptions"
          :key="opt.value"
          type="button"
          class="kanji-seg__btn"
          :class="{ 'kanji-seg__btn--active': sortMode === opt.value }"
          :aria-pressed="sortMode === opt.value"
          @click="sortMode = opt.value"
        >
          <VIcon
            :icon="opt.icon"
            size="18"
          />
          <span>{{ t(opt.label) }}</span>
        </button>
      </div>

      <button
        type="button"
        class="kanji-pill"
        :class="{ 'kanji-pill--active': onlyLearned }"
        :aria-pressed="onlyLearned"
        @click="onlyLearned = !onlyLearned"
      >
        <VIcon
          :icon="onlyLearned ? 'tabler-circle-check-filled' : 'tabler-school'"
          size="18"
        />
        <span>{{ t('kanji.only_learned') }}</span>
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

    <VAlert
      v-else-if="!items.length"
      type="info"
      variant="tonal"
    >
      {{ emptyMessage }}
      <template
        v-if="onlyLearned"
        #append
      >
        <VBtn
          size="small"
          variant="text"
          @click="onlyLearned = false"
        >
          {{ t('kanji.show_all') }}
        </VBtn>
      </template>
    </VAlert>

    <template v-else>
      <div class="text-caption text-medium-emphasis mb-3">
        {{ t('kanji.total_count', { n: total }) }}
      </div>

      <div class="kanji-grid mb-6">
        <button
          v-for="k in items"
          :key="k.id"
          class="kanji-card"
          :class="`kanji-card--${k.status}`"
          type="button"
          @click="openCharacter(k)"
        >
          <span
            v-if="k.stroke_count"
            class="kanji-card__strokes"
          >{{ k.stroke_count }}<small>画</small></span>
          <VIcon
            v-if="k.status === 'mastered'"
            icon="tabler-circle-check-filled"
            size="16"
            color="success"
            class="kanji-card__badge"
          />
          <VIcon
            v-else-if="k.status === 'learning'"
            icon="tabler-flame"
            size="16"
            color="warning"
            class="kanji-card__badge"
          />
          <span class="kanji-card__char">{{ k.character }}</span>
          <span class="kanji-card__meaning">{{ k.meaning }}</span>
        </button>
      </div>

      <div
        v-if="lastPage > 1"
        class="d-flex justify-center mb-4"
      >
        <VPagination
          v-model="page"
          :length="lastPage"
          :total-visible="7"
        />
      </div>

      <div class="d-flex justify-center">
        <VBtn
          :to="{ name: 'kanji-sources' }"
          variant="text"
          size="small"
          color="secondary"
        >
          {{ t('kanji.sources_link') }}
        </VBtn>
      </div>
    </template>

    <!-- Detail: full screen, same focus layout as Kana's writing/detail mode -->
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
            <KanjiCharacterDetail
              v-else
              :key="selected.character"
              :kanji-id="selected.id"
              :character="selected.character"
              :onyomi="selected.onyomi"
              :kunyomi="selected.kunyomi"
              :meaning="selected.meaning"
              :stroke-count="selected.stroke_count"
              :jlpt-level="selected.jlpt_level"
              :grade="selected.grade"
              :vocabulary="selected.vocabulary"
              :curriculum-words="selected.curriculum_words"
              :progress="selected.progress"
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
              {{ t('kana.character_of', { current: currentIndex + 1, total: items.length }) }}
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
/* ---------- level picker ---------- */
.kanji-levels {
  display: grid;
  grid-auto-columns: minmax(0, 1fr);
  grid-auto-flow: column;
  gap: 0.5rem;
  max-inline-size: 640px;
}

.kanji-level {
  --lv: var(--v-theme-primary);

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  padding: 10px 6px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.kanji-level--n5 { --lv: var(--v-theme-success); }
.kanji-level--n4 { --lv: var(--v-theme-info); }
.kanji-level--n3 { --lv: var(--v-theme-primary); }
.kanji-level--n2 { --lv: var(--v-theme-warning); }
.kanji-level--n1 { --lv: var(--v-theme-error); }

.kanji-level__code {
  color: rgba(var(--v-theme-on-surface), 0.85);
  font-size: 1.125rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  line-height: 1.3;
}

.kanji-level__caption {
  overflow: hidden;
  max-inline-size: 100%;
  color: rgba(var(--v-theme-on-surface), 0.55);
  font-size: 0.7rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.kanji-level:hover {
  border-color: rgba(var(--lv), 0.6);
  transform: translateY(-2px);
}

.kanji-level:focus-visible {
  outline: 2px solid rgb(var(--lv));
  outline-offset: 2px;
}

.kanji-level--active {
  border-color: rgb(var(--lv));
  background: rgba(var(--lv), 0.12);
  box-shadow: 0 6px 14px -6px rgba(var(--lv), 0.55);
}

.kanji-level--active .kanji-level__code {
  color: rgb(var(--lv));
}

/* ---------- toolbar ---------- */
.kanji-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.kanji-search {
  flex: 1 1 240px;
  max-inline-size: 360px;
}

/* segmented sort control (custom — VBtnToggle collapsed/overlapped in the flex row) */
.kanji-seg {
  display: inline-flex;
  flex-shrink: 0;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.kanji-seg__btn,
.kanji-pill {
  display: inline-flex;
  align-items: center;
  border: 0;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  gap: 6px;
  transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
  white-space: nowrap;
}

.kanji-seg__btn {
  justify-content: center;
  padding: 7px 14px;
  border-radius: 9px;
  background: transparent;
}

.kanji-seg__btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.kanji-seg__btn--active,
.kanji-seg__btn--active:hover {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.kanji-pill {
  flex-shrink: 0;
  padding: 9px 14px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
}

.kanji-pill:hover {
  border-color: rgba(var(--v-theme-primary), 0.6);
}

.kanji-pill--active {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
  color: rgb(var(--v-theme-primary));
}

.kanji-seg__btn:focus-visible,
.kanji-pill:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

/* ---------- kanji grid ---------- */
.kanji-grid {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: repeat(auto-fill, minmax(104px, 1fr));
}

.kanji-card {
  position: relative;
  display: flex;
  overflow: hidden;
  align-items: center;
  flex-direction: column;
  justify-content: center;
  padding: 22px 8px 12px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 14px;
  background: rgb(var(--v-theme-surface));
  cursor: pointer;
  gap: 4px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

/* status accent along the top edge */
.kanji-card::before {
  position: absolute;
  background: transparent;
  block-size: 3px;
  content: "";
  inline-size: 100%;
  inset-block-start: 0;
  inset-inline-start: 0;
}

.kanji-card--learning::before { background: rgb(var(--v-theme-warning)); }
.kanji-card--mastered::before { background: rgb(var(--v-theme-success)); }
.kanji-card--mastered { background: rgba(var(--v-theme-success), 0.06); }

.kanji-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.7);
  box-shadow: 0 10px 20px -10px rgba(var(--v-theme-primary), 0.5);
  transform: translateY(-3px);
}

.kanji-card:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

.kanji-card__char {
  color: rgba(var(--v-theme-on-surface), 0.92);
  font-family: 'Noto Sans JP', sans-serif;
  font-size: 2.25rem;
  line-height: 1.15;
  transition: color 0.15s ease;
}

.kanji-card:hover .kanji-card__char {
  color: rgb(var(--v-theme-primary));
}

.kanji-card__meaning {
  overflow: hidden;
  max-inline-size: 92px;
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.7rem;
  text-align: center;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.kanji-card__strokes {
  position: absolute;
  color: rgba(var(--v-theme-on-surface), 0.4);
  font-size: 0.65rem;
  inset-block-start: 8px;
  inset-inline-start: 8px;
}

.kanji-card__strokes small {
  margin-inline-start: 1px;
  font-size: 0.6rem;
}

.kanji-card__badge {
  position: absolute;
  inset-block-start: 7px;
  inset-inline-end: 7px;
}

@media (max-width: 599px) {
  .kanji-level__caption { display: none; }
  .kanji-search { max-inline-size: none; }
  .kanji-seg { inline-size: 100%; }
  .kanji-seg__btn { flex: 1; padding-inline: 8px; }
  .kanji-grid { grid-template-columns: repeat(auto-fill, minmax(88px, 1fr)); }
  .kanji-card__char { font-size: 2rem; }
}

@media (prefers-reduced-motion: reduce) {
  .kanji-level,
  .kanji-card,
  .kanji-seg__btn,
  .kanji-pill { transition: none; }

  .kanji-level:hover,
  .kanji-card:hover { transform: none; }
}
</style>
