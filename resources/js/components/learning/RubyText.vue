<script setup>
// Renders Japanese text that carries inline furigana markers and turns them
// into real <ruby> elements.
//
// Marker syntax used across the grammar/vocabulary seeders:
//   漢字《かんじ》  ->  <ruby>漢字<rt>かんじ</rt></ruby>
//
// Text without any marker is rendered unchanged, so old cards keep working.
const props = defineProps({
  text: { type: String, default: '' },

  // when false the furigana is stripped instead of rendered (e.g. for a
  // "hide readings" toggle later on)
  showFurigana: { type: Boolean, default: true },
})

// The base is the run of kanji (plus 々 and the iteration/katakana extenders)
// immediately before the marker — NOT everything up to the previous space, or
// 「これは辞書《じしょ》です」 would put the reading over 「これは辞書」.
const MARKER = /([\u3400-\u9FFF々〇〻ヵヶ]+)《([^》]+)》/g

const segments = computed(() => {
  const source = props.text ?? ''
  const out = []
  let lastIndex = 0

  MARKER.lastIndex = 0

  let match = MARKER.exec(source)
  while (match !== null) {
    if (match.index > lastIndex)
      out.push({ type: 'text', value: source.slice(lastIndex, match.index) })

    out.push({ type: 'ruby', base: match[1], reading: match[2] })
    lastIndex = match.index + match[0].length
    match = MARKER.exec(source)
  }

  if (lastIndex < source.length)
    out.push({ type: 'text', value: source.slice(lastIndex) })

  return out
})
</script>

<template>
  <span
    class="ruby-text"
    lang="ja"
  >
    <template
      v-for="(seg, i) in segments"
      :key="i"
    >
      <template v-if="seg.type === 'text'">{{ seg.value }}</template>
      <ruby v-else-if="showFurigana">{{ seg.base }}<rp>(</rp><rt>{{ seg.reading }}</rt><rp>)</rp></ruby>
      <template v-else>{{ seg.base }}</template>
    </template>
  </span>
</template>

<style scoped>
.ruby-text {
  ruby-position: over;
}

.ruby-text rt {
  font-size: 0.55em;
  font-weight: 400;
  line-height: 1.1;
  opacity: 0.75;
  user-select: none;
}
</style>
