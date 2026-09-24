<script setup>
import { $api } from '@/utils/api'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()

const path = ref(null)
const isLoading = ref(true)
const hasError = ref(false)

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    path.value = await $api('/learning-path')
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

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
      <h4 class="text-h4 mb-0">
        {{ t('path.title') }}
      </h4>
      <VChip
        v-if="path"
        color="primary"
        size="large"
      >
        {{ path.level.code }} · {{ path.level.name }}
      </VChip>
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
