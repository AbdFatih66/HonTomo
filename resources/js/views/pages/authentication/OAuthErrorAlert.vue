<script setup>
// Shows a translated message for ?oauth_error=<code> (set by the backend
// callback). Unknown codes fall back to a generic failure message.
const { t, te } = useI18n()
const route = useRoute()

const dismissed = ref(false)

const code = computed(() => {
  const value = route.query.oauth_error

  return typeof value === 'string' ? value : ''
})

const message = computed(() => {
  if (!code.value)
    return ''

  return te(`auth.oauth.errors.${code.value}`)
    ? t(`auth.oauth.errors.${code.value}`)
    : t('auth.oauth.errors.failed')
})

watch(code, () => {
  dismissed.value = false
})
</script>

<template>
  <VAlert
    v-if="message && !dismissed"
    type="error"
    variant="tonal"
    class="mb-6"
    closable
    @click:close="dismissed = true"
  >
    {{ message }}
  </VAlert>
</template>
