import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

// "Install app" support.
// - Android / desktop Chrome & Edge: uses the `beforeinstallprompt` event (captured in application.blade.php).
// - iPhone / iPad: there is no install API, so we show "Add to Home Screen" instructions instead.
function detectStandalone() {
  return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true
}

function detectIos() {
  const ua = window.navigator.userAgent

  return /iphone|ipad|ipod/i.test(ua) || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1)
}

export function useInstallPrompt() {
  const promptEvent = ref(null)
  const isInstalled = ref(false)
  const isIos = ref(false)
  const showIosGuide = ref(false)

  const sync = () => {
    promptEvent.value = window.__hontomoInstallPrompt || null
  }

  const onInstalled = () => {
    isInstalled.value = true
    promptEvent.value = null
  }

  onMounted(() => {
    isInstalled.value = detectStandalone()
    isIos.value = detectIos()
    sync()

    window.addEventListener('hontomo:installable', sync)
    window.addEventListener('hontomo:installed', onInstalled)
  })

  onBeforeUnmount(() => {
    window.removeEventListener('hontomo:installable', sync)
    window.removeEventListener('hontomo:installed', onInstalled)
  })

  const canInstall = computed(() => !isInstalled.value && (!!promptEvent.value || isIos.value))

  async function install() {
    if (promptEvent.value) {
      const event = promptEvent.value

      // A saved prompt event can only be used once
      window.__hontomoInstallPrompt = null
      promptEvent.value = null

      await event.prompt()
      await event.userChoice

      return
    }

    if (isIos.value)
      showIosGuide.value = true
  }

  return { canInstall, isIos, showIosGuide, install }
}
