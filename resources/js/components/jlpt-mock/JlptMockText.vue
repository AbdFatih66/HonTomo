<script setup>
import RubyText from '@/components/learning/RubyText.vue'

// Teks soal JLPT: sama seperti RubyText (furigana 漢字《かんじ》), ditambah
// penanda garis bawah __kata__ untuk bagian soal yang digarisbawahi di
// ujian asli (mis. Mondai 1: kata kanji yang harus dibaca).
const props = defineProps({
  text: { type: String, default: '' },
  showFurigana: { type: Boolean, default: true },
})

const parts = computed(() => (props.text ?? '')
  .split(/(__.+?__)/)
  .filter(p => p !== '')
  .map(p => (p.length > 4 && p.startsWith('__') && p.endsWith('__'))
    ? { underline: true, value: p.slice(2, -2) }
    : { underline: false, value: p }))
</script>

<template>
  <span class="jlpt-text" lang="ja">
    <template
      v-for="(p, i) in parts"
      :key="i"
    >
      <u
        v-if="p.underline"
        class="jlpt-text__u"
      ><RubyText
        :text="p.value"
        :show-furigana="showFurigana"
      /></u>
      <RubyText
        v-else
        :text="p.value"
        :show-furigana="showFurigana"
      />
    </template>
  </span>
</template>

<style scoped>
.jlpt-text {
  white-space: pre-line;
}

.jlpt-text__u {
  text-decoration-thickness: 1.5px;
  text-underline-offset: 4px;
}
</style>
