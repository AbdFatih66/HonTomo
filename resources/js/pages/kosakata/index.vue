<script setup>
import { $api } from '@/utils/api'
import { useLevelChoice } from '@/composables/useLevelChoice'

const { t, locale } = useI18n()
const { level } = useLevelChoice()
const route = useRoute()
const router = useRouter()

const chapters = ref([])
const chaptersLoading = ref(true)
const chaptersError = ref(false)

const initialChapter = Number(route.query.chapter)
const selectedChapter = ref(initialChapter >= 1 && initialChapter <= 25 ? initialChapter : 1)
const words = ref([])
const wordsLoading = ref(true)
const wordsError = ref(false)
const search = ref('')

async function loadChapters() {
  chaptersLoading.value = true
  chaptersError.value = false
  try {
    const res = await $api('/vocabulary/chapters', { query: { level: level.value } })
    chapters.value = res.data ?? []
  }
  catch {
    chaptersError.value = true
  }
  finally {
    chaptersLoading.value = false
  }
}

async function loadWords() {
  wordsLoading.value = true
  wordsError.value = false
  try {
    const res = await $api('/vocabulary', { query: { chapter: selectedChapter.value, level: level.value } })
    words.value = res.data ?? []
  }
  catch {
    wordsError.value = true
  }
  finally {
    wordsLoading.value = false
  }
}

const currentChapter = computed(() => chapters.value.find(c => c.order === selectedChapter.value))
// Judul unit dari server sudah berawalan "Pelajaran 1: …" — buang awalan itu
// supaya tidak dobel dengan label "Pelajaran {n}" di judul kartu.
const chapterTitle = computed(() => {
  if (!currentChapter.value)
    return ''

  const raw = locale.value === 'en' ? currentChapter.value.title_en : currentChapter.value.title_id

  return (raw ?? '').replace(/^\s*(?:Pelajaran|Lesson|Bab|Chapter)\s*\d+\s*[:：\-–—]?\s*/i, '').trim()
})

const filteredWords = computed(() => {
  if (!search.value.trim())
    return words.value

  const q = search.value.trim().toLowerCase()

  return words.value.filter(w =>
    w.japanese?.toLowerCase().includes(q)
    || w.hiragana?.toLowerCase().includes(q)
    || w.romaji?.toLowerCase().includes(q)
    || w.meaning?.toLowerCase().includes(q),
  )
})

// Kuis kosakata pelajaran ini (1 kuis; Pelajaran 1 & 2 punya 2).
const quizzes = computed(() => currentChapter.value?.quiz ?? [])

function startQuiz(quiz) {
  router.push({ name: 'learn-id', params: { id: quiz.id } })
}

function quizLabel(quiz, index) {
  return quizzes.value.length > 1
    ? t('vocabulary.quiz_part', { n: index + 1 })
    : t('vocabulary.quiz_start')
}

function selectChapter(order) {
  selectedChapter.value = order
  search.value = ''
}

watch(selectedChapter, loadWords)

// Ganti level (N5 / N4): kembali ke pelajaran pertama level itu, muat ulang menu bab dan kata.
watch(level, async () => {
  search.value = ''
  selectedChapter.value = 1
  await loadChapters()
  await loadWords()
})

onMounted(async () => {
  await loadChapters()

  // ?chapter=… yang tidak ada di level ini (mis. 7 saat N4) → mulai dari pelajaran pertama yang ada.
  if (chapters.value.length && !chapters.value.some(c => c.order === selectedChapter.value))
    selectedChapter.value = chapters.value[0].order

  await loadWords()
})
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between mb-4 ga-2">
      <div>
        <h4 class="text-h4 mb-1">
          {{ t('vocabulary.title') }}
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ level === 'N4' ? t('vocabulary.subtitle_n4') : t('vocabulary.subtitle') }}
        </p>
      </div>
      <LevelSwitch />
    </div>

    <div
      v-if="chaptersLoading"
      class="d-flex justify-center pa-6"
    >
      <VProgressCircular indeterminate color="primary" />
    </div>

    <VAlert v-else-if="chaptersError" type="error" variant="tonal">
      {{ t('common.error') }}
      <template #append>
        <VBtn size="small" variant="text" @click="loadChapters">
          {{ t('common.retry') }}
        </VBtn>
      </template>
    </VAlert>

    <template v-else>
      <!-- Chapter selector: same pill-button visual language as Kana/Kanji/Referensi Tata Bahasa. -->
      <div
        class="kosakata-chapter-tabs mb-4"
        role="tablist"
        :aria-label="t('vocabulary.title')"
      >
        <button
          v-for="c in chapters"
          :key="c.order"
          type="button"
          role="tab"
          class="kosakata-chapter-tabs__btn"
          :class="{ 'kosakata-chapter-tabs__btn--active': selectedChapter === c.order }"
          :aria-selected="selectedChapter === c.order"
          @click="selectChapter(c.order)"
        >
          {{ c.order }}
        </button>
      </div>

      <VCard>
        <VCardItem>
          <VCardTitle>
            {{ t('vocabulary.chapter_label', { n: selectedChapter }) }}
            <span v-if="chapterTitle" class="text-medium-emphasis">— {{ chapterTitle }}</span>
          </VCardTitle>
          <template #append>
            <VChip size="small" variant="tonal" color="primary">
              {{ t('vocabulary.word_count', { n: currentChapter?.count ?? words.length }) }}
            </VChip>
          </template>
        </VCardItem>

        <VCardText>
          <div
            v-if="quizzes.length"
            class="d-flex flex-wrap align-center gap-3 mb-4"
          >
            <span class="text-body-1 font-weight-medium">
              <VIcon icon="tabler-bulb" size="20" class="me-1" />
              {{ t('vocabulary.quiz_title') }}
            </span>
            <VBtn
              v-for="(quiz, i) in quizzes"
              :key="quiz.id"
              size="small"
              color="primary"
              :variant="quiz.status === 'completed' || quiz.status === 'mastered' ? 'tonal' : 'flat'"
              :prepend-icon="quiz.status === 'completed' || quiz.status === 'mastered' ? 'tabler-check' : 'tabler-player-play-filled'"
              @click="startQuiz(quiz)"
            >
              {{ quizLabel(quiz, i) }} · {{ t('vocabulary.quiz_questions', { n: quiz.question_count }) }}
            </VBtn>
          </div>

          <AppTextField
            v-model="search"
            :placeholder="t('vocabulary.search_placeholder')"
            prepend-inner-icon="tabler-search"
            density="compact"
            class="mb-4"
            style="max-inline-size: 360px;"
          />

          <div v-if="wordsLoading" class="d-flex justify-center pa-6">
            <VProgressCircular indeterminate color="primary" />
          </div>

          <VAlert v-else-if="wordsError" type="error" variant="tonal">
            {{ t('common.error') }}
            <template #append>
              <VBtn size="small" variant="text" @click="loadWords">
                {{ t('common.retry') }}
              </VBtn>
            </template>
          </VAlert>

          <VAlert v-else-if="!filteredWords.length" type="info" variant="tonal">
            {{ t('vocabulary.empty') }}
          </VAlert>

          <VTable v-else density="comfortable">
            <thead>
              <tr>
                <th>{{ t('vocabulary.col_japanese') }}</th>
                <th>{{ t('vocabulary.col_hiragana') }}</th>
                <th>{{ t('vocabulary.col_romaji') }}</th>
                <th>{{ t('vocabulary.col_meaning') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="w in filteredWords" :key="w.id">
                <td class="text-h6 font-weight-medium">
                  {{ w.japanese }}
                </td>
                <td>{{ w.hiragana }}</td>
                <td class="text-medium-emphasis">
                  {{ w.romaji }}
                </td>
                <td>{{ w.meaning }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>
      </VCard>
    </template>
  </div>
</template>

<style scoped>
/* ---------- chapter selector — same visual language as kana/kanji ---------- */
.kosakata-chapter-tabs {
  display: flex;
  flex-wrap: wrap;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.kosakata-chapter-tabs__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-inline-size: 40px;
  padding: 8px 12px;
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

.kosakata-chapter-tabs__btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.kosakata-chapter-tabs__btn--active,
.kosakata-chapter-tabs__btn--active:hover {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.kosakata-chapter-tabs__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}
</style>
