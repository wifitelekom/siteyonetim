<script setup lang="ts">
import Shepherd from 'shepherd.js'
import type { RouteLocationRaw } from 'vue-router'
import { useAuthSession } from '@/composables/useAuthSession'
import { canAccessPath } from '@/utils/access'
import { useConfigStore } from '@core/stores/config'

interface Suggestion {
  icon: string
  title: string
  url: RouteLocationRaw
}

interface SuggestionGroup {
  title: string
  content: Suggestion[]
}

interface SearchResults {
  title: string
  children: Suggestion[]
}

defineOptions({
  inheritAttrs: false,
})

const configStore = useConfigStore()
const authSession = useAuthSession()

const isAppSearchBarVisible = ref(false)

const baseSuggestionGroups: SuggestionGroup[] = [
  {
    title: 'Hizli Erisim',
    content: [
      { icon: 'ri-home-4-line', title: 'Panel', url: { path: '/' } },
      { icon: 'ri-file-list-3-line', title: 'Tahakkuklar', url: { path: '/charges' } },
      { icon: 'ri-receipt-line', title: 'Tahsilatlar', url: { path: '/receipts' } },
      { icon: 'ri-bar-chart-grouped-line', title: 'Raporlar', url: { path: '/reports' } },
      { icon: 'ri-wallet-line', title: 'Kasa/Banka', url: { path: '/cash-accounts' } },
      { icon: 'ri-lock-password-line', title: 'Sifre Degistir', url: { path: '/profile/password' } },
    ],
  },
  {
    title: 'Yonetim',
    content: [
      { icon: 'ri-wallet-3-line', title: 'Giderler', url: { path: '/expenses' } },
      { icon: 'ri-secure-payment-line', title: 'Odemeler', url: { path: '/payments' } },
      { icon: 'ri-bank-card-line', title: 'Hesaplar', url: { path: '/accounts' } },
      { icon: 'ri-building-line', title: 'Daireler', url: { path: '/management/apartments' } },
      { icon: 'ri-group-line', title: 'Kullanicilar', url: { path: '/management/users' } },
      { icon: 'ri-store-2-line', title: 'Tedarikciler', url: { path: '/management/vendors' } },
      { icon: 'ri-settings-3-line', title: 'Site Ayarlari', url: { path: '/management/site-settings' } },
      { icon: 'ri-file-copy-2-line', title: 'Aidat Sablonlari', url: { path: '/templates/aidat' } },
      { icon: 'ri-file-list-2-line', title: 'Gider Sablonlari', url: { path: '/templates/expense' } },
      { icon: 'ri-add-circle-line', title: 'Yeni Tahakkuk', url: { path: '/charges/create' } },
    ],
  },
]

const baseNoDataSuggestions: Suggestion[] = [
  { icon: 'ri-home-4-line', title: 'Panel', url: { path: '/' } },
  { icon: 'ri-file-list-3-line', title: 'Tahakkuklar', url: { path: '/charges' } },
  { icon: 'ri-wallet-3-line', title: 'Giderler', url: { path: '/expenses' } },
  { icon: 'ri-receipt-line', title: 'Tahsilatlar', url: { path: '/receipts' } },
  { icon: 'ri-secure-payment-line', title: 'Odemeler', url: { path: '/payments' } },
  { icon: 'ri-bar-chart-grouped-line', title: 'Raporlar', url: { path: '/reports' } },
  { icon: 'ri-bank-card-line', title: 'Hesaplar', url: { path: '/accounts' } },
  { icon: 'ri-wallet-line', title: 'Kasa/Banka', url: { path: '/cash-accounts' } },
  { icon: 'ri-building-line', title: 'Daireler', url: { path: '/management/apartments' } },
  { icon: 'ri-group-line', title: 'Kullanicilar', url: { path: '/management/users' } },
  { icon: 'ri-store-2-line', title: 'Tedarikciler', url: { path: '/management/vendors' } },
  { icon: 'ri-settings-3-line', title: 'Site Ayarlari', url: { path: '/management/site-settings' } },
  { icon: 'ri-file-copy-2-line', title: 'Aidat Sablonlari', url: { path: '/templates/aidat' } },
  { icon: 'ri-file-list-2-line', title: 'Gider Sablonlari', url: { path: '/templates/expense' } },
  { icon: 'ri-lock-password-line', title: 'Sifre Degistir', url: { path: '/profile/password' } },
]

const router = useRouter()
const searchQuery = ref('')
const searchResult = ref<SearchResults[]>([])
const isLoading = ref(false)

const canAccessSuggestion = (suggestion: Suggestion) => {
  const path = typeof suggestion.url === 'string'
    ? suggestion.url
    : (suggestion.url as any)?.path

  if (!path)
    return true

  return canAccessPath(path, authSession)
}

const suggestionGroups = computed<SuggestionGroup[]>(() => {
  return baseSuggestionGroups
    .map(group => ({
      ...group,
      content: group.content.filter(canAccessSuggestion),
    }))
    .filter(group => group.content.length > 0)
})

const noDataSuggestions = computed<Suggestion[]>(() => {
  return baseNoDataSuggestions.filter(canAccessSuggestion)
})

const fetchResults = () => {
  if (!searchQuery.value.trim()) {
    searchResult.value = []
    isLoading.value = false
    return
  }

  isLoading.value = true

  const query = searchQuery.value.trim().toLocaleLowerCase()
  searchResult.value = suggestionGroups.value
    .map(group => ({
      title: group.title,
      children: group.content.filter(item => item.title.toLocaleLowerCase().includes(query)),
    }))
    .filter(group => group.children.length > 0)

  isLoading.value = false
}

watch(searchQuery, fetchResults)

const redirectToSuggestedOrSearchedPage = (selected: Suggestion) => {
  router.push(selected.url as any)
  isAppSearchBarVisible.value = false
  searchQuery.value = ''
}

const resolveSearchResultTitle = (item: unknown) => (item as SearchResults).title
const resolveSearchResultChildren = (item: unknown) => (item as SearchResults).children

const LazyAppBarSearch = defineAsyncComponent(() => import('@core/components/AppBarSearch.vue'))
</script>

<template>
  <div
    class="d-flex align-center cursor-pointer gap-x-2"
    v-bind="$attrs"
    style="user-select: none;"
    @click="isAppSearchBarVisible = !isAppSearchBarVisible"
  >
    <IconBtn @click="Shepherd.activeTour?.cancel()">
      <VIcon icon="ri-search-line" />
    </IconBtn>

    <div
      v-if="configStore.appContentLayoutNav === 'vertical'"
      class="d-none d-md-flex text-disabled text-body-1 gap-x-2"
      @click="Shepherd.activeTour?.cancel()"
    >
      <div>Arama</div>
      <div class="meta-key">?K</div>
    </div>
  </div>

  <LazyAppBarSearch
    v-model:is-dialog-visible="isAppSearchBarVisible"
    :search-results="searchResult"
    :is-loading="isLoading"
    @search="searchQuery = $event"
  >
    <template #suggestions>
      <VCardText class="app-bar-search-suggestions pa-12">
        <VRow v-if="suggestionGroups.length">
          <VCol
            v-for="suggestion in suggestionGroups"
            :key="suggestion.title"
            cols="12"
            sm="6"
          >
            <p class="custom-letter-spacing text-xs text-disabled text-uppercase py-2 px-4 mb-0">
              {{ suggestion.title }}
            </p>
            <VList class="card-list">
              <VListItem
                v-for="item in suggestion.content"
                :key="item.title"
                link
                class="app-bar-search-suggestion mx-4 mt-2"
                @click="redirectToSuggestedOrSearchedPage(item)"
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

    <template #noDataSuggestion>
      <div class="mt-6">
        <div class="text-center text-disabled py-2">Bunlari deneyin</div>
        <h6
          v-for="suggestion in noDataSuggestions"
          :key="suggestion.title"
          class="app-bar-search-suggestion text-h6 font-weight-regular cursor-pointer py-2 px-4"
          @click="redirectToSuggestedOrSearchedPage(suggestion)"
        >
          <VIcon
            size="20"
            :icon="suggestion.icon"
            class="me-2"
          />
          <span class="d-inline-block">{{ suggestion.title }}</span>
        </h6>
      </div>
    </template>

    <template #searchResult="{ item }">
      <VListSubheader class="text-disabled custom-letter-spacing font-weight-regular ps-4">
        {{ resolveSearchResultTitle(item) }}
      </VListSubheader>
      <VListItem
        v-for="list in resolveSearchResultChildren(item)"
        :key="list.title"
        @click="redirectToSuggestedOrSearchedPage(list)"
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
            icon="ri-corner-down-left-line"
            class="enter-icon text-medium-emphasis"
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
  padding-block: 0.1rem;
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
