import { i18n } from '@/i18n'

const { t } = i18n.global

/**
 * Only allow same-origin, in-app redirects (blocks open-redirect via ?redirect=).
 */
export function safeRedirect(target, fallback = '/') {
  if (typeof target !== 'string')
    return fallback

  // must be a path, and not protocol-relative (//evil.com) or backslash tricks
  if (!target.startsWith('/') || target.startsWith('//') || target.includes('\\'))
    return fallback

  // never bounce back into an auth page
  if (/^\/(login|register|forgot-password|reset-password|verify-email|auth\/callback)(\/|\?|$)/.test(target))
    return fallback

  return target
}

/**
 * Turn an ofetch error into { message, fieldErrors } for the UI.
 * Validation texts come from the server already translated
 * (X-UI-Language header); transport-level cases are translated here.
 */
export function resolveAuthError(err) {
  const status = err?.response?.status ?? err?.status ?? err?.statusCode

  if (status === 422) {
    const errors = err?.data?.errors ?? {}

    const fieldErrors = Object.fromEntries(
      Object.entries(errors).map(([field, msgs]) => [field, msgs]),
    )

    return { message: '', fieldErrors }
  }

  if (status === 429)
    return { message: t('auth.errors.too_many_requests'), fieldErrors: {} }

  if (!status)
    return { message: t('auth.errors.network'), fieldErrors: {} }

  return { message: t('auth.errors.generic'), fieldErrors: {} }
}

// Client-side rules (UX only — the server is the source of truth).
export const rules = {
  required: v => (v !== null && v !== undefined && String(v).trim() !== '') || t('auth.validation.required'),
  email: v => !v || /^[^\s@]+@[^\s@][^\s.@]*\.[^\s@]+$/.test(v) || t('auth.validation.email'),
  minLength: n => v => !v || String(v).length >= n || t('auth.validation.min_length', { n }),
  password: v => !v || (/[A-Z]/i.test(v) && /\d/.test(v)) || t('auth.validation.password_strength'),
  confirmed: other => v => v === other() || t('auth.validation.confirmed'),
}
