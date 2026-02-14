<script setup lang="ts">
import { getApiErrorMessage } from '@/utils/errorHandler'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import type { PaginationMeta } from '@/types/api'
import { $api } from '@/utils/api'

interface VendorItem {
  id: number
  name: string
  tax_no: string | null
  phone: string | null
  email: string | null
  address: string | null
  is_active: boolean
  expenses_count: number
}

interface VendorsResponse {
  data: VendorItem[]
  meta: PaginationMeta
}

const { withAbort } = useAbortOnUnmount()
const loading = ref(false)
const deletingId = ref<number | null>(null)
const errorMessage = ref('')

const vendors = ref<VendorItem[]>([])
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

const fetchVendors = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<VendorsResponse>('/vendors', {
      query: {
        page,
        is_active: filters.value.is_active === null ? undefined : filters.value.is_active ? 1 : 0,
        search: filters.value.search || undefined,
      },
      signal,
    }))

    vendors.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tedarikçi listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchVendors(1)
}

const resetFilters = async () => {
  filters.value = {
    is_active: null,
    search: '',
  }

  await fetchVendors(1)
}

const deleteVendor = async (vendor: VendorItem) => {
  deletingId.value = vendor.id
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/vendors/${vendor.id}`, { method: 'DELETE', signal }))
    await fetchVendors(pagination.value.current_page)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tedarikçi silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

onMounted(() => fetchVendors(1))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Tedarikçiler
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.vendors.indexSubtitle') }}
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          to="/management/vendors/create"
        >
          Yeni Tedarikçi
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
                placeholder="Ad, vergi no, telefon, e-posta"
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
              <th>Firma</th>
              <th>Vergi No</th>
              <th>{{ $t('common.contact') }}</th>
              <th class="text-right">
                Gider
              </th>
              <th>{{ $t('common.status') }}</th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="vendor in vendors"
              :key="vendor.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ vendor.name }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ vendor.email || '-' }}
                </div>
              </td>
              <td>{{ vendor.tax_no || '-' }}</td>
              <td>{{ vendor.phone || '-' }}</td>
              <td class="text-right">
                {{ vendor.expenses_count }}
              </td>
              <td>
                <VChip
                  size="small"
                  :color="vendor.is_active ? 'success' : 'secondary'"
                  variant="tonal"
                >
                  {{ vendor.is_active ? $t('common.active') : $t('common.passive') }}
                </VChip>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/management/vendors/${vendor.id}/edit`"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="deletingId === vendor.id"
                  :disabled="deletingId === vendor.id"
                  @click="deleteVendor(vendor)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="vendors.length === 0">
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
            @update:model-value="fetchVendors"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>


