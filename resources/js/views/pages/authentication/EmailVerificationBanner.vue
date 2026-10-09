<script setup>
import { useResendVerification } from '@/composables/useResendVerification'
import { useAuthStore } from '@/stores/auth'

// Gentle, dismissible nudge for unverified accounts. Never blocks anything.
const { t } = useI18n()
const authStore = useAuthStore()
const { resend, isSending, cooldown, message, errorMessage } = useResendVerification()

const DISMISS_KEY = 'hontomo:verify-banner-dismissed'

const dismissed = ref(false)

try {
  dismissed.value = sessionStorage.getItem(DISMISS_KEY) === '1'
}
catch {
  // storage unavailable: just show the banner
}

const isVisible = computed(() =>
  !!authStore.user && authStore.user.email_verified === false && !dismissed.value,
)

function dismiss() {
  dismissed.value = true
  try {
    sessionStorage.setItem(DISMISS_KEY, '1')
  }
  catch {
    // ignore
  }
}
</script>

<template>
  <VAlert
    v-if="isVisible"
    type="warning"
    variant="tonal"
    class="mb-6"
    closable
    @click:close="dismiss"
  >
    <div class="d-flex flex-wrap align-center justify-space-between gap-2">
      <span>{{ message || errorMessage || t('verification.banner', { email: authStore.user.email }) }}</span>

      <VBtn
        size="small"
        variant="outlined"
        color="warning"
        :loading="isSending"
        :disabled="isSending || cooldown > 0"
        @click="resend"
      >
        {{ cooldown > 0 ? t('verification.resend_in', { s: cooldown }) : t('verification.resend') }}
      </VBtn>
    </div>
  </VAlert>
</template>
