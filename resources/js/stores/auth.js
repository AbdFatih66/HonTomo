import { $api } from '@/utils/api'
import { applyUiLanguage } from '@/i18n'

// Last known profile, so the installed app can still open when there is no connection
const CACHED_USER_KEY = 'hontomo:user'

function saveCachedUser(user) {
  try {
    localStorage.setItem(CACHED_USER_KEY, JSON.stringify(user))
  }
  catch {}
}

function readCachedUser() {
  try {
    return JSON.parse(localStorage.getItem(CACHED_USER_KEY))
  }
  catch {
    return null
  }
}

function clearOfflineData() {
  try {
    localStorage.removeItem(CACHED_USER_KEY)
  }
  catch {}

  // Also drop cached API data (service worker) when the user signs out
  if ('caches' in window) {
    caches.keys()
      .then(keys => keys.filter(key => key.startsWith('hontomo-api')).forEach(key => caches.delete(key)))
      .catch(() => {})
  }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  // Shared by login() and register(): keep the token in a cookie that expires
  // together with the server-side token, and cache the profile for offline use.
  function persistSession(res) {
    const expiresAt = res.expires_at ? new Date(res.expires_at).getTime() : null
    const maxAge = expiresAt ? Math.max(Math.floor((expiresAt - Date.now()) / 1000), 60) : undefined

    const accessToken = useCookie('accessToken', {
      maxAge,
      sameSite: 'lax',
      secure: window.location.protocol === 'https:',
    })

    accessToken.value = res.token
    user.value = res.user
    saveCachedUser(res.user)
    applyUiLanguage(res.user?.ui_language)

    return res.user
  }

  async function login(email, password, remember = false) {
    const res = await $api('/login', {
      method: 'POST',
      body: { email, password, remember },
    })

    return persistSession(res)
  }

  // Google sign-in: the callback gives the SPA a short-lived, single-use code;
  // it is exchanged here for a normal API token (no token ever sits in a URL).
  async function loginWithCode(code) {
    const res = await $api('/auth/google/exchange', {
      method: 'POST',
      body: { code },
    })

    return persistSession(res)
  }

  // Called with the already-completed WebAuthn login response
  // (see utils/webauthn.js loginWithPasskey()) — same {user, token,
  // expires_at} shape as every other login path.
  function persistPasskeySession(res) {
    return persistSession(res)
  }

  async function register(payload) {
    const res = await $api('/register', {
      method: 'POST',
      body: payload,
    })

    return persistSession(res)
  }

  async function logout() {
    try {
      await $api('/logout', { method: 'POST' })
    }
    catch {
      // token may already be invalid/expired — proceed to clear locally regardless
    }
    clearLocalSession()
  }

  // Same local cleanup as logout(), without calling the (already-invalid) API.
  // Used when a device is kicked out server-side (e.g. the 3-device limit).
  function clearLocalSession() {
    useCookie('accessToken').value = null
    user.value = null
    clearOfflineData()
  }

  // Restores the session from the stored token (e.g. on page refresh).
  async function fetchUser() {
    const accessToken = useCookie('accessToken')
    if (!accessToken.value)
      return null

    isLoading.value = true
    try {
      const res = await $api('/me')

      user.value = res.user
      saveCachedUser(res.user)
      applyUiLanguage(res.user?.ui_language)

      return res.user
    }
    catch (error) {
      const status = error?.response?.status ?? error?.status ?? error?.statusCode

      // Token rejected by the server -> really signed out
      if (status === 401 || status === 403) {
        accessToken.value = null
        user.value = null
        clearOfflineData()

        return null
      }

      // Offline / server unreachable: keep the session and use the last known profile
      const cached = readCachedUser()

      if (cached) {
        user.value = cached
        applyUiLanguage(cached.ui_language)

        return cached
      }

      return null
    }
    finally {
      isLoading.value = false
    }
  }

  return { user, isLoading, isAuthenticated, isAdmin, login, register, loginWithCode, persistPasskeySession, logout, clearLocalSession, fetchUser }
})
