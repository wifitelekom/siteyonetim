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

const router = useRouter()
const { withAbort } = useAbortOnUnmount()
const listRoute = { path: '/templates/expense' } as const

const loadingMeta = ref(false)
const loading = ref(false)
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
    errorMessage.value = getApiErrorMessage(error, 'Form verileri alınamadı.')
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
    await withAbort(signal => $api('/templates/expense', {
      method: 'POST',
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

    await router.push(listRoute)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Gider şablonu oluşturulamadı.')
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
            Yeni Gider Şablonu
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.expenseTemplates.createSubtitle') }}
          </p>
        </div>

        <VBtn
          variant="outlined"
          :to="listRoute"
        >
          Listeye Don
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading || loadingMeta">
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
                  :label="$t('common.templateName')"
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
                  :label="$t('common.vendor')"
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
                  :label="$t('common.account')"
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
                  :label="$t('common.periodicity')"
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
                  :label="$t('common.dueDay')"
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
                  :label="$t('common.amount')"
                  :rules="amountRules"
                  :error-messages="fieldErrors.amount ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="form.is_active"
                  :label="$t('common.active')"
                  color="primary"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    :to="listRoute"
                  >
                    Iptal
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="loading"
                    :disabled="loading"
                  >
                    {{ $t('common.save') }}
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


