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
const { t } = useI18n({ useScope: 'global' })

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

    errorMessage.value = getApiErrorMessage(error, t('reports.receivableStatus.error'))
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
        {{ $t('common.reportFetch') }}
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
          {{ $t('reports.receivableStatus.grandDebt') }}: {{ formatCurrency(report.grand_total) }}
        </VChip>
        <VBtn
          variant="outlined"
          prepend-icon="ri-download-line"
          @click="downloadPdf"
        >
          {{ $t('common.pdf') }}
        </VBtn>
      </div>

      <VTable density="comfortable">
        <thead>
          <tr>
            <th>{{ $t('common.vendor') }}</th>
            <th class="text-right">
              {{ $t('common.total') }}
            </th>
            <th class="text-right">
              {{ $t('common.paid') }}
            </th>
            <th class="text-right">
              {{ $t('common.remaining') }}
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
