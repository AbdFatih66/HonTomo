<script setup>
// Pembahasan Tes JLPT — terbuka SETELAH tes selesai (kunci tidak pernah ada di
// klien selama ujian). Satu halaman untuk kedua format soal:
//  - mock    : JlptMockQuestion mode review + pembahasan + transkrip + putar ulang audio
//  - classic : stem + pilihan dengan jawaban Anda/kunci ditandai (+ pembahasan bila ada)
// Jawaban 1-based dari server; JlptMockQuestion 0-based → dikonversi di sini.
import JlptMockImage from '@/components/jlpt-mock/JlptMockImage.vue'
import JlptMockPassage from '@/components/jlpt-mock/JlptMockPassage.vue'
import JlptMockQuestion from '@/components/jlpt-mock/JlptMockQuestion.vue'
import JlptMockText from '@/components/jlpt-mock/JlptMockText.vue'
import JlptPassage from '@/components/jlpt/JlptPassage.vue'
import JlptText from '@/components/jlpt/JlptText.vue'
import { $api } from '@/utils/api'
import { useStorage } from '@vueuse/core'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const data = ref(null)
const isLoading = ref(true)
const hasError = ref(false)
const onlyWrong = ref(false)
const furigana = useStorage('jlptTest:furigana', false)
const reviewAudio = ref(null)

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    data.value = await $api(`/jlpt-test/attempts/${route.params.attempt}/review`)
  }
  catch (e) {
    if ([404, 409].includes(e?.response?.status))
      router.replace({ name: 'jlpt-test' })
    else
      hasError.value = true
  }
  finally {
    isLoading.value = false
  }
}

const isMock = computed(() => data.value?.format === 'mock')

function packTitle() {
  const title = data.value?.pack_title

  return title ? (title[locale.value] ?? title.id ?? title.en ?? '') : ''
}

function sectionTitle(s) {
  const title = s.test.title

  return typeof title === 'string' ? title : (s.test.jp ?? title?.[locale.value] ?? title?.id ?? '')
}

const isWrong = (s, q) => s.answers[q.id] !== q.answer

// ── Format mock: mondai → groups → questions (filter "hanya yang salah") ──
function mockMondai(s) {
  return s.test.mondai
    .map(m => ({
      id: m.id,
      label: m.label,
      groups: m.groups
        .map(g => ({ passage: g.passage ?? null, intro: g.intro ?? '', questions: g.questions.filter(q => !onlyWrong.value || isWrong(s, q)) }))
        .filter(g => g.questions.length),
    }))
    .filter(m => m.groups.length)
}

// ── Format classic: soal datar, dikelompokkan per もんだい ──
function classicGroups(s) {
  const byMondai = new Map()
  const passages = s.test.passages ?? {}
  let last = null

  for (const q of s.test.questions) {
    if (onlyWrong.value && !isWrong(s, q)) {
      last = q.passage ?? null
      continue
    }
    if (!byMondai.has(q.mondai))
      byMondai.set(q.mondai, [])
    const showPassage = q.passage && q.passage !== last ? (passages[q.passage] ?? null) : null

    last = q.passage ?? null
    byMondai.get(q.mondai).push({ q, passage: showPassage })
  }

  return [...byMondai.entries()].map(([mondai, items]) => ({ mondai, items }))
}

const sections = computed(() => (data.value?.sections ?? []).map(s => ({
  ...s,
  mockMondai: isMock.value ? mockMondai(s) : [],
  classicGroups: isMock.value ? [] : classicGroups(s),
})))

const nothingToShow = computed(() => sections.value.every(s => !s.mockMondai.length && !s.classicGroups.length))

function playReview(src) {
  if (!src || !reviewAudio.value)
    return
  reviewAudio.value.src = src
  reviewAudio.value.play().catch(() => {})
}

function choiceState(s, q, n) {
  if (n === q.answer)
    return 'is-correct'

  return s.answers[q.id] === n ? 'is-wrong' : ''
}

onMounted(load)
</script>

<template>
  <div class="jlpt-review mx-auto">
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
      v-else-if="hasError"
      class="text-center pa-10"
    >
      <p class="mb-4">
        {{ t('common.error') }}
      </p>
      <VBtn @click="load">
        {{ t('common.retry') }}
      </VBtn>
    </div>

    <template v-else-if="data">
      <VBtn
        variant="text"
        size="small"
        prepend-icon="tabler-chevron-left"
        class="mb-2"
        :to="{ name: 'jlpt-test-recap-attempt', params: { attempt: data.attempt_id } }"
      >
        {{ t('jlptTest.review.back') }}
      </VBtn>

      <div class="d-flex align-center flex-wrap gap-4 mb-4">
        <div class="flex-grow-1">
          <h4 class="text-h4 mb-0">
            {{ t('jlptMock.review_title') }}
          </h4>
          <div class="text-body-2 text-medium-emphasis">
            {{ packTitle() }}
          </div>
        </div>
        <VSwitch
          v-model="onlyWrong"
          :label="t('jlptMock.only_wrong')"
          color="primary"
          density="compact"
          hide-details
        />
        <VSwitch
          v-if="isMock"
          v-model="furigana"
          :label="t('jlptMock.furigana_short')"
          color="primary"
          density="compact"
          hide-details
        />
      </div>

      <VAlert
        v-if="nothingToShow"
        type="success"
        variant="tonal"
      >
        {{ t('jlptMock.all_correct') }}
      </VAlert>

      <template
        v-for="s in sections"
        :key="s.key"
      >
        <template v-if="s.mockMondai.length || s.classicGroups.length">
          <h6 class="text-h6 mt-6 mb-2">
            {{ sectionTitle(s) }}
            <VChip
              size="small"
              variant="tonal"
              class="ms-2"
            >
              {{ t('jlptTest.recap.correct_of', { c: s.correct, n: s.total }) }}
            </VChip>
          </h6>

          <!-- ── format mock ── -->
          <template
            v-for="m in s.mockMondai"
            :key="m.id"
          >
            <div class="jlpt-mondai">
              <span class="jlpt-mondai__label">{{ m.label }}</span>
            </div>
            <div
              v-for="(g, gi) in m.groups"
              :key="`${m.id}-${gi}`"
              class="mb-4"
            >
              <JlptMockPassage
                v-if="g.passage"
                :passage="g.passage"
                :show-furigana="furigana"
                class="mb-3"
              />
              <VCard
                v-for="q in g.questions"
                :key="q.id"
                variant="outlined"
                class="mb-3"
              >
                <VCardText>
                  <JlptMockQuestion
                    :q="{ ...q, answer: q.answer - 1 }"
                    :model-value="s.answers[q.id] ? s.answers[q.id] - 1 : null"
                    :show-furigana="furigana"
                    mode="review"
                  />
                  <VAlert
                    v-if="!s.answers[q.id]"
                    type="warning"
                    variant="tonal"
                    density="compact"
                    class="mt-3"
                  >
                    {{ t('jlptMock.not_answered') }}
                  </VAlert>
                  <p
                    v-if="q.exp"
                    class="text-body-2 mt-3 mb-0"
                  >
                    <strong>{{ t('jlptMock.explanation') }}:</strong> {{ q.exp }}
                  </p>

                  <div
                    v-if="q.transcript"
                    class="jlpt-transcript mt-3"
                  >
                    <div class="d-flex align-center mb-1">
                      <span class="text-body-2 font-weight-medium">{{ t('jlptMock.transcript') }}</span>
                      <VBtn
                        v-if="q.audio"
                        size="x-small"
                        variant="tonal"
                        prepend-icon="tabler-player-play"
                        class="ms-3"
                        @click="playReview(q.audio)"
                      >
                        {{ t('jlptMock.play') }}
                      </VBtn>
                    </div>
                    <div
                      v-for="(turn, ti) in q.transcript"
                      :key="ti"
                      class="text-body-2"
                    >
                      <JlptMockText
                        :text="turn.text"
                        :show-furigana="furigana"
                      />
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </div>
          </template>

          <!-- ── format classic ── -->
          <section
            v-for="g in s.classicGroups"
            :key="g.mondai"
            class="mb-6"
          >
            <div class="jlpt-mondai">
              <span class="jlpt-mondai__label">もんだい {{ g.mondai }}</span>
            </div>
            <template
              v-for="{ q, passage } in g.items"
              :key="q.id"
            >
              <JlptPassage
                v-if="passage"
                :passage="passage"
              />
              <VCard
                variant="outlined"
                class="mb-3"
              >
                <VCardText>
                  <div class="jlpt-q__stem">
                    <span class="jlpt-q__no">{{ q.label ?? q.no ?? q.id }}</span>
                    <span v-if="q.stem"><JlptText :text="q.stem" /></span>
                  </div>
                  <div
                    v-if="q.choices"
                    class="jlpt-rchoices"
                  >
                    <span
                      v-for="(c, ci) in q.choices"
                      :key="ci"
                      class="jlpt-rchoice"
                      :class="choiceState(s, q, ci + 1)"
                    >{{ ci + 1 }}
                      <JlptMockImage
                        v-if="c && typeof c === 'object' && c.image"
                        :src="c.image"
                        :alt="c.alt"
                      />
                      <JlptText
                        v-else
                        :text="String(c)"
                      />
                    </span>
                  </div>
                  <div
                    v-else
                    class="jlpt-rchoices"
                  >
                    <span
                      v-for="n in (q.choice_count ?? 4)"
                      :key="n"
                      class="jlpt-rchoice"
                      :class="choiceState(s, q, n)"
                    >{{ n }}</span>
                  </div>
                  <VAlert
                    v-if="!s.answers[q.id]"
                    type="warning"
                    variant="tonal"
                    density="compact"
                    class="mt-3"
                  >
                    {{ t('jlptMock.not_answered') }}
                  </VAlert>
                  <p
                    v-if="q.exp"
                    class="text-body-2 mt-3 mb-0"
                  >
                    <strong>{{ t('jlptMock.explanation') }}:</strong> {{ q.exp }}
                  </p>
                  <p
                    v-if="q.script"
                    class="jlpt-transcript text-body-2 mt-3 mb-0"
                  >
                    <strong>{{ t('jlptMock.transcript') }}:</strong> {{ q.script }}
                  </p>
                </VCardText>
              </VCard>
            </template>
          </section>
        </template>
      </template>
    </template>

    <audio
      ref="reviewAudio"
      preload="none"
    />
  </div>
</template>

<style scoped>
.jlpt-review {
  max-inline-size: 860px;
}

.jlpt-mondai {
  border-block-end: 2px solid rgba(var(--v-theme-on-surface), 0.3);
  margin-block-end: 16px;
  padding-block-end: 8px;
}

.jlpt-mondai__label {
  font-size: 1.15rem;
  font-weight: 700;
}

.jlpt-transcript {
  border-inline-start: 3px solid rgba(var(--v-theme-primary), 0.5);
  padding-inline-start: 12px;
  white-space: pre-line;
}

.jlpt-q__stem {
  display: flex;
  align-items: baseline;
  font-size: 1.1rem;
  gap: 12px;
  line-height: 1.9;
}

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

.jlpt-rchoices {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 12px;
  margin-block-start: 8px;
  padding-inline-start: 46px;
}

.jlpt-rchoice {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 8px;
  padding: 4px 10px;
}

.jlpt-rchoice.is-correct {
  border-color: rgb(var(--v-theme-success));
  background: rgba(var(--v-theme-success), 0.12);
}

.jlpt-rchoice.is-wrong {
  border-color: rgb(var(--v-theme-error));
  background: rgba(var(--v-theme-error), 0.12);
}
</style>
