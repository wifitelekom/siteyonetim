<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api, getApiBaseUrl } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'

interface ReportOptionItem {
  id: number
  name: string
}

interface CashStatementResponse {
  data: {
    account: { id: number; name: string; type: string }
    from: string
    to: string
    opening_balance: number
    closing_balance: number
    transactions: Array<{
      date: string
      description: string
      type: string
      receipt_no: string | null
      amount: number
      direction: 'in' | 'out'
      balance: number
    }>
  }
}

const props = withDefaults(defineProps<{
  cashAccounts: ReportOptionItem[]
  metaLoading?: boolean
}>(), {
  metaLoading: false,
})

const loading = ref(false)
const errorMessage = ref('')
const statement = ref<CashStatementResponse['data'] | null>(null)
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
  cash_account_id: null as number | null,
  from: toDateInput(monthStart),
  to: toDateInput(now),
})

watch(() => props.cashAccounts, items => {
  if (items.length === 0) {
    filters.cash_account_id = null
    return
  }

  if (!items.some(item => item.id === filters.cash_account_id))
    filters.cash_account_id = items[0].id
}, { immediate: true })

const loadReport = async () => {
  if (!filters.cash_account_id)
    return

  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<CashStatementResponse>('/reports/cash-statement', {
      query: {
        cash_account_id: filters.cash_account_id,
        from: filters.from,
        to: filters.to,
      },
      signal,
    }))

    statement.value = response.data
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, t('reports.cashStatement.error'))
  }
  finally {
    loading.value = false
  }
}

const downloadPdf = () => {
  if (!filters.cash_account_id)
    return

  const params = new URLSearchParams({
    cash_account_id: String(filters.cash_account_id),
    from: filters.from,
    to: filters.to,
  })

  window.open(`${getApiBaseUrl()}/reports/cash-statement/pdf?${params.toString()}`, '_blank', 'noopener')
}
</script>

<template>
  <div>
    <VRow>
      <VCol
        cols="12"
        md="3"
      >
        <VSelect
          v-model="filters.cash_account_id"
          :items="cashAccounts"
          item-title="name"
          item-value="id"
          :label="$t('reports.cashStatement.cashAccount')"
          :loading="metaLoading"
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

    <template v-if="statement">
      <div class="d-flex gap-4 flex-wrap align-center mb-4">
        <VChip
          color="primary"
          variant="tonal"
        >
          {{ $t('reports.cashStatement.opening') }}: {{ formatCurrency(statement.opening_balance) }}
        </VChip>
        <VChip
          color="success"
          variant="tonal"
        >
          {{ $t('reports.cashStatement.closing') }}: {{ formatCurrency(statement.closing_balance) }}
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
            <th>{{ $t('common.description') }}</th>
            <th class="text-right">
              {{ $t('reports.cashStatement.inflow') }}
            </th>
            <th class="text-right">
              {{ $t('reports.cashStatement.outflow') }}
            </th>
            <th class="text-right">
              {{ $t('reports.cashStatement.balance') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in statement.transactions"
            :key="`cash-${row.date}-${row.description}-${row.amount}`"
          >
            <td>{{ formatDate(row.date) }}</td>
            <td>{{ row.description }}</td>
            <td class="text-right text-success">
              {{ row.direction === 'in' ? formatCurrency(row.amount) : '-' }}
            </td>
            <td class="text-right text-error">
              {{ row.direction === 'out' ? formatCurrency(row.amount) : '-' }}
            </td>
            <td class="text-right font-weight-medium">
              {{ formatCurrency(row.balance) }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
