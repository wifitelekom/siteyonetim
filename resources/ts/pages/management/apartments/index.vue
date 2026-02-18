<script setup lang="ts">
import { getApiErrorMessage } from '@/utils/errorHandler'
import type { PaginationMeta } from '@/types/api'
import { $api } from '@/utils/api'
import { formatCurrency } from '@/utils/formatters'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

interface ApartmentItem {
  id: number
  block: string | null
  floor: number
  number: string
  m2: number | null
  arsa_payi: number | null
  is_active: boolean
  full_label: string
  resident_count: number
  current_owner: { id: number; name: string } | null
  current_tenant: { id: number; name: string } | null
  group: { id: number; name: string } | null
  balance: number
}

interface ApartmentsResponse {
  data: ApartmentItem[]
  meta: PaginationMeta
}

const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const deletingId = ref<number | null>(null)
const errorMessage = ref('')

const apartments = ref<ApartmentItem[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const filters = ref({
  is_active: null as boolean | null,
  search: '',
})

const fetchApartments = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ApartmentsResponse>('/apartments', {
      query: {
        page,
        is_active: filters.value.is_active === null ? undefined : filters.value.is_active ? 1 : 0,
        search: filters.value.search || undefined,
      },
      signal,
    }))

    apartments.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Daire listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchApartments(1)
}

const resetFilters = async () => {
  filters.value = {
    is_active: null,
    search: '',
  }

  await fetchApartments(1)
}

const normalizeBlock = (block: string | null) => block?.trim() ?? ''

const hasVisibleBlock = (block: string | null) => {
  const normalized = normalizeBlock(block)

  return normalized !== '' && normalized !== '-' && normalized.toLowerCase() !== 'null'
}

const deleteApartment = async (apartment: ApartmentItem) => {
  deletingId.value = apartment.id
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/apartments/${apartment.id}`, { method: 'DELETE', signal }))
    await fetchApartments(pagination.value.current_page)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Daire silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

onMounted(() => fetchApartments(1))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Daireler
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.apartments.indexSubtitle') }}
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          to="/management/apartments/create"
        >
          Yeni Daire
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
                placeholder="Blok, kat, no"
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
              <th>{{ $t('common.apartment') }}</th>
              <th>Grup</th>
              <th>Kat Maliki</th>
              <th>Kiraci</th>
              <th class="text-right">
                Bakiye
              </th>
              <th>{{ $t('common.status') }}</th>
              <th class="text-right">
                Islemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="apartment in apartments"
              :key="apartment.id"
            >
              <td>
                <RouterLink
                  :to="`/management/apartments/${apartment.id}`"
                  class="font-weight-medium text-primary text-decoration-underline d-inline-block"
                >
                  {{ apartment.full_label }}
                </RouterLink>
                <div
                  v-if="hasVisibleBlock(apartment.block)"
                  class="text-caption text-medium-emphasis"
                >
                  Blok {{ normalizeBlock(apartment.block) }}
                </div>
              </td>
              <td>
                <VChip
                  v-if="apartment.group"
                  size="small"
                  variant="tonal"
                >
                  {{ apartment.group.name }}
                </VChip>
                <span v-else class="text-medium-emphasis">-</span>
              </td>
              <td>
                <RouterLink
                  v-if="apartment.current_owner"
                  :to="`/management/users/${apartment.current_owner.id}`"
                  class="text-decoration-none"
                >
                  {{ apartment.current_owner.name }}
                </RouterLink>
                <span v-else class="text-medium-emphasis">-</span>
              </td>
              <td>
                <RouterLink
                  v-if="apartment.current_tenant"
                  :to="`/management/users/${apartment.current_tenant.id}`"
                  class="text-decoration-none"
                >
                  {{ apartment.current_tenant.name }}
                </RouterLink>
                <span v-else class="text-medium-emphasis">-</span>
              </td>
              <td class="text-right">
                <span
                  class="font-weight-medium"
                  :class="apartment.balance > 0 ? 'text-error' : apartment.balance < 0 ? 'text-success' : ''"
                >
                  {{ formatCurrency(apartment.balance) }}
                </span>
              </td>
              <td>
                <VChip
                  size="small"
                  :color="apartment.is_active ? 'success' : 'secondary'"
                  variant="tonal"
                >
                  {{ apartment.is_active ? $t('common.active') : $t('common.passive') }}
                </VChip>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/management/apartments/${apartment.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/management/apartments/${apartment.id}/edit`"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="deletingId === apartment.id"
                  :disabled="deletingId === apartment.id"
                  @click="deleteApartment(apartment)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="apartments.length === 0">
              <td
                colspan="7"
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
            @update:model-value="fetchApartments"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
