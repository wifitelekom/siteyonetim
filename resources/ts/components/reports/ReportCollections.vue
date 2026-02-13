<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api, getApiBaseUrl } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { methodLabel } from '@/utils/statusHelpers'

interface CollectionsResponse {
  data: {
    from: string
    to: string
    total: number
    receipts: Array<{
      id: number
      receipt_no: string
      paid_at: string
      apartment: string | null
      method: string
      cash_account: string | null
      total_amount: number
      description: string | null
    }>
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<CollectionsResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()

const toDateInput = (date: Date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

const now = new Date()
const monthStart = new Date(now.getFullYear(), now.getMonth(), 1)

const filters = reactive({
  from: toDateInput(monthStart),
  to: toDateInput(now),
})

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<CollectionsResponse>('/reports/collections', {
      query: {
        from: filters.from,
        to: filters.to,
      },
      signal,
    }))

    report.value = response.data
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Tahsilat raporu alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const downloadPdf = () => {
  const params = new URLSearchParams({
    from: filters.from,
    to: filters.to,
  })

  window.open(`${getApiBaseUrl()}/reports/collections/pdf?${params.toString()}`, '_blank', 'noopener')
}
</script>

<template>
  <div>
    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VTextField
          v-model="filters.from"
          type="date"
          label="Baslangic"
        />
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VTextField
          v-model="filters.to"
          type="date"
          label="Bitis"
        />
      </VCol>
      <VCol
        cols="12"
        md="4"
        class="d-flex align-end"
      >
        <VBtn
          color="primary"
          block
          :loading="loading"
          @click="loadReport"
        >
          Raporu Getir
        </VBtn>
      </VCol>
    </VRow>

    <VAlert
      v-if="errorMessage"
      type="error"
      variant="tonal"
      class="mb-4"
    >
      {{ errorMessage }}
    </VAlert>

    <template v-if="report">
      <div class="d-flex gap-4 flex-wrap align-center mb-4">
        <VChip
          color="success"
          variant="tonal"
        >
          Toplam Tahsilat: {{ formatCurrency(report.total) }}
        </VChip>
        <VBtn
          variant="outlined"
          prepend-icon="ri-download-line"
          @click="downloadPdf"
        >
          PDF
        </VBtn>
      </div>

      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Makbuz</th>
            <th>Tarih</th>
            <th>Daire</th>
            <th>Yontem / Kasa</th>
            <th class="text-right">
              Tutar
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="receipt in report.receipts"
            :key="receipt.id"
          >
            <td>#{{ receipt.receipt_no }}</td>
            <td>{{ formatDate(receipt.paid_at) }}</td>
            <td>{{ receipt.apartment ?? '-' }}</td>
            <td>{{ methodLabel(receipt.method) }} / {{ receipt.cash_account ?? '-' }}</td>
            <td class="text-right font-weight-medium text-success">
              {{ formatCurrency(receipt.total_amount) }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
