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
  status: 'unpaid' | 'partial' | 'paid'
  vendor: { id: number; name: string } | null
  account: { id: number; name: string } | null
  creator: { id: number; name: string } | null
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

interface ExpenseShowResponse {
  data: ExpenseDetail
  meta: {
    cash_accounts: Array<{ id: number; name: string; type: string; balance: number }>
    payment_methods: Array<{ value: 'cash' | 'bank'; label: string }>
  }
}

const route = useRoute()
const router = useRouter()

const expenseId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const paying = ref(false)
const deleting = ref(false)
const errorMessage = ref('')

const detail = ref<ExpenseDetail | null>(null)
const cashAccounts = ref<Array<{ id: number; name: string; type: string; balance: number }>>([])
const paymentMethods = ref<Array<{ value: 'cash' | 'bank'; label: string }>>([])

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

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<ExpenseShowResponse>(`/expenses/${expenseId.value}`)
    detail.value = response.data
    cashAccounts.value = response.meta.cash_accounts
    paymentMethods.value = response.meta.payment_methods
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Gider detayi alinamadi.')
  }
  finally {
    loading.value = false
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

onMounted(fetchDetail)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Gider Detayi
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ detail?.vendor?.name ?? 'Tedarikci yok' }}
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/expenses"
          >
            Listeye Don
          </VBtn>

          <VBtn
            color="success"
            prepend-icon="ri-secure-payment-line"
            :disabled="!detail || detail.remaining <= 0"
            @click="openPayDialog"
          >
            Odeme Yap
          </VBtn>

          <VBtn
            color="error"
            variant="outlined"
            prepend-icon="ri-delete-bin-line"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteExpense"
          >
            Sil
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
      >
        {{ errorMessage }}
      </VAlert>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VCardText v-if="detail">
          <VRow>
            <VCol
              cols="12"
              md="3"
            >
              <div class="text-caption text-medium-emphasis">
                Gider Tarihi
              </div>
              <div class="font-weight-medium">
                {{ formatDate(detail.expense_date) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <div class="text-caption text-medium-emphasis">
                Vade
              </div>
              <div class="font-weight-medium">
                {{ formatDate(detail.due_date) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <div class="text-caption text-medium-emphasis">
                Hesap
              </div>
              <div class="font-weight-medium">
                {{ detail.account?.name ?? '-' }}
              </div>
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <div class="text-caption text-medium-emphasis">
                Durum
              </div>
              <VChip
                size="small"
                :color="statusColor(detail.status)"
                variant="tonal"
              >
                {{ statusLabel(detail.status) }}
              </VChip>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">Tutar</div>
              <div class="text-h6">
                {{ formatCurrency(detail.amount) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">Odenen</div>
              <div class="text-h6 text-success">
                {{ formatCurrency(detail.paid_amount) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">Kalan</div>
              <div class="text-h6 text-error">
                {{ formatCurrency(detail.remaining) }}
              </div>
            </VCol>

            <VCol cols="12">
              <div class="text-caption text-medium-emphasis">Aciklama</div>
              <div>{{ detail.description || '-' }}</div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VCardItem title="Odeme Hareketleri" />

        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Tarih</th>
              <th>Yontem</th>
              <th>Kasa/Banka</th>
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
                Odeme kaydi yok.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>

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
                  label="Odeme Tarihi"
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
                  label="Yontem"
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
                  label="Kasa/Banka Hesabi"
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
                  label="Tutar"
                  :rules="amountRules"
                  :error-messages="payErrors.amount ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="payForm.description"
                  label="Aciklama"
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
            Vazgec
          </VBtn>
          <VBtn
            color="primary"
            type="submit"
            :loading="paying"
            :disabled="paying"
          >
            Ode
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VRow>
</template>

