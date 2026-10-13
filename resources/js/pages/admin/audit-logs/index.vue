<script setup>
import { $api } from '@/utils/api'

definePage({
  meta: {
    requiresAdmin: true,
  },
})

const { t, locale } = useI18n()

const headers = computed(() => [
  { title: t('admin.audit_logs.headers.created_at'), key: 'created_at' },
  { title: t('admin.audit_logs.headers.action'), key: 'action' },
  { title: t('admin.audit_logs.headers.user'), key: 'user' },
  { title: t('admin.audit_logs.headers.ip'), key: 'ip_address' },
  { title: t('admin.audit_logs.headers.meta'), key: 'meta', sortable: false },
])

const logs = ref([])
const totalLogs = ref(0)
const isLoading = ref(false)
const page = ref(1)
const itemsPerPage = ref(25)

const actionFilter = ref(null)
const emailFilter = ref('')
const fromFilter = ref('')
const toFilter = ref('')
const availableActions = ref([])

// Human-readable labels for each raw action key, in the active language.
const actionLabels = {
  account_registered: { id: 'Registrasi akun', en: 'Account registered' },
  login_succeeded: { id: 'Login berhasil', en: 'Login succeeded' },
  login_failed: { id: 'Login gagal', en: 'Login failed' },
  password_reset: { id: 'Reset kata sandi', en: 'Password reset' },
  email_verified: { id: 'Email diverifikasi', en: 'Email verified' },
  google_linked: { id: 'Google dihubungkan', en: 'Google linked' },
  google_unlinked: { id: 'Google diputuskan', en: 'Google unlinked' },
  google_login: { id: 'Login via Google', en: 'Google login' },
  admin_user_created: { id: 'Admin: user dibuat', en: 'Admin: user created' },
  admin_password_changed: { id: 'Admin: kata sandi diubah', en: 'Admin: password changed' },
  admin_role_changed: { id: 'Admin: role diubah', en: 'Admin: role changed' },
  admin_user_deleted: { id: 'Admin: user dihapus', en: 'Admin: user deleted' },
}

function actionLabel(action) {
  const entry = actionLabels[action]

  if (!entry)
    return action

  return locale.value === 'en' ? entry.en : entry.id
}

function actionColor(action) {
  if (action.startsWith('admin_'))
    return 'warning'
  if (action === 'login_failed')
    return 'error'
  if (action.includes('unlinked') || action === 'admin_user_deleted')
    return 'error'

  return 'success'
}

function formatDate(iso) {
  if (!iso)
    return '-'

  return new Intl.DateTimeFormat(locale.value, { dateStyle: 'medium', timeStyle: 'medium' }).format(new Date(iso))
}

async function fetchLogs() {
  isLoading.value = true
  try {
    const res = await $api('/admin/audit-logs', {
      query: {
        page: page.value,
        per_page: itemsPerPage.value,
        action: actionFilter.value,
        email: emailFilter.value || undefined,
        from: fromFilter.value || undefined,
        to: toFilter.value || undefined,
      },
    })

    logs.value = res.data
    totalLogs.value = res.total
  }
  finally {
    isLoading.value = false
  }
}

async function fetchActions() {
  const res = await $api('/admin/audit-logs/actions')

  availableActions.value = res.actions.map(a => ({ title: actionLabel(a), value: a }))
}

watch([page, itemsPerPage, actionFilter, fromFilter, toFilter], fetchLogs)
watchDebounced(emailFilter, () => {
  page.value = 1
  fetchLogs()
}, { debounce: 400 })

onMounted(() => {
  fetchActions()
  fetchLogs()
})
</script>

<template>
  <VCard :title="t('admin.audit_logs.title')">
    <VCardText>
      <p class="text-body-2 text-medium-emphasis mb-4">
        {{ t('admin.audit_logs.description') }}
      </p>

      <VRow>
        <VCol
          cols="12"
          md="3"
        >
          <AppSelect
            v-model="actionFilter"
            :items="availableActions"
            :label="t('admin.audit_logs.filter_action')"
            clearable
          />
        </VCol>
        <VCol
          cols="12"
          md="3"
        >
          <AppTextField
            v-model="emailFilter"
            :label="t('admin.audit_logs.search_email')"
            prepend-inner-icon="tabler-search"
          />
        </VCol>
        <VCol
          cols="12"
          md="3"
        >
          <AppDateTimePicker
            v-model="fromFilter"
            :label="t('admin.audit_logs.from_date')"
            :config="{ dateFormat: 'Y-m-d' }"
          />
        </VCol>
        <VCol
          cols="12"
          md="3"
        >
          <AppDateTimePicker
            v-model="toFilter"
            :label="t('admin.audit_logs.to_date')"
            :config="{ dateFormat: 'Y-m-d' }"
          />
        </VCol>
      </VRow>
    </VCardText>

    <VDataTableServer
      v-model:page="page"
      v-model:items-per-page="itemsPerPage"
      :headers="headers"
      :items="logs"
      :items-length="totalLogs"
      :loading="isLoading"
      class="text-no-wrap"
    >
      <template #item.created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>

      <template #item.action="{ item }">
        <VChip
          size="small"
          :color="actionColor(item.action)"
          variant="tonal"
        >
          {{ actionLabel(item.action) }}
        </VChip>
      </template>

      <template #item.user="{ item }">
        <span v-if="item.user">{{ item.user.name }} ({{ item.user.email }})</span>
        <span
          v-else
          class="text-medium-emphasis"
        >—</span>
      </template>

      <template #item.ip_address="{ item }">
        {{ item.ip_address || '—' }}
      </template>

      <template #item.meta="{ item }">
        <code
          v-if="item.meta"
          class="text-caption"
        >{{ JSON.stringify(item.meta) }}</code>
        <span
          v-else
          class="text-medium-emphasis"
        >—</span>
      </template>
    </VDataTableServer>
  </VCard>
</template>
