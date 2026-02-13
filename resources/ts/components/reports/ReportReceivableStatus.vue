<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api, getApiBaseUrl } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency } from '@/utils/formatters'

interface ReceivableStatusResponse {
  data: {
    grand_total: number
    receivables: Array<{
      vendor: string
      total: number
      paid: number
      remaining: number
    }>
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<ReceivableStatusResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ReceivableStatusResponse>('/reports/receivable-status', { signal }))
    report.value = response.data
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Alacak durumu raporu alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const downloadPdf = () => {
  window.open(`${getApiBaseUrl()}/reports/receivable-status/pdf`, '_blank', 'noopener')
}
</script>

<template>
  <div>
    <div class="d-flex justify-end mb-4">
      <VBtn
        color="primary"
        :loading="loading"
        @click="loadReport"
      >
        Raporu Getir
      </VBtn>
    </div>

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
          color="warning"
          variant="tonal"
        >
          Genel Borc: {{ formatCurrency(report.grand_total) }}
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
            <th>Tedarikci</th>
            <th class="text-right">
              Toplam
            </th>
            <th class="text-right">
              Odenen
            </th>
            <th class="text-right">
              Kalan
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in report.receivables"
            :key="`recv-${row.vendor}`"
          >
            <td>{{ row.vendor }}</td>
            <td class="text-right">{{ formatCurrency(row.total) }}</td>
            <td class="text-right text-success">{{ formatCurrency(row.paid) }}</td>
            <td class="text-right text-error font-weight-medium">
              {{ formatCurrency(row.remaining) }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
