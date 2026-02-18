<script setup lang="ts">
import type { OptionItem } from '@/types/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { positiveNumberRule, requiredRule } from '@/utils/validators'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

interface ExpenseShowResponse {
  data: {
    id: number
    expense_date: string
    due_date: string
    amount: number
    paid_amount: number
    remaining: number
    description: string | null
    invoice_no: string | null
    status: string
    vendor: { id: number; name: string } | null
    account: { id: number; name: string } | null
  }
  meta: {
    vendors: OptionItem[]
    accounts: OptionItem[]
  }
}

const route = useRoute()
const router = useRouter()
const expenseId = computed(() => Number((route.params as Record<string, unknown>).id))
const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const vendors = ref<OptionItem[]>([])
const accounts = ref<OptionItem[]>([])
const isPaid = ref(false)

const form = ref({
  vendor_id: null as number | null,
  account_id: null as number | null,
  expense_date: '',
  due_date: '',
  amount: null as number | null,
  description: '',
  invoice_no: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const accountRules = [requiredRule()]
const expenseDateRules = [requiredRule()]
const dueDateRules = [requiredRule()]
const amountRules = [requiredRule(), positiveNumberRule()]

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal =>
      $api<ExpenseShowResponse>(`/expenses/${expenseId.value}`, { signal }),
    )

    form.value = {
      vendor_id: response.data.vendor?.id ?? null,
      account_id: response.data.account?.id ?? null,
      expense_date: response.data.expense_date,
      due_date: response.data.due_date,
      amount: response.data.amount,
      description: response.data.description ?? '',
      invoice_no: response.data.invoice_no ?? '',
    }

    isPaid.value = response.data.paid_amount > 0
    vendors.value = response.meta.vendors
    accounts.value = response.meta.accounts
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Gider bilgileri alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  saving.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api(`/expenses/${expenseId.value}`, {
      method: 'PUT',
      body: {
        vendor_id: form.value.vendor_id,
        account_id: form.value.account_id,
        expense_date: form.value.expense_date,
        due_date: form.value.due_date,
        amount: Number(form.value.amount),
        description: form.value.description || null,
        invoice_no: form.value.invoice_no || null,
      },
      signal,
    }))

    await router.push(`/expenses/${expenseId.value}`)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Gider guncellenemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

const deleteExpense = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/expenses/${expenseId.value}`, { method: 'DELETE', signal }))
    await router.push('/expenses')
  }
  catch (error) {
    if (isAbortError(error)) return
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
            Gider Duzenle
          </h4>
          <p class="text-medium-emphasis mb-0">
            Gider bilgilerini guncelleyin
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            :to="`/expenses/${expenseId}`"
          >
            Detaya Don
          </VBtn>
          <VBtn
            color="error"
            variant="outlined"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteExpense"
          >
            Sil
          </VBtn>
        </div>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading || saving">
        <VCardText>
          <VForm
            ref="formRef"
            @submit.prevent="submit"
          >
            <VRow>
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

              <VCol
                v-if="isPaid"
                cols="12"
              >
                <VAlert
                  type="warning"
                  variant="tonal"
                >
                  Bu giderin odemesi yapilmis. Tutari degistiremezsiniz.
                </VAlert>
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="form.vendor_id"
                  :items="vendors"
                  item-title="label"
                  item-value="id"
                  :label="$t('common.vendor')"
                  clearable
                  :error-messages="fieldErrors.vendor_id ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="form.account_id"
                  :items="accounts"
                  item-title="label"
                  item-value="id"
                  label="Gider Hesabi"
                  :rules="accountRules"
                  :error-messages="fieldErrors.account_id ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.expense_date"
                  type="date"
                  :label="$t('common.expenseDate')"
                  :rules="expenseDateRules"
                  :error-messages="fieldErrors.expense_date ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.due_date"
                  type="date"
                  :label="$t('common.dueDate')"
                  :rules="dueDateRules"
                  :error-messages="fieldErrors.due_date ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  :label="$t('common.amount')"
                  :rules="amountRules"
                  :disabled="isPaid"
                  :error-messages="fieldErrors.amount ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="form.description"
                  :label="$t('common.description')"
                  rows="3"
                  :error-messages="fieldErrors.description ?? []"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.invoice_no"
                  label="Fatura/Fis No"
                  :error-messages="fieldErrors.invoice_no ?? []"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    :to="`/expenses/${expenseId}`"
                  >
                    Iptal
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="saving"
                    :disabled="saving"
                  >
                    {{ $t('common.update') }}
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
