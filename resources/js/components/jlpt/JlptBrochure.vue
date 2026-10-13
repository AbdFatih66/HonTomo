<script setup>
// Daftar tur bertabel (N3 もんだい 7, "XYZ旅行社 2月の旅行"). Isi teks & furigana datang dari
// bank soal di server; komponen ini hanya menatanya seperti lembar soal.
import JlptText from '@/components/jlpt/JlptText.vue'

defineProps({
  data: { type: Object, required: true },
})
</script>

<template>
  <div class="jlpt-brochure">
    <div class="jlpt-brochure__title">
      <JlptText :text="data.title" />
    </div>

    <section
      v-for="(sec, si) in data.sections"
      :key="si"
      class="jlpt-brochure__section"
    >
      <div class="jlpt-brochure__heading">
        <JlptText :text="sec.heading" />
      </div>

      <div class="jlpt-brochure__scroll">
        <table class="jlpt-brochure__table">
          <thead>
            <tr>
              <th />
              <th
                v-for="(h, i) in data.headers"
                :key="i"
              >
                <JlptText :text="h" />
              </th>
              <th class="jlpt-brochure__blank" />
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, ri) in sec.rows"
              :key="ri"
            >
              <td class="jlpt-brochure__mark">
                {{ row.mark }}
              </td>
              <td><JlptText :text="row.name" /></td>
              <td><JlptText :text="row.dates" /></td>
              <td class="jlpt-brochure__price">
                {{ row.price }}
              </td>
              <td class="jlpt-brochure__notes">
                <div
                  v-for="(n, ni) in row.notes"
                  :key="ni"
                >
                  ・<JlptText :text="n" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <p
      v-if="data.remark"
      class="jlpt-brochure__remark"
    >
      <JlptText :text="data.remark" />
    </p>

    <ul class="jlpt-brochure__bullets">
      <li
        v-for="(b, i) in data.bullets"
        :key="i"
      >
        <JlptText :text="b" />
      </li>
    </ul>
  </div>
</template>

<style scoped>
.jlpt-brochure {
  padding: 18px 20px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  background: rgb(var(--v-theme-surface));
  font-size: 1rem;
  line-height: 1.8;
}

.jlpt-brochure__title { font-size: 1.35rem; font-weight: 600; text-align: center; margin-block-end: 14px; }
.jlpt-brochure__heading { font-weight: 700; margin-block: 10px 6px; }
.jlpt-brochure__scroll { overflow-x: auto; }

.jlpt-brochure__table {
  border-collapse: collapse;
  inline-size: 100%;
  min-inline-size: 560px;
}

.jlpt-brochure__table th,
.jlpt-brochure__table td {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  padding: 4px 10px;
  vertical-align: middle;
  text-align: center;
}

.jlpt-brochure__table th { background: rgba(var(--v-theme-on-surface), 0.1); font-weight: 500; }
.jlpt-brochure__blank { border: 0 !important; background: transparent !important; }
.jlpt-brochure__mark { inline-size: 2.2em; }
.jlpt-brochure__price { text-align: end !important; white-space: nowrap; }
.jlpt-brochure__notes { text-align: start !important; }
.jlpt-brochure__remark { margin-block: 12px 8px; text-align: center; }
.jlpt-brochure__bullets { margin: 0; padding-inline-start: 1.2em; }

@media (max-width: 599px) {
  .jlpt-brochure { padding: 12px; font-size: 0.95rem; }
  .jlpt-brochure__title { font-size: 1.15rem; }
}
</style>
