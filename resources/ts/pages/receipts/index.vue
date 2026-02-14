<script setup lang="ts">
import type { OptionItem, PaginationMeta } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { methodLabel } from '@/utils/statusHelpers'

const { withAbort } = useAbortOnUnmount()

interface ReceiptItem {
  id: number
  receipt_no: string
  paid_at: string
  method: string
  total_amount: number
  description: string | null
  apartment: { id: number; label: string } | null
  cash_account: { id: number; name: string } | null
}

interface ReceiptsResponse {
  data: ReceiptItem[]
  meta: PaginationMeta
}

interface ReceiptsMetaResponse {
  data: {
    apartments: OptionItem[]
  }
}

const loading = ref(false)
const loadingMeta = ref(false)
const errorMessage = ref('')

const receipts = ref<ReceiptItem[]>([])
const apartments = ref<OptionItem[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  apartment_id: null as number | null,
  from: '',
  to: '',
  search: '',
})

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await $api<ReceiptsMetaResponse>('/receipts/meta')
    apartments.value = response.data.apartments
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Filtre verileri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchReceipts = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<ReceiptsResponse>('/receipts', {
      query: {
        page,
        apartment_id: filters.value.apartment_id || undefined,
        from: filters.value.from || undefined,
        to: filters.value.to || undefined,
        search: filters.value.search || undefined,
      },
    })

    receipts.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Tahsilat listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchReceipts(1)
}

const resetFilters = async () => {
  filters.value = {
    apartment_id: null,
    from: '',
    to: '',
    search: '',
  }

  await fetchReceipts(1)
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchReceipts(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Tahsilatlar
          </h4>
          <p class="text-medium-emphasis mb-0">
            Makbuz ve tahsilat hareketlerini inceleyin
          </p>
        </div>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta">
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.apartment_id"
                :items="apartments"
                item-title="label"
                item-value="id"
                :label="$t('common.apartment')"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model="filters.from"
                type="date"
                :label="$t('common.startDate')"
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model="filters.to"
                type="date"
                :label="$t('common.endDate')"
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model="filters.search"
                :label="$t('common.search')"
                placeholder="Makbuz no veya açıklama"
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
              <th>{{ $t('common.receiptNo') }}</th>
              <th>{{ $t('common.date') }}</th>
              <th>{{ $t('common.apartment') }}</th>
              <th>{{ $t('common.methodAndCash') }}</th>
              <th class="text-right">
                Tutar
              </th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="receipt in receipts"
              :key="receipt.id"
            >
              <td class="font-weight-medium">
                #{{ receipt.receipt_no }}
              </td>
              <td>{{ formatDate(receipt.paid_at) }}</td>
              <td>{{ receipt.apartment?.label ?? '-' }}</td>
              <td>
                <div>{{ methodLabel(receipt.method) }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ receipt.cash_account?.name ?? '-' }}
                </div>
              </td>
              <td class="text-right text-success font-weight-medium">
                {{ formatCurrency(receipt.total_amount) }}
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/receipts/${receipt.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="receipts.length === 0">
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
            @update:model-value="fetchReceipts"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

