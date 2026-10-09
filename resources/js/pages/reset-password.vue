<script setup>
import AuthLayout from '@/views/pages/authentication/AuthLayout.vue'
import { $api } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'
import { resolveAuthError, rules } from '@/utils/auth'

// Not guestOnly on purpose: someone who is still signed in on this browser
// must be able to open the link from their email.
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

const token = computed(() => (typeof route.query.token === 'string' ? route.query.token : ''))
const email = computed(() => (typeof route.query.email === 'string' ? route.query.email : ''))
const hasValidParams = computed(() => !!token.value && !!email.value)

const formRef = ref()

const form = ref({
  password: '',
  password_confirmation: '',
})

const isSubmitting = ref(false)
const isPasswordVisible = ref(false)
const isConfirmVisible = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})
const isDone = ref(false)
const successMessage = ref('')
const linkRejected = ref(false)

async function onSubmit() {
  if (isSubmitting.value)
    return

  errorMessage.value = ''
  fieldErrors.value = {}

  const { valid } = await formRef.value.validate()
  if (!valid)
    return

  isSubmitting.value = true
  try {
    const res = await $api('/reset-password', {
      method: 'POST',
      body: {
        token: token.value,
        email: email.value,
        ...form.value,
      },
    })

    successMessage.value = res.message
    isDone.value = true

    // The server revoked every session; drop the local one too if this
    // browser was still signed in.
    if (authStore.isAuthenticated)
      await authStore.logout()

    // the token in the URL is spent — remove it from history
    router.replace({ name: 'reset-password' })
  }
  catch (err) {
    const { message, fieldErrors: errors } = resolveAuthError(err)

    if (errors.email?.length && !errors.password?.length && !errors.token?.length) {
      // server says the link itself is invalid / expired / already used
      linkRejected.value = true

      return
    }

    errorMessage.value = message
    fieldErrors.value = errors
  }
  finally {
    isSubmitting.value = false
  }
}

const showInvalid = computed(() => !isDone.value && (!hasValidParams.value || linkRejected.value))
</script>

<template>
  <AuthLayout>
    <!-- Success -->
    <template v-if="isDone">
      <VCardText class="text-center">
        <VAvatar
          color="success"
          variant="tonal"
          size="64"
          class="mb-4"
        >
          <VIcon
            icon="tabler-check"
            size="32"
          />
        </VAvatar>
        <h4 class="text-h4 mb-2">
          {{ t('auth.reset_password.success_title') }}
        </h4>
        <p class="mb-6">
          {{ successMessage }}
        </p>
        <VBtn
          block
          :to="{ name: 'login' }"
        >
          {{ t('auth.reset_password.go_login') }}
        </VBtn>
      </VCardText>
    </template>

    <!-- Missing / invalid / expired / used link -->
    <template v-else-if="showInvalid">
      <VCardText class="text-center">
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
          {{ t('auth.reset_password.invalid_title') }}
        </h4>
        <p class="mb-6">
          {{ t('auth.reset_password.invalid_text') }}
        </p>
        <VBtn
          block
          :to="{ name: 'forgot-password' }"
        >
          {{ t('auth.reset_password.request_new') }}
        </VBtn>
      </VCardText>
    </template>

    <!-- Form -->
    <template v-else>
      <VCardText>
        <h4 class="text-h4 mb-1">
          {{ t('auth.reset_password.title') }}
        </h4>
        <p class="mb-0">
          {{ t('auth.reset_password.subtitle') }}
        </p>
      </VCardText>

      <VCardText>
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
                :model-value="email"
                :label="t('auth.email')"
                type="email"
                autocomplete="username"
                readonly
                disabled
              />
            </VCol>

            <VCol cols="12">
              <AppTextField
                v-model="form.password"
                autofocus
                :label="t('auth.reset_password.new_password')"
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

            <VCol cols="12">
              <VBtn
                block
                type="submit"
                :loading="isSubmitting"
                :disabled="isSubmitting"
              >
                {{ t('auth.reset_password.submit') }}
              </VBtn>
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
    </template>
  </AuthLayout>
</template>
