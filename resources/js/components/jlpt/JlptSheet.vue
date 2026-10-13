<script setup>
// Lembar pengumuman berbaris "judul — isi" (N1 もんだい 13, "清森市 秋の美術コンクール 作品募集").
// Isi teks datang dari bank soal di server; komponen ini hanya menatanya seperti lembar soal.
//   data: { title, subtitle?, rows[{ head, lines?[], table?[[sel, sel, …]] }], footer? }
import JlptText from '@/components/jlpt/JlptText.vue'

defineProps({
  data: { type: Object, required: true },
})
</script>

<template>
  <div class="jlpt-sheet">
    <div class="jlpt-sheet__title">
      <JlptText :text="data.title" />
    </div>
    <div
      v-if="data.subtitle"
      class="jlpt-sheet__subtitle"
    >
      <JlptText :text="data.subtitle" />
    </div>

    <dl class="jlpt-sheet__rows">
      <div
        v-for="(row, ri) in data.rows"
        :key="ri"
        class="jlpt-sheet__row"
      >
        <dt><JlptText :text="row.head" /></dt>
        <dd>
          <div
            v-for="(ln, li) in row.lines ?? []"
            :key="li"
            class="jlpt-sheet__line"
          >
            <JlptText :text="ln" />
          </div>
          <div
            v-if="row.table"
            class="jlpt-sheet__scroll"
          >
            <table class="jlpt-sheet__table">
              <tbody>
                <tr
                  v-for="(tr, ti) in row.table"
                  :key="ti"
                >
                  <td
                    v-for="(cell, ci) in tr"
                    :key="ci"
                  >
                    <JlptText :text="cell" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </dd>
      </div>
    </dl>

    <div
      v-if="data.footer"
      class="jlpt-sheet__footer"
    >
      <JlptText :text="data.footer" />
    </div>
  </div>
</template>

<style scoped>
.jlpt-sheet {
  padding: 18px 20px;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.7);
  background: rgb(var(--v-theme-surface));
  font-size: 1rem;
  line-height: 1.8;
}

.jlpt-sheet__title { font-size: 1.6rem; font-weight: 800; text-align: center; }

.jlpt-sheet__subtitle {
  inline-size: fit-content;
  margin: 8px auto 14px;
  padding: 2px 36px;
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.5);
  border-radius: 8px;
  font-size: 1.3rem;
  font-weight: 700;
  letter-spacing: 0.6em;
}

.jlpt-sheet__rows { margin: 0; }
.jlpt-sheet__row { display: flex; gap: 14px; margin-block-end: 8px; }
.jlpt-sheet__row dt { flex: none; inline-size: 5.5em; font-weight: 700; letter-spacing: 0.2em; }
.jlpt-sheet__row dd { flex: 1; margin: 0; min-inline-size: 0; }
.jlpt-sheet__line { overflow-wrap: anywhere; }
.jlpt-sheet__scroll { overflow-x: auto; }
.jlpt-sheet__table { border-collapse: collapse; min-inline-size: 420px; }

.jlpt-sheet__table td {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  padding: 2px 10px;
}

.jlpt-sheet__footer { margin-block-start: 12px; font-size: 0.95rem; }

@media (max-width: 599px) {
  .jlpt-sheet__row { flex-direction: column; gap: 0; }
}
</style>
