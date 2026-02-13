<script setup lang="ts">
import type { OptionItem } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { positiveNumberRule, requiredRule } from '@/utils/validators'

interface ExpensesMetaResponse {
  data: {
    vendors: OptionItem[]
    accounts: OptionItem[]
  }
}

const { withAbort } = useAbortOnUnmount()
const router = useRouter()
const loading = ref(false)
const loadingMeta = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const vendors = ref<OptionItem[]>([])
const accounts = ref<OptionItem[]>([])

const form = ref({
  vendor_id: null as number | null,
  account_id: null as number | null,
  expense_date: '',
  due_date: '',
  amount: null as number | null,
  description: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const vendorRules = [requiredRule()]
const accountRules = [requiredRule()]
const expenseDateRules = [requiredRule()]
const dueDateRules = [requiredRule()]
const amountRules = [requiredRule(), positiveNumberRule()]

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await withAbort(signal => $api<ExpensesMetaResponse>('/expenses/meta', { signal }))
    vendors.value = response.data.vendors
    accounts.value = response.data.accounts
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Form verileri alinamadi.')
  }
  finally {
    loadingMeta.value = false
  }
}

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  loading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api('/expenses', {
      method: 'POST',
      body: {
        vendor_id: form.value.vendor_id,
        account_id: form.value.account_id,
        expense_date: form.value.expense_date,
        due_date: form.value.due_date,
        amount: form.value.amount,
        description: form.value.description || null,
      },
      signal,
    }))

    await router.push('/expenses')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Gider olusturulamadi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    loading.value = false
  }
}

onMounted(fetchMeta)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Yeni Gider
          </h4>
          <p class="text-medium-emphasis mb-0">
            Yeni bir gider kaydi olusturun
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/expenses"
        >
          Listeye Don
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta || loading">
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
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="form.vendor_id"
                  :items="vendors"
                  item-title="label"
                  item-value="id"
                  label="Tedarikci"
                  clearable
                  :rules="vendorRules"
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
                  label="Gider Tarihi"
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
                  label="Vade Tarihi"
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
                  label="Tutar"
                  :rules="amountRules"
                  :error-messages="fieldErrors.amount ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="form.description"
                  label="Aciklama"
                  rows="3"
                  :error-messages="fieldErrors.description ?? []"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/expenses"
                  >
                    Iptal
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="loading"
                    :disabled="loading"
                  >
                    Kaydet
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
