// Vue i18n setup.
// UI language only (Indonesian/English) — the Japanese being studied
// never goes through this; it comes straight from the API as-is.

import { createI18n } from 'vue-i18n'
import id from './locales/id.json'
import en from './locales/en.json'

const SUPPORTED = ['id', 'en']

function initialLocale() {
  const saved = localStorage.getItem('ui_language')

  return SUPPORTED.includes(saved) ? saved : 'id'
}

export const i18n = createI18n({
  legacy: false,
  locale: initialLocale(),
  fallbackLocale: 'en',
  messages: { id, en },
})

// Apply a language locally only (no server call). Used when restoring the
// language saved on the user's profile after login / page refresh.
export function applyUiLanguage(locale) {
  if (!SUPPORTED.includes(locale))
    return
  i18n.global.locale.value = locale
  localStorage.setItem('ui_language', locale)
  document.documentElement.setAttribute('lang', locale)
}

// Called by the language switcher: apply locally, then persist server-side
// so it survives login on another device.
export async function setUiLanguage(locale) {
  applyUiLanguage(locale)

  // Imported lazily to avoid a circular import (api.js reads i18n).
  const { $api } = await import('@/utils/api')

  try {
    await $api('/locale', { method: 'POST', body: { locale } })
  }
  catch {
    // Not logged in yet, or offline — the local choice still applies.
  }
}
