<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import verticalNavItems from '@/navigation/vertical'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

// 👉 Katalog halaman yang boleh dijadikan pintasan — persis halaman nyata di
// aplikasi (sidebar/menu horizontal), bukan daftar demo. `key` di sini sama
// dengan nama route, dipakai juga sebagai `page_key` di backend.
const catalog = computed(() => verticalNavItems
  .filter(item => !item.adminOnly || authStore.isAdmin)
  .map(item => ({
    key: item.to.name,
    icon: item.icon?.icon ?? 'tabler-point',
    title: t(item.title),
    to: item.to,
  })))

const catalogByKey = computed(() => Object.fromEntries(catalog.value.map(item => [item.key, item])))

// 👉 Disimpan per akun lewat backend (tabel user_shortcuts), bukan lagi
// daftar statis. `order` = urutan tampil, diatur dengan menggeser (drag).
const order = ref([]) // array of page_key, in display order
const isLoading = ref(false)
const isPickerOpen = ref(false)
const draggedKey = ref(null)

const shortcuts = computed(() => order.value
  .map(key => catalogByKey.value[key])
  .filter(Boolean))

const availableToAdd = computed(() => catalog.value.filter(item => !order.value.includes(item.key)))

async function loadShortcuts() {
  isLoading.value = true
  try {
    const res = await $api('/shortcuts')

    order.value = (res.shortcuts ?? [])
      .slice()
      .sort((a, b) => a.position - b.position)
      .map(row => row.page_key)
  }
  catch {
    // Offline/gagal: biarkan grid kosong, ikon "+" tetap bisa dicoba lagi.
  }
  finally {
    isLoading.value = false
  }
}

loadShortcuts()

async function addShortcut(key) {
  order.value.push(key)
  try {
    await $api('/shortcuts', { method: 'POST', body: { page_key: key } })
  }
  catch {
    order.value = order.value.filter(k => k !== key)
  }
}

async function removeShortcut(key) {
  const prevOrder = order.value.slice()

  order.value = order.value.filter(k => k !== key)
  try {
    await $api(`/shortcuts/${encodeURIComponent(key)}`, { method: 'DELETE' })
  }
  catch {
    order.value = prevOrder
  }
}

function persistOrder() {
  $api('/shortcuts/reorder', { method: 'PUT', body: { page_keys: order.value } }).catch(() => {
    // Best-effort — kalau gagal terkirim, urutan lokal tetap seperti yang
    // baru saja digeser pengguna, akan tersinkron lagi di percobaan berikut.
  })
}

// 👉 Geser untuk mengatur urutan (drag & drop bawaan browser, tanpa
// dependency tambahan).
function onDragStart(key) {
  draggedKey.value = key
}

function onDrop(targetKey) {
  if (!draggedKey.value || draggedKey.value === targetKey)
    return

  const from = order.value.indexOf(draggedKey.value)
  const to = order.value.indexOf(targetKey)
  if (from === -1 || to === -1)
    return

  const next = order.value.slice()

  next.splice(from, 1)
  next.splice(to, 0, draggedKey.value)
  order.value = next
  draggedKey.value = null
  persistOrder()
}

function openShortcut(shortcut) {
  router.push(shortcut.to)
}
</script>

<template>
  <IconBtn>
    <VIcon icon="tabler-layout-grid-add" />

    <VMenu
      activator="parent"
      offset="12px"
      location="bottom end"
    >
      <VCard
        :width="$vuetify.display.smAndDown ? 330 : 380"
        max-height="560"
        class="d-flex flex-column"
      >
        <VCardItem class="py-3">
          <h6 class="text-base font-weight-medium">
            {{ isPickerOpen ? t('shortcuts.add_title') : t('shortcuts.title') }}
          </h6>

          <template #append>
            <IconBtn
              size="small"
              color="high-emphasis"
              @click="isPickerOpen = !isPickerOpen"
            >
              <VIcon
                size="20"
                :icon="isPickerOpen ? 'tabler-arrow-left' : 'tabler-plus'"
              />
            </IconBtn>
          </template>
        </VCardItem>

        <VDivider />

        <!-- 👉 Halaman yang belum ditambahkan sebagai pintasan -->
        <PerfectScrollbar
          v-if="isPickerOpen"
          :options="{ wheelPropagation: false }"
        >
          <VList v-if="availableToAdd.length">
            <VListItem
              v-for="item in availableToAdd"
              :key="item.key"
              @click="addShortcut(item.key)"
            >
              <template #prepend>
                <VIcon :icon="item.icon" />
              </template>
              <VListItemTitle>{{ item.title }}</VListItemTitle>
              <template #append>
                <VIcon
                  icon="tabler-plus"
                  size="18"
                  color="primary"
                />
              </template>
            </VListItem>
          </VList>
          <p
            v-else
            class="text-body-2 text-medium-emphasis text-center pa-6 mb-0"
          >
            {{ t('shortcuts.all_added') }}
          </p>
        </PerfectScrollbar>

        <!-- 👉 Pintasan yang sudah ditambahkan — bisa digeser urutannya -->
        <template v-else>
          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <p
              v-if="shortcuts.length"
              class="text-caption text-medium-emphasis px-4 pt-2 mb-0"
            >
              {{ t('shortcuts.drag_hint') }}
            </p>
            <p
              v-if="!isLoading && !shortcuts.length"
              class="text-body-2 text-medium-emphasis text-center pa-6 mb-0"
            >
              {{ t('shortcuts.empty') }}
            </p>
            <VRow class="ma-0 mt-n1">
              <VCol
                v-for="(shortcut, index) in shortcuts"
                :key="shortcut.key"
                cols="6"
                class="text-center border-t cursor-pointer pa-6 shortcut-icon position-relative"
                :class="[(index + 1) % 2 ? 'border-e' : '', { 'is-dragging': draggedKey === shortcut.key }]"
                draggable="true"
                @dragstart="onDragStart(shortcut.key)"
                @dragover.prevent
                @drop="onDrop(shortcut.key)"
                @click="openShortcut(shortcut)"
              >
                <IconBtn
                  size="x-small"
                  class="shortcut-icon__remove"
                  :title="t('shortcuts.remove')"
                  @click.stop="removeShortcut(shortcut.key)"
                >
                  <VIcon
                    icon="tabler-x"
                    size="14"
                  />
                </IconBtn>

                <VAvatar
                  variant="tonal"
                  size="50"
                >
                  <VIcon
                    size="26"
                    color="high-emphasis"
                    :icon="shortcut.icon"
                  />
                </VAvatar>

                <h6 class="text-base font-weight-medium mt-3 mb-0">
                  {{ shortcut.title }}
                </h6>
              </VCol>
            </VRow>
          </PerfectScrollbar>
        </template>
      </VCard>
    </VMenu>
  </IconBtn>
</template>

<style lang="scss">
.shortcut-icon:hover {
  background-color: rgba(var(--v-theme-on-surface), var(--v-hover-opacity));

  .shortcut-icon__remove {
    opacity: 1;
  }
}

.shortcut-icon.is-dragging {
  opacity: 0.5;
}

.shortcut-icon__remove {
  position: absolute;
  z-index: 1;
  opacity: 0;
  transition: opacity 0.15s ease;
  inset-block-start: 4px;
  inset-inline-end: 4px;
}
</style>
