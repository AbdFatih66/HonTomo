<script setup>
// Brosur toko "あらきや" untuk もんだい 6 no. 32. Isi teksnya (dan furigana-nya)
// datang dari bank soal di server; komponen ini hanya menatanya seperti
// gambar di lembar soal — bukan scan buku.
import JlptText from '@/components/jlpt/JlptText.vue'

defineProps({
  data: { type: Object, required: true },
})

// Bintang (starburst) lewat clip-path: n titik luar/dalam berselang-seling.
function burst(n = 12, inner = 0.78) {
  const pts = []
  for (let i = 0; i < n * 2; i++) {
    const r = i % 2 === 0 ? 50 : 50 * inner
    const a = (Math.PI * i) / n - Math.PI / 2

    pts.push(`${(50 + r * Math.cos(a)).toFixed(1)}% ${(50 + r * Math.sin(a)).toFixed(1)}%`)
  }

  return `polygon(${pts.join(',')})`
}

const bigBurst = burst(10, 0.66)
const smallBurst = burst(12, 0.8)
</script>

<template>
  <div class="jlpt-flyer">
    <div class="jlpt-flyer__top">
      <div
        class="jlpt-flyer__burst jlpt-flyer__burst--big"
        :style="{ clipPath: bigBurst }"
      >
        <JlptText :text="data.badge" />
      </div>
      <div class="jlpt-flyer__shop">
        <div class="jlpt-flyer__name">
          {{ data.title }}
        </div>
        <div class="jlpt-flyer__hours">
          <JlptText :text="data.hours" />
        </div>
        <div class="jlpt-flyer__tel">
          <JlptText :text="data.tel" />
        </div>
      </div>
    </div>

    <div
      v-for="(s, i) in data.sales"
      :key="i"
      class="jlpt-flyer__card"
    >
      <div
        class="jlpt-flyer__burst jlpt-flyer__burst--small"
        :style="{ clipPath: smallBurst }"
      >
        <JlptText :text="data.badge" />
      </div>
      <div>
        <div><JlptText :text="s.period" /></div>
        <div><JlptText :text="s.items" /></div>
      </div>
    </div>

    <div class="jlpt-flyer__card jlpt-flyer__card--weekly">
      <div
        class="jlpt-flyer__burst jlpt-flyer__burst--weekly"
        :style="{ clipPath: smallBurst }"
      >
        <JlptText :text="data.weekly_badge" />
      </div>
      <div class="jlpt-flyer__weekly">
        <div
          v-for="(w, i) in data.weekly"
          :key="i"
          class="jlpt-flyer__weekly-row"
        >
          <span class="jlpt-flyer__days"><JlptText :text="w.days" /></span>
          <span><JlptText :text="w.items" /></span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.jlpt-flyer {
  padding: 18px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.5);
  background: rgb(var(--v-theme-surface));
  font-size: 1.05rem;
  line-height: 2;
}

.jlpt-flyer__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-block-end: 14px;
}

.jlpt-flyer__burst {
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  background: rgba(var(--v-theme-on-surface), 0.55);
  font-weight: 700;
  text-align: center;
}

/* isi bintang: bintang terang di dalam bintang gelap = tepi tampak bergaris */
.jlpt-flyer__burst > * {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgb(var(--v-theme-surface));
  block-size: 100%;
  clip-path: inherit;
  inline-size: 100%;
}

.jlpt-flyer__burst--big { block-size: 130px; inline-size: 150px; font-size: 1.9rem; padding: 3px; }
.jlpt-flyer__burst--small { block-size: 66px; inline-size: 74px; font-size: 1.05rem; padding: 2px; }
.jlpt-flyer__burst--weekly { block-size: 84px; inline-size: 92px; font-size: 1.05rem; padding: 2px; }

.jlpt-flyer__shop { text-align: start; }
.jlpt-flyer__name { font-size: 2rem; font-weight: 700; }
.jlpt-flyer__hours { font-weight: 600; }
.jlpt-flyer__tel { font-size: 0.85rem; }

.jlpt-flyer__card {
  display: flex;
  align-items: center;
  padding: 10px 14px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.55);
  border-radius: 22px;
  gap: 14px;
  margin-block-start: 12px;
}

.jlpt-flyer__weekly { display: flex; flex-direction: column; }
.jlpt-flyer__weekly-row { display: flex; gap: 16px; }
.jlpt-flyer__days { flex: none; font-weight: 600; min-inline-size: 4.2em; }

@media (max-width: 599px) {
  .jlpt-flyer { padding: 12px; font-size: 0.95rem; }
  .jlpt-flyer__burst--big { block-size: 104px; inline-size: 120px; font-size: 1.5rem; }
  .jlpt-flyer__name { font-size: 1.6rem; }
}
</style>
