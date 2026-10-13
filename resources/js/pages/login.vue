<script setup>
import { useAuthStore } from '@/stores/auth'
import { resolveAuthError, rules, safeRedirect } from '@/utils/auth'
import { isWebAuthnSupported, loginWithPasskey } from '@/utils/webauthn'
import AuthLayout from '@/views/pages/authentication/AuthLayout.vue'
import GoogleButton from '@/views/pages/authentication/GoogleButton.vue'
import OAuthErrorAlert from '@/views/pages/authentication/OAuthErrorAlert.vue'
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'blank',
    public: true,
    guestOnly: true,
  },
})

const { t } = useI18n()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const formRef = ref()

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const isSubmitting = ref(false)
const isPasswordVisible = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})

const supportsBiometric = isWebAuthnSupported()
const isBiometricLoading = ref(false)

// A device kicked out by the 3-device limit lands here with an explanation.
if (route.query.auth_notice === 'device_limit')
  errorMessage.value = t('auth.device_evicted.device_limit')

async function onBiometricLogin() {
  if (isBiometricLoading.value)
    return

  errorMessage.value = ''
  isBiometricLoading.value = true
  try {
    const res = await loginWithPasskey()

    authStore.persistPasskeySession(res)
    await router.replace(safeRedirect(route.query.redirect))
  }
  catch {
    // The browser itself cancels/times out with no server response in many
    // cases (user dismissed the prompt) — treat every failure the same way.
    errorMessage.value = t('auth.webauthn.failed')
  }
  finally {
    isBiometricLoading.value = false
  }
}

async function onSubmit() {
  if (isSubmitting.value)
    return // prevent double submission

  errorMessage.value = ''
  fieldErrors.value = {}

  const { valid } = await formRef.value.validate()
  if (!valid)
    return

  isSubmitting.value = true
  try {
    await authStore.login(form.value.email, form.value.password, form.value.remember)
    await router.replace(safeRedirect(route.query.redirect))
  }
  catch (err) {
    const { message, fieldErrors: errors } = resolveAuthError(err)

    errorMessage.value = message
    fieldErrors.value = errors

    // Wrong credentials are reported on the email field; show them as one alert
    if (errors.email?.length && !message)
      errorMessage.value = errors.email[0]
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
        {{ t('auth.login.title', { app: themeConfig.app.title }) }} 👋🏻
      </h4>
      <p class="mb-0">
        {{ t('auth.login.subtitle') }}
      </p>
    </VCardText>

    <VCardText>
      <OAuthErrorAlert />

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
              v-model="form.email"
              autofocus
              :label="t('auth.email')"
              type="email"
              autocomplete="email"
              placeholder="nama@email.com"
              :rules="[rules.required, rules.email]"
              :error-messages="fieldErrors.email && !errorMessage ? fieldErrors.email : []"
            />
          </VCol>

          <VCol cols="12">
            <AppTextField
              v-model="form.password"
              :label="t('auth.password')"
              placeholder="············"
              :type="isPasswordVisible ? 'text' : 'password'"
              autocomplete="current-password"
              :rules="[rules.required]"
              :error-messages="fieldErrors.password"
              :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
            />

            <div class="d-flex align-center flex-wrap justify-space-between my-6">
              <VCheckbox
                v-model="form.remember"
                :label="t('auth.remember_me')"
              />
              <RouterLink
                class="text-primary"
                :to="{ path: '/forgot-password' }"
              >
                {{ t('auth.forgot_password.link') }}
              </RouterLink>
            </div>

            <VBtn
              block
              type="submit"
              :loading="isSubmitting"
              :disabled="isSubmitting"
            >
              {{ t('auth.login.submit') }}
            </VBtn>
          </VCol>

          <VCol cols="12">
            <div class="d-flex align-center gap-x-3">
              <VDivider />
              <span class="text-medium-emphasis text-body-2">{{ t('auth.oauth.or') }}</span>
              <VDivider />
            </div>
          </VCol>

          <VCol cols="12">
            <GoogleButton :redirect="typeof route.query.redirect === 'string' ? route.query.redirect : ''" />
          </VCol>

          <VCol
            v-if="supportsBiometric"
            cols="12"
          >
            <VBtn
              block
              variant="outlined"
              color="default"
              :loading="isBiometricLoading"
              :disabled="isBiometricLoading"
              @click="onBiometricLogin"
            >
              <template #prepend>
                <VIcon icon="tabler-fingerprint" />
              </template>
              {{ t('auth.webauthn.login_button') }}
            </VBtn>
          </VCol>

          <VCol
            cols="12"
            class="text-body-1 text-center"
          >
            <span class="d-inline-block">
              {{ t('auth.login.no_account') }}
            </span>
            <RouterLink
              class="text-primary ms-1 d-inline-block text-body-1"
              :to="{ path: '/register', query: route.query.redirect ? { redirect: route.query.redirect } : {} }"
            >
              {{ t('auth.login.create_account') }}
            </RouterLink>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </AuthLayout>
</template>
