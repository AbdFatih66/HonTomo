import { $api } from '@/utils/api'

const COOLDOWN_SECONDS = 60

// Shared by the banner and the profile page: resend the verification email
// with a client-side cooldown (the server enforces its own limit as well).
export function useResendVerification() {
  const { t } = useI18n()

  const isSending = ref(false)
  const cooldown = ref(0)
  const message = ref('')
  const errorMessage = ref('')

  let timer = null

  function startCooldown() {
    cooldown.value = COOLDOWN_SECONDS
    clearInterval(timer)
    timer = setInterval(() => {
      cooldown.value -= 1
      if (cooldown.value <= 0)
        clearInterval(timer)
    }, 1000)
  }

  async function resend() {
    if (isSending.value || cooldown.value > 0)
      return null

    isSending.value = true
    message.value = ''
    errorMessage.value = ''
    try {
      const res = await $api('/email/verification-notification', { method: 'POST' })

      message.value = res.message
      startCooldown()

      return res
    }
    catch (err) {
      const status = err?.response?.status ?? err?.status ?? err?.statusCode

      errorMessage.value = status === 429
        ? t('auth.errors.too_many_requests')
        : t('auth.errors.generic')

      return null
    }
    finally {
      isSending.value = false
    }
  }

  onBeforeUnmount(() => clearInterval(timer))

  return { resend, isSending, cooldown, message, errorMessage }
}
