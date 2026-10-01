<script setup>
// Satu butir soal menyimak: gambar (opsional) + pilihan bernomor.
// Bentuk pilihan tergantung data soal:
//   choice_art N  → tiap pilihan berupa gambar (JlptChokaiArt "N-1".."N-n")
//   choices []    → pilihan bertulis (tercetak di lembar soal)
//   (keduanya tidak ada) → hanya bulatan 1..choice_count, pilihan hanya terdengar
//   image         → satu gambar di atas pilihan (mis. rak tas, adegan ➡)
import JlptChokaiArt from '@/components/jlpt/JlptChokaiArt.vue'
import JlptText from '@/components/jlpt/JlptText.vue'

const props = defineProps({
  item: { type: Object, required: true },
  selected: { type: Number, default: null },
  readonly: { type: Boolean, default: false },
})

const emit = defineEmits(['pick'])

const count = computed(() => props.item.choices?.length ?? props.item.choice_count ?? 4)
const long = computed(() => (props.item.choices ?? []).some(c => c.length > 12))

function pick(n) {
  if (!props.readonly)
    emit('pick', n)
}
</script>

<template>
  <div class="jli">
    <JlptChokaiArt
      v-if="item.image"
      :name="item.image"
      class="jli__image"
    />

    <div
      class="jli__choices"
      :class="{
        'jli__choices--art': item.choice_art,
        'jli__choices--long': long,
        'jli__choices--bare': !item.choices && !item.choice_art,
      }"
      role="radiogroup"
    >
      <button
        v-for="n in count"
        :key="n"
        type="button"
        class="jli__choice"
        :class="{ 'is-selected': selected === n, 'is-readonly': readonly }"
        role="radio"
        :aria-checked="selected === n"
        :aria-label="String(n)"
        :tabindex="readonly ? -1 : 0"
        @click="pick(n)"
      >
        <span class="jli__bubble">{{ n }}</span>
        <JlptChokaiArt
          v-if="item.choice_art"
          :name="`${item.choice_art}-${n}`"
        />
        <span
          v-else-if="item.choices"
          class="jli__text"
        ><JlptText :text="item.choices[n - 1]" /></span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.jli__image {
  margin-block: 8px 14px;
  max-inline-size: 360px;
}

.jli__choices {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.jli__choices--long { grid-template-columns: 1fr; }
.jli__choices--art { grid-template-columns: repeat(2, minmax(0, 1fr)); }

/* pilihan yang hanya terdengar: bulatan besar berjajar */
.jli__choices--bare {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
}

.jli__choice {
  display: flex;
  align-items: center;
  padding: 10px 14px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 10px;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font-size: 1.05rem;
  gap: 12px;
  text-align: start;
  transition: background 0.12s ease, border-color 0.12s ease;
}

.jli__choices--art .jli__choice { align-items: flex-start; flex-direction: column; }
.jli__choices--bare .jli__choice { padding: 8px; border-radius: 50%; }

.jli__choice:hover:not(.is-readonly) {
  border-color: rgba(var(--v-theme-primary), 0.7);
  background: rgba(var(--v-theme-primary), 0.05);
}

.jli__choice:focus-visible { outline: 2px solid rgb(var(--v-theme-primary)); outline-offset: 2px; }
.jli__choice.is-readonly { cursor: default; }
.jli__choice.is-selected { border-color: rgb(var(--v-theme-primary)); background: rgba(var(--v-theme-primary), 0.1); }

.jli__bubble {
  display: inline-flex;
  flex: none;
  align-items: center;
  justify-content: center;
  border: 1.5px solid rgba(var(--v-theme-on-surface), 0.55);
  border-radius: 50%;
  block-size: 30px;
  font-size: 0.9rem;
  font-weight: 600;
  inline-size: 30px;
}

.jli__choices--bare .jli__bubble { block-size: 44px; font-size: 1.15rem; inline-size: 44px; }

.jli__choice.is-selected .jli__bubble {
  border-color: rgb(var(--v-theme-primary));
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

@media (max-width: 599px) {
  .jli__choices { grid-template-columns: 1fr; }
  .jli__choices--art { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
</style>
