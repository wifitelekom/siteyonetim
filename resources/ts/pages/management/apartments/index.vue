<script setup lang="ts">
import { getApiErrorMessage } from '@/utils/errorHandler'
import type { PaginationMeta } from '@/types/api'
import { $api } from '@/utils/api'
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
              <th>{{ $t('common.resident') }}</th>
              <th class="text-right">
                m2
              </th>
              <th class="text-right">
                {{ $t('common.landShare') }}
              </th>
              <th>{{ $t('common.status') }}</th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="apartment in apartments"
              :key="apartment.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ apartment.full_label }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  Blok {{ apartment.block || '-' }}, Kat {{ apartment.floor }}
                </div>
              </td>
              <td>
                <div>{{ apartment.current_tenant?.name || apartment.current_owner?.name || '-' }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ apartment.resident_count }} sakin
                </div>
              </td>
              <td class="text-right">
                {{ apartment.m2 ?? '-' }}
              </td>
              <td class="text-right">
                {{ apartment.arsa_payi ?? '-' }}
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
            @update:model-value="fetchApartments"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>


