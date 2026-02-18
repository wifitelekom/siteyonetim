<script setup lang="ts">
import type { OptionItem, PaginationMeta } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'

const { withAbort } = useAbortOnUnmount()

interface ExpenseItem {
  id: number
  expense_date: string
  due_date: string
  amount: number
  paid_amount: number
  remaining: number
  description: string | null
  invoice_no: string | null
  status: 'unpaid' | 'partial' | 'paid'
  vendor: { id: number; name: string } | null
  account: { id: number; name: string } | null
}

interface ExpensesResponse {
  data: ExpenseItem[]
  meta: PaginationMeta
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
const bulkMessage = ref('')

const expenses = ref<ExpenseItem[]>([])
const vendors = ref<OptionItem[]>([])
const accounts = ref<OptionItem[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  vendor_id: null as number | null,
  status: null as 'unpaid' | 'partial' | 'paid' | null,
  from: '',
  to: '',
  search: '',
})

const showAdvancedFilters = ref(false)
const selectedExpenseIds = ref<number[]>([])
const bulkDeleting = ref(false)
const bulkUpdating = ref(false)

const categoryDialog = ref(false)
const dueDateDialog = ref(false)
const descriptionDialog = ref(false)

const bulkForm = ref({
  account_id: null as number | null,
  due_date: '',
  description: '',
})

const quickStatuses: Array<{ label: string; value: 'unpaid' | 'partial' | 'paid' }> = [
  { label: 'Ödenen', value: 'paid' },
  { label: 'Ödenmeyen', value: 'unpaid' },
  { label: 'Kısmi', value: 'partial' },
]

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await withAbort(signal => $api<ExpensesMetaResponse>('/expenses/meta', { signal }))
    vendors.value = response.data.vendors
    accounts.value = response.data.accounts
  }
  catch (error) {
    if (isAbortError(error))
      return

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
    const response = await withAbort(signal => $api<ExpensesResponse>('/expenses', {
      query: {
        page,
        vendor_id: filters.value.vendor_id || undefined,
        status: filters.value.status || undefined,
        from: filters.value.from || undefined,
        to: filters.value.to || undefined,
        search: filters.value.search || undefined,
      },
      signal,
    }))

    expenses.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Gider listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchExpenses(1)
}

const toggleQuickStatus = async (status: 'unpaid' | 'partial' | 'paid') => {
  filters.value.status = filters.value.status === status ? null : status
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

  selectedExpenseIds.value = []
  bulkMessage.value = ''
  await fetchExpenses(1)
}

const isQuickStatusActive = (status: 'unpaid' | 'partial' | 'paid') => filters.value.status === status

const visibleExpenseIds = computed(() => expenses.value.map(expense => expense.id))

const allVisibleSelected = computed(() =>
  visibleExpenseIds.value.length > 0
  && visibleExpenseIds.value.every(id => selectedExpenseIds.value.includes(id)))

const someVisibleSelected = computed(() =>
  visibleExpenseIds.value.some(id => selectedExpenseIds.value.includes(id))
  && !allVisibleSelected.value)

const toggleSelectAll = (checked: boolean) => {
  bulkMessage.value = ''

  if (checked) {
    const merged = new Set([...selectedExpenseIds.value, ...visibleExpenseIds.value])
    selectedExpenseIds.value = Array.from(merged)

    return
  }

  selectedExpenseIds.value = selectedExpenseIds.value.filter(id => !visibleExpenseIds.value.includes(id))
}

const toggleRowSelection = (id: number, checked: boolean) => {
  bulkMessage.value = ''

  if (checked) {
    if (!selectedExpenseIds.value.includes(id))
      selectedExpenseIds.value.push(id)

    return
  }

  selectedExpenseIds.value = selectedExpenseIds.value.filter(selectedId => selectedId !== id)
}

const buildExpenseTitle = (expense: ExpenseItem) => {
  const description = expense.description?.trim() ?? ''

  return description || `Gider #${expense.id}`
}

const buildExpenseSubtitle = (expense: ExpenseItem) => {
  return expense.vendor?.name ?? '-'
}

const paymentIcon = (status: ExpenseItem['status']) => {
  if (status === 'paid')
    return 'ri-check-line'
  if (status === 'partial')
    return 'ri-time-line'

  return 'ri-alarm-line'
}

const paymentClass = (status: ExpenseItem['status']) => {
  if (status === 'paid')
    return 'text-success'
  if (status === 'partial')
    return 'text-info'

  return 'text-warning'
}

const paymentText = (expense: ExpenseItem) => {
  if (expense.status === 'paid')
    return 'Ödendi'

  if (expense.status === 'partial')
    return 'Kısmi ödendi'

  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dueDate = new Date(`${expense.due_date}T00:00:00`)
  const diff = Math.floor((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))

  if (diff < 0)
    return `${Math.abs(diff)} gün gecikti`

  if (diff === 0)
    return 'Bugün'

  return `${diff} gün sonra`
}

const selectedCount = computed(() => selectedExpenseIds.value.length)
const firstSelectedExpense = computed(() =>
  expenses.value.find(expense => selectedExpenseIds.value.includes(expense.id)) ?? null)

const normalizeDescription = (description: string | null | undefined) => {
  const value = description?.trim() ?? ''

  return value.length > 0 ? value : null
}

const getExpenseForBulkUpdate = async (id: number): Promise<ExpenseItem | null> => {
  const listed = expenses.value.find(expense => expense.id === id)
  if (listed)
    return listed

  try {
    const response = await withAbort(signal => $api<{ data: ExpenseItem }>(`/expenses/${id}`, { signal }))

    return response.data
  }
  catch (error) {
    if (isAbortError(error))
      return null

    return null
  }
}

const runBulkDelete = async (action: 'sil' | 'arsivle') => {
  if (selectedExpenseIds.value.length === 0 || bulkDeleting.value || bulkUpdating.value)
    return

  const total = selectedExpenseIds.value.length
  const actionLabel = action === 'arsivle' ? 'arşivlensin' : 'silinsin'
  const confirmed = window.confirm(`${total} seçili gider kaydı ${actionLabel} mi?`)
  if (!confirmed)
    return

  bulkDeleting.value = true
  bulkMessage.value = ''
  errorMessage.value = ''

  try {
    const results = await Promise.allSettled(selectedExpenseIds.value.map(id =>
      withAbort(signal => $api(`/expenses/${id}`, { method: 'DELETE', signal })),
    ))

    const success = results.filter(result => result.status === 'fulfilled').length
    const failed = total - success

    selectedExpenseIds.value = []
    await fetchExpenses(pagination.value.current_page)

    if (success > 0)
      bulkMessage.value = action === 'arsivle' ? `${success} kayıt arşivlendi.` : `${success} kayıt silindi.`

    if (failed > 0)
      errorMessage.value = `${failed} kayıt silinemedi.`
  }
  finally {
    bulkDeleting.value = false
  }
}

const archiveSelectedExpenses = async () => {
  await runBulkDelete('arsivle')
}

const deleteSelectedExpenses = async () => {
  await runBulkDelete('sil')
}

const applyBulkExpenseChanges = async (overrides: { account_id?: number | null; due_date?: string; description?: string | null }, successText: string) => {
  if (selectedExpenseIds.value.length === 0 || bulkUpdating.value || bulkDeleting.value)
    return

  bulkUpdating.value = true
  bulkMessage.value = ''
  errorMessage.value = ''

  try {
    const results = await Promise.allSettled(selectedExpenseIds.value.map(async id => {
      const expense = await getExpenseForBulkUpdate(id)

      const accountId = overrides.account_id ?? expense?.account?.id ?? null
      if (!expense || !accountId)
        throw new Error('missing-related-data')

      await withAbort(signal => $api(`/expenses/${id}`, {
        method: 'PUT',
        body: {
          vendor_id: expense.vendor?.id ?? null,
          account_id: accountId,
          expense_date: expense.expense_date,
          due_date: overrides.due_date ?? expense.due_date,
          amount: expense.amount,
          description: Object.prototype.hasOwnProperty.call(overrides, 'description')
            ? normalizeDescription(overrides.description ?? null)
            : normalizeDescription(expense.description),
          invoice_no: expense.invoice_no ?? null,
        },
        signal,
      }))
    }))

    const success = results.filter(result => result.status === 'fulfilled').length
    const failed = selectedExpenseIds.value.length - success

    selectedExpenseIds.value = []
    await fetchExpenses(pagination.value.current_page)

    if (success > 0)
      bulkMessage.value = `${success} kayıt için ${successText}`

    if (failed > 0)
      errorMessage.value = `${failed} kayıt güncellenemedi.`
  }
  finally {
    bulkUpdating.value = false
  }
}

const openCategoryDialog = () => {
  bulkMessage.value = ''
  bulkForm.value.account_id = firstSelectedExpense.value?.account?.id ?? accounts.value[0]?.id ?? null
  categoryDialog.value = true
}

const openDueDateDialog = () => {
  bulkMessage.value = ''
  bulkForm.value.due_date = firstSelectedExpense.value?.due_date ?? new Date().toISOString().slice(0, 10)
  dueDateDialog.value = true
}

const openDescriptionDialog = () => {
  bulkMessage.value = ''
  bulkForm.value.description = firstSelectedExpense.value?.description ?? ''
  descriptionDialog.value = true
}

const submitBulkCategory = async () => {
  if (!bulkForm.value.account_id)
    return

  await applyBulkExpenseChanges({ account_id: bulkForm.value.account_id }, 'kategori güncellendi.')
  categoryDialog.value = false
}

const submitBulkDueDate = async () => {
  if (!bulkForm.value.due_date)
    return

  await applyBulkExpenseChanges({ due_date: bulkForm.value.due_date }, 'ödeme tarihi güncellendi.')
  dueDateDialog.value = false
}

const submitBulkDescription = async () => {
  await applyBulkExpenseChanges({ description: bulkForm.value.description }, 'gider açıklaması güncellendi.')
  descriptionDialog.value = false
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchExpenses(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between flex-wrap gap-3 mb-2">
        <h4 class="text-h4 mb-0">
          Giderler
        </h4>

        <div class="d-flex gap-2 flex-wrap">
          <VBtn
            variant="outlined"
            color="primary"
            to="/expenses/create"
          >
            Gider Ekle
          </VBtn>
        </div>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta">
        <VCardText class="py-3">
          <div class="d-flex align-center justify-space-between flex-wrap gap-3">
            <div class="d-flex align-center flex-wrap gap-2">
              <VBtnGroup divided>
                <VBtn
                  v-for="item in quickStatuses"
                  :key="item.value"
                  :variant="isQuickStatusActive(item.value) ? 'flat' : 'outlined'"
                  :color="isQuickStatusActive(item.value) ? 'primary' : undefined"
                  @click="toggleQuickStatus(item.value)"
                >
                  {{ item.label }}
                </VBtn>
              </VBtnGroup>

              <VBtn
                variant="outlined"
                prepend-icon="ri-filter-3-line"
                @click="showAdvancedFilters = !showAdvancedFilters"
              >
                Süz
              </VBtn>
            </div>

            <VTextField
              v-model="filters.search"
              class="expenses-search"
              label="Gider ara"
              prepend-inner-icon="ri-search-line"
              clearable
              hide-details
              @keyup.enter="applyFilters"
              @click:clear="applyFilters"
            />
          </div>

          <VExpandTransition>
            <div v-show="showAdvancedFilters" class="mt-4">
              <VRow>
                <VCol
                  cols="12"
                  md="4"
                >
                  <VSelect
                    v-model="filters.vendor_id"
                    :items="vendors"
                    item-title="label"
                    item-value="id"
                    :label="$t('common.vendor')"
                    hide-details
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
                    :label="$t('common.startDate')"
                    hide-details
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
                    hide-details
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="2"
                  class="d-flex align-end justify-end gap-2"
                >
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
                </VCol>
              </VRow>
            </div>
          </VExpandTransition>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-if="selectedCount > 0"
      cols="12"
    >
      <VCard>
        <VCardText class="d-flex align-center flex-wrap gap-2 py-3">
          <span class="text-body-2 text-medium-emphasis">
            {{ selectedCount }} kayıt seçildi
          </span>

          <VBtn
            size="small"
            variant="outlined"
            :loading="bulkDeleting"
            :disabled="bulkDeleting || bulkUpdating"
            @click="archiveSelectedExpenses"
          >
            Arşivle
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            color="error"
            :loading="bulkDeleting"
            :disabled="bulkDeleting || bulkUpdating"
            @click="deleteSelectedExpenses"
          >
            Sil
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            :disabled="bulkDeleting || bulkUpdating"
            @click="openCategoryDialog"
          >
            Kategori Ekle
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            :disabled="bulkDeleting || bulkUpdating"
            @click="openDueDateDialog"
          >
            Ödeme Tarihini Değiştir
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            :disabled="bulkDeleting || bulkUpdating"
            @click="openDescriptionDialog"
          >
            Gider Açıklamalarını Değiştir
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-if="bulkMessage"
      cols="12"
    >
      <VAlert
        type="info"
        variant="tonal"
      >
        {{ bulkMessage }}
      </VAlert>
    </VCol>

    <VDialog
      v-model="categoryDialog"
      max-width="460"
    >
      <VCard title="Kategori Ekle">
        <VCardText>
          <VSelect
            v-model="bulkForm.account_id"
            :items="accounts"
            item-title="label"
            item-value="id"
            label="Kategori"
          />
        </VCardText>
        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="categoryDialog = false"
          >
            Vazgeç
          </VBtn>
          <VBtn
            color="primary"
            :loading="bulkUpdating"
            :disabled="bulkUpdating || !bulkForm.account_id"
            @click="submitBulkCategory"
          >
            Kaydet
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="dueDateDialog"
      max-width="460"
    >
      <VCard title="Ödeme Tarihini Değiştir">
        <VCardText>
          <VTextField
            v-model="bulkForm.due_date"
            type="date"
            label="Yeni Ödeme Tarihi"
          />
        </VCardText>
        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="dueDateDialog = false"
          >
            Vazgeç
          </VBtn>
          <VBtn
            color="primary"
            :loading="bulkUpdating"
            :disabled="bulkUpdating || !bulkForm.due_date"
            @click="submitBulkDueDate"
          >
            Kaydet
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="descriptionDialog"
      max-width="560"
    >
      <VCard title="Gider Açıklamalarını Değiştir">
        <VCardText>
          <VTextarea
            v-model="bulkForm.description"
            label="Yeni Açıklama"
            rows="3"
            hint="Boş bırakırsanız açıklama temizlenir."
            persistent-hint
          />
        </VCardText>
        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="descriptionDialog = false"
          >
            Vazgeç
          </VBtn>
          <VBtn
            color="primary"
            :loading="bulkUpdating"
            :disabled="bulkUpdating"
            @click="submitBulkDescription"
          >
            Kaydet
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

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
              <th class="text-center" style="width: 50px;">
                <VCheckboxBtn
                  :model-value="allVisibleSelected"
                  :indeterminate="someVisibleSelected"
                  @update:model-value="value => toggleSelectAll(!!value)"
                />
              </th>
              <th>Açıklama</th>
              <th>Kategori</th>
              <th>Düzenleme</th>
              <th>Ödeme</th>
              <th class="text-right">
                Toplam
              </th>
              <th class="text-right">
                Kalan
              </th>
              <th class="text-right">
                {{ $t('common.actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="expense in expenses"
              :key="expense.id"
            >
              <td class="text-center">
                <VCheckboxBtn
                  :model-value="selectedExpenseIds.includes(expense.id)"
                  @update:model-value="value => toggleRowSelection(expense.id, !!value)"
                />
              </td>
              <td>
                <div class="font-weight-medium">
                  <RouterLink
                    :to="`/expenses/${expense.id}`"
                    class="text-decoration-none text-primary"
                  >
                    {{ buildExpenseTitle(expense) }}
                  </RouterLink>
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ buildExpenseSubtitle(expense) }}
                </div>
              </td>
              <td>
                <VChip
                  size="small"
                  variant="tonal"
                  color="secondary"
                >
                  {{ expense.account?.name ?? '-' }}
                </VChip>
              </td>
              <td>
                {{ formatDate(expense.expense_date) }}
              </td>
              <td>
                <div class="d-flex align-center gap-2" :class="paymentClass(expense.status)">
                  <VIcon :icon="paymentIcon(expense.status)" size="18" />
                  <span>{{ paymentText(expense) }}</span>
                </div>
              </td>
              <td class="text-right">
                <span :class="expense.status === 'paid' ? 'text-success' : ''">
                  {{ formatCurrency(expense.amount) }}
                </span>
              </td>
              <td class="text-right">
                <span :class="expense.remaining <= 0 ? 'text-success' : 'text-error'">
                  {{ formatCurrency(expense.remaining) }}
                </span>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/expenses/${expense.id}/edit`"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
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
                colspan="8"
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

<style scoped>
.expenses-search {
  inline-size: 320px;
  max-inline-size: 100%;
}

@media (max-width: 960px) {
  .expenses-search {
    inline-size: 100%;
  }
}
</style>
