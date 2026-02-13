<script setup lang="ts">
import type { OptionItem } from '@/types/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { positiveNumberRule, requiredRule } from '@/utils/validators'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

interface ExpenseMetaResponse {
  data: {
    accounts: OptionItem[]
    vendors: OptionItem[]
    periods: Array<{ value: 'monthly' | 'quarterly' | 'yearly'; label: string }>
  }
}

interface ExpenseDetailResponse {
  data: {
    id: number
    name: string
    amount: number
    due_day: number
    period: 'monthly' | 'quarterly' | 'yearly'
    is_active: boolean
    vendor: { id: number; label: string } | null
    account: { id: number; label: string } | null
  }
}

const route = useRoute()
const router = useRouter()
const templateId = computed(() => Number((route.params as Record<string, unknown>).id))
const { withAbort } = useAbortOnUnmount()

const loadingMeta = ref(false)
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const accounts = ref<OptionItem[]>([])
const vendors = ref<OptionItem[]>([])
const periods = ref<Array<{ value: 'monthly' | 'quarterly' | 'yearly'; label: string }>>([])

const form = ref({
  name: '',
  amount: null as number | null,
  due_day: 15,
  period: 'monthly' as 'monthly' | 'quarterly' | 'yearly',
  vendor_id: null as number | null,
  account_id: null as number | null,
  is_active: true,
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const nameRules = [requiredRule()]
const accountRules = [requiredRule()]
const periodRules = [requiredRule()]
const amountRules = [requiredRule(), positiveNumberRule()]
const dueDayRules = [
  requiredRule(),
  (value: unknown) => {
    const parsed = Number(value)
    if (!Number.isInteger(parsed) || parsed < 1 || parsed > 28)
      return 'Vade gunu 1 ile 28 arasinda olmalidir.'

    return true
  },
]

const fetchMeta = async () => {
  loadingMeta.value = true
  try {
    const response = await withAbort(signal => $api<ExpenseMetaResponse>('/templates/expense/meta', { signal }))
    accounts.value = response.data.accounts
    vendors.value = response.data.vendors
    periods.value = response.data.periods
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Form verileri alinamadi.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ExpenseDetailResponse>(`/templates/expense/${templateId.value}`, { signal }))
    form.value = {
      name: response.data.name,
      amount: response.data.amount,
      due_day: response.data.due_day,
      period: response.data.period,
      vendor_id: response.data.vendor?.id ?? null,
      account_id: response.data.account?.id ?? null,
      is_active: response.data.is_active,
    }
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Gider sablonu alinamadi.')
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
    await withAbort(signal => $api(`/templates/expense/${templateId.value}`, {
      method: 'PUT',
      body: {
        name: form.value.name,
        amount: form.value.amount,
        due_day: form.value.due_day,
        period: form.value.period,
        vendor_id: form.value.vendor_id,
        account_id: form.value.account_id,
        is_active: form.value.is_active,
      },
      signal,
    }))

    await router.push('/templates/expense')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Gider sablonu guncellenemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

const deleteTemplate = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/templates/expense/${templateId.value}`, { method: 'DELETE', signal }))
    await router.push('/templates/expense')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Gider sablonu silinemedi.')
  }
  finally {
    deleting.value = false
  }
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchDetail()])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Gider Sablonu Duzenle
          </h4>
          <p class="text-medium-emphasis mb-0">
            Sablon bilgilerini guncelleyin
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/templates/expense"
          >
            Listeye Don
          </VBtn>
          <VBtn
            color="error"
            variant="outlined"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteTemplate"
          >
            Sil
          </VBtn>
        </div>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta || loading || saving">
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
                <VTextField
                  v-model="form.name"
                  label="Sablon Adi"
                  :rules="nameRules"
                  :error-messages="fieldErrors.name ?? []"
                />
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
                  :error-messages="fieldErrors.vendor_id ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.account_id"
                  :items="accounts"
                  item-title="label"
                  item-value="id"
                  label="Hesap"
                  :rules="accountRules"
                  :error-messages="fieldErrors.account_id ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.period"
                  :items="periods"
                  item-title="label"
                  item-value="value"
                  label="Periyot"
                  :rules="periodRules"
                  :error-messages="fieldErrors.period ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.due_day"
                  type="number"
                  min="1"
                  max="28"
                  label="Vade Gunu"
                  :rules="dueDayRules"
                  :error-messages="fieldErrors.due_day ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.amount"
                  type="number"
                  min="0.01"
                  step="0.01"
                  label="Tutar"
                  :rules="amountRules"
                  :error-messages="fieldErrors.amount ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="form.is_active"
                  label="Aktif"
                  color="primary"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/templates/expense"
                  >
                    Vazgec
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="saving"
                    :disabled="saving"
                  >
                    Guncelle
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

