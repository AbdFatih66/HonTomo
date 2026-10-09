/* HonTomo service worker
 *
 * - App shell: the HTML page is fetched network-first and the last good copy is kept,
 *   so the app still opens without a connection (falls back to /offline.html on a first offline visit).
 * - Static files (/build/assets/*, images, css): served from cache after the first visit.
 * - Kana reference data (GET /api/kana, /api/kana/{id}): network-first, cached copy when offline.
 * - Every other /api/* request goes straight to the network and is never cached.
 *
 * Bump VERSION when this file's caching rules change.
 */
const VERSION = 'v1'
const SHELL_CACHE = `hontomo-shell-${VERSION}`
const ASSET_CACHE = `hontomo-assets-${VERSION}`
const API_CACHE = `hontomo-api-${VERSION}`
const CURRENT_CACHES = [SHELL_CACHE, ASSET_CACHE, API_CACHE]

const SCOPE = self.registration.scope
const OFFLINE_URL = new URL('offline.html', SCOPE).href
const SHELL_KEY = new URL('__app-shell', SCOPE).href
const PRECACHE = ['offline.html', 'logo.png', 'logo-dark.png'].map(p => new URL(p, SCOPE).href)

const NAVIGATION_TIMEOUT = 6000
const MAX_ASSETS = 150

self.addEventListener('install', event => {
  event.waitUntil((async () => {
    const cache = await caches.open(SHELL_CACHE)

    await Promise.all(PRECACHE.map(url => cache.add(url).catch(() => {})))
    await self.skipWaiting()
  })())
})

self.addEventListener('activate', event => {
  event.waitUntil((async () => {
    const names = await caches.keys()

    await Promise.all(
      names
        .filter(name => name.startsWith('hontomo-') && !CURRENT_CACHES.includes(name))
        .map(name => caches.delete(name)),
    )
    await self.clients.claim()
  })())
})

self.addEventListener('fetch', event => {
  const request = event.request

  if (request.method !== 'GET')
    return

  const url = new URL(request.url)

  if (url.origin !== self.location.origin)
    return

  if (request.mode === 'navigate') {
    event.respondWith(handleNavigation(request))

    return
  }

  if (url.pathname === '/api/kana/quiz')
    return // random per request – never cache

  if (url.pathname === '/api/kana' || url.pathname.startsWith('/api/kana/')) {
    event.respondWith(handleKanaApi(request, url))

    return
  }

  if (url.pathname.startsWith('/api/') || url.pathname === '/sw.js')
    return // network only

  if (url.pathname.startsWith('/build/assets/')) {
    event.respondWith(cacheFirst(request)) // hashed file names -> immutable

    return
  }

  if (/\.(?:png|jpe?g|svg|webp|gif|ico|woff2?|css|js|webmanifest)$/i.test(url.pathname))
    event.respondWith(staleWhileRevalidate(request))
})

function withTimeout(promise, ms) {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error('timeout')), ms)

    promise.then(
      value => { clearTimeout(timer); resolve(value) },
      error => { clearTimeout(timer); reject(error) },
    )
  })
}

async function handleNavigation(request) {
  const cache = await caches.open(SHELL_CACHE)

  try {
    const response = await withTimeout(fetch(request), NAVIGATION_TIMEOUT)
    const type = response.headers.get('content-type') || ''

    if (response.ok && !response.redirected && type.includes('text/html'))
      cache.put(SHELL_KEY, response.clone())

    return response
  }
  catch {
    return (await cache.match(SHELL_KEY)) || (await cache.match(OFFLINE_URL)) || Response.error()
  }
}

// Cache key includes the UI language, because the API returns localized fields
function apiKey(request, url) {
  const lang = request.headers.get('X-UI-Language') || 'en'
  const sep = url.search ? '&' : '?'

  return `${url.origin}${url.pathname}${url.search}${sep}__lang=${encodeURIComponent(lang)}`
}

async function handleKanaApi(request, url) {
  const cache = await caches.open(API_CACHE)
  const key = apiKey(request, url)

  try {
    const response = await fetch(request)

    if (response.ok)
      cache.put(key, response.clone())

    return response
  }
  catch {
    const cached = await cache.match(key)

    if (cached)
      return cached

    return new Response(JSON.stringify({ message: 'You are offline.' }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' },
    })
  }
}

async function cacheFirst(request) {
  const cache = await caches.open(ASSET_CACHE)
  const cached = await cache.match(request)

  if (cached)
    return cached

  const response = await fetch(request)

  if (response.ok) {
    cache.put(request, response.clone())
    trimCache(cache, MAX_ASSETS)
  }

  return response
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(ASSET_CACHE)
  const cached = await cache.match(request)

  const network = fetch(request)
    .then(response => {
      if (response.ok)
        cache.put(request, response.clone())

      return response
    })
    .catch(() => null)

  return cached || (await network) || Response.error()
}

async function trimCache(cache, max) {
  const keys = await cache.keys()

  if (keys.length > max)
    await Promise.all(keys.slice(0, keys.length - max).map(key => cache.delete(key)))
}
