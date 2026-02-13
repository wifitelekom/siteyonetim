<script setup lang="ts">
import type { OptionItem, PaginationMeta } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { methodLabel } from '@/utils/statusHelpers'

const { withAbort } = useAbortOnUnmount()

interface PaymentItem {
  id: number
  paid_at: string
  method: string
  total_amount: number
  description: string | null
  vendor: { id: number; name: string } | null
  cash_account: { id: number; name: string } | null
}

interface PaymentsResponse {
  data: PaymentItem[]
  meta: PaginationMeta
}

interface PaymentsMetaResponse {
  data: {
    vendors: OptionItem[]
  }
}

const loading = ref(false)
const loadingMeta = ref(false)
const errorMessage = ref('')

const payments = ref<PaymentItem[]>([])
const vendors = ref<OptionItem[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  vendor_id: null as number | null,
  from: '',
  to: '',
  search: '',
})

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await $api<PaymentsMetaResponse>('/payments/meta')
    vendors.value = response.data.vendors
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Filtre verileri alinamadi.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchPayments = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<PaymentsResponse>('/payments', {
      query: {
        page,
        vendor_id: filters.value.vendor_id || undefined,
        from: filters.value.from || undefined,
        to: filters.value.to || undefined,
        search: filters.value.search || undefined,
      },
    })

    payments.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Odeme listesi alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchPayments(1)
}

const resetFilters = async () => {
  filters.value = {
    vendor_id: null,
    from: '',
    to: '',
    search: '',
  }

  await fetchPayments(1)
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchPayments(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Odemeler
          </h4>
          <p class="text-medium-emphasis mb-0">
            Tedarikci odeme hareketlerini inceleyin
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
                v-model="filters.vendor_id"
                :items="vendors"
                item-title="label"
                item-value="id"
                label="Tedarikci"
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
                label="Baslangic"
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model="filters.to"
                type="date"
                label="Bitis"
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model="filters.search"
                label="Arama"
                placeholder="Aciklama veya tedarikci"
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex gap-3 justify-end">
                <VBtn
                  variant="outlined"
                  @click="resetFilters"
                >
                  Temizle
                </VBtn>
                <VBtn
                  color="primary"
                  @click="applyFilters"
                >
                  Filtrele
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
              <th>Tarih</th>
              <th>Tedarikci</th>
              <th>Yontem / Kasa</th>
              <th class="text-right">Tutar</th>
              <th class="text-right">Islemler</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="payment in payments"
              :key="payment.id"
            >
              <td>{{ formatDate(payment.paid_at) }}</td>
              <td>{{ payment.vendor?.name ?? '-' }}</td>
              <td>
                <div>{{ methodLabel(payment.method) }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ payment.cash_account?.name ?? '-' }}
                </div>
              </td>
              <td class="text-right text-error font-weight-medium">
                {{ formatCurrency(payment.total_amount) }}
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/payments/${payment.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="payments.length === 0">
              <td
                colspan="5"
                class="text-center text-medium-emphasis py-6"
              >
                Kayit bulunamadi.
              </td>
            </tr>
          </tbody>
        </VTable>

        <VCardText class="d-flex justify-space-between align-center flex-wrap gap-3">
          <span class="text-sm text-medium-emphasis">Toplam {{ pagination.total }} kayit</span>

          <VPagination
            :model-value="pagination.current_page"
            :length="pagination.last_page"
            :total-visible="7"
            @update:model-value="fetchPayments"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
