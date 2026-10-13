<script setup>
import JlptMockText from '@/components/jlpt-mock/JlptMockText.vue'
import RubyText from '@/components/learning/RubyText.vue'

// Bacaan / memo / brosur untuk soal Dokkai & Bunpou (Mondai 3–6).
// Penanda soal isian teks ditulis ［22］ dan digambar sebagai kotak bernomor.
const props = defineProps({
  passage: { type: Object, required: true },
  showFurigana: { type: Boolean, default: true },
})

const clozeParts = computed(() => {
  const text = props.passage?.text ?? ''
  const out = []
  let last = 0

  for (const m of text.matchAll(/［(\d+)］/g)) {
    if (m.index > last)
      out.push({ box: false, value: text.slice(last, m.index) })
    out.push({ box: true, value: m[1] })
    last = m.index + m[0].length
  }
  if (last < text.length)
    out.push({ box: false, value: text.slice(last) })

  return out
})
</script>

<template>
  <!-- MEMO -->
  <div
    v-if="passage.kind === 'memo'"
    class="jlpt-memo"
  >
    <div class="jlpt-memo__to">
      <JlptMockText
        :text="passage.to"
        :show-furigana="showFurigana"
      />
      <span class="ms-1">さん</span>
    </div>
    <div class="jlpt-memo__body">
      <JlptMockText
        :text="passage.text"
        :show-furigana="showFurigana"
      />
    </div>
    <div class="jlpt-memo__from">
      <JlptMockText
        :text="passage.from"
        :show-furigana="showFurigana"
      />
    </div>
  </div>

  <!-- BROSUR TOKO -->
  <div
    v-else-if="passage.kind === 'flyer'"
    class="jlpt-flyer"
  >
    <div class="jlpt-flyer__head">
      <div class="jlpt-flyer__shop">
        <RubyText
          :text="passage.shop"
          :show-furigana="showFurigana"
        />
      </div>
      <div class="text-body-2">
        <RubyText
          :text="passage.hours"
          :show-furigana="showFurigana"
        />
      </div>
    </div>

    <div
      v-for="(s, i) in passage.specials"
      :key="i"
      class="jlpt-flyer__box"
    >
      <div class="font-weight-bold mb-1">
        <RubyText
          :text="s.period"
          :show-furigana="showFurigana"
        />
      </div>
      <div>
        <RubyText
          :text="s.items"
          :show-furigana="showFurigana"
        />
      </div>
    </div>

    <div class="jlpt-flyer__box">
      <div class="font-weight-bold mb-2">
        <RubyText
          :text="passage.weekly_title"
          :show-furigana="showFurigana"
        />
      </div>
      <div
        v-for="(w, i) in passage.weekly"
        :key="i"
        class="jlpt-flyer__row"
      >
        <span class="jlpt-flyer__days">
          <RubyText
            :text="w.days"
            :show-furigana="showFurigana"
          />
        </span>
        <span>
          <RubyText
            :text="w.items"
            :show-furigana="showFurigana"
          />
        </span>
      </div>
    </div>
  </div>

  <!-- TEKS BIASA / SOAL ISIAN -->
  <div
    v-else
    class="jlpt-passage"
  >
    <div
      v-if="passage.title"
      class="font-weight-bold mb-2"
    >
      <RubyText
        :text="passage.title"
        :show-furigana="showFurigana"
      />
    </div>
    <p
      class="jlpt-passage__text mb-0"
      lang="ja"
    >
      <template
        v-for="(p, i) in clozeParts"
        :key="i"
      >
        <span
          v-if="p.box"
          class="jlpt-cloze"
        >{{ p.value }}</span>
        <RubyText
          v-else
          :text="p.value"
          :show-furigana="showFurigana"
        />
      </template>
    </p>
  </div>
</template>

<style scoped>
.jlpt-passage,
.jlpt-memo,
.jlpt-flyer {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 10px;
  background: rgb(var(--v-theme-surface));
  padding: 16px 20px;
}

.jlpt-passage__text {
  line-height: 2.3;
  white-space: pre-line;
}

.jlpt-cloze {
  display: inline-block;
  border: 1.5px solid rgb(var(--v-theme-on-surface));
  border-radius: 4px;
  font-size: 0.85em;
  font-weight: 700;
  line-height: 1.5;
  margin-inline: 4px;
  min-inline-size: 2.6em;
  padding-inline: 6px;
  text-align: center;
}

.jlpt-memo {
  position: relative;
  background: rgba(var(--v-theme-warning), 0.08);
}

.jlpt-memo__body {
  padding-block: 12px;
  line-height: 2.2;
  white-space: pre-line;
}

.jlpt-memo__from {
  text-align: end;
}

.jlpt-flyer__head {
  margin-block-end: 12px;
  text-align: center;
}

.jlpt-flyer__shop {
  font-size: 1.6rem;
  font-weight: 700;
}

.jlpt-flyer__box {
  border: 1.5px solid rgba(var(--v-theme-on-surface), 0.5);
  border-radius: 14px;
  margin-block-end: 10px;
  padding: 10px 16px;
}

.jlpt-flyer__row {
  display: flex;
  gap: 16px;
  line-height: 2;
}

.jlpt-flyer__days {
  font-weight: 700;
  min-inline-size: 4.5em;
}
</style>
