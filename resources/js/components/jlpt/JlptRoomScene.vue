<script setup>
// Empat gambar ruangan untuk もんだい 4 (2) no. 28 — SVG buatan sendiri (bukan scan
// buku). Meja + dua kursi selalu ada; yang berbeda hanya rak dan tumpukan buku:
//   1: rak tinggi + rak rendah berisi buku
//   2: rak rendah berisi buku dengan buku berserakan + rak rendah kedua
//   3: satu rak rendah dengan buku berserakan
//   4: rak tinggi saja
defineProps({
  variant: { type: Number, required: true }, // 1..4
})

const BOOK_COLS = [0, 1, 2, 3, 4, 5, 6, 7]
</script>

<template>
  <svg
    class="jlpt-room"
    viewBox="0 0 240 170"
    role="img"
    aria-hidden="true"
  >
    <!-- ruangan: sudut dinding, lantai, jendela + tirai -->
    <rect
      x="1"
      y="1"
      width="238"
      height="168"
      class="jlpt-room__frame"
    />
    <path
      d="M52 1 V104 H196 L239 150"
      class="jlpt-room__line"
    />
    <rect
      x="206"
      y="12"
      width="26"
      height="84"
      class="jlpt-room__window"
    />
    <path
      d="M198 8 H236 M198 8 q-2 40 4 88 M214 8 q-4 40 0 88"
      class="jlpt-room__line"
    />
    <path
      d="M200 60 q6 4 12 0"
      class="jlpt-room__line"
    />

    <!-- rak tinggi (varian 1 & 4) -->
    <g v-if="variant === 1 || variant === 4">
      <rect
        x="98"
        y="16"
        width="30"
        height="88"
        class="jlpt-room__furn"
      />
      <path
        d="M98 42 H128 M98 68 H128 M98 92 H128"
        class="jlpt-room__line"
      />
      <path
        d="M102 30 l3 -10 M104 60 l1 -12 M108 84 h16 M112 92 v-10 M116 92 v-10 M120 92 v-10"
        class="jlpt-room__book"
      />
    </g>

    <!-- rak rendah berisi buku rapi (varian 1: kanan rak tinggi; varian 2: kanan rak berserakan) -->
    <g v-if="variant === 1 || variant === 2">
      <rect
        :x="variant === 1 ? 140 : 148"
        y="66"
        width="34"
        height="38"
        class="jlpt-room__furn"
      />
      <path
        :d="`M${variant === 1 ? 140 : 148} 86 H${variant === 1 ? 174 : 182}`"
        class="jlpt-room__line"
      />
      <g class="jlpt-room__spines">
        <path
          v-for="c in BOOK_COLS"
          :key="`a${c}`"
          :d="`M${(variant === 1 ? 143 : 151) + c * 3.8} 68 v16`"
        />
        <path
          v-for="c in BOOK_COLS"
          :key="`b${c}`"
          :d="`M${(variant === 1 ? 143 : 151) + c * 3.8} 88 v14`"
        />
      </g>
    </g>

    <!-- rak rendah + buku berserakan (varian 2 & 3) -->
    <g v-if="variant === 2 || variant === 3">
      <rect
        :x="variant === 2 ? 104 : 118"
        y="66"
        width="34"
        height="38"
        class="jlpt-room__furn"
      />
      <path
        :d="`M${variant === 2 ? 104 : 118} 86 H${variant === 2 ? 138 : 152}`"
        class="jlpt-room__line"
      />
      <g class="jlpt-room__spines">
        <path
          v-for="c in BOOK_COLS"
          :key="`c${c}`"
          :d="`M${(variant === 2 ? 107 : 121) + c * 3.8} 68 v16`"
        />
      </g>
      <!-- tumpukan buku jatuh di depan rak -->
      <g :transform="`translate(${variant === 2 ? 0 : 14} 0)`">
        <path
          d="M104 100 l22 -6 l4 8 l-22 6 Z"
          class="jlpt-room__pile"
        />
        <path
          d="M110 108 l24 -4 l3 8 l-24 4 Z"
          class="jlpt-room__pile"
        />
        <path
          d="M120 116 l26 -3 l3 8 l-26 3 Z"
          class="jlpt-room__pile"
        />
        <path
          d="M100 94 l14 -8 l6 10 l-14 8 Z"
          class="jlpt-room__pile"
        />
        <path
          d="M132 122 l20 -4 l3 7 l-20 4 Z"
          class="jlpt-room__pile"
        />
      </g>
    </g>

    <!-- meja (kiri depan) -->
    <path
      d="M6 122 L58 108 L104 116 L52 132 Z"
      class="jlpt-room__furn"
    />
    <path
      d="M6 122 V126 M52 132 V136 M104 116 V120 M6 126 V158 M52 136 V166 M104 120 V152 M58 108 V112"
      class="jlpt-room__leg"
    />

    <!-- kursi: satu di depan meja, satu di belakang -->
    <g class="jlpt-room__chair">
      <path d="M22 132 V158 M22 132 h20 V158 M22 150 h20 M24 132 V116 h16 V132" />
      <path d="M74 122 V150 M74 122 h18 V150 M74 142 h18 M92 122 V106 h-16 V122" />
    </g>
  </svg>
</template>

<style scoped>
.jlpt-room {
  display: block;
  inline-size: 100%;
  max-inline-size: 240px;
}

.jlpt-room__frame { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.8); stroke-width: 2; }
.jlpt-room__line { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.6); stroke-linecap: round; stroke-width: 1.5; }
.jlpt-room__window { fill: rgba(var(--v-theme-on-surface), 0.05); stroke: rgba(var(--v-theme-on-surface), 0.6); stroke-width: 1.5; }
.jlpt-room__furn { fill: rgba(var(--v-theme-on-surface), 0.07); stroke: rgba(var(--v-theme-on-surface), 0.75); stroke-linejoin: round; stroke-width: 1.5; }
.jlpt-room__leg { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-linecap: round; stroke-width: 2; }
.jlpt-room__book { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-linecap: round; stroke-width: 3; }
.jlpt-room__spines path { stroke: rgba(var(--v-theme-on-surface), 0.7); stroke-width: 1.6; }
.jlpt-room__pile { fill: rgba(var(--v-theme-on-surface), 0.22); stroke: rgba(var(--v-theme-on-surface), 0.8); stroke-linejoin: round; stroke-width: 1.3; }
.jlpt-room__chair path { fill: none; stroke: rgba(var(--v-theme-on-surface), 0.75); stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.6; }
</style>
