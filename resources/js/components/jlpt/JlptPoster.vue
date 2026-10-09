<script setup>
// Pengumuman bertabel (N4 もんだい 6, "あおぞら一日スポーツ教室"). Isi teks & furigana
// datang dari bank soal di server; komponen ini hanya menatanya seperti lembar soal.
import JlptText from '@/components/jlpt/JlptText.vue'

defineProps({
  data: { type: Object, required: true },
})
</script>

<template>
  <div class="jlpt-poster">
    <div class="jlpt-poster__title">
      <JlptText :text="data.title" />
    </div>
    <div
      v-for="(l, i) in data.lines"
      :key="i"
      class="jlpt-poster__center"
    >
      <JlptText :text="l" />
    </div>

    <p class="jlpt-poster__intro">
      <JlptText :text="data.intro" />
    </p>

    <div class="jlpt-poster__scroll">
      <table class="jlpt-poster__table">
        <thead>
          <tr>
            <th />
            <th
              v-for="(h, i) in data.headers"
              :key="i"
            >
              <JlptText :text="h" />
            </th>
          </tr>
        </thead>
        <tbody
          v-for="(g, gi) in data.groups"
          :key="gi"
        >
          <tr
            v-for="(row, ri) in g.rows"
            :key="ri"
          >
            <td
              v-if="ri === 0"
              :rowspan="g.rows.length"
              class="jlpt-poster__mark"
            >
              {{ g.mark }}
            </td>
            <td
              v-for="(c, ci) in row"
              :key="ci"
            >
              <JlptText :text="c" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="jlpt-poster__note">
      <JlptText :text="data.note" />
    </p>

    <div
      v-for="(d, i) in data.details"
      :key="i"
      class="jlpt-poster__detail"
    >
      <div><JlptText :text="d.head" /></div>
      <div
        v-for="(line, li) in d.lines"
        :key="li"
        class="jlpt-poster__line"
      >
        <JlptText :text="line" />
      </div>
    </div>

    <div class="jlpt-poster__sign">
      <div><JlptText :text="data.sign" /></div>
      <div><JlptText :text="data.tel" /></div>
    </div>
  </div>
</template>

<style scoped>
.jlpt-poster {
  padding: 20px 22px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  background: rgb(var(--v-theme-surface));
  font-size: 1.05rem;
  line-height: 2;
}

.jlpt-poster__title { font-size: 1.6rem; font-weight: 700; text-align: center; margin-block-end: 6px; }
.jlpt-poster__center { text-align: center; }
.jlpt-poster__intro { margin-block: 14px; }
.jlpt-poster__scroll { overflow-x: auto; }

.jlpt-poster__table {
  border-collapse: collapse;
  inline-size: 100%;
  min-inline-size: 460px;
}

.jlpt-poster__table th,
.jlpt-poster__table td {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  padding: 4px 10px;
  text-align: center;
}

.jlpt-poster__table th { font-weight: 500; letter-spacing: 0.3em; }
.jlpt-poster__mark { inline-size: 2.4em; }
.jlpt-poster__note { margin-block: 8px 12px; text-align: end; }
.jlpt-poster__detail { margin-block-end: 8px; }
.jlpt-poster__line { padding-inline-start: 1.5em; }
.jlpt-poster__sign { margin-block-start: 14px; text-align: end; font-weight: 600; }

@media (max-width: 599px) {
  .jlpt-poster { padding: 12px; font-size: 0.95rem; }
  .jlpt-poster__title { font-size: 1.3rem; }
}
</style>
