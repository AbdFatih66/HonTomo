<script setup>
import { useAuthStore } from '@/stores/auth'
import { safeRedirect } from '@/utils/auth'

// Landing page of the Google flow: trade the one-time code for a session.
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

onMounted(async () => {
  const code = typeof route.query.code === 'string' ? route.query.code : ''
  const redirect = route.query.redirect

  // Take the (single-use) code out of the address bar / history right away.
  await router.replace({ name: 'auth-callback' })

  if (!code) {
    await router.replace({ name: 'login', query: { oauth_error: 'failed' } })

    return
  }

  try {
    await authStore.loginWithCode(code)
    await router.replace(safeRedirect(redirect))
  }
  catch {
    await router.replace({ name: 'login', query: { oauth_error: 'failed' } })
  }
})
</script>

<template>
  <div class="d-flex flex-column align-center justify-center gap-4 w-100 min-vh-100 pa-6">
    <VProgressCircular
      indeterminate
      color="primary"
      size="48"
    />
    <p class="text-body-1 mb-0">
      {{ t('auth.oauth.signing_in') }}
    </p>
  </div>
</template>
