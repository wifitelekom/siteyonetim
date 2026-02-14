<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api, getApiBaseUrl } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { typeLabel } from '@/utils/statusHelpers'

interface ReportOptionItem {
  id: number
  full_name?: string
}

interface AccountStatementResponse {
  data: {
    account: { id: number; code: string; name: string; full_name: string }
    from: string
    to: string
    rows: Array<{
      date: string
      type: 'charge' | 'expense'
      description: string
      amount: number
    }>
    totals: {
      charges: number
      expenses: number
    }
  }
}

const props = withDefaults(defineProps<{
  accounts: ReportOptionItem[]
  metaLoading?: boolean
}>(), {
  metaLoading: false,
})

const loading = ref(false)
const errorMessage = ref('')
const statement = ref<AccountStatementResponse['data'] | null>(null)
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
  account_id: null as number | null,
  from: toDateInput(monthStart),
  to: toDateInput(now),
})

watch(() => props.accounts, items => {
  if (items.length === 0) {
    filters.account_id = null
    return
  }

  if (!items.some(item => item.id === filters.account_id))
    filters.account_id = items[0].id
}, { immediate: true })

const loadReport = async () => {
  if (!filters.account_id)
    return

  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<AccountStatementResponse>('/reports/account-statement', {
      query: {
        account_id: filters.account_id,
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

    errorMessage.value = getApiErrorMessage(error, t('reports.accountStatement.error'))
  }
  finally {
    loading.value = false
  }
}

const downloadPdf = () => {
  if (!filters.account_id)
    return

  const params = new URLSearchParams({
    account_id: String(filters.account_id),
    from: filters.from,
    to: filters.to,
  })

  window.open(`${getApiBaseUrl()}/reports/account-statement/pdf?${params.toString()}`, '_blank', 'noopener')
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
          v-model="filters.account_id"
          :items="accounts"
          item-title="full_name"
          item-value="id"
          :label="$t('reports.accountStatement.accountingAccount')"
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
          color="info"
          variant="tonal"
        >
          {{ $t('reports.accountStatement.totalCharges') }}: {{ formatCurrency(statement.totals.charges) }}
        </VChip>
        <VChip
          color="error"
          variant="tonal"
        >
          {{ $t('reports.accountStatement.totalExpense') }}: {{ formatCurrency(statement.totals.expenses) }}
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
            <th>{{ $t('common.type') }}</th>
            <th>{{ $t('common.description') }}</th>
            <th class="text-right">
              {{ $t('common.amount') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in statement.rows"
            :key="`acct-${row.date}-${row.type}-${row.amount}`"
          >
            <td>{{ formatDate(row.date) }}</td>
            <td>
              <VChip
                size="small"
                :color="row.type === 'charge' ? 'info' : 'error'"
                variant="tonal"
              >
                {{ typeLabel(row.type) }}
              </VChip>
            </td>
            <td>{{ row.description }}</td>
            <td class="text-right font-weight-medium">
              {{ formatCurrency(row.amount) }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
