<script setup>
// Ilustrasi soal ujian JLPT (mis. もんだい 3 no. 27–28) berupa SVG inline —
// bukan gambar scan buku. Warna memakai token tema supaya aman di dark mode.
defineProps({
  name: { type: String, required: true }, // 'apples' | 'desk'
})
</script>

<template>
  <svg
    v-if="name === 'apples'"
    class="jlpt-illustration"
    viewBox="0 0 320 180"
    role="img"
    aria-hidden="true"
  >
    <!-- kotak -->
    <path d="M40 90 L150 60 L250 100 L140 140 Z" class="jlpt-il__box" />
    <path d="M40 90 L140 140 L140 165 L40 115 Z" class="jlpt-il__box-side" />
    <path d="M140 140 L250 100 L250 125 L140 165 Z" class="jlpt-il__box-side" />
    <!-- tutup kotak -->
    <path d="M190 30 L285 55 L250 100 L150 60 Z" class="jlpt-il__lid" />
    <!-- 5 apel -->
    <g v-for="(a, i) in [[95, 92], [150, 80], [130, 108], [185, 100], [160, 124]]" :key="i">
      <circle :cx="a[0]" :cy="a[1]" r="19" class="jlpt-il__apple" />
      <path :d="`M${a[0]} ${a[1] - 18} q3 -8 8 -10`" class="jlpt-il__stem" />
    </g>
  </svg>

  <svg
    v-else-if="name === 'desk'"
    class="jlpt-illustration"
    viewBox="0 0 320 210"
    role="img"
    aria-hidden="true"
  >
    <!-- meja -->
    <path d="M40 70 L170 45 L260 70 L130 100 Z" class="jlpt-il__box" />
    <path d="M40 70 L130 100 L130 106 L40 76 Z" class="jlpt-il__box-side" />
    <path d="M62 78 v110 M120 96 v100 M245 76 v100" class="jlpt-il__leg" />
    <!-- kacamata di atas meja -->
    <g class="jlpt-il__glasses">
      <ellipse cx="150" cy="72" rx="12" ry="7" />
      <ellipse cx="178" cy="66" rx="12" ry="7" />
      <path d="M162 71 q4 -3 4 -3" />
    </g>
    <!-- tas di bawah meja -->
    <rect x="70" y="150" width="46" height="34" rx="5" class="jlpt-il__bag" />
    <path d="M82 150 v-14 q11 -12 22 0 v14" class="jlpt-il__handle" />
    <!-- payung di lantai -->
    <path d="M150 196 L285 178" class="jlpt-il__umbrella-stick" />
    <path d="M150 196 q40 -22 70 -20 M150 196 q42 -6 68 -14" class="jlpt-il__umbrella" />
    <!-- kursi -->
    <rect x="205" y="100" width="45" height="52" rx="8" class="jlpt-il__chair" />
    <path d="M215 152 v40 M242 152 v40" class="jlpt-il__leg" />
  </svg>
</template>

<style scoped>
.jlpt-illustration {
  display: block;
  inline-size: min(100%, 320px);
  margin-inline: auto;
}

.jlpt-il__box { fill: rgba(var(--v-theme-on-surface), 0.08); stroke: rgba(var(--v-theme-on-surface), 0.6); stroke-width: 2; }
.jlpt-il__box-side { fill: rgba(var(--v-theme-on-surface), 0.16); stroke: rgba(var(--v-theme-on-surface), 0.6); stroke-width: 2; }
.jlpt-il__lid { fill: rgba(var(--v-theme-on-surface), 0.05); stroke: rgba(var(--v-theme-on-surface), 0.6); stroke-width: 2; }
.jlpt-il__apple { fill: rgba(var(--v-theme-error), 0.55); stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-width: 2; }
.jlpt-il__stem { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-linecap: round; stroke-width: 2; }
.jlpt-il__leg { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.6); stroke-linecap: round; stroke-width: 4; }
.jlpt-il__glasses ellipse, .jlpt-il__glasses path { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.85); stroke-width: 2; }
.jlpt-il__bag { fill: rgba(var(--v-theme-on-surface), 0.35); stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-width: 2; }
.jlpt-il__handle { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-width: 3; }
.jlpt-il__umbrella-stick { stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-linecap: round; stroke-width: 3; }
.jlpt-il__umbrella { fill: rgba(var(--v-theme-on-surface), 0.3); stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-width: 2; }
.jlpt-il__chair { fill: rgba(var(--v-theme-on-surface), 0.12); stroke: rgba(var(--v-theme-on-surface), 0.6); stroke-width: 2; }
</style>
