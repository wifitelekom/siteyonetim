<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api, getApiBaseUrl } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { methodLabel } from '@/utils/statusHelpers'

interface PaymentsResponse {
  data: {
    from: string
    to: string
    total: number
    payments: Array<{
      id: number
      paid_at: string
      vendor: string | null
      method: string
      cash_account: string | null
      total_amount: number
      description: string | null
    }>
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<PaymentsResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()
const { t } = useI18n({ useScope: 'global' })

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
    const response = await withAbort(signal => $api<PaymentsResponse>('/reports/payments', {
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

    errorMessage.value = getApiErrorMessage(error, t('reports.payments.error'))
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

  window.open(`${getApiBaseUrl()}/reports/payments/pdf?${params.toString()}`, '_blank', 'noopener')
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
          :label="$t('common.startDate')"
        />
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VTextField
          v-model="filters.to"
          type="date"
          :label="$t('common.endDate')"
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
          {{ $t('common.reportFetch') }}
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
          color="error"
          variant="tonal"
        >
          {{ $t('reports.payments.totalPayment') }}: {{ formatCurrency(report.total) }}
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
            <th>{{ $t('common.date') }}</th>
            <th>{{ $t('common.vendor') }}</th>
            <th>{{ $t('common.methodAndCash') }}</th>
            <th class="text-right">
              {{ $t('common.amount') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="payment in report.payments"
            :key="payment.id"
          >
            <td>{{ formatDate(payment.paid_at) }}</td>
            <td>{{ payment.vendor ?? '-' }}</td>
            <td>{{ methodLabel(payment.method) }} / {{ payment.cash_account ?? '-' }}</td>
            <td class="text-right font-weight-medium text-error">
              {{ formatCurrency(payment.total_amount) }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
