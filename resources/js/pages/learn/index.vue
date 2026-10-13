<script setup>
import { $api } from '@/utils/api'
import { useLevelChoice } from '@/composables/useLevelChoice'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()
const { level: sharedLevel, setLevel } = useLevelChoice()

// Tata Bahasa punya N3 di samping N5 / N4; level bersama (Referensi dll.) tetap N5 / N4.
// Pilihan N3 diingat sendiri supaya tidak hilang saat kembali dari sebuah pelajaran.
const LEARN_LEVELS = ['N5', 'N4', 'N3']
const LEARN_LEVEL_KEY = 'hontomo.learn.level'

function readLearnLevel() {
  try {
    const stored = localStorage.getItem(LEARN_LEVEL_KEY)

    if (stored === 'N3')
      return 'N3'
  }
  catch {
    // penyimpanan tidak tersedia — pakai level bersama
  }

  return LEARN_LEVELS.includes(sharedLevel.value) ? sharedLevel.value : 'N5'
}

const level = ref(readLearnLevel())

function chooseLevel(code) {
  level.value = code
  setLevel(code) // N3 diabaikan oleh level bersama

  try {
    if (code === 'N3')
      localStorage.setItem(LEARN_LEVEL_KEY, 'N3')
    else
      localStorage.removeItem(LEARN_LEVEL_KEY)
  }
  catch {
    // abaikan
  }
}

const path = ref(null)
const isLoading = ref(true)
const hasError = ref(false)

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    path.value = await $api('/learning-path', { query: { level: level.value } })
  }
  catch {
    hasError.value = true
  }
  finally {
    isLoading.value = false
  }

  focusRequestedLesson()
}

// ?focus=<lessonId> (set when a lesson sends the user back here) scrolls the
// path to that node instead of starting at the very top.
function focusRequestedLesson() {
  const focusId = route.query.focus
  if (!focusId)
    return

  nextTick(() => {
    const node = document.querySelector(`[data-lesson-id="${focusId}"]`)

    node?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  })
}

function openLesson(lesson) {
  router.push({ name: 'learn-id', params: { id: lesson.id } })
}

watch(level, load)
onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('path.title') }}
      </h4>
      <div class="d-flex align-center flex-wrap gap-2">
        <LevelSwitch
          :levels="LEARN_LEVELS"
          :model-value="level"
          @update:model-value="chooseLevel"
        />
        <VChip
          v-if="path"
          color="primary"
          size="large"
        >
          {{ path.level.code }} · {{ path.level.name }}
        </VChip>
      </div>
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

    <LearningPath
      v-else
      :units="path.units"
      @open="openLesson"
    />
  </div>
</template>
