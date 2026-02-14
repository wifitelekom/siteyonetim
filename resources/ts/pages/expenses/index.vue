<script setup lang="ts">
import type { OptionItem, PaginationMeta } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { expenseStatusColor as statusColor, expenseStatusLabel as statusLabel } from '@/utils/statusHelpers'

const { withAbort } = useAbortOnUnmount()

interface ExpenseItem {
  id: number
  expense_date: string
  due_date: string
  amount: number
  paid_amount: number
  remaining: number
  description: string | null
  status: 'unpaid' | 'partial' | 'paid'
  vendor: { id: number; name: string } | null
  account: { id: number; name: string } | null
}

interface ExpensesResponse {
  data: ExpenseItem[]
  meta: PaginationMeta
  filters: {
    status: Array<{ value: string; label: string }>
  }
}

interface ExpensesMetaResponse {
  data: {
    vendors: OptionItem[]
    accounts: OptionItem[]
  }
}

const loading = ref(false)
const loadingMeta = ref(false)
const errorMessage = ref('')

const expenses = ref<ExpenseItem[]>([])
const vendors = ref<OptionItem[]>([])
const statusOptions = ref<Array<{ value: string; label: string }>>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  vendor_id: null as number | null,
  status: null as string | null,
  from: '',
  to: '',
  search: '',
})





const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await $api<ExpensesMetaResponse>('/expenses/meta')
    vendors.value = response.data.vendors
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Filtre verileri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchExpenses = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<ExpensesResponse>('/expenses', {
      query: {
        page,
        vendor_id: filters.value.vendor_id || undefined,
        status: filters.value.status || undefined,
        from: filters.value.from || undefined,
        to: filters.value.to || undefined,
        search: filters.value.search || undefined,
      },
    })

    expenses.value = response.data
    pagination.value = response.meta
    statusOptions.value = response.filters.status
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Gider listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchExpenses(1)
}

const resetFilters = async () => {
  filters.value = {
    vendor_id: null,
    status: null,
    from: '',
    to: '',
    search: '',
  }

  await fetchExpenses(1)
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchExpenses(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Giderler
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.expenses.indexSubtitle') }}
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          to="/expenses/create"
        >
          Yeni Gider
        </VBtn>
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
                :label="$t('common.vendor')"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.status"
                :items="statusOptions"
                item-title="label"
                item-value="value"
                :label="$t('common.status')"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="2"
            >
              <VTextField
                v-model="filters.from"
                type="date"
                :label="$t('common.startDate')"
              />
            </VCol>

            <VCol
              cols="12"
              md="2"
            >
              <VTextField
                v-model="filters.to"
                type="date"
                :label="$t('common.endDate')"
              />
            </VCol>

            <VCol
              cols="12"
              md="2"
            >
              <VTextField
                v-model="filters.search"
                :label="$t('common.search')"
                :placeholder="$t('common.description')"
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
              <th>{{ $t('common.vendor') }}</th>
              <th>{{ $t('common.periodAndDue') }}</th>
              <th class="text-right">
                Tutar
              </th>
              <th class="text-right">
                {{ $t('common.paid') }}
              </th>
              <th class="text-right">
                Kalan
              </th>
              <th>{{ $t('common.status') }}</th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="expense in expenses"
              :key="expense.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ expense.vendor?.name ?? '-' }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ expense.account?.name ?? '-' }}
                </div>
              </td>
              <td>
                <div>{{ formatDate(expense.expense_date) }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ formatDate(expense.due_date) }}
                </div>
              </td>
              <td class="text-right">
                {{ formatCurrency(expense.amount) }}
              </td>
              <td class="text-right">
                {{ formatCurrency(expense.paid_amount) }}
              </td>
              <td class="text-right">
                {{ formatCurrency(expense.remaining) }}
              </td>
              <td>
                <VChip
                  size="small"
                  :color="statusColor(expense.status)"
                  variant="tonal"
                >
                  {{ statusLabel(expense.status) }}
                </VChip>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/expenses/${expense.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="expenses.length === 0">
              <td
                colspan="7"
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
            @update:model-value="fetchExpenses"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

