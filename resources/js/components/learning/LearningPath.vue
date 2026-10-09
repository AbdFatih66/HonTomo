<script setup>
// Duolingo-style vertical path: units -> lesson nodes with status.
const props = defineProps({
  units: { type: Array, required: true },
})

const emit = defineEmits(['open'])
const { t } = useI18n()

const ICONS = {
  locked: 'tabler-lock',
  available: 'tabler-player-play-filled',
  in_progress: 'tabler-player-play-filled',
  completed: 'tabler-check',
  mastered: 'tabler-crown',
}

// Zig-zag offsets so the path curves instead of a straight column.
const OFFSETS = [0, 44, 66, 44, 0, -44, -66, -44]

function offsetFor(i) {
  return `${OFFSETS[i % OFFSETS.length]}px`
}

function open(lesson) {
  if (lesson.status !== 'locked')
    emit('open', lesson)
}

const hasLessons = computed(() => props.units.some(u => u.lessons.length))
</script>

<template>
  <div
    v-if="hasLessons"
    class="path"
  >
    <template
      v-for="unit in units"
      :key="unit.id"
    >
      <div class="path__unit">
        <h3>{{ unit.title }}</h3>
        <p v-if="unit.description">
          {{ unit.description }}
        </p>
      </div>

      <div
        v-for="(lesson, i) in unit.lessons"
        :key="lesson.id"
        class="path__node-wrap"
        :data-lesson-id="lesson.id"
        :style="{ '--offset': offsetFor(i) }"
      >
        <span
          v-if="lesson.status === 'available' || lesson.status === 'in_progress'"
          class="path__start"
        >{{ t('path.start') }}</span>

        <button
          type="button"
          class="path__node"
          :class="`path__node--${lesson.status}`"
          :disabled="lesson.status === 'locked'"
          :aria-label="`${lesson.title} — ${t(`path.${lesson.status}`)}`"
          @click="open(lesson)"
        >
          <VIcon :icon="ICONS[lesson.status]" />
        </button>

        <div class="path__label">
          {{ lesson.title }}
        </div>
      </div>
    </template>
  </div>

  <VAlert
    v-else
    type="info"
    variant="tonal"
  >
    {{ t('path.empty') }}
  </VAlert>
</template>
