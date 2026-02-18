<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { expenseStatusColor as statusColor, expenseStatusLabel as statusLabel } from '@/utils/statusHelpers'
import { positiveNumberRule, requiredRule } from '@/utils/validators'

const { withAbort } = useAbortOnUnmount()

interface ExpenseDetail {
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
  creator: { id: number; name: string } | null
  created_at: string | null
  payment_items: Array<{
    id: number
    amount: number
    payment: {
      id: number
      paid_at: string
      method: string
      description: string | null
      cash_account: { id: number; name: string } | null
    } | null
  }>
}

interface NoteItem {
  id: number
  content: string
  created_by: { id: number; name: string } | null
  created_at: string
}

interface TimelineEvent {
  date: string
  type: 'created' | 'payment'
  description: string
  by: string | null
  amount?: number
  icon: string
  color: string
}

interface ExpenseShowResponse {
  data: ExpenseDetail
  meta: {
    cash_accounts: Array<{ id: number; name: string; type: string; balance: number }>
    payment_methods: Array<{ value: 'cash' | 'bank'; label: string }>
    vendors: Array<{ id: number; label: string }>
    accounts: Array<{ id: number; label: string }>
  }
}

const route = useRoute()
const router = useRouter()

const expenseId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const paying = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const activeTab = ref('payments')

const detail = ref<ExpenseDetail | null>(null)
const cashAccounts = ref<Array<{ id: number; name: string; type: string; balance: number }>>([])
const paymentMethods = ref<Array<{ value: 'cash' | 'bank'; label: string }>>([])
const vendors = ref<Array<{ id: number; label: string }>>([])
const accounts = ref<Array<{ id: number; label: string }>>([])

// Notes
const notes = ref<NoteItem[]>([])
const noteContent = ref('')
const addingNote = ref(false)

// Actions dropdown
const otherMenu = ref(false)

// Pay dialog
const payDialog = ref(false)
const payErrors = ref<Record<string, string[]>>({})
const payForm = ref({
  paid_at: new Date().toISOString().slice(0, 10),
  method: 'cash' as 'cash' | 'bank',
  cash_account_id: null as number | null,
  amount: null as number | null,
  description: '',
})
const payFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const paidAtRules = [requiredRule()]
const methodRules = [requiredRule()]
const cashAccountRules = [requiredRule()]
const amountRules = [requiredRule(), positiveNumberRule()]

// Edit dialog
const editDialog = ref(false)
const editing = ref(false)
const editErrors = ref<Record<string, string[]>>({})
const editForm = ref({
  vendor_id: null as number | null,
  account_id: null as number | null,
  expense_date: '',
  due_date: '',
  amount: null as number | null,
  description: '',
  invoice_no: '',
})
const editFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const editAccountRules = [requiredRule()]
const editExpenseDateRules = [requiredRule()]
const editDueDateRules = [requiredRule()]
const editAmountRules = [requiredRule(), positiveNumberRule()]

const isAmountLocked = computed(() => Number(detail.value?.paid_amount ?? 0) > 0)

// Timeline computed
const timelineEvents = computed<TimelineEvent[]>(() => {
  if (!detail.value) return []

  const events: TimelineEvent[] = []

  if (detail.value.created_at) {
    events.push({
      date: detail.value.created_at,
      type: 'created',
      description: 'Gider olusturuldu',
      by: detail.value.creator?.name ?? null,
      icon: 'ri-add-circle-line',
      color: 'info',
    })
  }

  for (const item of detail.value.payment_items) {
    if (item.payment) {
      events.push({
        date: item.payment.paid_at,
        type: 'payment',
        description: item.payment.description || 'Odeme yapildi',
        by: item.payment.cash_account?.name ?? null,
        amount: item.amount,
        icon: 'ri-money-dollar-circle-line',
        color: 'success',
      })
    }
  }

  events.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())

  return events
})

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<ExpenseShowResponse>(`/expenses/${expenseId.value}`)
    detail.value = response.data
    cashAccounts.value = response.meta.cash_accounts
    paymentMethods.value = response.meta.payment_methods
    vendors.value = response.meta.vendors
    accounts.value = response.meta.accounts
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Gider detayi alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const fetchNotes = async () => {
  try {
    const response = await withAbort(signal =>
      $api<{ data: NoteItem[] }>(`/expenses/${expenseId.value}/notes`, { signal }),
    )
    notes.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
  }
}

const addNote = async () => {
  if (!noteContent.value.trim()) return
  addingNote.value = true
  try {
    await $api(`/expenses/${expenseId.value}/notes`, {
      method: 'POST',
      body: { content: noteContent.value },
    })
    noteContent.value = ''
    await fetchNotes()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Not eklenemedi.')
  }
  finally {
    addingNote.value = false
  }
}

const deleteNote = async (noteId: number) => {
  try {
    await $api(`/expenses/${expenseId.value}/notes/${noteId}`, { method: 'DELETE' })
    await fetchNotes()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Not silinemedi.')
  }
}

const openPayDialog = () => {
  payErrors.value = {}
  payForm.value.amount = detail.value?.remaining ?? null
  payDialog.value = true
}

const submitPay = async () => {
  const validation = await payFormRef.value?.validate()
  if (!validation?.valid)
    return

  paying.value = true
  errorMessage.value = ''
  payErrors.value = {}

  try {
    await $api(`/expenses/${expenseId.value}/pay`, {
      method: 'POST',
      body: {
        paid_at: payForm.value.paid_at,
        method: payForm.value.method,
        cash_account_id: payForm.value.cash_account_id,
        amount: payForm.value.amount,
        description: payForm.value.description || null,
      },
    })

    payDialog.value = false
    await fetchDetail()
    await fetchNotes()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Odeme islemi basarisiz.')
    payErrors.value = getApiFieldErrors(error)
  }
  finally {
    paying.value = false
  }
}

const deleteExpense = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await $api(`/expenses/${expenseId.value}`, { method: 'DELETE' })
    await router.push('/expenses')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Gider silinemedi.')
  }
  finally {
    deleting.value = false
  }
}

const copyExpense = () => {
  router.push(`/expenses/create?copy=${expenseId.value}`)
}

const goToEdit = () => {
  otherMenu.value = false

  if (!detail.value)
    return

  editErrors.value = {}
  editForm.value = {
    vendor_id: detail.value.vendor?.id ?? null,
    account_id: detail.value.account?.id ?? null,
    expense_date: detail.value.expense_date,
    due_date: detail.value.due_date,
    amount: detail.value.amount,
    description: detail.value.description ?? '',
    invoice_no: detail.value.invoice_no ?? '',
  }
  editDialog.value = true
}

const submitEdit = async () => {
  const validation = await editFormRef.value?.validate()
  if (!validation?.valid)
    return

  editing.value = true
  editErrors.value = {}
  errorMessage.value = ''

  try {
    await $api(`/expenses/${expenseId.value}`, {
      method: 'PUT',
      body: {
        vendor_id: editForm.value.vendor_id,
        account_id: editForm.value.account_id,
        expense_date: editForm.value.expense_date,
        due_date: editForm.value.due_date,
        amount: Number(editForm.value.amount),
        description: editForm.value.description || null,
        invoice_no: editForm.value.invoice_no || null,
      },
    })

    editDialog.value = false
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Gider guncellenemedi.')
    editErrors.value = getApiFieldErrors(error)
  }
  finally {
    editing.value = false
  }
}

onMounted(async () => {
  await fetchDetail()
  fetchNotes()
})
</script>

<template>
  <VRow>
    <!-- Header -->
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            {{ $t('pages.expenses.detailTitle') }}
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ detail?.vendor?.name ?? $t('common.noVendor') }}
          </p>
        </div>
        <div class="d-flex gap-2">
          <VBtn
            color="primary"
            variant="outlined"
            @click="goToEdit"
          >
            Duzenle
          </VBtn>
          <VBtn
            variant="outlined"
            to="/expenses"
          >
            Listeye Don
          </VBtn>
        </div>
      </div>
    </VCol>

    <VCol
      v-if="errorMessage"
      cols="12"
    >
      <VAlert
        type="error"
        variant="tonal"
        closable
        @click:close="errorMessage = ''"
      >
        {{ errorMessage }}
      </VAlert>
    </VCol>

    <!-- Left Column -->
    <VCol
      cols="12"
      md="8"
    >
      <!-- Info Card -->
      <VCard
        :loading="loading"
        class="mb-4"
      >
        <VCardText v-if="detail">
          <VList class="card-list">
            <VListItem v-if="detail.description">
              <VListItemTitle>{{ $t('common.description') }}</VListItemTitle>
              <template #append>
                <div class="d-flex align-center gap-2">
                  <span>{{ detail.description }}</span>
                  <VChip
                    v-if="detail.vendor"
                    size="small"
                    color="primary"
                    variant="tonal"
                  >
                    {{ detail.vendor.name }}
                  </VChip>
                </div>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>{{ $t('common.amount') }}</VListItemTitle>
              <template #append>
                <span class="font-weight-medium">{{ formatCurrency(detail.amount) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Gider Tarihi</VListItemTitle>
              <template #append>
                <span>{{ formatDate(detail.expense_date) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Vade</VListItemTitle>
              <template #append>
                <span>{{ formatDate(detail.due_date) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Hesap</VListItemTitle>
              <template #append>
                <span>{{ detail.account?.name ?? '-' }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Durum</VListItemTitle>
              <template #append>
                <VChip
                  size="small"
                  :color="statusColor(detail.status)"
                  variant="tonal"
                >
                  {{ statusLabel(detail.status) }}
                </VChip>
              </template>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>

      <!-- Tabs -->
      <VCard>
        <VTabs
          v-model="activeTab"
          class="v-tabs-pill"
        >
          <VTab value="payments">
            Odemeler
          </VTab>
          <VTab value="notes">
            Notlar
          </VTab>
          <VTab value="history">
            Gecmis
          </VTab>
        </VTabs>

        <VDivider />

        <!-- Payments Tab -->
        <div v-show="activeTab === 'payments'">
          <VTable density="comfortable">
            <thead>
              <tr>
                <th>{{ $t('common.date') }}</th>
                <th>{{ $t('common.method') }}</th>
                <th>{{ $t('common.cashAccount') }}</th>
                <th class="text-right">
                  Tutar
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in detail?.payment_items ?? []"
                :key="item.id"
              >
                <td>{{ formatDate(item.payment?.paid_at ?? '') }}</td>
                <td>{{ item.payment?.method ?? '-' }}</td>
                <td>{{ item.payment?.cash_account?.name ?? '-' }}</td>
                <td class="text-right">
                  {{ formatCurrency(item.amount) }}
                </td>
              </tr>
              <tr v-if="(detail?.payment_items ?? []).length === 0">
                <td
                  colspan="4"
                  class="text-center text-medium-emphasis py-6"
                >
                  {{ $t('common.noPaymentRecords') }}
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>

        <!-- Notes Tab -->
        <div v-show="activeTab === 'notes'">
          <VCardText>
            <VRow>
              <VCol
                cols="12"
                md="10"
              >
                <VTextarea
                  v-model="noteContent"
                  label="Not ekleyin..."
                  rows="2"
                  hide-details
                />
              </VCol>
              <VCol
                cols="12"
                md="2"
                class="d-flex align-end"
              >
                <VBtn
                  color="primary"
                  block
                  :loading="addingNote"
                  :disabled="!noteContent.trim()"
                  @click="addNote"
                >
                  Ekle
                </VBtn>
              </VCol>
            </VRow>
          </VCardText>
          <VDivider />
          <VList v-if="notes.length">
            <template
              v-for="(note, i) in notes"
              :key="note.id"
            >
              <VListItem>
                <VListItemTitle>{{ note.content }}</VListItemTitle>
                <VListItemSubtitle>
                  {{ note.created_by?.name ?? '-' }} - {{ formatDate(note.created_at) }}
                </VListItemSubtitle>
                <template #append>
                  <VBtn
                    icon
                    size="small"
                    variant="text"
                    color="error"
                    @click="deleteNote(note.id)"
                  >
                    <VIcon icon="ri-delete-bin-line" />
                  </VBtn>
                </template>
              </VListItem>
              <VDivider v-if="i < notes.length - 1" />
            </template>
          </VList>
          <VCardText
            v-else
            class="text-center text-medium-emphasis"
          >
            Henuz not eklenmemis.
          </VCardText>
        </div>

        <!-- History Tab -->
        <div v-show="activeTab === 'history'">
          <VList v-if="timelineEvents.length">
            <template
              v-for="(event, i) in timelineEvents"
              :key="i"
            >
              <VListItem>
                <template #prepend>
                  <VIcon
                    :icon="event.icon"
                    :color="event.color"
                    class="me-2"
                  />
                </template>
                <VListItemTitle>
                  {{ event.description }}
                  <span
                    v-if="event.amount"
                    class="text-success font-weight-medium"
                  >
                    ({{ formatCurrency(event.amount) }})
                  </span>
                </VListItemTitle>
                <VListItemSubtitle>
                  {{ event.by ?? '' }} - {{ formatDate(event.date) }}
                </VListItemSubtitle>
              </VListItem>
              <VDivider v-if="i < timelineEvents.length - 1" />
            </template>
          </VList>
          <VCardText
            v-else
            class="text-center text-medium-emphasis"
          >
            Gecmis kaydi yok.
          </VCardText>
        </div>
      </VCard>
    </VCol>

    <!-- Right Column -->
    <VCol
      cols="12"
      md="4"
    >
      <!-- Amount + Actions Card -->
      <VCard class="mb-4">
        <VCardText class="d-flex align-center justify-space-between">
          <div>
            <div class="text-overline text-medium-emphasis">
              KALAN
            </div>
            <div
              class="text-h4"
              :class="(detail?.remaining ?? 0) > 0 ? 'text-error' : 'text-success'"
            >
              {{ formatCurrency(detail?.remaining ?? 0) }}
            </div>
          </div>

          <VMenu
            v-model="otherMenu"
            location="bottom end"
          >
            <template #activator="{ props: menuProps }">
              <VBtn
                variant="outlined"
                v-bind="menuProps"
                append-icon="ri-arrow-down-s-line"
              >
                Diger
              </VBtn>
            </template>
            <VList density="compact">
              <VListItem @click="copyExpense">
                <template #prepend>
                  <VIcon
                    icon="ri-file-copy-line"
                    class="me-2"
                  />
                </template>
                Kopyala
              </VListItem>
              <VListItem @click="goToEdit">
                <template #prepend>
                  <VIcon
                    icon="ri-edit-line"
                    class="me-2"
                  />
                </template>
                Duzenle
              </VListItem>
              <VDivider />
              <VListItem
                :loading="deleting"
                class="text-error"
                @click="deleteExpense"
              >
                <template #prepend>
                  <VIcon
                    icon="ri-delete-bin-line"
                    class="me-2"
                  />
                </template>
                Sil
              </VListItem>
            </VList>
          </VMenu>
        </VCardText>

        <VDivider />

        <VCardText>
          <VBtn
            color="success"
            block
            prepend-icon="ri-secure-payment-line"
            :disabled="!detail || detail.remaining <= 0"
            @click="openPayDialog"
          >
            Odeme Yap
          </VBtn>
        </VCardText>
      </VCard>

      <!-- Summary Card -->
      <VCard v-if="detail">
        <VCardItem title="Ozet" />
        <VCardText>
          <VList
            class="card-list"
            density="compact"
          >
            <VListItem>
              <VListItemTitle>Toplam Tutar</VListItemTitle>
              <template #append>
                <span>{{ formatCurrency(detail.amount) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Odenen</VListItemTitle>
              <template #append>
                <span class="text-success">{{ formatCurrency(detail.paid_amount) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Kalan</VListItemTitle>
              <template #append>
                <span class="text-error">{{ formatCurrency(detail.remaining) }}</span>
              </template>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- Edit Dialog -->
  <VDialog
    v-model="editDialog"
    max-width="760"
  >
    <VCard title="Gider Duzenle">
      <VCardText>
        <VForm
          ref="editFormRef"
          @submit.prevent="submitEdit"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="editForm.vendor_id"
                :items="vendors"
                item-title="label"
                item-value="id"
                :label="$t('common.vendor')"
                clearable
                :error-messages="editErrors.vendor_id ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="editForm.account_id"
                :items="accounts"
                item-title="label"
                item-value="id"
                label="Gider Hesabi"
                :rules="editAccountRules"
                :error-messages="editErrors.account_id ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.expense_date"
                type="date"
                :label="$t('common.expenseDate')"
                :rules="editExpenseDateRules"
                :error-messages="editErrors.expense_date ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.due_date"
                type="date"
                :label="$t('common.dueDate')"
                :rules="editDueDateRules"
                :error-messages="editErrors.due_date ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.amount"
                type="number"
                step="0.01"
                min="0"
                :label="$t('common.amount')"
                :rules="editAmountRules"
                :disabled="isAmountLocked"
                :error-messages="editErrors.amount ?? []"
              />
            </VCol>

            <VCol cols="12">
              <VTextarea
                v-model="editForm.description"
                :label="$t('common.description')"
                rows="2"
                :error-messages="editErrors.description ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editForm.invoice_no"
                label="Fatura/Fis No"
                :error-messages="editErrors.invoice_no ?? []"
              />
            </VCol>

            <VCol
              v-if="isAmountLocked"
              cols="12"
            >
              <VAlert
                type="warning"
                variant="tonal"
              >
                Bu giderin odemesi yapilmis. Tutar degistirilemez.
              </VAlert>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions class="px-6 pb-4">
        <VSpacer />
        <VBtn
          variant="outlined"
          :disabled="editing"
          @click="editDialog = false"
        >
          Iptal
        </VBtn>
        <VBtn
          color="primary"
          :loading="editing"
          :disabled="editing"
          @click="submitEdit"
        >
          Kaydet
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Pay Dialog -->
  <VDialog
    v-model="payDialog"
    max-width="560"
  >
    <VCard title="Odeme Yap">
      <VCardText>
        <VForm
          ref="payFormRef"
          @submit.prevent="submitPay"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="payForm.paid_at"
                type="date"
                :label="$t('common.paymentDate')"
                :rules="paidAtRules"
                :error-messages="payErrors.paid_at ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="payForm.method"
                :items="paymentMethods"
                item-title="label"
                item-value="value"
                :label="$t('common.method')"
                :rules="methodRules"
                :error-messages="payErrors.method ?? []"
              />
            </VCol>

            <VCol cols="12">
              <VSelect
                v-model="payForm.cash_account_id"
                :items="cashAccounts"
                item-title="name"
                item-value="id"
                :label="$t('common.cashAccount')"
                :rules="cashAccountRules"
                :error-messages="payErrors.cash_account_id ?? []"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="payForm.amount"
                type="number"
                step="0.01"
                min="0"
                :label="$t('common.amount')"
                :rules="amountRules"
                :error-messages="payErrors.amount ?? []"
              />
            </VCol>

            <VCol cols="12">
              <VTextarea
                v-model="payForm.description"
                :label="$t('common.description')"
                rows="2"
                :error-messages="payErrors.description ?? []"
              />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions class="px-6 pb-4">
        <VSpacer />
        <VBtn
          variant="outlined"
          @click="payDialog = false"
        >
          {{ $t('common.cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          :loading="paying"
          :disabled="paying"
          @click="submitPay"
        >
          Ode
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
