<script setup>
import { $api } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const { t } = useI18n()

const data = ref(null)
const isLoading = ref(true)
const hasError = ref(false)

async function load() {
  isLoading.value = true
  hasError.value = false
  try {
    data.value = await $api('/dashboard')
  }
  catch {
    hasError.value = true
  }
  finally {
    isLoading.value = false
  }
}

const goalPercent = computed(() => {
  if (!data.value?.daily_goal_target)
    return 0

  return Math.min(100, Math.round((data.value.xp_today / data.value.daily_goal_target) * 100))
})

const stats = computed(() => {
  const d = data.value
  if (!d)
    return []

  return [
    { icon: 'tabler-flame', color: 'error', value: d.streak, label: t('dashboard.day_streak') },
    { icon: 'tabler-bolt', color: 'warning', value: d.xp.toLocaleString(), label: t('dashboard.total_xp') },
    { icon: 'tabler-trophy', color: 'primary', value: `${t('common.level')} ${d.player_level}`, label: d.current_level ? `${d.current_level.code}` : '' },
  ]
})

function startLesson() {
  router.push({ name: 'learn-id', params: { id: data.value.next_lesson.id } })
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
      <div class="mb-6">
        <h4 class="text-h4 mb-1">
          {{ t('dashboard.greeting', { name: data.name || authStore.user?.name }) }}
        </h4>
        <p class="text-medium-emphasis mb-0">
          {{ t('dashboard.greeting_sub') }}
        </p>
      </div>

      <VRow>
        <!-- Next lesson -->
        <VCol
          cols="12"
          md="7"
        >
          <VCard height="100%">
            <VCardText class="d-flex flex-column justify-center gap-3 h-100 pa-6">
              <div class="text-overline text-medium-emphasis">
                {{ t('dashboard.next_lesson') }}
              </div>
              <template v-if="data.next_lesson">
                <div class="text-h4">
                  {{ data.next_lesson.title }}
                </div>
                <div>
                  <VBtn
                    color="primary"
                    size="large"
                    prepend-icon="tabler-player-play-filled"
                    @click="startLesson"
                  >
                    {{ data.next_lesson.status === 'in_progress' ? t('dashboard.continue_learning') : t('dashboard.start_learning') }}
                  </VBtn>
                </div>
              </template>
              <div
                v-else
                class="text-h6"
              >
                {{ t('dashboard.all_done') }}
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Daily goal -->
        <VCol
          cols="12"
          md="5"
        >
          <VCard height="100%">
            <VCardText class="pa-6">
              <div class="d-flex align-center gap-2 mb-3">
                <VIcon
                  icon="tabler-target-arrow"
                  color="success"
                />
                <span class="text-h6">{{ t('dashboard.daily_goal') }}</span>
              </div>
              <VProgressLinear
                :model-value="goalPercent"
                color="success"
                height="14"
                rounded
              />
              <div class="text-body-2 text-medium-emphasis mt-2">
                {{ t('dashboard.goal_progress', { done: data.xp_today, target: data.daily_goal_target }) }}
              </div>

              <div class="text-body-2 text-medium-emphasis mt-5 mb-1">
                {{ t('common.level') }} {{ data.player_level }}
              </div>
              <VProgressLinear
                :model-value="data.level_progress"
                color="primary"
                height="10"
                rounded
              />
              <div class="text-caption text-medium-emphasis mt-1">
                {{ t('dashboard.level_progress', { xp: data.level_progress }) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Stat tiles -->
        <VCol
          v-for="s in stats"
          :key="s.icon"
          cols="12"
          sm="4"
        >
          <VCard>
            <VCardText class="stat-tile">
              <div
                class="stat-tile__icon"
                :style="{ background: `rgba(var(--v-theme-${s.color}), 0.16)`, color: `rgb(var(--v-theme-${s.color}))` }"
              >
                <VIcon :icon="s.icon" />
              </div>
              <div>
                <div class="stat-tile__value">
                  {{ s.value }}
                </div>
                <div class="stat-tile__label">
                  {{ s.label }}
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Progress summary -->
        <VCol cols="12">
          <VCard :title="t('dashboard.progress')">
            <VCardText>
              <VRow>
                <VCol
                  cols="12"
                  sm="4"
                >
                  <div class="stat-tile__value">
                    {{ data.progress.lessons_completed }}
                  </div>
                  <div class="stat-tile__label">
                    {{ t('dashboard.lessons_completed') }}
                  </div>
                </VCol>
                <VCol
                  cols="12"
                  sm="4"
                >
                  <div class="stat-tile__value">
                    {{ data.progress.lessons_mastered }}
                  </div>
                  <div class="stat-tile__label">
                    {{ t('dashboard.lessons_mastered') }}
                  </div>
                </VCol>
                <VCol
                  cols="12"
                  sm="4"
                >
                  <div class="stat-tile__value">
                    {{ data.progress.vocabulary_mastered }}
                  </div>
                  <div class="stat-tile__label">
                    {{ t('dashboard.vocabulary_mastered') }}
                  </div>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>
