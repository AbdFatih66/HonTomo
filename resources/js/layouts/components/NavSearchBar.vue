<script setup>
import Shepherd from 'shepherd.js'
import verticalNavItems from '@/navigation/vertical'
import { useAuthStore } from '@/stores/auth'
import { useConfigStore } from '@core/stores/config'

defineOptions({
  inheritAttrs: false,
})

const { t } = useI18n()
const configStore = useConfigStore()
const authStore = useAuthStore()
const router = useRouter()

const isAppSearchBarVisible = ref(false)
const searchQuery = ref('')

// 👉 Semua halaman nyata di aplikasi ini — sama persis dengan menu di sidebar
// (dan menu horizontal, yang sekarang menggunakan daftar yang sama), jadi
// pencarian selalu mengarah ke halaman yang benar-benar ada.
const pages = computed(() => verticalNavItems
  .filter(item => !item.adminOnly || authStore.isAdmin)
  .map(item => ({
    icon: item.icon?.icon ?? 'tabler-point',
    title: t(item.title),
    url: item.to,
  })))

// 👉 Suggestion ditampilkan sebelum mengetik apa pun.
const suggestionGroups = computed(() => [
  {
    title: t('search.pages'),
    content: pages.value,
  },
])

// 👉 Ditampilkan waktu tidak ada hasil pencarian sama sekali.
const noDataSuggestions = computed(() => pages.value.slice(0, 3))

// 👉 Pencarian di sisi klien: cocokkan judul halaman (sudah diterjemahkan)
// dengan kata kunci — tidak perlu memanggil API sama sekali.
const searchResult = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query)
    return []

  const matches = pages.value.filter(page => page.title.toLowerCase().includes(query))
  if (!matches.length)
    return []

  return [{ title: t('search.pages'), children: matches }]
})

const closeSearchBar = () => {
  isAppSearchBarVisible.value = false
  searchQuery.value = ''
}

const redirectToSuggestedPage = selected => {
  router.push(selected.url)
  closeSearchBar()
}

const LazyAppBarSearch = defineAsyncComponent(() => import('@core/components/AppBarSearch.vue'))
</script>

<template>
  <div
    class="d-flex align-center cursor-pointer"
    v-bind="$attrs"
    style="user-select: none;"
    @click="isAppSearchBarVisible = !isAppSearchBarVisible"
  >
    <!-- 👉 Search Trigger button -->
    <!-- close active tour while opening search bar using icon -->
    <IconBtn @click="Shepherd.activeTour?.cancel()">
      <VIcon icon="tabler-search" />
    </IconBtn>

    <span
      v-if="configStore.appContentLayoutNav === 'vertical'"
      class="d-none d-md-flex align-center text-disabled ms-2"
      @click="Shepherd.activeTour?.cancel()"
    >
      <span class="me-2">{{ t('search.trigger') }}</span>
      <span class="meta-key">&#8984;K</span>
    </span>
  </div>

  <!-- 👉 App Bar Search -->
  <LazyAppBarSearch
    v-model:is-dialog-visible="isAppSearchBarVisible"
    :search-results="searchResult"
    :is-loading="false"
    @search="searchQuery = $event"
  >
    <!-- suggestion: seluruh halaman aplikasi, sebelum mengetik apa pun -->
    <template #suggestions>
      <VCardText class="app-bar-search-suggestions pa-12">
        <VRow v-if="suggestionGroups">
          <VCol
            v-for="suggestion in suggestionGroups"
            :key="suggestion.title"
            cols="12"
          >
            <p
              class="custom-letter-spacing text-disabled text-uppercase py-2 px-4 mb-0"
              style="font-size: 0.75rem; line-height: 0.875rem;"
            >
              {{ suggestion.title }}
            </p>
            <VList class="card-list">
              <VListItem
                v-for="item in suggestion.content"
                :key="item.title"
                class="app-bar-search-suggestion mx-4 mt-2"
                @click="redirectToSuggestedPage(item)"
              >
                <VListItemTitle>{{ item.title }}</VListItemTitle>
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="20"
                    class="me-n1"
                  />
                </template>
              </VListItem>
            </VList>
          </VCol>
        </VRow>
      </VCardText>
    </template>

    <!-- no data suggestion -->
    <template #noDataSuggestion>
      <div class="mt-9">
        <span class="d-flex justify-center text-disabled mb-2">{{ t('search.try_searching') }}</span>
        <h6
          v-for="suggestion in noDataSuggestions"
          :key="suggestion.title"
          class="app-bar-search-suggestion text-h6 font-weight-regular cursor-pointer py-2 px-4"
          @click="redirectToSuggestedPage(suggestion)"
        >
          <VIcon
            size="20"
            :icon="suggestion.icon"
            class="me-2"
          />
          <span>{{ suggestion.title }}</span>
        </h6>
      </div>
    </template>

    <!-- search result -->
    <template #searchResult="{ item }">
      <VListSubheader class="text-disabled custom-letter-spacing font-weight-regular ps-4">
        {{ item.title }}
      </VListSubheader>
      <VListItem
        v-for="list in item.children"
        :key="list.title"
        :to="list.url"
        @click="closeSearchBar"
      >
        <template #prepend>
          <VIcon
            size="20"
            :icon="list.icon"
            class="me-n1"
          />
        </template>
        <template #append>
          <VIcon
            size="20"
            icon="tabler-corner-down-left"
            class="enter-icon flip-in-rtl"
          />
        </template>
        <VListItemTitle>
          {{ list.title }}
        </VListItemTitle>
      </VListItem>
    </template>
  </LazyAppBarSearch>
</template>

<style lang="scss">
@use "@styles/variables/vuetify.scss";

.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  font-size: 0.8125rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.app-bar-search-dialog {
  .custom-letter-spacing {
    letter-spacing: 0.8px;
  }

  .card-list {
    --v-card-list-gap: 8px;
  }
}
</style>
