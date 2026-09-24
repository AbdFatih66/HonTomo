<script setup>
import { useResendVerification } from '@/composables/useResendVerification'
import { useAuthStore } from '@/stores/auth'
import { $api } from '@/utils/api'
import { resolveAuthError, rules } from '@/utils/auth'
import { isWebAuthnSupported, listPasskeys, registerPasskey, removePasskey } from '@/utils/webauthn'
import OAuthErrorAlert from '@/views/pages/authentication/OAuthErrorAlert.vue'

const { t, locale } = useI18n()
const route = useRoute()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const { resend, isSending: isResending, cooldown, message: resendMessage, errorMessage: resendError } = useResendVerification()
const hasGoogle = computed(() => user.value?.providers?.includes('google'))

const isLinking = ref(false)
const isUnlinking = ref(false)
const isSendingLink = ref(false)
const isUnlinkDialogOpen = ref(false)

const errorMessage = ref('')
const successMessage = ref(route.query.linked === 'google' ? t('profile.google_linked') : '')

const initials = computed(() => (user.value?.name || '?').trim().charAt(0).toUpperCase())

// Providers / verification may have changed (e.g. returning from Google).
onMounted(() => {
  authStore.fetchUser()
  loadPasskeys()
})

// ---- Passkeys (WebAuthn) ---------------------------------------------------
const supportsBiometric = isWebAuthnSupported()
const passkeys = ref([])
const isLoadingPasskeys = ref(false)
const isAddingPasskey = ref(false)
const busyPasskeyId = ref(null)
const isAddPasskeyDialogOpen = ref(false)
const newPasskeyAlias = ref('')

function formatDate(iso) {
  if (!iso)
    return '-'

  return new Intl.DateTimeFormat(locale.value, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(iso))
}

async function loadPasskeys() {
  if (!supportsBiometric)
    return

  isLoadingPasskeys.value = true
  try {
    passkeys.value = await listPasskeys()
  }
  catch {
    // the list is informational; the rest of the page still works
  }
  finally {
    isLoadingPasskeys.value = false
  }
}

function openAddPasskeyDialog() {
  newPasskeyAlias.value = ''
  isAddPasskeyDialogOpen.value = true
}

async function confirmAddPasskey() {
  if (isAddingPasskey.value)
    return

  errorMessage.value = ''
  isAddingPasskey.value = true
  try {
    const res = await registerPasskey(newPasskeyAlias.value.trim() || 'Passkey')

    successMessage.value = res.message
    isAddPasskeyDialogOpen.value = false
    await loadPasskeys()
  }
  catch {
    // Most failures here are the person cancelling the browser's own
    // fingerprint/Face ID prompt — no need to alarm them with a red alert.
    errorMessage.value = t('auth.webauthn.failed')
  }
  finally {
    isAddingPasskey.value = false
  }
}

async function deletePasskey(passkey) {
  if (busyPasskeyId.value)
    return

  busyPasskeyId.value = passkey.id
  errorMessage.value = ''
  try {
    const res = await removePasskey(passkey.id)

    successMessage.value = res.message
    await loadPasskeys()
  }
  catch {
    errorMessage.value = t('auth.errors.generic')
  }
  finally {
    busyPasskeyId.value = null
  }
}

async function linkGoogle() {
  if (isLinking.value)
    return

  errorMessage.value = ''
  isLinking.value = true
  try {
    // A signed-in request proves who is linking; the browser then leaves for Google.
    const { url } = await $api('/auth/google/link-intent', { method: 'POST' })

    window.location.href = url
  }
  catch {
    errorMessage.value = t('auth.errors.generic')
    isLinking.value = false
  }
}

async function unlinkGoogle() {
  if (isUnlinking.value)
    return

  errorMessage.value = ''
  isUnlinking.value = true
  try {
    const res = await $api('/auth/google', { method: 'DELETE' })

    authStore.user = res.user
    successMessage.value = res.message
    isUnlinkDialogOpen.value = false
  }
  catch (err) {
    errorMessage.value = err?.data?.errors?.provider?.[0] || t('auth.errors.generic')
    isUnlinkDialogOpen.value = false
  }
  finally {
    isUnlinking.value = false
  }
}

// Accounts created with Google have no password. Reuse the reset flow so
// they can set one (this also makes disconnecting Google possible).
async function sendSetPasswordLink() {
  if (isSendingLink.value)
    return

  errorMessage.value = ''
  isSendingLink.value = true
  try {
    const res = await $api('/forgot-password', {
      method: 'POST',
      body: { email: user.value.email },
    })

    successMessage.value = res.message
  }
  catch {
    errorMessage.value = t('auth.errors.generic')
  }
  finally {
    isSendingLink.value = false
  }
}

// ---- Change password -------------------------------------------------------
const passwordFormRef = ref()
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const isCurrentPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const isChangingPassword = ref(false)
const passwordErrorMessage = ref('')
const passwordFieldErrors = ref({})

async function changePassword() {
  if (isChangingPassword.value)
    return

  passwordErrorMessage.value = ''
  passwordFieldErrors.value = {}

  const { valid } = await passwordFormRef.value.validate()
  if (!valid)
    return

  isChangingPassword.value = true
  try {
    const res = await $api('/change-password', {
      method: 'POST',
      body: passwordForm.value,
    })

    successMessage.value = res.revoked
      ? t('profile.password_changed_sessions', { n: res.revoked })
      : t('profile.password_changed')

    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    passwordFormRef.value.reset()
  }
  catch (err) {
    const { message, fieldErrors: errors } = resolveAuthError(err)

    passwordErrorMessage.value = message
    passwordFieldErrors.value = errors
  }
  finally {
    isChangingPassword.value = false
  }
}
</script>

<template>
  <div>
    <h4 class="text-h4 mb-6">
      {{ t('profile.title') }}
    </h4>

    <OAuthErrorAlert />

    <VAlert
      v-if="successMessage"
      type="success"
      variant="tonal"
      class="mb-6"
      closable
      @click:close="successMessage = ''"
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

    <VRow v-if="user">
      <!-- Account -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard :title="t('profile.account')">
          <VCardText class="d-flex align-center gap-x-4">
            <VAvatar
              size="72"
              color="primary"
              variant="tonal"
            >
              <VImg
                v-if="user.avatar"
                :src="user.avatar"
                referrerpolicy="no-referrer"
              />
              <span
                v-else
                class="text-h4"
              >{{ initials }}</span>
            </VAvatar>

            <div class="text-wrap">
              <h5 class="text-h5">
                {{ user.name }}
              </h5>
              <p class="mb-2 text-medium-emphasis">
                {{ user.email }}
              </p>
              <VChip
                size="small"
                :color="user.email_verified ? 'success' : 'warning'"
                variant="tonal"
              >
                {{ user.email_verified ? t('profile.email_verified') : t('profile.email_unverified') }}
              </VChip>

              <div
                v-if="!user.email_verified"
                class="mt-3"
              >
                <VBtn
                  size="small"
                  variant="outlined"
                  :loading="isResending"
                  :disabled="isResending || cooldown > 0"
                  @click="resend"
                >
                  {{ cooldown > 0 ? t('verification.resend_in', { s: cooldown }) : t('verification.resend') }}
                </VBtn>
                <p
                  v-if="resendMessage || resendError"
                  class="text-caption mt-2 mb-0"
                  :class="resendError ? 'text-error' : 'text-success'"
                >
                  {{ resendMessage || resendError }}
                </p>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Connected accounts -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard :title="t('profile.connected_accounts')">
          <VCardText>
            <div class="d-flex align-center justify-space-between gap-x-4">
              <div class="d-flex align-center gap-x-3">
                <VAvatar
                  variant="tonal"
                  size="40"
                >
                  <VIcon icon="tabler-brand-google" />
                </VAvatar>
                <div>
                  <div class="font-weight-medium">
                    Google
                  </div>
                  <VChip
                    size="x-small"
                    :color="hasGoogle ? 'success' : 'default'"
                    variant="tonal"
                  >
                    {{ hasGoogle ? t('profile.connected') : t('profile.not_connected') }}
                  </VChip>
                </div>
              </div>

              <VBtn
                v-if="!hasGoogle"
                variant="outlined"
                :loading="isLinking"
                :disabled="isLinking"
                @click="linkGoogle"
              >
                {{ t('profile.connect') }}
              </VBtn>

              <VBtn
                v-else
                variant="outlined"
                color="error"
                :disabled="!user.has_password"
                @click="isUnlinkDialogOpen = true"
              >
                {{ t('profile.disconnect') }}
              </VBtn>
            </div>

            <VAlert
              v-if="hasGoogle && !user.has_password"
              type="info"
              variant="tonal"
              class="mt-4"
            >
              <p class="mb-2">
                {{ t('profile.set_password_hint') }}
              </p>
              <VBtn
                size="small"
                variant="text"
                :loading="isSendingLink"
                :disabled="isSendingLink"
                @click="sendSetPasswordLink"
              >
                {{ t('profile.send_password_link') }}
              </VBtn>
            </VAlert>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Change password -->
      <VCol
        v-if="user.has_password"
        cols="12"
        md="6"
      >
        <VCard :title="t('profile.change_password')">
          <VCardText>
            <VAlert
              v-if="passwordErrorMessage"
              type="error"
              variant="tonal"
              class="mb-6"
              closable
              @click:close="passwordErrorMessage = ''"
            >
              {{ passwordErrorMessage }}
            </VAlert>

            <VForm
              ref="passwordFormRef"
              validate-on="submit"
              @submit.prevent="changePassword"
            >
              <VRow>
                <VCol cols="12">
                  <AppTextField
                    v-model="passwordForm.current_password"
                    :label="t('profile.current_password')"
                    placeholder="············"
                    :type="isCurrentPasswordVisible ? 'text' : 'password'"
                    autocomplete="current-password"
                    :rules="[rules.required]"
                    :error-messages="passwordFieldErrors.current_password"
                    :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible"
                  />
                </VCol>

                <VCol cols="12">
                  <AppTextField
                    v-model="passwordForm.password"
                    :label="t('profile.new_password')"
                    placeholder="············"
                    :type="isNewPasswordVisible ? 'text' : 'password'"
                    autocomplete="new-password"
                    :hint="t('auth.register.password_hint')"
                    persistent-hint
                    :rules="[rules.required, rules.minLength(8), rules.password]"
                    :error-messages="passwordFieldErrors.password"
                    :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                  />
                </VCol>

                <VCol cols="12">
                  <AppTextField
                    v-model="passwordForm.password_confirmation"
                    :label="t('auth.confirm_password')"
                    placeholder="············"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    autocomplete="new-password"
                    :rules="[rules.required, rules.confirmed(() => passwordForm.password)]"
                    :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  />
                </VCol>

                <VCol cols="12">
                  <VBtn
                    type="submit"
                    :loading="isChangingPassword"
                    :disabled="isChangingPassword"
                  >
                    {{ t('profile.save_password') }}
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Passkeys / biometric sign-in -->
      <VCol
        v-if="supportsBiometric"
        cols="12"
        md="6"
      >
        <VCard>
          <VCardItem :title="t('profile.passkeys')">
            <template #append>
              <VBtn
                size="small"
                variant="outlined"
                @click="openAddPasskeyDialog"
              >
                {{ t('profile.add_passkey') }}
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <p class="text-body-2 text-medium-emphasis mb-4">
              {{ t('profile.passkeys_hint') }}
            </p>

            <div
              v-if="isLoadingPasskeys && !passkeys.length"
              class="text-center py-4"
            >
              <VProgressCircular
                indeterminate
                size="24"
              />
            </div>

            <p
              v-else-if="!passkeys.length"
              class="text-medium-emphasis text-body-2 mb-0"
            >
              {{ t('profile.no_passkeys') }}
            </p>

            <VList
              v-else
              lines="two"
              class="pa-0"
            >
              <VListItem
                v-for="passkey in passkeys"
                :key="passkey.id"
              >
                <template #prepend>
                  <VAvatar variant="tonal">
                    <VIcon icon="tabler-fingerprint" />
                  </VAvatar>
                </template>

                <VListItemTitle>{{ passkey.alias || t('profile.passkey_default_name') }}</VListItemTitle>
                <VListItemSubtitle>
                  {{ t('profile.added_on') }}: {{ formatDate(passkey.created_at) }}
                </VListItemSubtitle>

                <template #append>
                  <VBtn
                    size="small"
                    variant="text"
                    color="error"
                    :loading="busyPasskeyId === passkey.id"
                    :disabled="!!busyPasskeyId"
                    @click="deletePasskey(passkey)"
                  >
                    {{ t('profile.remove') }}
                  </VBtn>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VDialog
      v-model="isAddPasskeyDialogOpen"
      max-width="440"
    >
      <VCard :title="t('profile.add_passkey')">
        <VCardText>
          <p class="mb-4">
            {{ t('profile.add_passkey_hint') }}
          </p>
          <AppTextField
            v-model="newPasskeyAlias"
            :label="t('profile.passkey_name')"
            :placeholder="t('profile.passkey_default_name')"
          />
        </VCardText>
        <VCardActions class="justify-end">
          <VBtn
            variant="text"
            @click="isAddPasskeyDialogOpen = false"
          >
            {{ t('profile.cancel') }}
          </VBtn>
          <VBtn
            :loading="isAddingPasskey"
            :disabled="isAddingPasskey"
            @click="confirmAddPasskey"
          >
            {{ t('profile.continue') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="isUnlinkDialogOpen"
      max-width="440"
    >
      <VCard :title="t('profile.disconnect_title')">
        <VCardText>{{ t('profile.disconnect_text') }}</VCardText>
        <VCardActions class="justify-end">
          <VBtn
            variant="text"
            @click="isUnlinkDialogOpen = false"
          >
            {{ t('profile.cancel') }}
          </VBtn>
          <VBtn
            color="error"
            :loading="isUnlinking"
            :disabled="isUnlinking"
            @click="unlinkGoogle"
          >
            {{ t('profile.disconnect') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
