<script setup>
// Teks soal Tes JLPT: furigana (RubyText) + garis bawah ⟦…⟧, kolom kosong ⟦　⟧/⟦★⟧
// dan kotak nomor {{22}}. Baris baru (\n) dipertahankan seperti di lembar soal.
import RubyText from '@/components/learning/RubyText.vue'
import { parseStem } from '@/data/jlpt'

defineProps({
  text: { type: String, default: '' },
})
</script>

<template>
  <span
    class="jlpt-text"
    lang="ja"
  >
    <template
      v-for="(seg, i) in parseStem(text)"
      :key="i"
    >
      <span
        v-if="seg.box"
        class="jlpt-text__box"
      >{{ seg.text }}</span>
      <span
        v-else-if="seg.blank"
        class="jlpt-text__blank"
        :class="{ 'jlpt-text__blank--star': seg.star }"
      >{{ seg.star ? '★' : '' }}</span>
      <u v-else-if="seg.underline"><RubyText :text="seg.text" /></u>
      <RubyText
        v-else
        :text="seg.text"
      />
    </template>
  </span>
</template>

<style scoped>
.jlpt-text {
  white-space: pre-line;
}

.jlpt-text u {
  text-underline-offset: 5px;
}

/* Garis kosong (soal ★): garis bawah dengan lebar tetap, ★ duduk di atas garis */
.jlpt-text__blank {
  display: inline-block;
  border-block-end: 1.5px solid currentcolor;
  inline-size: 3.4em;
  line-height: 1.1;
  margin-inline: 0.15em;
  text-align: center;
  vertical-align: baseline;
}

/* Kotak bernomor ala lembar soal, mis. [22] */
.jlpt-text__box {
  display: inline-block;
  padding-inline: 0.7em;
  border: 1.5px solid currentcolor;
  border-radius: 2px;
  font-weight: 700;
  line-height: 1.3;
  margin-inline: 0.15em;
}
</style>
