<script setup>
// Tes JLPT — satu pintu untuk semua paket soal (bank asli + paket orisinal).
//  - tanpa ?pack : daftar paket (skor terbaik, tes yang sedang berjalan);
//  - ?pack=<key> : status satu paket → pilih mode (ujian / latihan), kerjakan sesi
//                  berurutan, lihat riwayat.
import { $api } from '@/utils/api'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const packs = ref([])
const state = ref(null)
const isLoading = ref(true)
const hasError = ref(false)
const busy = ref(false)
const confirmAbandon = ref(false)
const mode = ref('strict')

const packKey = computed(() => (route.query.pack ? String(route.query.pack) : null))
const hasAttempt = computed(() => state.value?.attempt_id != null)
const justDone = computed(() => route.query.done ?? null)
const activeMode = computed(() => (hasAttempt.value ? state.value.mode : mode.value))

function localized(obj) {
  return obj ? (obj[locale.value] ?? obj.id ?? obj.en ?? '') : ''
}

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    if (packKey.value) {
      state.value = await $api(`/jlpt-test/packs/${packKey.value}`)
      if (state.value.mode)
        mode.value = state.value.mode
    }
    else {
      state.value = null
      packs.value = (await $api('/jlpt-test/packs')).packs
    }
  }
  catch (e) {
    if (e?.response?.status === 404 && packKey.value)
      router.replace({ name: 'jlpt-test' })
    else
      hasError.value = true
  }
  finally {
    isLoading.value = false
  }
}

async function startTest() {
  busy.value = true
  try {
    state.value = await $api(`/jlpt-test/packs/${packKey.value}/attempts`, { method: 'POST', body: { mode: mode.value } })
  }
  catch (e) {
    // 409: ada tes berjalan dengan mode lain
    if (e?.response?.status === 409)
      await load()
  }
  finally {
    busy.value = false
  }
}

async function openSection(s) {
  busy.value = true
  try {
    await $api(`/jlpt-test/attempts/${state.value.attempt_id}/sections/${s.key}/start`, { method: 'POST' })
    router.push({ name: 'jlpt-test-exam-attempt-section', params: { attempt: state.value.attempt_id, section: s.key } })
  }
  catch {
    await load() // status berubah di server (mis. sesi sudah selesai)
  }
  finally {
    busy.value = false
  }
}

async function abandon() {
  confirmAbandon.value = false
  busy.value = true
  try {
    state.value = await $api(`/jlpt-test/packs/${packKey.value}/attempts/current`, { method: 'DELETE' })
  }
  finally {
    busy.value = false
  }
}

function fmtDate(v) {
  return v ? new Date(v).toLocaleString(locale.value === 'en' ? 'en-GB' : 'id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : ''
}

const modeOptions = [
  { value: 'strict', label: 'jlptMock.mode_strict', icon: 'tabler-clock' },
  { value: 'practice', label: 'jlptMock.mode_practice', icon: 'tabler-bulb' },
]

const statusMeta = {
  done: { color: 'success', icon: 'tabler-circle-check-filled' },
  running: { color: 'warning', icon: 'tabler-clock' },
  ready: { color: 'primary', icon: 'tabler-player-play' },
  unavailable: { color: 'secondary', icon: 'tabler-lock' },
}

function sectionCta(s) {
  return s.status === 'running' ? t('jlptTest.home.resume') : t('jlptTest.home.start_section')
}

function sectionMeta(s) {
  return activeMode.value === 'practice'
    ? t('jlptTest.home.meta_practice', { total: s.total })
    : t('jlptTest.home.meta', { minutes: s.minutes, total: s.total })
}

function minutesTotal(p) {
  return p.sections.filter(s => s.enabled).reduce((a, s) => a + s.minutes, 0)
}

function questionsTotal(p) {
  return p.sections.filter(s => s.enabled).reduce((a, s) => a + s.total, 0)
}

watch(packKey, load)
onMounted(load)
</script>

<template>
  <div>
    <h4 class="text-h4 mb-1">
      {{ state ? localized(state.title) : t('jlptTest.title') }}
    </h4>
    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ t('jlptTest.subtitle') }}
    </p>

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

    <!-- ── Daftar paket ── -->
    <template v-else-if="!packKey">
      <VAlert
        v-if="!packs.length"
        type="info"
        variant="tonal"
      >
        {{ t('jlptTest.packs.empty') }}
      </VAlert>

      <div class="d-flex flex-column gap-4">
        <VCard
          v-for="p in packs"
          :key="p.key"
          :to="{ name: 'jlpt-test', query: { pack: p.key } }"
        >
          <VCardText class="d-flex align-center flex-wrap gap-4">
            <VAvatar
              color="primary"
              variant="tonal"
              size="48"
            >
              <VIcon icon="tabler-certificate" />
            </VAvatar>

            <div class="flex-grow-1">
              <div class="text-h6">
                {{ localized(p.title) }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                {{ t('jlptTest.packs.meta', { level: p.level, minutes: minutesTotal(p), total: questionsTotal(p) }) }}
              </div>
            </div>

            <VChip
              v-if="p.in_progress"
              color="warning"
              size="small"
              prepend-icon="tabler-clock"
            >
              {{ t('jlptTest.packs.in_progress', { mode: t(`jlptMock.mode_${p.in_progress.mode}_short`) }) }}
            </VChip>
            <VChip
              v-if="p.best"
              :color="p.best.passed ? 'success' : 'secondary'"
              size="small"
              variant="tonal"
            >
              {{ t('jlptTest.packs.best', { score: p.best.total_score, max: p.best.total_max }) }}
            </VChip>
          </VCardText>
        </VCard>
      </div>
    </template>

    <!-- ── Satu paket ── -->
    <template v-else-if="state">
      <VBtn
        variant="text"
        size="small"
        prepend-icon="tabler-chevron-left"
        class="mb-4"
        :to="{ name: 'jlpt-test' }"
      >
        {{ t('jlptTest.packs.back') }}
      </VBtn>

      <VAlert
        v-if="justDone"
        type="success"
        variant="tonal"
        class="mb-6"
      >
        {{ t('jlptTest.home.section_done', { name: t(`jlptTest.section.${justDone}.name`) }) }}
      </VAlert>

      <VAlert
        v-if="!state.all_sections_available"
        type="info"
        variant="tonal"
        class="mb-6"
      >
        {{ t('jlptTest.home.partial_notice') }}
      </VAlert>

      <!-- Pilih mode (terkunci selama ada tes berjalan) -->
      <VCard class="mb-6">
        <VCardText>
          <h6 class="text-h6 mb-3">
            {{ t('jlptMock.mode_label') }}
          </h6>
          <div
            class="jlpt-seg mb-3"
            role="group"
            :aria-label="t('jlptMock.mode_label')"
          >
            <button
              v-for="opt in modeOptions"
              :key="opt.value"
              type="button"
              class="jlpt-seg__btn"
              :class="{ 'jlpt-seg__btn--active': activeMode === opt.value }"
              :aria-pressed="activeMode === opt.value"
              :disabled="hasAttempt"
              @click="mode = opt.value"
            >
              <VIcon
                :icon="opt.icon"
                size="18"
              />
              <span>{{ t(opt.label) }}</span>
            </button>
          </div>
          <p class="text-body-2 text-medium-emphasis mb-3">
            {{ t(activeMode === 'practice' ? 'jlptTest.home.mode_practice_hint' : 'jlptTest.home.mode_strict_hint') }}
          </p>

          <h6 class="text-h6 mb-2">
            {{ t('jlptTest.home.rules_title') }}
          </h6>
          <ul class="jlpt-rules">
            <li>{{ t('jlptTest.home.rule_order') }}</li>
            <li v-if="activeMode === 'strict'">
              {{ t('jlptTest.home.rule_timer') }}
            </li>
            <li v-if="activeMode === 'strict'">
              {{ t('jlptTest.home.rule_no_feedback') }}
            </li>
            <li v-else>
              {{ t('jlptTest.home.rule_practice') }}
            </li>
            <li>{{ t('jlptTest.home.rule_pass', { n: state.pass_total }) }}</li>
          </ul>
        </VCardText>
      </VCard>

      <div class="d-flex flex-column gap-4 mb-6">
        <VCard
          v-for="s in state.sections"
          :key="s.key"
          :class="{ 'opacity-60': s.status === 'unavailable' }"
        >
          <VCardText class="d-flex align-center flex-wrap gap-4">
            <VAvatar
              :color="statusMeta[s.status].color"
              variant="tonal"
              size="44"
            >
              <VIcon :icon="statusMeta[s.status].icon" />
            </VAvatar>

            <div class="flex-grow-1">
              <div class="text-h6">
                {{ t(`jlptTest.section.${s.key}.name`) }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                {{ t(`jlptTest.section.${s.key}.desc`) }}
              </div>
              <div class="text-caption mt-1">
                <template v-if="s.enabled">
                  {{ sectionMeta(s) }}
                </template>
                <template v-else>
                  {{ t('jlptTest.home.coming_soon') }}
                </template>
              </div>
            </div>

            <VChip
              v-if="s.status === 'done'"
              color="success"
              size="small"
            >
              {{ t('jlptTest.home.status_done') }}
            </VChip>
            <VBtn
              v-else-if="s.can_start"
              :loading="busy"
              :color="s.status === 'running' ? 'warning' : 'primary'"
              @click="openSection(s)"
            >
              {{ sectionCta(s) }}
            </VBtn>
          </VCardText>
        </VCard>
      </div>

      <div class="d-flex flex-wrap gap-3 mb-10">
        <VBtn
          v-if="!hasAttempt"
          size="large"
          :loading="busy"
          @click="startTest"
        >
          <VIcon
            start
            icon="tabler-player-play"
          />
          {{ t('jlptTest.home.start_test') }}
        </VBtn>
        <VBtn
          v-else
          variant="tonal"
          color="error"
          :disabled="busy"
          @click="confirmAbandon = true"
        >
          {{ t('jlptTest.home.abandon') }}
        </VBtn>
      </div>

      <template v-if="state.history.length">
        <h6 class="text-h6 mb-3">
          {{ t('jlptTest.home.history') }}
        </h6>
        <VCard>
          <VList>
            <VListItem
              v-for="h in state.history"
              :key="h.attempt_id"
              :to="{ name: 'jlpt-test-recap-attempt', params: { attempt: h.attempt_id } }"
            >
              <VListItemTitle>
                {{ h.total_score }} / {{ h.total_max }}
                <VChip
                  size="x-small"
                  variant="tonal"
                  class="ms-2"
                >
                  {{ t(`jlptMock.mode_${h.mode}_short`) }}
                </VChip>
              </VListItemTitle>
              <VListItemSubtitle>{{ fmtDate(h.finished_at) }}</VListItemSubtitle>
              <template #append>
                <VChip
                  size="small"
                  :color="h.provisional ? 'secondary' : h.passed ? 'success' : 'error'"
                >
                  {{ h.provisional ? t('jlptTest.recap.provisional_chip') : h.passed ? t('jlptTest.recap.passed_chip') : t('jlptTest.recap.failed_chip') }}
                </VChip>
              </template>
            </VListItem>
          </VList>
        </VCard>
      </template>
    </template>

    <VDialog
      v-model="confirmAbandon"
      max-width="420"
    >
      <VCard class="pa-2">
        <VCardTitle>{{ t('jlptTest.home.abandon_title') }}</VCardTitle>
        <VCardText>{{ t('jlptTest.home.abandon_body') }}</VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            color="secondary"
            @click="confirmAbandon = false"
          >
            {{ t('common.cancel') }}
          </VBtn>
          <VBtn
            color="error"
            @click="abandon"
          >
            {{ t('jlptTest.home.abandon_confirm') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
/* segmented mode control (custom, sama dengan halaman Kanji — VBtnToggle menumpuk) */
.jlpt-seg {
  display: inline-flex;
  max-inline-size: 100%;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.jlpt-seg__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 7px 14px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  gap: 6px;
  transition: background 0.15s ease, color 0.15s ease;
  white-space: nowrap;
}

.jlpt-seg__btn:hover:not(:disabled) {
  background: rgba(var(--v-theme-primary), 0.08);
}

.jlpt-seg__btn--active,
.jlpt-seg__btn--active:hover:not(:disabled) {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.jlpt-seg__btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.jlpt-seg__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

@media (max-width: 599px) {
  .jlpt-seg { inline-size: 100%; }
  .jlpt-seg__btn { flex: 1; padding-inline: 8px; }
}

@media (prefers-reduced-motion: reduce) {
  .jlpt-seg__btn { transition: none; }
}

.jlpt-rules {
  padding-inline-start: 20px;
}

.jlpt-rules li {
  margin-block-end: 4px;
}
</style>
