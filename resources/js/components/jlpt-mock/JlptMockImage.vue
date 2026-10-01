<script setup>
// Gambar soal JLPT. Kalau file gambar belum ada (belum dibuat / gagal
// dimuat), tampil kotak pengganti berisi deskripsi (alt) supaya ujian tetap
// bisa dikerjakan. `arrow` = {x, y} dalam persen: tanda panah (➡ di ujian
// asli, Mondai 3 Chokai) digambar oleh aplikasi di atas gambar.
const props = defineProps({
  src: { type: String, default: '' },
  alt: { type: String, default: '' },
  arrow: { type: Object, default: null },
})

const { t } = useI18n()
const failed = ref(false)

watch(() => props.src, () => { failed.value = false })
</script>

<template>
  <div class="jlpt-img">
    <img
      v-if="src && !failed"
      :src="src"
      :alt="alt"
      class="jlpt-img__pic"
      loading="lazy"
      @error="failed = true"
    >
    <div
      v-else
      class="jlpt-img__missing"
    >
      <VIcon
        icon="tabler-photo-off"
        size="28"
        class="mb-2"
      />
      <span class="text-caption d-block">{{ t('jlptMock.image_missing') }}</span>
      <span class="text-body-2 d-block mt-1">{{ alt }}</span>
    </div>

    <svg
      v-if="arrow"
      class="jlpt-img__arrow"
      :style="{ insetInlineStart: `${arrow.x}%`, insetBlockStart: `${arrow.y}%` }"
      viewBox="0 0 24 32"
      aria-hidden="true"
    >
      <path
        d="M12 2 V24 M4 15 L12 27 L20 15"
        fill="none"
        stroke="currentColor"
        stroke-width="4"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
  </div>
</template>

<style scoped>
/* Warna hex disengaja: gambar soal adalah garis hitam di atas putih di kedua
   tema (light/dark), seperti kertas ujian asli — bukan token tema. */
.jlpt-img {
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  border-radius: 10px;
  background: #fff;
  color: #000;
  inline-size: 100%;
}

.jlpt-img__pic {
  display: block;
  inline-size: 100%;
  block-size: auto;
}

.jlpt-img__missing {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 16px;
  aspect-ratio: 4 / 3;
  background: rgba(var(--v-theme-on-surface), 0.04);
  color: rgba(var(--v-theme-on-surface), 0.7);
  text-align: center;
}

.jlpt-img__arrow {
  position: absolute;
  color: #000;
  block-size: 15%;
  inline-size: auto;
  transform: translateX(-50%);
}
</style>
