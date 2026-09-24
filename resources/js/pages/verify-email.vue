<script setup>
import AuthLayout from '@/views/pages/authentication/AuthLayout.vue'
import { $api } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'
import { useResendVerification } from '@/composables/useResendVerification'

// Landing page of the link in the verification email. Public and not
// guest-only: it works whether or not this browser is signed in.
definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { resend, isSending, cooldown, message: resendMessage, errorMessage: resendError } = useResendVerification()

const state = ref('loading') // loading | success | already | invalid
const message = ref('')

onMounted(async () => {
  const { id, hash, expires, signature } = route.query

  // drop the signed parameters from the address bar / history
  await router.replace({ name: 'verify-email' })

  if (![id, hash, expires, signature].every(v => typeof v === 'string' && v)) {
    state.value = 'invalid'

    return
  }

  try {
    // Same order as the signed link: expires, then signature.
    const res = await $api(
      `/email/verify/${encodeURIComponent(id)}/${encodeURIComponent(hash)}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`,
      { method: 'POST' },
    )

    message.value = res.message
    state.value = res.already_verified ? 'already' : 'success'

    if (authStore.isAuthenticated)
      await authStore.fetchUser() // refresh the "verified" status in the UI
  }
  catch {
    state.value = 'invalid'
  }
})

const primaryTarget = computed(() => (authStore.isAuthenticated ? { name: 'root' } : { name: 'login' }))
</script>

<template>
  <AuthLayout>
    <VCardText class="text-center">
      <!-- Working -->
      <template v-if="state === 'loading'">
        <VProgressCircular
          indeterminate
          color="primary"
          size="48"
          class="mb-4"
        />
        <p class="mb-0">
          {{ t('verification.verifying') }}
        </p>
      </template>

      <!-- Verified -->
      <template v-else-if="state === 'success' || state === 'already'">
        <VAvatar
          color="success"
          variant="tonal"
          size="64"
          class="mb-4"
        >
          <VIcon
            icon="tabler-mail-check"
            size="32"
          />
        </VAvatar>
        <h4 class="text-h4 mb-2">
          {{ t('verification.success_title') }}
        </h4>
        <p class="mb-6">
          {{ message }}
        </p>
        <VBtn
          block
          :to="primaryTarget"
        >
          {{ authStore.isAuthenticated ? t('verification.continue') : t('auth.reset_password.go_login') }}
        </VBtn>
      </template>

      <!-- Invalid / expired -->
      <template v-else>
        <VAvatar
          color="error"
          variant="tonal"
          size="64"
          class="mb-4"
        >
          <VIcon
            icon="tabler-link-off"
            size="32"
          />
        </VAvatar>
        <h4 class="text-h4 mb-2">
          {{ t('verification.invalid_title') }}
        </h4>
        <p class="mb-6">
          {{ t('verification.invalid_text') }}
        </p>

        <VAlert
          v-if="resendMessage"
          type="success"
          variant="tonal"
          class="mb-4"
        >
          {{ resendMessage }}
        </VAlert>
        <VAlert
          v-if="resendError"
          type="error"
          variant="tonal"
          class="mb-4"
        >
          {{ resendError }}
        </VAlert>

        <VBtn
          v-if="authStore.isAuthenticated"
          block
          :loading="isSending"
          :disabled="isSending || cooldown > 0"
          @click="resend"
        >
          {{ cooldown > 0 ? t('verification.resend_in', { s: cooldown }) : t('verification.resend') }}
        </VBtn>
        <VBtn
          v-else
          block
          :to="{ name: 'login', query: { redirect: '/profile' } }"
        >
          {{ t('verification.login_to_resend') }}
        </VBtn>
      </template>
    </VCardText>
  </AuthLayout>
</template>
