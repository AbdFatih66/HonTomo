<script setup>
import AuthLayout from '@/views/pages/authentication/AuthLayout.vue'
import GoogleButton from '@/views/pages/authentication/GoogleButton.vue'
import OAuthErrorAlert from '@/views/pages/authentication/OAuthErrorAlert.vue'
import TurnstileWidget from '@/views/pages/authentication/TurnstileWidget.vue'
import { useAuthStore } from '@/stores/auth'
import { resolveAuthError, rules, safeRedirect } from '@/utils/auth'

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
const turnstileRef = ref()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  website: '', // honeypot — hidden from real users
  cf_turnstile_response: '',
})

const isSubmitting = ref(false)
const isPasswordVisible = ref(false)
const isConfirmVisible = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})

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
    await authStore.register({ ...form.value })
    await router.replace(safeRedirect(route.query.redirect))
  }
  catch (err) {
    const { message, fieldErrors: errors } = resolveAuthError(err)

    errorMessage.value = message
    fieldErrors.value = errors

    // A Turnstile token is single-use; get a fresh one for the retry.
    form.value.cf_turnstile_response = ''
    turnstileRef.value?.reset()
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
        {{ t('auth.register.title') }} 🚀
      </h4>
      <p class="mb-0">
        {{ t('auth.register.subtitle') }}
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
              v-model="form.name"
              autofocus
              :label="t('auth.name')"
              autocomplete="name"
              :placeholder="t('auth.name_placeholder')"
              :rules="[rules.required, rules.minLength(2)]"
              :error-messages="fieldErrors.name"
            />
          </VCol>

          <VCol cols="12">
            <AppTextField
              v-model="form.email"
              :label="t('auth.email')"
              type="email"
              autocomplete="email"
              placeholder="nama@email.com"
              :rules="[rules.required, rules.email]"
              :error-messages="fieldErrors.email"
            />
          </VCol>

          <VCol cols="12">
            <AppTextField
              v-model="form.password"
              :label="t('auth.password')"
              placeholder="············"
              :type="isPasswordVisible ? 'text' : 'password'"
              autocomplete="new-password"
              :hint="t('auth.register.password_hint')"
              persistent-hint
              :rules="[rules.required, rules.minLength(8), rules.password]"
              :error-messages="fieldErrors.password"
              :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
            />
          </VCol>

          <VCol cols="12">
            <AppTextField
              v-model="form.password_confirmation"
              :label="t('auth.confirm_password')"
              placeholder="············"
              :type="isConfirmVisible ? 'text' : 'password'"
              autocomplete="new-password"
              :rules="[rules.required, rules.confirmed(() => form.password)]"
              :append-inner-icon="isConfirmVisible ? 'tabler-eye-off' : 'tabler-eye'"
              @click:append-inner="isConfirmVisible = !isConfirmVisible"
            />
          </VCol>

          <!-- Honeypot: invisible to people, tempting to bots -->
          <div
            class="d-none"
            aria-hidden="true"
          >
            <input
              v-model="form.website"
              type="text"
              name="website"
              tabindex="-1"
              autocomplete="off"
            >
          </div>

          <VCol cols="12">
            <TurnstileWidget
              ref="turnstileRef"
              @verified="token => (form.cf_turnstile_response = token)"
              @expired="form.cf_turnstile_response = ''"
            />

            <VBtn
              block
              type="submit"
              :loading="isSubmitting"
              :disabled="isSubmitting"
            >
              {{ t('auth.register.submit') }}
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
            cols="12"
            class="text-body-1 text-center"
          >
            <span class="d-inline-block">
              {{ t('auth.register.have_account') }}
            </span>
            <RouterLink
              class="text-primary ms-1 d-inline-block text-body-1"
              :to="{ path: '/login', query: route.query.redirect ? { redirect: route.query.redirect } : {} }"
            >
              {{ t('auth.register.login_instead') }}
            </RouterLink>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </AuthLayout>
</template>
