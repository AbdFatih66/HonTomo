<script setup>
// Bacaan / bahan soal untuk Tes JLPT (dikirim server dalam `passages`):
//   text  — paragraf (boleh berkotak, boleh memuat kotak nomor {{22}})
//   note  — memo/catatan
//   flyer — brosur (JlptFlyer)
import JlptFlyer from '@/components/jlpt/JlptFlyer.vue'
import JlptText from '@/components/jlpt/JlptText.vue'

defineProps({
  passage: { type: Object, required: true },
})
</script>

<template>
  <div class="jlpt-passage">
    <div
      v-if="passage.label"
      class="jlpt-passage__label"
      lang="ja"
    >
      {{ passage.label }}
    </div>

    <template v-if="passage.kind === 'text'">
      <div
        class="jlpt-passage__body"
        :class="{ 'jlpt-passage__body--boxed': passage.boxed }"
      >
        <JlptText :text="passage.body" />
      </div>
    </template>

    <template v-else-if="passage.kind === 'note'">
      <p
        v-if="passage.lead"
        class="jlpt-passage__lead"
      >
        <JlptText :text="passage.lead" />
      </p>
      <div class="jlpt-note">
        <div class="jlpt-note__to">
          <JlptText :text="passage.to" />
        </div>
        <p
          v-for="(line, i) in passage.lines"
          :key="i"
          class="jlpt-note__line"
        >
          <JlptText :text="line" />
        </p>
        <div class="jlpt-note__from">
          <JlptText :text="passage.from" />
        </div>
      </div>
    </template>

    <JlptFlyer
      v-else-if="passage.kind === 'flyer'"
      :data="passage.data"
    />
  </div>
</template>

<style scoped>
.jlpt-passage {
  margin-block: 12px 4px;
}

.jlpt-passage__label {
  font-weight: 600;
  margin-block-end: 6px;
}

.jlpt-passage__body {
  font-size: 1.15rem;
  line-height: 2.1;
}

.jlpt-passage__body--boxed {
  padding: 14px 18px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
}

.jlpt-passage__lead {
  font-size: 1.15rem;
  line-height: 2;
  margin-block-end: 10px;
}

/* memo dengan sudut terlipat */
.jlpt-note {
  position: relative;
  padding: 20px 24px 16px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  background: rgb(var(--v-theme-surface));
  clip-path: polygon(0 0, 100% 0, 100% calc(100% - 26px), calc(100% - 26px) 100%, 0 100%);
  font-size: 1.15rem;
  line-height: 2.1;
}

.jlpt-note__to { letter-spacing: 0.2em; margin-block-end: 14px; }
.jlpt-note__line { padding-inline-start: 1em; margin-block: 0; }
.jlpt-note__from { margin-block-start: 4px; padding-inline-end: 8px; text-align: end; }
</style>
