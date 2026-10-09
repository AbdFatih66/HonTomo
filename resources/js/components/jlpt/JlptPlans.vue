<script setup>
// Dua panduan layanan berdampingan (N2 もんだい 14, "A社／B社 海外引越サービス"):
//   a — tabel perbandingan plan (groups[{headers, rows[{label, cells}]}], notes[])
//   b — diagram alur diagnosis (intro[], steps[{question?, options[{key, text, result?}]}], notes[])
// Isi teks datang dari bank soal di server; komponen ini hanya menatanya seperti lembar soal.
import JlptText from '@/components/jlpt/JlptText.vue'

defineProps({
  data: { type: Object, required: true },
})
</script>

<template>
  <div class="jlpt-plans">
    <section class="jlpt-plans__box">
      <div class="jlpt-plans__title">
        <JlptText :text="data.a.title" />
      </div>

      <div
        v-for="(g, gi) in data.a.groups"
        :key="gi"
        class="jlpt-plans__scroll"
      >
        <table class="jlpt-plans__table">
          <thead>
            <tr>
              <th class="jlpt-plans__blank" />
              <th
                v-for="(h, hi) in g.headers"
                :key="hi"
              >
                <JlptText :text="h" />
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, ri) in g.rows"
              :key="ri"
            >
              <th class="jlpt-plans__rowhead">
                <JlptText :text="row.label" />
              </th>
              <td
                v-for="(c, ci) in row.cells"
                :key="ci"
                :class="{ 'jlpt-plans__left': ri === 0 }"
              >
                <JlptText :text="c" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <ul class="jlpt-plans__notes">
        <li
          v-for="(n, i) in data.a.notes"
          :key="i"
        >
          <JlptText :text="n" />
        </li>
      </ul>
    </section>

    <section class="jlpt-plans__box">
      <div class="jlpt-plans__title">
        <JlptText :text="data.b.title" />
      </div>
      <p
        v-for="(l, i) in data.b.intro"
        :key="i"
        class="jlpt-plans__intro"
      >
        <JlptText :text="l" />
      </p>

      <template
        v-for="(step, si) in data.b.steps"
        :key="si"
      >
        <div
          v-if="si > 0"
          class="jlpt-plans__arrow"
          aria-hidden="true"
        >
          ↓ b
        </div>
        <div class="jlpt-plans__step">
          <div
            v-if="step.question"
            class="jlpt-plans__question"
          >
            <JlptText :text="step.question" />
          </div>
          <div class="jlpt-plans__options">
            <div
              v-for="o in step.options"
              :key="o.key"
              class="jlpt-plans__option"
            >
              <span class="jlpt-plans__key">{{ o.key }}</span>
              <JlptText :text="o.text" />
              <span
                v-if="o.result"
                class="jlpt-plans__result"
              >→ <JlptText :text="o.result" /></span>
            </div>
          </div>
        </div>
      </template>

      <ul class="jlpt-plans__notes">
        <li
          v-for="(n, i) in data.b.notes"
          :key="i"
        >
          <JlptText :text="n" />
        </li>
      </ul>
    </section>
  </div>
</template>

<style scoped>
.jlpt-plans { display: flex; flex-direction: column; gap: 16px; font-size: 1rem; line-height: 1.8; }

.jlpt-plans__box {
  padding: 16px 18px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  background: rgb(var(--v-theme-surface));
}

.jlpt-plans__title { font-size: 1.25rem; font-weight: 700; text-align: center; margin-block-end: 12px; }
.jlpt-plans__scroll { overflow-x: auto; margin-block-end: 8px; }

.jlpt-plans__table { border-collapse: collapse; inline-size: 100%; min-inline-size: 420px; }

.jlpt-plans__table th,
.jlpt-plans__table td {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  padding: 3px 10px;
  text-align: center;
  vertical-align: middle;
}

.jlpt-plans__table thead th:not(.jlpt-plans__blank) { background: rgba(var(--v-theme-on-surface), 0.1); font-weight: 500; }
.jlpt-plans__rowhead { font-weight: 400; white-space: nowrap; }
.jlpt-plans__blank { border: 0 !important; }
.jlpt-plans__left { text-align: start !important; }
.jlpt-plans__notes { margin: 8px 0 0; padding-inline-start: 1.2em; }
.jlpt-plans__intro { margin: 0; text-align: center; }

.jlpt-plans__step {
  margin-block: 6px;
  padding: 8px 12px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
}

.jlpt-plans__question { text-align: center; margin-block-end: 4px; }
.jlpt-plans__options { display: flex; flex-wrap: wrap; gap: 6px 24px; justify-content: space-around; }
.jlpt-plans__option { display: flex; flex-wrap: wrap; gap: 6px; align-items: baseline; }
.jlpt-plans__key { font-weight: 700; }

.jlpt-plans__result {
  padding-inline: 8px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.6);
  border-radius: 2px;
  font-weight: 600;
}

.jlpt-plans__arrow { text-align: center; color: rgba(var(--v-theme-on-surface), 0.7); }

@media (max-width: 599px) {
  .jlpt-plans__box { padding: 12px; }
}
</style>
