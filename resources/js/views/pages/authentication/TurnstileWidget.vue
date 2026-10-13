<script setup>
// Renders a Cloudflare Turnstile CAPTCHA challenge and emits the resulting
// token. Renders nothing (and never blocks the form) if no site key is
// configured — CAPTCHA is opt-in (see App\Rules\Turnstile on the backend).
const siteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY

const emit = defineEmits(['verified', 'expired'])

const container = ref(null)
let widgetId = null

const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js'

function loadScript() {
  if (window.turnstile)
    return Promise.resolve()

  if (window.__turnstileLoading)
    return window.__turnstileLoading

  window.__turnstileLoading = new Promise((resolve, reject) => {
    const script = document.createElement('script')

    script.src = SCRIPT_URL
    script.async = true
    script.defer = true
    script.onload = resolve
    script.onerror = reject
    document.head.appendChild(script)
  })

  return window.__turnstileLoading
}

function render() {
  if (!window.turnstile || !container.value || widgetId !== null)
    return

  widgetId = window.turnstile.render(container.value, {
    sitekey: siteKey,
    callback: token => emit('verified', token),
    'expired-callback': () => emit('expired'),
    'error-callback': () => emit('expired'),
  })
}

function reset() {
  if (widgetId !== null && window.turnstile)
    window.turnstile.reset(widgetId)
}

defineExpose({ reset })

onMounted(async () => {
  if (!siteKey)
    return

  try {
    await loadScript()
    render()
  }
  catch {
    // Turnstile failed to load (e.g. blocked by an ad-blocker or offline):
    // the form still works, App\Rules\Turnstile only fails a submission
    // when the server actually has a secret key configured AND receives
    // no/invalid token, so this degrades to "no CAPTCHA" rather than a dead end.
  }
})

onBeforeUnmount(() => {
  if (widgetId !== null && window.turnstile)
    window.turnstile.remove(widgetId)
})
</script>

<template>
  <div
    v-if="siteKey"
    ref="container"
    class="mb-4"
  />
</template>
