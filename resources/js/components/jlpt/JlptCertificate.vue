<script setup>
// Sertifikat SIMULASI HonTomo untuk tes JLPT yang lulus. Desain milik HonTomo dan
// sengaja BUKAN tiruan sertifikat resmi JLPT (tanpa logo/cap/nama The Japan
// Foundation atau JEES); disclaimer "bukan sertifikat resmi" selalu tampil.
// Dicetak lewat window.print() (Simpan sebagai PDF) — tanpa dependensi tambahan.
const props = defineProps({
  recap: { type: Object, required: true },
})

const { t, locale } = useI18n()

const dateText = computed(() => {
  const v = props.recap.finished_at
  if (!v)
    return ''

  return new Date(v).toLocaleDateString(locale.value === 'en' ? 'en-GB' : 'id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
})

const groupScore = key => props.recap.groups.find(g => g.key === key)

function print() {
  window.print()
}
</script>

<template>
  <div class="jlpt-cert-wrap">
    <div class="d-flex justify-end mb-3 jlpt-cert-actions">
      <VBtn
        prepend-icon="tabler-printer"
        @click="print"
      >
        {{ t('jlptTest.certificate.print') }}
      </VBtn>
    </div>

    <div class="jlpt-cert">
      <div class="jlpt-cert__frame">
        <div class="jlpt-cert__brand">
          {{ t('jlptTest.certificate.issuer') }}
        </div>
        <div class="jlpt-cert__level">
          {{ recap.level }}
        </div>
        <h2 class="jlpt-cert__title">
          {{ t('jlptTest.certificate.title') }}
        </h2>
        <div class="jlpt-cert__jp">
          {{ t('jlptTest.certificate.jp_title') }}
        </div>

        <div class="jlpt-cert__label">
          {{ t('jlptTest.certificate.awarded_to') }}
        </div>
        <div class="jlpt-cert__name">
          {{ recap.participant }}
        </div>
        <p class="jlpt-cert__statement">
          {{ t('jlptTest.certificate.statement', { level: recap.level }) }}
        </p>

        <div class="jlpt-cert__scores">
          <div>
            <span>{{ t('jlptTest.certificate.language') }}</span>
            <strong>{{ groupScore('language_knowledge')?.score }} / {{ groupScore('language_knowledge')?.max }}</strong>
          </div>
          <div>
            <span>{{ t('jlptTest.certificate.listening') }}</span>
            <strong>{{ groupScore('listening')?.score }} / {{ groupScore('listening')?.max }}</strong>
          </div>
          <div class="jlpt-cert__total">
            <span>{{ t('jlptTest.certificate.total') }}</span>
            <strong>{{ recap.total_score }} / {{ recap.total_max }}</strong>
          </div>
        </div>

        <div class="jlpt-cert__meta">
          <div>
            <span>{{ t('jlptTest.certificate.test_date') }}</span>
            <strong>{{ dateText }}</strong>
          </div>
          <div class="jlpt-cert__seal">
            <span>模擬</span>
            <small>{{ t('jlptTest.certificate.seal') }}</small>
          </div>
          <div class="text-end">
            <span>{{ t('jlptTest.certificate.cert_no') }}</span>
            <strong>{{ recap.certificate_no }}</strong>
          </div>
        </div>

        <p class="jlpt-cert__disclaimer">
          {{ t('jlptTest.certificate.disclaimer') }}
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.jlpt-cert {
  --cert-ink: #1f2a44;
  --cert-accent: #b4232a;
  --cert-paper: #fffdf7;

  max-inline-size: 1000px;
  margin-inline: auto;
  aspect-ratio: 297 / 210;
  padding: 14px;
  background: var(--cert-paper);
  border: 2px solid var(--cert-ink);
  color: var(--cert-ink);
  container-type: inline-size;
}

.jlpt-cert__frame {
  display: flex;
  flex-direction: column;
  align-items: center;
  block-size: 100%;
  padding: 2.2cqw 4cqw;
  border: 1px solid var(--cert-accent);
  text-align: center;
}

.jlpt-cert__brand { font-size: 2cqw; letter-spacing: 0.4em; text-transform: uppercase; }
.jlpt-cert__level { font-size: 7cqw; font-weight: 700; line-height: 1; color: var(--cert-accent); }
.jlpt-cert__title { font-size: 3cqw; font-weight: 700; margin-block: 0.6cqw 0; }
.jlpt-cert__jp { font-size: 1.7cqw; opacity: 0.75; margin-block-end: 2cqw; }
.jlpt-cert__label { font-size: 1.4cqw; text-transform: uppercase; letter-spacing: 0.2em; opacity: 0.7; }
.jlpt-cert__name { font-size: 4cqw; font-weight: 600; margin-block: 0.4cqw 1cqw; padding-inline: 4cqw; border-block-end: 1px solid var(--cert-ink); }
.jlpt-cert__statement { font-size: 1.6cqw; max-inline-size: 70cqw; margin-block: 1.2cqw 1.6cqw; }

.jlpt-cert__scores { display: flex; margin-block: auto; gap: 3cqw; justify-content: center; font-size: 1.4cqw; }
.jlpt-cert__scores > div { display: flex; flex-direction: column; gap: 0.3cqw; }
.jlpt-cert__scores strong { font-size: 2.2cqw; }
.jlpt-cert__total strong { color: var(--cert-accent); font-size: 2.8cqw; }

.jlpt-cert__meta { display: flex; align-items: flex-end; justify-content: space-between; inline-size: 100%; margin-block-start: auto; font-size: 1.3cqw; }
.jlpt-cert__meta > div { display: flex; flex-direction: column; gap: 0.2cqw; min-inline-size: 22cqw; }
.jlpt-cert__meta span { opacity: 0.7; }
.jlpt-cert__meta strong { font-size: 1.6cqw; }

.jlpt-cert__seal {
  align-items: center;
  justify-content: center;
  inline-size: 9cqw;
  block-size: 9cqw;
  min-inline-size: 0 !important;
  border: 0.3cqw double var(--cert-accent);
  border-radius: 50%;
  color: var(--cert-accent);
}
.jlpt-cert__seal span { font-size: 3cqw; font-weight: 700; opacity: 1; }
.jlpt-cert__seal small { font-size: 0.9cqw; letter-spacing: 0.15em; }

.jlpt-cert__disclaimer { font-size: 1.05cqw; opacity: 0.7; max-inline-size: 80cqw; margin-block: 1.4cqw 0; }
</style>

<style>
@media print {
  @page { size: A4 landscape; margin: 0; }

  body * { visibility: hidden !important; }

  .jlpt-cert, .jlpt-cert * { visibility: visible !important; }

  .jlpt-cert {
    position: fixed;
    inset: 0;
    inline-size: 100vw !important;
    max-inline-size: none !important;
    block-size: 100vh;
    aspect-ratio: auto !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
