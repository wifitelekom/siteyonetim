<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api, getApiBaseUrl } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { chargeStatusColor, chargeStatusLabel } from '@/utils/statusHelpers'

interface ChargeListResponse {
  data: {
    period: string
    totals: {
      amount: number
      paid: number
      remaining: number
    }
    charges: Array<{
      id: number
      apartment: string | null
      account: string | null
      period: string
      due_date: string
      amount: number
      paid_amount: number
      remaining: number
      status: string | null
    }>
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<ChargeListResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()
const { t } = useI18n({ useScope: 'global' })

const toPeriodInput = (date: Date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')

  return `${year}-${month}`
}

const filters = reactive({
  period: toPeriodInput(new Date()),
})

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ChargeListResponse>('/reports/charge-list', {
      query: {
        period: filters.period,
      },
      signal,
    }))

    report.value = response.data
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, t('reports.chargeList.error'))
  }
  finally {
    loading.value = false
  }
}

const downloadPdf = () => {
  const params = new URLSearchParams({
    period: filters.period,
  })

  window.open(`${getApiBaseUrl()}/reports/charge-list/pdf?${params.toString()}`, '_blank', 'noopener')
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
          v-model="filters.period"
          type="month"
          :label="$t('common.period')"
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
          color="info"
          variant="tonal"
        >
          {{ $t('common.total') }}: {{ formatCurrency(report.totals.amount) }}
        </VChip>
        <VChip
          color="success"
          variant="tonal"
        >
          {{ $t('common.paid') }}: {{ formatCurrency(report.totals.paid) }}
        </VChip>
        <VChip
          color="error"
          variant="tonal"
        >
          {{ $t('common.remaining') }}: {{ formatCurrency(report.totals.remaining) }}
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
            <th>{{ $t('common.account') }}</th>
            <th>{{ $t('common.period') }}</th>
            <th>{{ $t('common.due') }}</th>
            <th class="text-right">
              {{ $t('common.amount') }}
            </th>
            <th class="text-right">
              {{ $t('common.paid') }}
            </th>
            <th class="text-right">
              {{ $t('common.remaining') }}
            </th>
            <th>{{ $t('common.status') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in report.charges"
            :key="row.id"
          >
            <td>{{ row.apartment ?? '-' }}</td>
            <td>{{ row.account ?? '-' }}</td>
            <td>{{ row.period }}</td>
            <td>{{ formatDate(row.due_date) }}</td>
            <td class="text-right">{{ formatCurrency(row.amount) }}</td>
            <td class="text-right text-success">{{ formatCurrency(row.paid_amount) }}</td>
            <td class="text-right text-error font-weight-medium">
              {{ formatCurrency(row.remaining) }}
            </td>
            <td>
              <VChip
                size="small"
                :color="chargeStatusColor(row.status)"
                variant="tonal"
              >
                {{ chargeStatusLabel(row.status) }}
              </VChip>
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
