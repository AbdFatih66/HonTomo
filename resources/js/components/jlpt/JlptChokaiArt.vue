<script setup>
// Ilustrasi soal menyimak (聴解) Tes JLPT — SVG buatan sendiri, bukan scan buku.
// Semua gambar memakai token tema (aman di dark mode) dan viewBox yang sama
// (240×170) supaya rapi dalam grid pilihan. Nama gambar dipakai oleh bank soal:
//   book-1..4     (contoh もんだい 1)     socks-1..4    (1-1)
//   bags          (1-3, bernomor 1–4)     items-1..4    (1-4)
//   acts-1..4     (1-5)                   food-1..4     (1-6)
//   talk-ex, talk-1..5  (もんだい 3, ➡ menandai orang yang bicara)
import { computed } from 'vue'

const props = defineProps({
  name: { type: String, required: true },
})

const W = 240
const H = 170

// ── primitif ────────────────────────────────────────────────────────
const frame = `<rect class="ln" x="1" y="1" width="${W - 2}" height="${H - 2}" />`

function person(x, y, o = {}) {
  const s = o.s ?? 1
  const r = 10 * s
  const dir = o.dir ?? 1 // 1 = menghadap kanan, -1 = kiri
  const top = y + 11 * s
  let g = ''

  if (o.long) {
    // rambut panjang: dua helai di sisi wajah (tidak menutupi dagu)
    g += `<rect class="hair" x="${x - r - 2.5 * s}" y="${y - 3 * s}" width="${5 * s}" height="${r * 2.1}" rx="${2.5 * s}" /><rect class="hair" x="${x + r - 2.5 * s}" y="${y - 3 * s}" width="${5 * s}" height="${r * 2.1}" rx="${2.5 * s}" />`
  }
  if (o.bun)
    g += `<circle class="hair" cx="${x - dir * r * 0.9}" cy="${y - r * 0.8}" r="${r * 0.45}" />`
  g += `<circle class="skin" cx="${x}" cy="${y}" r="${r}" />`
  g += `<path class="hair" d="M${x - r} ${y + 1} a${r} ${r} 0 0 1 ${2 * r} 0 q${-r * 0.55} ${-r * 0.7} ${-r * 2} 0 z" />`
  g += `<circle class="dot" cx="${x + dir * 3 * s - 3 * s}" cy="${y + 1.5 * s}" r="${1.2 * s}" /><circle class="dot" cx="${x + dir * 3 * s + 3 * s}" cy="${y + 1.5 * s}" r="${1.2 * s}" />`
  if (o.glasses)
    g += `<circle class="ln" cx="${x - 3.4 * s}" cy="${y + 1.5 * s}" r="${3.4 * s}" /><circle class="ln" cx="${x + 3.4 * s}" cy="${y + 1.5 * s}" r="${3.4 * s}" />`

  const tw = 12 * s
  const th = (o.seated ? 30 : 34) * s

  // badan
  g += `<rect class="${o.shirt ?? 'cloth'}" x="${x - tw}" y="${top}" width="${tw * 2}" height="${th}" rx="${5 * s}" />`
  if (o.apron)
    g += `<rect class="fl" x="${x - tw + 3 * s}" y="${top + 8 * s}" width="${tw * 2 - 6 * s}" height="${th - 6 * s + 22 * s}" rx="${3 * s}" />`

  // kaki / rok
  const hip = top + th
  if (o.seated) {
    const lw = tw * 2 + 22 * s

    g += `<rect class="pants" x="${dir > 0 ? x - tw : x - tw - 22 * s}" y="${hip - 2 * s}" width="${lw}" height="${10 * s}" rx="${3 * s}" />`
    g += `<path class="ln thick2" d="M${x + dir * (tw + 16 * s)} ${hip + 6 * s} v${22 * s}" />`
  }
  else if (o.skirt) {
    g += `<path class="pants" d="M${x - tw} ${hip - 2 * s} h${tw * 2} l${5 * s} ${26 * s} h${-(tw * 2 + 10 * s)} z" />`
    g += `<path class="ln thick2" d="M${x - 5 * s} ${hip + 24 * s} v${18 * s} M${x + 5 * s} ${hip + 24 * s} v${18 * s}" />`
  }
  else {
    g += `<path class="ln thick2 pantsline" d="M${x - 6 * s} ${hip - 1} v${38 * s} M${x + 6 * s} ${hip - 1} v${38 * s}" />`
    g += `<ellipse class="fd" cx="${x - 6 * s + dir * 3 * s}" cy="${hip + 39 * s}" rx="${6 * s}" ry="${2.6 * s}" /><ellipse class="fd" cx="${x + 6 * s + dir * 3 * s}" cy="${hip + 39 * s}" rx="${6 * s}" ry="${2.6 * s}" />`
  }

  // lengan (default: menggantung)
  g += o.arms ?? `<path class="ln thick2" d="M${x - tw} ${top + 5 * s} l${-2 * s} ${22 * s} M${x + tw} ${top + 5 * s} l${2 * s} ${22 * s}" />`

  return g
}

// ➡ tanda panah hitam; ujung panah menunjuk (tx, ty)
function arrow(tx, ty, deg = 45) {
  const rad = (deg * Math.PI) / 180
  const ox = tx - 26 * Math.cos(rad)
  const oy = ty - 26 * Math.sin(rad)

  return `<path class="arrow" d="M0 -3.5 H15 V-9 L27 0 L15 9 V3.5 H0 Z" transform="translate(${ox.toFixed(1)} ${oy.toFixed(1)}) rotate(${deg})" />`
}

const table = (x1, y1, x2, y2, legs = true) =>
  `<path class="fl" d="M${x1} ${y1} H${x2} V${y2} H${x1} Z" />${legs ? `<path class="ln thick2" d="M${x1 + 6} ${y2} V${y2 + 40} M${x2 - 6} ${y2} V${y2 + 40}" />` : ''}`

// ── 1-1 kaus kaki ───────────────────────────────────────────────────
function sockPath(x, y, leg) {
  // x,y = pojok kiri atas betis; kaki menjulur ke kiri-bawah
  return `M${x} ${y} h30 v${leg} q0 18 -20 26 l-46 10 q-22 4 -24 -12 q-2 -14 24 -18 l36 -6 z`
}

function sock(x, y, leg, kind, tilt = 0) {
  let g = `<g transform="rotate(${tilt} ${x + 15} ${y + 40})">`

  g += `<path class="fl" d="${sockPath(x, y, leg)}" />`
  g += `<path class="ln" d="M${x} ${y + 8} h30 M${x + 4} ${y + 4} v4 M${x + 10} ${y + 4} v4 M${x + 16} ${y + 4} v4 M${x + 22} ${y + 4} v4" />`

  const cx = x + 15
  const cy = y + 26

  if (kind === 'fruit') {
    g += `<circle class="apple" cx="${cx + 2}" cy="${cy}" r="8" /><path class="ln" d="M${cx + 2} ${cy - 8} q1 -5 5 -6" />`
    g += `<g class="grape"><circle cx="${cx - 6}" cy="${cy + 14}" r="3" /><circle cx="${cx}" cy="${cy + 14}" r="3" /><circle cx="${cx + 6}" cy="${cy + 14}" r="3" /><circle cx="${cx - 3}" cy="${cy + 20}" r="3" /><circle cx="${cx + 3}" cy="${cy + 20}" r="3" /><circle cx="${cx}" cy="${cy + 26}" r="3" /></g>`
  }
  else {
    // kucing (atas) + anjing (bawah)
    g += `<path class="fl" d="M${cx - 8} ${cy - 2} l0 -9 l6 5 h4 l6 -5 l0 9 q0 8 -8 8 q-8 0 -8 -8 z" />`
    g += `<circle class="dot" cx="${cx - 3}" cy="${cy + 1}" r="1.1" /><circle class="dot" cx="${cx + 3}" cy="${cy + 1}" r="1.1" />`
    g += `<ellipse class="dark" cx="${cx - 8}" cy="${cy + 20}" rx="4" ry="7" /><ellipse class="dark" cx="${cx + 8}" cy="${cy + 20}" rx="4" ry="7" /><circle class="skin" cx="${cx}" cy="${cy + 20}" r="8" />`
    g += `<circle class="dot" cx="${cx - 3}" cy="${cy + 18}" r="1.3" /><circle class="dot" cx="${cx + 3}" cy="${cy + 18}" r="1.3" /><ellipse class="dot" cx="${cx}" cy="${cy + 22}" rx="2.6" ry="1.8" />`
  }

  return `${g}</g>`
}

const socks = (n) => {
  const kind = n % 2 === 1 ? 'fruit' : 'animal'
  const long = n <= 2

  return long
    ? `${sock(96, 14, 84, kind, 0)}${sock(126, 14, 84, kind, 0)}`
    : `${sock(96, 62, 34, kind, 0)}${sock(124, 62, 34, kind, 0)}`
}

// ── 1-3 rak tas ─────────────────────────────────────────────────────
const bags = () => `
  <path class="ln" d="M4 118 H236" /><path class="fm" d="M2 120 H238 V148 H2 Z" />
  <path class="ln" d="M4 156 H236" /><path class="fm" d="M2 158 H238 V168 H2 Z" />
  <g class="fl"><path d="M30 84 q-6 4 -6 16 v14 q0 4 6 4 h32 q6 0 6 -4 v-14 q0 -12 -6 -16 z" /></g>
  <path class="ln" d="M36 84 q10 -14 20 0" /><path class="ln" d="M40 96 h12" />
  <g class="fl"><path d="M72 46 v66 q0 6 6 6 h44 q6 0 6 -6 v-66 z" /></g>
  <path class="ln" d="M84 46 q4 -22 12 -22 q8 0 12 22 M92 46 q5 -20 12 -20 q7 0 11 20" /><path class="ln" d="M78 70 q22 4 44 0" />
  <path class="dark" d="M134 60 q-6 0 -6 6 v46 q0 6 6 6 h40 q6 0 6 -6 v-46 q0 -6 -6 -6 q-6 10 -14 10 q-8 0 -14 -10 z" />
  <path class="ln" d="M142 60 q4 -20 10 -20 q6 0 10 20 M152 60 q4 -20 10 -20 q6 0 10 20" />
  <path class="dark" d="M190 96 q-8 2 -8 14 v4 q0 4 6 4 h26 q6 0 6 -4 v-4 q0 -12 -8 -14 z" />
  <path class="ln" d="M194 96 q8 -14 20 0" />
  <g class="ln"><path d="M46 12 V94" /><path d="M100 12 V80" /><path d="M152 12 V80" /><path d="M204 12 V104" /></g>
  <g class="dotf"><circle cx="46" cy="94" r="2.4" /><circle cx="100" cy="80" r="2.4" /><circle cx="152" cy="80" r="2.4" /><circle cx="204" cy="104" r="2.4" /></g>
  <g class="num"><text x="42" y="12">1</text><text x="96" y="12">2</text><text x="148" y="12">3</text><text x="200" y="12">4</text></g>
  <g class="fm"><path d="M12 146 q4 -22 30 -22 q26 0 30 22 z" /><path d="M86 146 q4 -22 30 -22 q26 0 30 22 z" /><path d="M158 146 q4 -22 30 -22 q26 0 30 22 z" /></g>
`

// ── 1-4 barang di meja ──────────────────────────────────────────────
const pencils = (x, y) => `
  <g transform="translate(${x} ${y}) rotate(-62)">
    <rect class="fd" x="0" y="0" width="64" height="7" rx="1.5" /><path class="fl" d="M64 0 l10 3.5 l-10 3.5 z" /><rect class="fl" x="-6" y="0" width="6" height="7" />
    <rect class="fd" x="0" y="13" width="64" height="7" rx="1.5" /><path class="fl" d="M64 13 l10 3.5 l-10 3.5 z" /><rect class="fl" x="-6" y="13" width="6" height="7" />
  </g>
  <g transform="translate(${x + 46} ${y + 34}) rotate(-28)"><rect class="fl" x="0" y="0" width="26" height="16" rx="4" /><path class="ln" d="M0 8 h26" /></g>`

const items = (n) => {
  const base = pencils(n === 1 ? 98 : 150, n === 1 ? 82 : 92)

  if (n === 1)
    return base
  if (n === 2)
    return `<g transform="rotate(-8 90 90)"><rect class="fl" x="36" y="40" width="70" height="98" rx="3" /><path class="ln" d="M48 62 h46 M48 84 h46 M48 106 h46" /><g class="ln"><path d="M36 48 h-4 M36 56 h-4 M36 64 h-4 M36 72 h-4 M36 80 h-4 M36 88 h-4 M36 96 h-4 M36 104 h-4 M36 112 h-4 M36 120 h-4 M36 128 h-4" /></g></g>${base}`
  if (n === 3)
    return `<g transform="rotate(-10 84 96)"><path class="fl" d="M30 54 l56 -10 l18 6 v76 l-56 10 l-18 -6 z" /><path class="fm" d="M30 54 l18 6 v76 l-18 -6 z" /><path class="ln" d="M48 60 l56 -10" /><rect class="fl" x="60" y="64" width="26" height="16" transform="rotate(-8 60 64)" /><path class="ln" d="M60 92 l32 -6 M58 102 l32 -6" /></g>${base}`

  return `<g transform="rotate(-18 76 96)"><path class="fm" d="M62 44 h32 l-3 26 h-26 z" /><path class="fm" d="M65 122 h26 l4 34 h-34 z" /><circle class="fl" cx="78" cy="96" r="24" /><circle class="ln" cx="78" cy="96" r="19" /><path class="ln" d="M78 96 v-12 M78 96 l9 5" /></g>${base}`
}

// ── 1-5 kegiatan ────────────────────────────────────────────────────
const dishes = (x, y) => `<g><path class="fl" d="M${x} ${y} q0 8 10 8 q10 0 10 -8 z" /><ellipse class="fl" cx="${x + 30}" cy="${y + 4}" rx="10" ry="3.5" /></g>`

const acts = (n) => {
  if (n === 1) {
    return `${person(60, 44, { long: true, seated: true, s: 1.25, arms: '<path class="ln thick2" d="M45 64 l18 32 M75 64 l-2 32" />' })}${person(180, 44, { bun: true, dir: -1, seated: true, s: 1.25, arms: '<path class="ln thick2" d="M165 64 l-18 32 M195 64 l2 32" />' })}
      ${table(14, 100, 226, 108)}
      <g class="fl"><rect x="28" y="92" width="26" height="8" rx="1" /><rect x="188" y="92" width="26" height="8" rx="1" /></g>
      ${dishes(78, 92)}${dishes(112, 92)}${dishes(146, 92)}
      <g class="ln"><path d="M70 96 l-12 -30 M76 96 l-12 -30 M170 96 l12 -30 M164 96 l12 -30" /></g>`
  }
  if (n === 2) {
    return `<g><path class="fm" d="M118 12 h62 V158 H118 z" /><path class="fl" d="M126 20 h46 V158 h-46 z" /><circle class="dotf" cx="164" cy="92" r="2.6" /></g>
      ${person(90, 46, { skirt: true, s: 1.1, dir: 1, arms: '<path class="ln thick2" d="M77 62 l-4 26 M103 62 l20 20 l26 8" />' })}
      <path class="dark" d="M40 104 h28 q6 0 6 6 v20 q0 6 -6 6 h-28 q-6 0 -6 -6 v-20 q0 -6 6 -6 z" /><path class="ln" d="M46 104 q8 -14 16 0" />
      <path class="fm" d="M2 152 H238 V168 H2 Z" />`
  }
  if (n === 3) {
    return `<path class="fm" d="M2 152 H238 V168 H2 Z" />
      <rect class="fm" x="10" y="96" width="112" height="46" rx="6" /><rect class="fl" x="10" y="60" width="22" height="82" rx="6" />
      ${person(62, 62, { long: true, seated: true, dir: 1, s: 1.05, arms: '<path class="ln thick2" d="M50 78 l14 10 M74 78 l6 10" />' })}
      ${person(98, 68, { seated: true, dir: 1, s: 1.0, arms: '<path class="ln thick2" d="M86 82 l12 10 M110 82 l4 10" />' })}
      <g><rect class="dark" x="148" y="34" width="72" height="50" rx="3" /><rect class="fl" x="153" y="39" width="62" height="40" /><path class="ln" d="M170 84 h28 M184 84 v10" /></g>
      <rect class="fl" x="146" y="102" width="82" height="42" rx="2" />`
  }

  return `<g class="fl"><rect x="8" y="30" width="86" height="102" /><path class="ln" d="M8 62 H94 M8 96 H94" /><rect x="14" y="40" width="20" height="18" /><rect x="40" y="40" width="20" height="18" /><rect x="66" y="40" width="20" height="18" /><rect x="14" y="72" width="26" height="20" /><rect x="46" y="72" width="26" height="20" /><rect x="14" y="106" width="30" height="22" /></g>
    <g class="fl"><path d="M96 142 l52 -14 l40 12 l-52 16 z" /><path d="M122 132 l40 -22 l36 18 l-44 22 z" /></g>
    ${person(112, 50, { long: true, skirt: true, s: 1.05, dir: 1, arms: '<path class="ln thick2" d="M99 66 l16 20 M125 66 l4 20" />' })}
    <path class="fl" d="M108 86 l22 -6 l6 14 l-22 6 z" />
    ${person(210, 42, { dir: -1, s: 0.9, arms: '<path class="ln thick2" d="M199 56 l-10 18 M221 56 l2 18" />' })}
    <g class="fl"><rect x="168" y="86" width="60" height="16" /><rect x="176" y="72" width="44" height="14" /></g>`
}

// ── 1-6 makanan ─────────────────────────────────────────────────────
function onigiri(x, y) {
  return `<path class="fl" d="M${x} ${y} q-3 0 -6 6 l-20 34 q-2 8 8 8 h36 q10 0 8 -8 l-20 -34 q-3 -6 -6 -6 z" /><path class="dark" d="M${x - 12} ${y + 24} l24 0 l4 22 h-32 z" />`
}

const drinks = (dx = 0, dy = 0) => `<g transform="translate(${dx} ${dy})">
  <g><rect class="fl" x="60" y="34" width="30" height="86" rx="10" /><rect class="fm" x="60" y="50" width="30" height="14" /><rect class="ln" x="68" y="22" width="14" height="14" rx="2" /><path class="leaf" d="M68 82 q7 -14 14 0 q-7 8 -14 0 z" /></g>
  <g><rect class="fl" x="92" y="30" width="30" height="90" rx="10" /><rect class="fm" x="92" y="46" width="30" height="14" /><rect class="ln" x="100" y="18" width="14" height="14" rx="2" /><path class="leaf" d="M100 82 q7 -14 14 0 q-7 8 -14 0 z" /></g>
  <g><rect class="fl" x="118" y="68" width="24" height="60" rx="8" /><rect class="fm" x="118" y="82" width="24" height="10" /><rect class="ln" x="124" y="58" width="12" height="12" rx="2" /><circle class="apple" cx="130" cy="108" r="7" /></g></g>`

const snacks = (dx = 0, dy = 0) => `<g transform="translate(${dx} ${dy})">
  <g transform="rotate(-8 140 80)"><rect class="fl" x="112" y="40" width="50" height="70" /></g>
  <g transform="rotate(6 160 90)"><rect class="fm" x="134" y="48" width="52" height="72" /><circle class="fl" cx="150" cy="80" r="7" /><circle class="fl" cx="172" cy="86" r="7" /><circle class="fl" cx="158" cy="102" r="7" /></g>
  <path class="fm" d="M104 122 l14 -6 l6 10 l-14 6 z" /><path class="ln" d="M100 120 l-6 -3 M108 132 l-5 4" /></g>`

const food = (n) => {
  if (n === 1)
    return `<path class="fm" d="M50 96 l20 -10 h100 l20 10 v40 q0 6 -6 6 h-128 q-6 0 -6 -6 z" />${onigiri(92, 60)}${onigiri(126, 56)}${onigiri(110, 84)}${onigiri(148, 80)}`
  if (n === 2)
    return drinks(-2, 8)
  if (n === 3)
    return `${drinks(-30, 8)}${snacks(6, 12)}`

  return snacks(-14, 8)
}

// ── もんだい 3 (➡) ─────────────────────────────────────────────────
const talk = (n) => {
  if (n === 0) { // contoh: restoran
    return `<path class="fm" d="M2 150 H238 V168 H2 Z" />
      ${person(66, 72, { long: true, seated: true, dir: 1, s: 1.08, arms: '<path class="ln thick2" d="M53 90 l22 12 M79 90 l6 12" />' })}
      <path class="dark" d="M74 84 l28 -14 l8 12 l-28 18 z" />
      ${table(28, 120, 156, 128)}
      <rect class="fl" x="122" y="106" width="10" height="14" rx="2" /><path class="fl" d="M56 116 l26 -6 l6 4 l-26 8 z" />
      ${person(196, 54, { dir: -1, apron: true, s: 1.1, arms: '<path class="ln thick2" d="M183 70 l-8 22 M209 70 l-6 24" />' })}
      <ellipse class="fl" cx="176" cy="96" rx="16" ry="5" />
      ${arrow(60, 48, 50)}`
  }
  if (n === 1) {
    return `${person(64, 60, { long: true, seated: true, dir: 1, s: 1.05, arms: '<path class="ln thick2" d="M52 74 l16 18 M76 74 l6 18" />' })}${person(176, 60, { long: false, bun: true, dir: -1, seated: true, s: 1.05, arms: '<path class="ln thick2" d="M164 74 l-14 18 M188 74 l-2 18" />' })}
      ${table(24, 100, 216, 108)}
      <g class="fl"><ellipse cx="60" cy="104" rx="14" ry="4" /><ellipse cx="180" cy="104" rx="14" ry="4" /><path d="M84 96 q0 8 8 8 q8 0 8 -8 z" /><path d="M140 96 q0 8 8 8 q8 0 8 -8 z" /></g>
      <path class="ln" d="M120 104 l30 -2 M124 100 l30 -2" /><path class="ln" d="M100 100 h20" />
      ${arrow(56, 40, 40)}`
  }
  if (n === 2) { // kereta
    return `<g class="ln"><path d="M18 6 V96 M30 6 V96 M212 6 V96" /></g>
      <g class="fl"><rect x="34" y="14" width="46" height="70" rx="4" /><rect x="84" y="14" width="70" height="70" rx="4" /></g>
      <path class="fm" d="M2 98 H238 V122 H2 z" opacity=".55" />
      ${person(112, 74, { long: false, seated: true, dir: 1, s: 1.0, arms: '<path class="ln thick2" d="M100 88 l16 14 M124 88 l4 14" />' })}
      ${person(78, 48, { long: true, skirt: true, dir: 1, s: 1.08, arms: '<path class="ln thick2" d="M64 64 l14 22 M92 64 l16 20" />' })}
      <path class="ln" d="M100 84 v78" /><path class="ln thick2" d="M108 84 v78" opacity="0" />
      ${person(178, 44, { dir: 1, s: 1.05, arms: '<path class="ln thick2" d="M166 58 l-4 24 M190 58 l-22 24 l-16 6" />' })}
      ${person(214, 78, { glasses: true, seated: true, dir: -1, s: 0.98 })}
      <path class="ln" d="M150 92 H236" />
      ${arrow(172, 26, 60)}`
  }
  if (n === 3) { // pintu
    return `<rect class="fm" x="2" y="2" width="90" height="166" /><rect class="fl" x="14" y="8" width="66" height="154" /><circle class="dotf" cx="26" cy="86" r="3" />
      ${person(52, 40, { dir: 1, s: 1.05, arms: '<path class="ln thick2" d="M40 54 l-22 -6 l-4 -20 M64 54 l8 22" />' })}
      <path class="dark" d="M60 80 l24 -8 l8 16 l-24 10 z" />
      <path class="fl" d="M104 108 L232 96 V168 H104 z" />
      ${person(160, 76, { long: true, seated: true, dir: -1, s: 0.98, arms: '<path class="ln thick2" d="M148 90 l-10 16 M172 90 l-16 16" />' })}
      <path class="fl" d="M120 106 l44 -6 l6 6 l-46 6 z" />
      ${person(214, 42, { dir: -1, s: 1.0, arms: '<path class="ln thick2" d="M202 56 l-14 30 M226 56 l-2 30" />' })}
      <path class="dark" d="M172 118 h44 v34 q0 6 -6 6 h-32 q-6 0 -6 -6 z" />
      ${arrow(62, 22, 30)}`
  }
  if (n === 4) { // loket
    return `<path class="fl" d="M2 108 H238 V168 H2 z" /><path class="ln" d="M2 108 H238 M78 108 V168 M158 108 V168" />
      ${person(180, 58, { long: false, bun: false, dir: -1, s: 1.05, shirt: 'cloth2', arms: '<path class="ln thick2" d="M168 74 l-6 26 M192 74 l4 26" />' })}
      <path class="fl" d="M2 96 H238 V108 H2 z" /><g class="fl"><rect x="186" y="80" width="42" height="18" /><rect x="192" y="68" width="30" height="14" /></g>
      ${person(74, 66, { dir: 1, s: 1.15, arms: '<path class="ln thick2" d="M60 84 l-4 20 M88 84 l30 12" />' })}
      <path class="fm" d="M40 74 q-16 8 -8 44 q4 12 18 8 l6 -50 z" opacity=".0" /><path class="fm" d="M52 84 q-16 -2 -18 20 q-2 16 16 18 z" />
      ${arrow(64, 34, 50)}`
  }

  // 5: pinjam pensil
  return `<path class="ln" d="M2 82 H238" />
    ${person(60, 52, { glasses: true, dir: 1, s: 1.15, shirt: 'cloth2', arms: '<path class="ln thick2" d="M46 70 l16 24 M76 70 l-8 24" />' })}
    <path class="dark" d="M50 88 l26 -10 l8 14 l-26 10 z" />
    ${person(176, 50, { long: false, bun: true, dir: -1, s: 1.15, arms: '<path class="ln thick2" d="M162 68 l-30 32 M190 68 l6 32" />' })}
    <rect class="dark" x="112" y="98" width="20" height="5" rx="1.5" transform="rotate(-8 112 98)" />
    <path class="fl" d="M18 110 l50 -10 l30 14 l-52 12 z" /><path class="fl" d="M150 108 l52 -10 l30 12 l-52 14 z" />
    <g transform="translate(206 126) rotate(30)"><rect class="fd" x="0" y="0" width="34" height="5" rx="1" /><path class="fl" d="M34 0 l8 2.5 l-8 2.5 z" /></g>
    ${arrow(166, 26, 60)}`
}

// ── contoh もんだい 1: halaman buku ─────────────────────────────────
const book = (n) => {
  const ell = [
    '<ellipse class="ring" cx="116" cy="82" rx="98" ry="58" />',
    '<ellipse class="ring" cx="164" cy="82" rx="48" ry="56" />',
    '<ellipse class="ring" cx="164" cy="60" rx="48" ry="24" />',
    '<ellipse class="ring" cx="164" cy="104" rx="48" ry="24" />',
  ][n - 1]
  const leader = [
    'M118 140 L134 152',
    'M164 138 L164 152',
    'M164 84 L170 152',
    'M164 128 L168 152',
  ][n - 1]

  return `<path class="fl" d="M30 44 q40 -8 88 6 q48 -14 92 -6 v86 q-44 -6 -92 8 q-48 -14 -88 -8 z" />
    <path class="ln" d="M118 50 V144" />
    <path class="fm" d="M30 130 q40 -6 88 8 q48 -14 92 -8 v6 q-44 -6 -92 8 q-48 -14 -88 -8 z" />
    <g class="fl"><rect x="46" y="54" width="60" height="26" rx="2" /><rect x="46" y="88" width="60" height="26" rx="2" /><rect x="132" y="54" width="60" height="26" rx="2" /><rect x="132" y="88" width="60" height="26" rx="2" /></g>
    <g class="num"><text x="34" y="70">1</text><text x="34" y="104">2</text><text x="122" y="70">1</text><text x="122" y="104">2</text></g>
    <g class="pg"><text x="62" y="128">−20−</text><text x="146" y="128">−21−</text></g>
    ${ell}<path class="ring" d="${leader}" /><text class="lbl" x="128" y="163">しゅくだい</text>`
}

// ── pemilih ─────────────────────────────────────────────────────────
const markup = computed(() => {
  const [kind, idx] = props.name.split('-')
  const n = Number(idx)
  let body = ''

  switch (kind) {
    case 'book': body = book(n); break
    case 'socks': body = socks(n); break
    case 'bags': body = bags(); break
    case 'items': body = items(n); break
    case 'acts': body = acts(n); break
    case 'food': body = food(n); break
    case 'talk': body = talk(idx === 'ex' ? 0 : n); break
    default: body = ''
  }

  return frame + body
})
</script>

<template>
  <!-- eslint-disable-next-line vue/no-v-html -- markup dibuat sendiri di atas, bukan input pengguna -->
  <svg
    class="jlpt-art"
    :viewBox="`0 0 ${W} ${H}`"
    role="img"
    aria-hidden="true"
    v-html="markup"
  />
</template>

<style scoped>
.jlpt-art {
  display: block;
  inline-size: 100%;
  max-inline-size: 320px;
  --ink: rgba(var(--v-theme-on-surface), 0.85);
}

.jlpt-art :deep(.ln),
.jlpt-art :deep(.fl),
.jlpt-art :deep(.fm),
.jlpt-art :deep(.fd),
.jlpt-art :deep(.dark),
.jlpt-art :deep(.skin),
.jlpt-art :deep(.cloth),
.jlpt-art :deep(.cloth2),
.jlpt-art :deep(.pants),
.jlpt-art :deep(.apple),
.jlpt-art :deep(.leaf) {
  stroke: var(--ink);
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.6;
}

.jlpt-art :deep(.ln) { fill: none; }
.jlpt-art :deep(.fl) { fill: rgba(var(--v-theme-on-surface), 0.06); }
.jlpt-art :deep(.fm) { fill: rgba(var(--v-theme-on-surface), 0.22); }
.jlpt-art :deep(.fd) { fill: rgba(var(--v-theme-on-surface), 0.6); }
.jlpt-art :deep(.dark) { fill: rgba(var(--v-theme-on-surface), 0.72); }
.jlpt-art :deep(.skin) { fill: rgb(var(--v-theme-surface)); }
.jlpt-art :deep(.cloth) { fill: rgba(var(--v-theme-on-surface), 0.12); }
.jlpt-art :deep(.cloth2) { fill: rgba(var(--v-theme-on-surface), 0.38); }
.jlpt-art :deep(.pants) { fill: rgba(var(--v-theme-on-surface), 0.3); }
.jlpt-art :deep(.hair) { fill: rgba(var(--v-theme-on-surface), 0.88); }
.jlpt-art :deep(.dot),
.jlpt-art :deep(.dotf) { fill: var(--ink); }
.jlpt-art :deep(.apple) { fill: rgba(var(--v-theme-error), 0.45); }
.jlpt-art :deep(.leaf) { fill: rgba(var(--v-theme-success), 0.5); }
.jlpt-art :deep(.grape circle) { fill: rgba(var(--v-theme-on-surface), 0.7); }
.jlpt-art :deep(.thick2) { stroke-width: 5; }
.jlpt-art :deep(.pantsline) { stroke-width: 6; }
.jlpt-art :deep(.arrow) { fill: var(--ink); }
.jlpt-art :deep(.ring) { fill: none; stroke: var(--ink); stroke-linecap: round; stroke-width: 2.6; }
.jlpt-art :deep(text) { fill: var(--ink); font-family: sans-serif; }
.jlpt-art :deep(.num text) { font-size: 13px; font-weight: 700; }
.jlpt-art :deep(.pg text) { font-size: 9px; }
.jlpt-art :deep(.lbl) { font-size: 12px; }
</style>
