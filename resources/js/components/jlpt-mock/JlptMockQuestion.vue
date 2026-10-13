<script setup>
import JlptMockImage from '@/components/jlpt-mock/JlptMockImage.vue'
import JlptMockText from '@/components/jlpt-mock/JlptMockText.vue'

// Satu soal JLPT. Tipe: choice (pilihan ganda), star (susun kalimat ★),
// listening (chokai; pilihan bisa disembunyikan). Dipakai dua mode:
//  - exam   : tanpa penilaian, hanya menandai pilihan (modelValue).
//  - review : hanya baca; menandai jawaban benar/salah.
const props = defineProps({
  q: { type: Object, required: true },
  modelValue: { type: Number, default: null },
  showFurigana: { type: Boolean, default: true },
  mode: { type: String, default: 'exam' },
  disabled: { type: Boolean, default: false },
  reveal: { type: Boolean, default: false }, // tampilkan kunci (contoh Chokai)
})

const emit = defineEmits(['update:modelValue'])

const isReview = computed(() => props.mode === 'review' || props.reveal)
const hasImageChoices = computed(() => props.q.choices.some(c => typeof c === 'object'))
const hiddenChoices = computed(() => !!props.q.hidden_choices && !isReview.value)
const isCloze = computed(() => props.q.type === 'choice' && !props.q.stem)

const orderedWords = computed(() => props.q.type === 'star' ? props.q.order.map(i => props.q.words[i]) : [])

function pick(i) {
  if (props.disabled || props.mode === 'review')
    return
  emit('update:modelValue', i)
}

function stateOf(i) {
  if (!isReview.value)
    return props.modelValue === i ? 'selected' : ''
  if (i === props.q.answer)
    return 'correct'
  if (props.modelValue === i)
    return 'wrong'

  return ''
}
</script>

<template>
  <div
    class="jlpt-q"
    :class="{ 'jlpt-q--cloze': isCloze }"
  >
    <div class="jlpt-q__head">
      <span
        v-if="q.no > 0 || isCloze"
        class="jlpt-q__no"
      >{{ q.no }}</span>
      <span
        v-else
        class="jlpt-q__no jlpt-q__no--ex"
      >例</span>

      <div
        v-if="q.stem"
        class="jlpt-q__stem"
      >
        <JlptMockText
          :text="q.stem"
          :show-furigana="showFurigana"
        />
      </div>
    </div>

    <!-- Gambar adegan (Chokai Mondai 3) -->
    <div
      v-if="q.image"
      class="jlpt-q__scene"
    >
      <JlptMockImage
        :src="q.image.image"
        :alt="q.image.alt"
        :arrow="q.arrow"
      />
    </div>

    <!-- Susun kalimat ★ -->
    <div
      v-if="q.type === 'star'"
      class="jlpt-star"
      lang="ja"
    >
      <JlptMockText
        :text="q.prefix"
        :show-furigana="showFurigana"
      />
      <span
        v-for="(slot, si) in 4"
        :key="slot"
        class="jlpt-star__blank"
        :class="{ 'jlpt-star__blank--star': si === 2, 'jlpt-star__blank--filled': isReview }"
      >
        <template v-if="isReview">
          <JlptMockText
            :text="orderedWords[si]"
            :show-furigana="showFurigana"
          />
        </template>
        <template v-else>{{ si === 2 ? '★' : '' }}</template>
      </span>
      <JlptMockText
        :text="q.suffix"
        :show-furigana="showFurigana"
      />
    </div>

    <!-- Pilihan jawaban -->
    <div
      class="jlpt-q__choices"
      :class="{
        'jlpt-q__choices--grid': hasImageChoices,
        'jlpt-q__choices--row': hiddenChoices || isCloze,
      }"
    >
      <button
        v-for="(c, i) in q.choices"
        :key="i"
        type="button"
        class="jlpt-choice"
        :class="[`jlpt-choice--${stateOf(i)}`, { 'jlpt-choice--image': typeof c === 'object', 'jlpt-choice--compact': hiddenChoices }]"
        :disabled="disabled || mode === 'review'"
        :aria-pressed="modelValue === i"
        @click="pick(i)"
      >
        <span class="jlpt-choice__no">{{ i + 1 }}</span>
        <template v-if="hiddenChoices" />
        <JlptMockImage
          v-else-if="typeof c === 'object'"
          :src="c.image"
          :alt="c.alt"
        />
        <JlptMockText
          v-else
          :text="c"
          :show-furigana="showFurigana"
        />
      </button>
    </div>
  </div>
</template>

<style scoped>
.jlpt-q__head {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.jlpt-q__no {
  display: inline-flex;
  flex: none;
  align-items: center;
  justify-content: center;
  border: 1.5px solid rgb(var(--v-theme-on-surface));
  font-weight: 700;
  min-block-size: 1.9em;
  min-inline-size: 1.9em;
  padding-inline: 4px;
}

.jlpt-q__no--ex {
  border-style: dashed;
}

.jlpt-q__stem {
  font-size: 1.05rem;
  line-height: 2.1;
  padding-block-start: 2px;
}

.jlpt-q--cloze .jlpt-q__head {
  margin-block-end: 0;
}

.jlpt-q__scene {
  margin-block: 12px;
  max-inline-size: 480px;
}

.jlpt-star {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  font-size: 1.05rem;
  gap: 6px;
  line-height: 2.1;
  margin-block: 10px 6px;
  padding-inline-start: 8px;
}

.jlpt-star__blank {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-block-end: 2px solid rgb(var(--v-theme-on-surface));
  min-block-size: 2em;
  min-inline-size: 4.2em;
  padding-inline: 4px;
}

.jlpt-star__blank--star {
  font-weight: 700;
}

.jlpt-star__blank--filled {
  border-block-end-style: dotted;
}

.jlpt-q__choices {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-block-start: 10px;
  padding-inline-start: 8px;
}

.jlpt-q__choices--grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.jlpt-q__choices--row {
  flex-direction: row;
  flex-wrap: wrap;
}

.jlpt-choice {
  display: flex;
  align-items: center;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 10px;
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  cursor: pointer;
  font-size: 1rem;
  gap: 12px;
  line-height: 2;
  padding-block: 6px;
  padding-inline: 12px;
  text-align: start;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.jlpt-choice--image {
  align-items: flex-start;
  flex-direction: column;
  gap: 6px;
}

.jlpt-choice--compact {
  justify-content: center;
  font-size: 1.15rem;
  font-weight: 700;
  min-inline-size: 64px;
}

.jlpt-choice__no {
  display: inline-flex;
  flex: none;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(var(--v-theme-on-surface), 0.08);
  block-size: 1.7em;
  font-size: 0.9em;
  font-weight: 700;
  inline-size: 1.7em;
}

.jlpt-choice:hover:not(:disabled) {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.06);
}

.jlpt-choice--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.12);
}

.jlpt-choice--selected .jlpt-choice__no {
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.jlpt-choice--correct {
  border-color: rgb(var(--v-theme-success));
  background: rgba(var(--v-theme-success), 0.14);
}

.jlpt-choice--wrong {
  border-color: rgb(var(--v-theme-error));
  background: rgba(var(--v-theme-error), 0.14);
}

.jlpt-choice:disabled {
  cursor: default;
}
</style>
