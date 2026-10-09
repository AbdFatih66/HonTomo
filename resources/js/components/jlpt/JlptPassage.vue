<script setup>
// Bacaan / bahan soal untuk Tes JLPT (dikirim server dalam `passages`):
//   text  — paragraf (boleh berkotak, boleh memuat kotak nomor {{22}})
//   note  — memo/catatan (variant 'mail' = email tanpa sudut terlipat; `to` opsional;
//           `headers` [[label, nilai]…] = kepala email あて先/件名/送信日時 di atas garis)
//   notice — papan pengumuman (judul + butir ◆ + sub-butir ・)
//   flyer — brosur toko (JlptFlyer, N5)
//   poster — pengumuman bertabel (JlptPoster, N4)
//   brochure — daftar tur bertabel + catatan (JlptBrochure, N3 もんだい 7)
//   pair — dua bacaan berkotak A dan B (N2 もんだい 12); `items[{label, body}]`, `footnote`
//   sheet — lembar pengumuman berbaris judul/isi + tabel hadiah (JlptSheet, N1 もんだい 13)
//   plans — dua panduan layanan: tabel A社 + diagram alur B社 (JlptPlans, N2 もんだい 14)
// `note` boleh punya `footnote` (catatan (注) di luar kotak surat).
// `text` boleh `vertical: true` (縦書き, mis. N1 もんだい 8 (2)).
// `text` boleh punya `title` (tengah) dan `author` (rata kanan).
import JlptBrochure from '@/components/jlpt/JlptBrochure.vue'
import JlptFlyer from '@/components/jlpt/JlptFlyer.vue'
import JlptPlans from '@/components/jlpt/JlptPlans.vue'
import JlptPoster from '@/components/jlpt/JlptPoster.vue'
import JlptSheet from '@/components/jlpt/JlptSheet.vue'
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
        :class="{ 'jlpt-passage__body--boxed': passage.boxed, 'jlpt-passage__body--vertical': passage.vertical }"
      >
        <div
          v-if="passage.title"
          class="jlpt-passage__title"
        >
          <JlptText :text="passage.title" />
        </div>
        <div
          v-if="passage.author"
          class="jlpt-passage__author"
        >
          <JlptText :text="passage.author" />
        </div>
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
      <div
        class="jlpt-note"
        :class="{ 'jlpt-note--mail': passage.variant === 'mail' }"
      >
        <div
          v-if="passage.headers?.length"
          class="jlpt-note__headers"
        >
          <div
            v-for="(h, i) in passage.headers"
            :key="i"
            class="jlpt-note__header"
          >
            <span class="jlpt-note__header-label"><JlptText :text="h[0]" /></span>
            <span>：<JlptText :text="h[1]" /></span>
          </div>
        </div>
        <div
          v-if="passage.to"
          class="jlpt-note__to"
        >
          <JlptText :text="passage.to" />
        </div>
        <p
          v-for="(line, i) in passage.lines"
          :key="i"
          class="jlpt-note__line"
        >
          <JlptText :text="line" />
        </p>
        <div
          v-if="passage.from"
          class="jlpt-note__from"
        >
          <JlptText :text="passage.from" />
        </div>
      </div>
      <p
        v-if="passage.footnote"
        class="jlpt-passage__footnote"
      >
        <JlptText :text="passage.footnote" />
      </p>
    </template>

    <template v-else-if="passage.kind === 'pair'">
      <div
        v-for="(it, i) in passage.items"
        :key="i"
        class="jlpt-pair"
      >
        <div class="jlpt-pair__label">
          {{ it.label }}
        </div>
        <div class="jlpt-passage__body jlpt-passage__body--boxed">
          <JlptText :text="it.body" />
        </div>
      </div>
      <p
        v-if="passage.footnote"
        class="jlpt-passage__footnote"
      >
        <JlptText :text="passage.footnote" />
      </p>
    </template>

    <template v-else-if="passage.kind === 'notice'">
      <p
        v-if="passage.lead"
        class="jlpt-passage__lead"
      >
        <JlptText :text="passage.lead" />
      </p>
      <div class="jlpt-notice">
        <div class="jlpt-notice__title">
          <JlptText :text="passage.title" />
        </div>
        <div
          v-for="(it, i) in passage.items"
          :key="i"
          class="jlpt-notice__item"
        >
          <span class="jlpt-notice__mark">◆</span>
          <div>
            <JlptText :text="it.text" />
            <div
              v-for="(sub, j) in it.sub ?? []"
              :key="j"
              class="jlpt-notice__sub"
            >
              ・<JlptText :text="sub" />
            </div>
          </div>
        </div>
      </div>
    </template>

    <JlptFlyer
      v-else-if="passage.kind === 'flyer'"
      :data="passage.data"
    />

    <JlptPoster
      v-else-if="passage.kind === 'poster'"
      :data="passage.data"
    />

    <JlptBrochure
      v-else-if="passage.kind === 'brochure'"
      :data="passage.data"
    />

    <JlptSheet
      v-else-if="passage.kind === 'sheet'"
      :data="passage.data"
    />

    <JlptPlans
      v-else-if="passage.kind === 'plans'"
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

.jlpt-passage__body--vertical {
  writing-mode: vertical-rl;
  block-size: 22em;
  max-inline-size: 100%;
  overflow-x: auto;
  line-height: 2.2;
  margin-inline: auto;
}

.jlpt-passage__footnote {
  font-size: 1.05rem;
  line-height: 1.9;
  margin-block: 10px 0;
}

.jlpt-pair { margin-block-end: 14px; }
.jlpt-pair__label { font-weight: 700; margin-block-end: 4px; }

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

.jlpt-passage__title { text-align: center; margin-block-end: 6px; }
.jlpt-passage__author { text-align: end; margin-block-end: 14px; }

/* email: dua garis tebal atas-bawah, tanpa sudut terlipat */
.jlpt-note--mail {
  padding: 16px 8px;
  border: 0;
  border-block: 3px solid rgba(var(--v-theme-on-surface), 0.35);
  clip-path: none;
}

.jlpt-note--mail .jlpt-note__to { letter-spacing: normal; }

/* kepala email (あて先 / 件名 / 送信日時): garis tipis di bawahnya, lalu isi surat */
.jlpt-note__headers {
  padding-block-end: 8px;
  margin-block-end: 12px;
  border-block-end: 3px solid rgba(var(--v-theme-on-surface), 0.25);
}

.jlpt-note__header-label { display: inline-block; min-inline-size: 5em; letter-spacing: 0.4em; }
.jlpt-note--mail .jlpt-note__line { padding-inline-start: 0; }

/* papan pengumuman */
.jlpt-notice {
  padding: 16px 28px 18px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  font-size: 1.15rem;
  line-height: 2;
}

.jlpt-notice__title { font-size: 1.35rem; font-weight: 700; text-align: center; margin-block-end: 10px; }
.jlpt-notice__item { display: flex; gap: 10px; margin-block-end: 10px; }
.jlpt-notice__mark { flex: none; }
.jlpt-notice__sub { padding-inline-start: 1.5em; }

.jlpt-note__to { letter-spacing: 0.2em; margin-block-end: 14px; }
.jlpt-note__line { padding-inline-start: 1em; margin-block: 0; }
.jlpt-note__from { margin-block-start: 4px; padding-inline-end: 8px; text-align: end; }
</style>
