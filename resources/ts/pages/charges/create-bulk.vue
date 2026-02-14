<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import type { OptionItem } from '@/types/api'
import { $api } from '@/utils/api'
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
  apartment_ids: [] as number[],
  account_id: null as number | null,
  charge_type: 'aidat' as 'aidat' | 'other',
  period: new Date().toISOString().slice(0, 7),
  due_date: new Date().toISOString().slice(0, 10),
  amount: null as number | null,
  description: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const apartmentRules = [
  (value: unknown) => Array.isArray(value) && value.length > 0 ? true : 'En az bir daire seçiniz.',
]
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
    errorMessage.value = getApiErrorMessage(error, 'Form verileri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const selectAllApartments = () => {
  form.value.apartment_ids = apartments.value.map(item => item.id)
}

const clearApartments = () => {
  form.value.apartment_ids = []
}

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  loading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api('/charges/bulk', {
      method: 'POST',
      body: {
        apartment_ids: form.value.apartment_ids,
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
    errorMessage.value = getApiErrorMessage(error, 'Toplu tahakkuk oluşturulamadı.')
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
            Toplu Tahakkuk
          </h4>
          <p class="text-medium-emphasis mb-0">
            Birden fazla daire için tek seferde tahakkuk oluşturun
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

              <VCol cols="12">
                <div class="d-flex justify-space-between align-center mb-2">
                  <label class="text-body-2">{{ $t('common.apartments') }}</label>
                  <div class="d-flex gap-2">
                    <VBtn
                      size="small"
                      variant="text"
                      @click="selectAllApartments"
                    >
                      Tümunu Sec
                    </VBtn>
                    <VBtn
                      size="small"
                      variant="text"
                      @click="clearApartments"
                    >
                      {{ $t('common.clear') }}
                    </VBtn>
                  </div>
                </div>

                <VSelect
                  v-model="form.apartment_ids"
                  :items="apartments"
                  item-title="label"
                  item-value="id"
                  :label="$t('common.apartments')"
                  multiple
                  chips
                  closable-chips
                  :rules="apartmentRules"
                  :error-messages="fieldErrors.apartment_ids ?? fieldErrors['apartment_ids.0'] ?? []"
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
                md="6"
              >
                <VSelect
                  v-model="form.charge_type"
                  :items="chargeTypes"
                  item-title="label"
                  item-value="value"
                  :label="$t('common.chargeType')"
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
                  :label="$t('common.period')"
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
                    Toplu Oluştur
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


