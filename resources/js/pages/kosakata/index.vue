<script setup>
import { $api } from '@/utils/api'

const { t, locale } = useI18n()

const chapters = ref([])
const chaptersLoading = ref(true)
const chaptersError = ref(false)

const selectedChapter = ref(1)
const words = ref([])
const wordsLoading = ref(true)
const wordsError = ref(false)
const search = ref('')

async function loadChapters() {
  chaptersLoading.value = true
  chaptersError.value = false
  try {
    const res = await $api('/vocabulary/chapters')
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
    const res = await $api('/vocabulary', { query: { chapter: selectedChapter.value } })
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
const chapterTitle = computed(() => currentChapter.value
  ? (locale.value === 'en' ? currentChapter.value.title_en : currentChapter.value.title_id)
  : '')

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

function selectChapter(order) {
  selectedChapter.value = order
  search.value = ''
}

watch(selectedChapter, loadWords)

onMounted(async () => {
  await loadChapters()
  await loadWords()
})
</script>

<template>
  <div>
    <div class="mb-4">
      <h4 class="text-h4 mb-1">
        {{ t('vocabulary.title') }}
      </h4>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('vocabulary.subtitle') }}
      </p>
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
