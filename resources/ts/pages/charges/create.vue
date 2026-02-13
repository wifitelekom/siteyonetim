<script setup lang="ts">
import type { OptionItem } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { positiveNumberRule, requiredRule } from '@/utils/validators'

interface ChargesMetaResponse {
  data: {
    apartments: OptionItem[]
    accounts: OptionItem[]
    charge_types: Array<{ value: 'aidat' | 'other'; label: string }>
  }
}

const { withAbort } = useAbortOnUnmount()
const router = useRouter()
const loading = ref(false)
const loadingMeta = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const apartments = ref<OptionItem[]>([])
const accounts = ref<OptionItem[]>([])
const chargeTypes = ref<Array<{ value: 'aidat' | 'other'; label: string }>>([])

const form = ref({
  apartment_id: null as number | null,
  account_id: null as number | null,
  charge_type: 'aidat' as 'aidat' | 'other',
  period: '',
  due_date: '',
  amount: null as number | null,
  description: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const apartmentRules = [requiredRule()]
const accountRules = [requiredRule()]
const chargeTypeRules = [requiredRule()]
const periodRules = [requiredRule()]
const dueDateRules = [requiredRule()]
const amountRules = [requiredRule(), positiveNumberRule()]

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await withAbort(signal => $api<ChargesMetaResponse>('/charges/meta', { signal }))
    apartments.value = response.data.apartments
    accounts.value = response.data.accounts
    chargeTypes.value = response.data.charge_types
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
    await withAbort(signal => $api('/charges', {
      method: 'POST',
      body: {
        apartment_id: form.value.apartment_id,
        account_id: form.value.account_id,
        charge_type: form.value.charge_type,
        period: form.value.period,
        due_date: form.value.due_date,
        amount: form.value.amount,
        description: form.value.description || null,
      },
      signal,
    }))

    await router.push('/charges')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tahakkuk olusturulamadi.')
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
            Yeni Tahakkuk
          </h4>
          <p class="text-medium-emphasis mb-0">
            Daireye yeni tahakkuk kaydi olusturun
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/charges"
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
                  v-model="form.apartment_id"
                  :items="apartments"
                  item-title="label"
                  item-value="id"
                  label="Daire"
                  :rules="apartmentRules"
                  :error-messages="fieldErrors.apartment_id ?? []"
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
                  label="Gelir Hesabi"
                  :rules="accountRules"
                  :error-messages="fieldErrors.account_id ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.charge_type"
                  :items="chargeTypes"
                  item-title="label"
                  item-value="value"
                  label="Tahakkuk Turu"
                  :rules="chargeTypeRules"
                  :error-messages="fieldErrors.charge_type ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.period"
                  type="month"
                  label="Donem"
                  :rules="periodRules"
                  :error-messages="fieldErrors.period ?? []"
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
                md="6"
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
                    to="/charges"
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
