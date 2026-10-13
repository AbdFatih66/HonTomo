import { ofetch } from 'ofetch'
import { i18n } from '@/i18n'

// Session-expiry reason from a device that was signed out by the 3-device
// limit (see RejectEvictedDeviceToken). Read once by the auth store when the
// person lands back on /login, then cleared.
export const authKickReason = ref(null)

export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  async onRequest({ options }) {
    const headers = new Headers(options.headers)
    const accessToken = useCookie('accessToken').value
    if (accessToken)
      headers.set('Authorization', `Bearer ${accessToken}`)

    // Tell the backend which UI language is active so localized fields
    // (prompts, option labels, titles) match the language switcher.
    headers.set('X-UI-Language', i18n.global.locale.value)
    headers.set('Accept', 'application/json')
    options.headers = headers
  },
  async onResponseError({ response }) {
    if (response.status === 401 && response._data?.reason === 'device_limit')
      authKickReason.value = response._data.reason
  },
})
