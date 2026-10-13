<script setup>
// Rekap nilai Tes JLPT — satu-satunya tempat skor & keputusan lulus muncul.
import JlptCertificate from '@/components/jlpt/JlptCertificate.vue'
import { $api } from '@/utils/api'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const recap = ref(null)
const isLoading = ref(true)
const hasError = ref(false)
const showCertificate = ref(false)

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    recap.value = await $api(`/jlpt-test/attempts/${route.params.attempt}/recap`)
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

const verdict = computed(() => {
  if (!recap.value)
    return null
  if (recap.value.provisional)
    return { color: 'info', icon: 'tabler-hourglass', key: 'provisional' }

  return recap.value.passed
    ? { color: 'success', icon: 'tabler-certificate', key: 'passed' }
    : { color: 'error', icon: 'tabler-mood-sad', key: 'failed' }
})

const backRoute = computed(() => ({ name: 'jlpt-test', query: recap.value?.pack ? { pack: recap.value.pack } : {} }))
const packTitle = computed(() => {
  const title = recap.value?.pack_title

  return title ? (title[locale.value] ?? title.id ?? title.en ?? '') : ''
})

const sectionByKey = computed(() => Object.fromEntries((recap.value?.sections ?? []).map(s => [s.key, s])))

function fmtTime(sec) {
  if (sec == null)
    return '–'

  return `${Math.floor(sec / 60)}:${String(sec % 60).padStart(2, '0')}`
}
function fmtDate(v) {
  return v ? new Date(v).toLocaleString(locale.value === 'en' ? 'en-GB' : 'id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : ''
}

onMounted(load)
</script>

<template>
  <div>
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

    <template v-else-if="recap">
      <h4 class="text-h4 mb-1">
        {{ t('jlptTest.recap.title') }} {{ recap.level }}
      </h4>
      <p class="text-body-2 text-medium-emphasis mb-6">
        {{ packTitle }} · {{ t(`jlptMock.mode_${recap.mode}_short`) }} · {{ fmtDate(recap.finished_at) }}
        <template v-if="recap.legacy && recap.legacy_duration_seconds != null">
          · {{ t('jlptMock.duration', { t: fmtTime(recap.legacy_duration_seconds) }) }}
        </template>
      </p>

      <VAlert
        v-if="recap.legacy"
        type="info"
        variant="tonal"
        class="mb-6"
      >
        {{ t('jlptTest.recap.legacy_note') }}
      </VAlert>
      <VAlert
        v-else-if="recap.mode === 'practice'"
        type="info"
        variant="tonal"
        class="mb-6"
      >
        {{ t('jlptMock.practice_no_xp') }}
      </VAlert>

      <VCard
        class="mb-6"
        :color="verdict.color"
        variant="tonal"
      >
        <VCardText class="text-center py-8">
          <VIcon
            :icon="verdict.icon"
            size="56"
            class="mb-2"
          />
          <div class="text-h4 mb-1">
            {{ t(`jlptTest.recap.${verdict.key}_title`) }}
          </div>
          <div class="text-h3 my-3">
            {{ recap.total_score }} / {{ recap.total_max }}
          </div>
          <div class="text-body-2">
            {{ t(`jlptTest.recap.${verdict.key}_body`, { n: recap.pass_total }) }}
          </div>
        </VCardText>
      </VCard>

      <div
        v-if="recap.passed === true && recap.mode === 'strict'"
        class="mb-8"
      >
        <VBtn
          v-if="!showCertificate"
          variant="tonal"
          color="success"
          prepend-icon="tabler-certificate"
          @click="showCertificate = true"
        >
          {{ t('Show Certificate') }}
        </VBtn>
        <JlptCertificate
          v-else
          :recap="recap"
        />
      </div>

      <h6 class="text-h6 mb-3">
        {{ t('jlptTest.recap.by_group') }}
      </h6>
      <div class="d-flex flex-column gap-4 mb-8">
        <VCard
          v-for="g in recap.groups"
          :key="g.key"
        >
          <VCardText>
            <div class="d-flex align-center justify-space-between flex-wrap gap-2 mb-2">
              <div class="text-subtitle-1 font-weight-medium">
                {{ t(`jlptTest.group.${g.key}`) }}
              </div>
              <div class="d-flex align-center gap-2">
                <span class="text-h6">{{ g.score }} / {{ g.max }}</span>
                <VChip
                  v-if="g.available"
                  size="small"
                  :color="g.met_minimum ? 'success' : 'error'"
                >
                  {{ g.met_minimum ? t('jlptTest.recap.min_met', { n: g.min }) : t('jlptTest.recap.min_not_met', { n: g.min }) }}
                </VChip>
                <VChip
                  v-else
                  size="small"
                  color="secondary"
                >
                  {{ t('jlptTest.home.coming_soon') }}
                </VChip>
              </div>
            </div>
            <VProgressLinear
              :model-value="(g.score / g.max) * 100"
              :color="g.available ? (g.met_minimum ? 'success' : 'error') : 'secondary'"
              height="8"
              rounded
            />
            <div
              v-for="k in g.sections"
              :key="k"
              class="text-body-2 text-medium-emphasis mt-2"
            >
              {{ t(`jlptTest.section.${k}.name`) }}:
              <template v-if="sectionByKey[k]?.correct != null">
                {{ t('jlptTest.recap.correct_of', { c: sectionByKey[k].correct, n: sectionByKey[k].total }) }}
                · {{ t('jlptTest.recap.time_used', { time: fmtTime(sectionByKey[k].time_spent_seconds) }) }}
                <VChip
                  v-if="sectionByKey[k].timed_out"
                  size="x-small"
                  color="warning"
                  class="ms-1"
                >
                  {{ t('jlptTest.recap.timed_out') }}
                </VChip>
              </template>
              <template v-else>
                –
              </template>
            </div>
          </VCardText>
        </VCard>
      </div>

      <template
        v-for="s in recap.sections.filter(x => x.correct != null)"
        :key="s.key"
      >
        <h6 class="text-h6 mb-3">
          {{ t('jlptTest.recap.by_mondai', { name: t(`jlptTest.section.${s.key}.name`) }) }}
        </h6>
        <VCard class="mb-8">
          <VTable>
            <thead>
              <tr>
                <th>{{ t('jlptTest.recap.col_mondai') }}</th>
                <th class="text-end">
                  {{ t('jlptTest.recap.col_correct') }}
                </th>
                <th class="text-end">
                  {{ t('jlptTest.recap.col_unanswered') }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="b in s.breakdown"
                :key="b.mondai"
              >
                <td>もんだい {{ b.mondai }}</td>
                <td class="text-end">
                  {{ b.correct }} / {{ b.total }}
                </td>
                <td class="text-end">
                  {{ b.unanswered }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </template>

      <p
        v-if="recap.provisional"
        class="text-caption text-medium-emphasis mb-6"
      >
        {{ t('jlptTest.recap.provisional_note') }}
      </p>

      <div class="d-flex flex-wrap gap-3">
        <VBtn
          v-if="recap.review_available"
          color="primary"
          prepend-icon="tabler-list-check"
          :to="{ name: 'jlpt-test-review-attempt', params: { attempt: recap.attempt_id } }"
        >
          {{ t('jlptTest.review.open') }}
        </VBtn>
        <VBtn
          variant="tonal"
          :to="backRoute"
        >
          {{ t('jlptTest.recap.back') }}
        </VBtn>
      </div>
    </template>
  </div>
</template>
