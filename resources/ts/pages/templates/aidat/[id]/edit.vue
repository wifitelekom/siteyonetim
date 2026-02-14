<script setup lang="ts">
import type { OptionItem } from '@/types/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { positiveNumberRule, requiredRule } from '@/utils/validators'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

interface AidatMetaResponse {
  data: {
    accounts: OptionItem[]
    apartments: OptionItem[]
    scopes: Array<{ value: 'all' | 'selected'; label: string }>
  }
}

interface AidatDetailResponse {
  data: {
    id: number
    name: string
    amount: number
    due_day: number
    scope: 'all' | 'selected'
    is_active: boolean
    apartment_ids: number[]
    account: { id: number; label: string } | null
  }
}

const route = useRoute()
const router = useRouter()
const templateId = computed(() => Number((route.params as Record<string, unknown>).id))
const { withAbort } = useAbortOnUnmount()
const listRoute = { path: '/templates/aidat' } as const

const loadingMeta = ref(false)
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const accounts = ref<OptionItem[]>([])
const apartments = ref<OptionItem[]>([])
const scopeOptions = ref<Array<{ value: 'all' | 'selected'; label: string }>>([])

const form = ref({
  name: '',
  amount: null as number | null,
  due_day: 15,
  account_id: null as number | null,
  scope: 'all' as 'all' | 'selected',
  apartment_ids: [] as number[],
  is_active: true,
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const nameRules = [requiredRule()]
const accountRules = [requiredRule()]
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
const scopeRules = [requiredRule()]
const apartmentRules = [
  (value: unknown) => {
    if (form.value.scope !== 'selected')
      return true

    return Array.isArray(value) && value.length > 0 ? true : 'En az bir daire seçiniz.'
  },
]

const fetchMeta = async () => {
  loadingMeta.value = true
  try {
    const response = await withAbort(signal => $api<AidatMetaResponse>('/templates/aidat/meta', { signal }))
    accounts.value = response.data.accounts
    apartments.value = response.data.apartments
    scopeOptions.value = response.data.scopes
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Form verileri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<AidatDetailResponse>(`/templates/aidat/${templateId.value}`, { signal }))
    form.value = {
      name: response.data.name,
      amount: response.data.amount,
      due_day: response.data.due_day,
      account_id: response.data.account?.id ?? null,
      scope: response.data.scope,
      apartment_ids: response.data.apartment_ids ?? [],
      is_active: response.data.is_active,
    }
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Aidat şablonu alınamadı.')
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
    await withAbort(signal => $api(`/templates/aidat/${templateId.value}`, {
      method: 'PUT',
      body: {
        name: form.value.name,
        amount: form.value.amount,
        due_day: form.value.due_day,
        account_id: form.value.account_id,
        scope: form.value.scope,
        apartment_ids: form.value.scope === 'selected' ? form.value.apartment_ids : [],
        is_active: form.value.is_active,
      },
      signal,
    }))

    await router.push(listRoute)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Aidat şablonu güncellenemedi.')
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
    await withAbort(signal => $api(`/templates/aidat/${templateId.value}`, { method: 'DELETE', signal }))
    await router.push(listRoute)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Aidat şablonu silinemedi.')
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
            Aidat Şablonu Düzenle
          </h4>
          <p class="text-medium-emphasis mb-0">
            Şablon bilgilerini güncelleyin
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            :to="listRoute"
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

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.scope"
                  :items="scopeOptions"
                  item-title="label"
                  item-value="value"
                  :label="$t('common.scope')"
                  :rules="scopeRules"
                  :error-messages="fieldErrors.scope ?? []"
                />
              </VCol>

              <VCol
                v-if="form.scope === 'selected'"
                cols="12"
              >
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
                  :error-messages="fieldErrors.apartment_ids ?? []"
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
                    {{ $t('common.cancel') }}
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
