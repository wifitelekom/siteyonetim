<script setup lang="ts">
import { getApiErrorMessage } from '@/utils/errorHandler'
import type { PaginationMeta } from '@/types/api'
import { useAuthSession } from '@/composables/useAuthSession'
import { $api } from '@/utils/api'
import { formatDateTr as formatDate } from '@/utils/formatters'

interface SiteItem {
  id: number
  name: string
  phone: string | null
  address: string | null
  tax_no: string | null
  is_active: boolean
  created_at: string | null
  admin: {
    id: number
    name: string
    email: string | null
  } | null
}

interface SitesResponse {
  data: SiteItem[]
  meta: PaginationMeta
}

const router = useRouter()
const authSession = useAuthSession()

const loading = ref(false)
const deletingId = ref<number | null>(null)
const switchingId = ref<number | null>(null)
const errorMessage = ref('')

const sites = ref<SiteItem[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  is_active: null as boolean | null,
  search: '',
})

const canManageSites = computed(() => authSession.can('sites.manage'))
const activeSiteId = computed(() => authSession.site.value?.id ?? null)


const fetchSites = async (page = 1) => {
  if (!canManageSites.value)
    return

  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<SitesResponse>('/super/sites', {
      query: {
        page,
        is_active: filters.value.is_active === null ? undefined : filters.value.is_active ? 1 : 0,
        search: filters.value.search || undefined,
      },
    })

    sites.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchSites(1)
}

const resetFilters = async () => {
  filters.value = {
    is_active: null,
    search: '',
  }

  await fetchSites(1)
}

const deleteSite = async (site: SiteItem) => {
  deletingId.value = site.id
  errorMessage.value = ''

  try {
    await $api(`/super/sites/${site.id}`, { method: 'DELETE' })
    await fetchSites(pagination.value.current_page)
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

const switchSite = async (site: SiteItem) => {
  switchingId.value = site.id
  errorMessage.value = ''

  try {
    await authSession.switchSiteContext(site.id)
    await router.push('/')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site baglami guncellenemedi.')
  }
  finally {
    switchingId.value = null
  }
}

const clearSiteContext = async () => {
  switchingId.value = -1
  errorMessage.value = ''

  try {
    await authSession.switchSiteContext(null)
    await fetchSites(1)
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site baglami temizlenemedi.')
  }
  finally {
    switchingId.value = null
  }
}

onMounted(async () => {
  await authSession.ensureSession()

  if (!canManageSites.value) {
    await router.replace('/')
    return
  }

  await fetchSites(1)
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Siteler
          </h4>
          <p class="text-medium-emphasis mb-0">
            Super admin site yönetimi
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          to="/super/sites/create"
        >
          Yeni Site
        </VBtn>

        <VBtn
          v-if="activeSiteId"
          variant="outlined"
          prepend-icon="ri-close-circle-line"
          :loading="switchingId === -1"
          :disabled="switchingId !== null"
          @click="clearSiteContext"
        >
          Site secimini kaldir
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.is_active"
                :items="[
                  { title: $t('common.active'), value: true },
                  { title: $t('common.passive'), value: false },
                ]"
                :label="$t('common.status')"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="9"
            >
              <VTextField
                v-model="filters.search"
                :label="$t('common.search')"
                placeholder="Site adi, telefon, vergi no"
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex gap-3 justify-end">
                <VBtn
                  variant="outlined"
                  @click="resetFilters"
                >
                  {{ $t('common.clear') }}
                </VBtn>
                <VBtn
                  color="primary"
                  @click="applyFilters"
                >
                  {{ $t('common.filter') }}
                </VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-if="errorMessage"
      cols="12"
    >
      <VAlert
        type="error"
        variant="tonal"
      >
        {{ errorMessage }}
      </VAlert>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>{{ $t('common.site') }}</th>
              <th>{{ $t('common.manager') }}</th>
              <th>{{ $t('common.phone') }}</th>
              <th>{{ $t('common.status') }}</th>
              <th>{{ $t('common.createdAt') }}</th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="site in sites"
              :key="site.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ site.name }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ site.address || '-' }}
                </div>
              </td>
              <td>
                <div>{{ site.admin?.name || '-' }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ site.admin?.email || '-' }}
                </div>
              </td>
              <td>{{ site.phone || '-' }}</td>
              <td>
                <VChip
                  size="small"
                  :color="site.is_active ? 'success' : 'secondary'"
                  variant="tonal"
                >
                  {{ site.is_active ? $t('common.active') : $t('common.passive') }}
                </VChip>
              </td>
              <td>{{ formatDate(site.created_at) }}</td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="primary"
                  :loading="switchingId === site.id"
                  :disabled="switchingId !== null"
                  @click="switchSite(site)"
                >
                  <VIcon icon="ri-login-box-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/super/sites/${site.id}/edit`"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="deletingId === site.id"
                  :disabled="deletingId === site.id || switchingId !== null"
                  @click="deleteSite(site)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="sites.length === 0">
              <td
                colspan="6"
                class="text-center text-medium-emphasis py-6"
              >
                {{ $t('common.noRecords') }}
              </td>
            </tr>
          </tbody>
        </VTable>

        <VCardText class="d-flex justify-space-between align-center flex-wrap gap-3">
          <span class="text-sm text-medium-emphasis">{{ $t('common.totalRecords', { count: pagination.total }) }}</span>

          <VPagination
            :model-value="pagination.current_page"
            :length="pagination.last_page"
            :total-visible="7"
            @update:model-value="fetchSites"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>


