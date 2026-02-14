<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api, getApiBaseUrl } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency } from '@/utils/formatters'

interface DebtStatusResponse {
  data: {
    grand_total: number
    debts: Array<{
      apartment: string
      resident: string
      total: number
      paid: number
      remaining: number
      overdue_count: number
      open_count: number
    }>
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<DebtStatusResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()
const { t } = useI18n({ useScope: 'global' })

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<DebtStatusResponse>('/reports/debt-status', { signal }))
    report.value = response.data
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, t('reports.debtStatus.error'))
  }
  finally {
    loading.value = false
  }
}

const downloadPdf = () => {
  window.open(`${getApiBaseUrl()}/reports/debt-status/pdf`, '_blank', 'noopener')
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
          color="error"
          variant="tonal"
        >
          {{ $t('reports.debtStatus.grandRemainingDebt') }}: {{ formatCurrency(report.grand_total) }}
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
            <th>{{ $t('common.apartment') }}</th>
            <th>{{ $t('common.resident') }}</th>
            <th class="text-right">
              {{ $t('common.total') }}
            </th>
            <th class="text-right">
              {{ $t('common.paid') }}
            </th>
            <th class="text-right">
              {{ $t('common.remaining') }}
            </th>
            <th class="text-right">
              {{ $t('reports.debtStatus.overdue') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in report.debts"
            :key="`debt-${row.apartment}-${row.resident}`"
          >
            <td>{{ row.apartment }}</td>
            <td>{{ row.resident }}</td>
            <td class="text-right">{{ formatCurrency(row.total) }}</td>
            <td class="text-right text-success">{{ formatCurrency(row.paid) }}</td>
            <td class="text-right text-error font-weight-medium">
              {{ formatCurrency(row.remaining) }}
            </td>
            <td class="text-right">
              {{ row.overdue_count }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
