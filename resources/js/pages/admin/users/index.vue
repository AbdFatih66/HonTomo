<script setup>
import { $api } from '@/utils/api'

definePage({
  meta: {
    requiresAdmin: true,
  },
})

const { t } = useI18n()

const headers = computed(() => [
  { title: t('admin.users.headers.name'), key: 'name' },
  { title: t('admin.users.headers.email'), key: 'email' },
  { title: t('admin.users.headers.role'), key: 'role' },
  { title: t('admin.users.headers.actions'), key: 'actions', sortable: false, align: 'end' },
])

const roleOptions = computed(() => [
  { title: t('admin.users.roles.user'), value: 'user' },
  { title: t('admin.users.roles.admin'), value: 'admin' },
])

const users = ref([])
const totalUsers = ref(0)
const isLoading = ref(false)
const search = ref('')
const page = ref(1)
const itemsPerPage = ref(15)

async function fetchUsers() {
  isLoading.value = true
  try {
    const res = await $api('/users', {
      query: {
        search: search.value,
        page: page.value,
        per_page: itemsPerPage.value,
      },
    })

    users.value = res.data
    totalUsers.value = res.total
  }
  finally {
    isLoading.value = false
  }
}

watch([page, itemsPerPage], fetchUsers)
watchDebounced(search, () => {
  page.value = 1
  fetchUsers()
}, { debounce: 400 })

onMounted(fetchUsers)

// --- Create / edit dialog ---
const isDialogOpen = ref(false)
const isSaving = ref(false)
const isEditing = ref(false)
const formErrors = ref({})
const form = ref({ id: null, name: '', email: '', password: '', role: 'user' })

function openCreateDialog() {
  isEditing.value = false
  formErrors.value = {}
  form.value = { id: null, name: '', email: '', password: '', role: 'user' }
  isDialogOpen.value = true
}

function openEditDialog(user) {
  isEditing.value = true
  formErrors.value = {}
  form.value = { id: user.id, name: user.name, email: user.email, password: '', role: user.role }
  isDialogOpen.value = true
}

async function saveUser() {
  isSaving.value = true
  formErrors.value = {}
  try {
    if (isEditing.value) {
      await $api(`/users/${form.value.id}`, {
        method: 'PUT',
        body: form.value,
      })
    }
    else {
      await $api('/users', {
        method: 'POST',
        body: form.value,
      })
    }
    isDialogOpen.value = false
    fetchUsers()
  }
  catch (err) {
    formErrors.value = err?.data?.errors || {}
  }
  finally {
    isSaving.value = false
  }
}

// --- Delete confirm ---
const isDeleteDialogOpen = ref(false)
const userToDelete = ref(null)
const isDeleting = ref(false)

function confirmDelete(user) {
  userToDelete.value = user
  isDeleteDialogOpen.value = true
}

async function deleteUser() {
  isDeleting.value = true
  try {
    await $api(`/users/${userToDelete.value.id}`, { method: 'DELETE' })
    isDeleteDialogOpen.value = false
    fetchUsers()
  }
  finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <VCard :title="t('admin.users.title')">
    <VCardText>
      <VRow>
        <VCol
          cols="12"
          md="4"
        >
          <AppTextField
            v-model="search"
            :placeholder="t('admin.users.search_placeholder')"
            prepend-inner-icon="tabler-search"
          />
        </VCol>
        <VCol
          cols="12"
          md="8"
          class="d-flex justify-end"
        >
          <VBtn
            prepend-icon="tabler-plus"
            @click="openCreateDialog"
          >
            {{ t('admin.users.add_user') }}
          </VBtn>
        </VCol>
      </VRow>
    </VCardText>

    <VDataTableServer
      v-model:page="page"
      v-model:items-per-page="itemsPerPage"
      :headers="headers"
      :items="users"
      :items-length="totalUsers"
      :loading="isLoading"
      class="text-no-wrap"
    >
      <template #item.role="{ item }">
        <VChip
          size="small"
          :color="item.role === 'admin' ? 'primary' : 'default'"
          class="text-capitalize"
        >
          {{ item.role }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <VBtn
          icon
          size="small"
          variant="text"
          @click="openEditDialog(item)"
        >
          <VIcon icon="tabler-edit" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          @click="confirmDelete(item)"
        >
          <VIcon icon="tabler-trash" />
        </VBtn>
      </template>
    </VDataTableServer>
  </VCard>

  <!-- Create / Edit dialog -->
  <VDialog
    v-model="isDialogOpen"
    max-width="500"
  >
    <VCard :title="isEditing ? t('admin.users.dialog.edit_title') : t('admin.users.dialog.create_title')">
      <VCardText>
        <VForm @submit.prevent="saveUser">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="form.name"
                :label="t('admin.users.dialog.name')"
                :error-messages="formErrors.name"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="form.email"
                :label="t('admin.users.dialog.email')"
                type="email"
                :error-messages="formErrors.email"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="form.password"
                :label="t('admin.users.dialog.password')"
                type="password"
                :placeholder="isEditing ? t('admin.users.dialog.password_placeholder_edit') : ''"
                :error-messages="formErrors.password"
              />
            </VCol>
            <VCol cols="12">
              <AppSelect
                v-model="form.role"
                :label="t('admin.users.dialog.role')"
                :items="roleOptions"
                :error-messages="formErrors.role"
              />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
      <VCardText class="d-flex justify-end gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          @click="isDialogOpen = false"
        >
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn
          :loading="isSaving"
          @click="saveUser"
        >
          {{ t('common.save') }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

  <!-- Delete confirm dialog -->
  <VDialog
    v-model="isDeleteDialogOpen"
    max-width="400"
  >
    <VCard :title="t('admin.users.delete_dialog.title')">
      <VCardText>
        {{ t('admin.users.delete_dialog.confirm', { name: userToDelete?.name }) }}
      </VCardText>
      <VCardText class="d-flex justify-end gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          @click="isDeleteDialogOpen = false"
        >
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn
          color="error"
          :loading="isDeleting"
          @click="deleteUser"
        >
          {{ t('common.delete') }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
