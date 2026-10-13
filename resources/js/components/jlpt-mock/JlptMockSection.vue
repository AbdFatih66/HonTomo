<script setup>
// Isi SATU もんだい pada sesi membaca untuk pack bergaya "mock" (paket orisinal):
// instruksi, bacaan, soal, bendera tandai, dan (mode latihan) umpan balik.
// Jawaban yang masuk/keluar komponen ini 1-based (kontrak API); JlptMockQuestion
// sendiri memakai indeks 0-based, jadi dikonversi di sini.
import JlptMockPassage from '@/components/jlpt-mock/JlptMockPassage.vue'
import JlptMockQuestion from '@/components/jlpt-mock/JlptMockQuestion.vue'
import JlptMockText from '@/components/jlpt-mock/JlptMockText.vue'
import JlptQuestionFeedback from '@/components/jlpt/JlptQuestionFeedback.vue'

defineProps({
  mondai: { type: Object, required: true },
  answers: { type: Object, required: true },
  flags: { type: Object, default: () => ({}) },
  feedback: { type: Object, default: () => ({}) },
  furigana: { type: Boolean, default: false },
})

const emit = defineEmits(['choose', 'flag'])

const { t } = useI18n()
</script>

<template>
  <div>
    <div class="jlpt-mondai">
      <span class="jlpt-mondai__label">{{ mondai.label }}</span>
      <JlptMockText
        :text="mondai.instruction"
        :show-furigana="furigana"
      />
    </div>

    <div
      v-for="(g, gi) in mondai.groups"
      :key="`${mondai.id}-${gi}`"
      class="mb-6"
    >
      <p
        v-if="g.intro"
        class="mb-2"
      >
        <JlptMockText
          :text="g.intro"
          :show-furigana="furigana"
        />
      </p>
      <JlptMockPassage
        v-if="g.passage"
        :passage="g.passage"
        :show-furigana="furigana"
        class="mb-4"
      />

      <div
        v-for="q in g.questions"
        :id="`jlpt-q-${q.id}`"
        :key="q.id"
        class="jlpt-item"
      >
        <VBtn
          icon
          size="x-small"
          variant="text"
          :color="flags[q.id] ? 'warning' : 'secondary'"
          class="jlpt-item__flag"
          :aria-label="t('jlptMock.flag')"
          :aria-pressed="!!flags[q.id]"
          @click="emit('flag', q)"
        >
          <VIcon :icon="flags[q.id] ? 'tabler-flag-filled' : 'tabler-flag'" />
        </VBtn>

        <JlptMockQuestion
          :q="q"
          :model-value="answers[q.id] ? answers[q.id] - 1 : null"
          :show-furigana="furigana"
          @update:model-value="emit('choose', q, $event + 1)"
        />

        <JlptQuestionFeedback
          v-if="feedback[q.id]"
          :fb="feedback[q.id]"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.jlpt-mondai {
  display: flex;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 12px;
  border-block-end: 2px solid rgba(var(--v-theme-on-surface), 0.3);
  margin-block-end: 16px;
  padding-block-end: 8px;
}

.jlpt-mondai__label {
  font-size: 1.15rem;
  font-weight: 700;
}

.jlpt-item {
  position: relative;
  margin-block-end: 22px;
  padding-inline-end: 36px;
  scroll-margin-block: 80px;
}

.jlpt-item__flag {
  position: absolute;
  inset-block-start: 0;
  inset-inline-end: 0;
}
</style>
