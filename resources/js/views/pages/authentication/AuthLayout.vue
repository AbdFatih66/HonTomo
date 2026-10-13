<script setup>
import I18n from '@core/components/I18n.vue'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import authBrandLight from '@images/pages/auth-hontomo-brand.png'
import authBrandDark from '@images/pages/auth-hontomo-brand-dark.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

// Shared shell for every guest auth page (login, register, forgot/reset
// password, email verification): branded panel + a card slot.
const { t } = useI18n()

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)
const authBrand = useGenerateImageVariant(authBrandLight, authBrandDark)

const languages = [
  { label: 'Bahasa Indonesia', i18nLang: 'id' },
  { label: 'English', i18nLang: 'en' },
]
</script>

<template>
  <a href="javascript:void(0)">
    <div class="auth-logo d-flex d-md-none align-center gap-x-3">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="auth-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </a>

  <div class="auth-language">
    <I18n :languages="languages" />
  </div>

  <VRow
    no-gutters
    class="auth-wrapper bg-surface"
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 6.25rem;"
        >
          <div class="auth-brand-card">
            <img
              :src="authBrand"
              alt="HonTomo"
              class="auth-brand-img"
            >
            <p class="auth-brand-tagline">
              {{ t('auth.tagline') }}
            </p>
          </div>
        </div>

        <img
          class="auth-footer-mask flip-in-rtl"
          :src="authThemeMask"
          alt=""
          height="280"
          width="100"
        >
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-6 w-100"
      >
        <slot />
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";

.auth-language {
  position: absolute;
  z-index: 2;
  inset-block-start: 1rem;
  inset-inline-end: 1rem;
}

// Banner: logo + tagline on a card that follows the light/dark theme
.auth-brand-card {
  position: relative;
  z-index: 1; // keep the card above the decorative footer mask
  display: flex;
  flex-direction: column;
  align-items: center;
  inline-size: 100%;
  max-inline-size: 540px;
  padding: 2.5rem 3rem 2.25rem;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 1.5rem;
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 12px 40px rgba(10, 90, 170, 0.1);
}

.v-theme--dark .auth-brand-card {
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
}

.auth-brand-img {
  display: block;
  inline-size: 100%;
  max-inline-size: 400px;
  block-size: auto;
}

.auth-brand-tagline {
  margin: 1.25rem 0 0;
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
  font-size: 1.375rem;
  font-weight: 500;
  letter-spacing: 0.01em;
  text-align: center;
}
</style>
