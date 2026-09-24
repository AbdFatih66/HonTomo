<script setup>
import AuthLayout from '@/views/pages/authentication/AuthLayout.vue'
import { $api } from '@/utils/api'
import { resolveAuthError, rules } from '@/utils/auth'

definePage({
  meta: {
    layout: 'blank',
    public: true,
    guestOnly: true,
  },
})

const { t } = useI18n()

const COOLDOWN_SECONDS = 60

const formRef = ref()
const email = ref('')
const isSubmitting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})
const successMessage = ref('')
const cooldown = ref(0)

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

onBeforeUnmount(() => clearInterval(timer))

async function onSubmit() {
  if (isSubmitting.value || cooldown.value > 0)
    return

  errorMessage.value = ''
  fieldErrors.value = {}

  const { valid } = await formRef.value.validate()
  if (!valid)
    return

  isSubmitting.value = true
  try {
    const res = await $api('/forgot-password', {
      method: 'POST',
      body: { email: email.value },
    })

    successMessage.value = res.message
    startCooldown()
  }
  catch (err) {
    const { message, fieldErrors: errors } = resolveAuthError(err)

    errorMessage.value = message
    fieldErrors.value = errors
  }
  finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <VCardText>
      <h4 class="text-h4 mb-1">
        {{ t('auth.forgot_password.title') }}
      </h4>
      <p class="mb-0">
        {{ t('auth.forgot_password.subtitle') }}
      </p>
    </VCardText>

    <VCardText>
      <VAlert
        v-if="successMessage"
        type="success"
        variant="tonal"
        class="mb-6"
        :title="t('auth.forgot_password.sent_title')"
      >
        {{ successMessage }}
      </VAlert>

      <VAlert
        v-if="errorMessage"
        type="error"
        variant="tonal"
        class="mb-6"
        closable
        @click:close="errorMessage = ''"
      >
        {{ errorMessage }}
      </VAlert>

      <VForm
        ref="formRef"
        validate-on="submit"
        @submit.prevent="onSubmit"
      >
        <VRow>
          <VCol cols="12">
            <AppTextField
              v-model="email"
              autofocus
              :label="t('auth.email')"
              type="email"
              autocomplete="email"
              placeholder="nama@email.com"
              :rules="[rules.required, rules.email]"
              :error-messages="fieldErrors.email"
            />
          </VCol>

          <VCol cols="12">
            <VBtn
              block
              type="submit"
              :loading="isSubmitting"
              :disabled="isSubmitting || cooldown > 0"
            >
              {{ successMessage ? t('auth.forgot_password.resend') : t('auth.forgot_password.submit') }}
            </VBtn>

            <p
              v-if="cooldown > 0"
              class="text-caption text-medium-emphasis text-center mt-2 mb-0"
            >
              {{ t('auth.forgot_password.resend_in', { s: cooldown }) }}
            </p>
          </VCol>

          <VCol cols="12">
            <RouterLink
              class="d-flex align-center justify-center text-primary"
              :to="{ name: 'login' }"
            >
              <VIcon
                icon="tabler-chevron-left"
                size="20"
                class="me-1 flip-in-rtl"
              />
              <span>{{ t('auth.forgot_password.back_to_login') }}</span>
            </RouterLink>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </AuthLayout>
</template>
