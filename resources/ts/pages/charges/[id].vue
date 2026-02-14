<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { chargeStatusColor as statusColor, chargeStatusLabel as statusLabel } from '@/utils/statusHelpers'
import { positiveNumberRule, requiredRule } from '@/utils/validators'

interface ChargeDetail {
  id: number
  period: string
  due_date: string
  amount: number
  paid_amount: number
  remaining: number
  description: string | null
  status: 'open' | 'paid' | 'overdue'
  charge_type: 'aidat' | 'other'
  apartment: { id: number; label: string } | null
  account: { id: number; name: string } | null
  creator: { id: number; name: string } | null
  receipt_items: Array<{
    id: number
    amount: number
    receipt: {
      id: number
      receipt_no: string
      paid_at: string
      method: string
      description: string | null
      cash_account: { id: number; name: string } | null
    } | null
  }>
}

interface ChargeShowResponse {
  data: ChargeDetail
  meta: {
    cash_accounts: Array<{ id: number; name: string; type: string; balance: number }>
    payment_methods: Array<{ value: 'cash' | 'bank'; label: string }>
  }
}

const { withAbort } = useAbortOnUnmount()
const route = useRoute()
const router = useRouter()

const chargeId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const collecting = ref(false)
const deleting = ref(false)
const errorMessage = ref('')

const detail = ref<ChargeDetail | null>(null)
const cashAccounts = ref<Array<{ id: number; name: string; type: string; balance: number }>>([])
const paymentMethods = ref<Array<{ value: 'cash' | 'bank'; label: string }>>([])

const collectDialog = ref(false)
const collectErrors = ref<Record<string, string[]>>({})
const collectForm = ref({
  paid_at: new Date().toISOString().slice(0, 10),
  method: 'cash' as 'cash' | 'bank',
  cash_account_id: null as number | null,
  amount: null as number | null,
  description: '',
})
const collectFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const paidAtRules = [requiredRule()]
const methodRules = [requiredRule()]
const cashAccountRules = [requiredRule()]
const amountRules = [requiredRule(), positiveNumberRule()]



const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ChargeShowResponse>(`/charges/${chargeId.value}`, { signal }))
    detail.value = response.data
    cashAccounts.value = response.meta.cash_accounts
    paymentMethods.value = response.meta.payment_methods
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tahakkuk detayı alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const openCollectDialog = () => {
  collectErrors.value = {}
  collectForm.value.amount = detail.value?.remaining ?? null
  collectDialog.value = true
}

const submitCollect = async () => {
  const validation = await collectFormRef.value?.validate()
  if (!validation?.valid)
    return

  collecting.value = true
  errorMessage.value = ''
  collectErrors.value = {}

  try {
    await withAbort(signal => $api(`/charges/${chargeId.value}/collect`, {
      method: 'POST',
      body: {
        paid_at: collectForm.value.paid_at,
        method: collectForm.value.method,
        cash_account_id: collectForm.value.cash_account_id,
        amount: collectForm.value.amount,
        description: collectForm.value.description || null,
      },
      signal,
    }))

    collectDialog.value = false
    await fetchDetail()
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tahsilat işlemi başarısız.')
    collectErrors.value = getApiFieldErrors(error)
  }
  finally {
    collecting.value = false
  }
}

const deleteCharge = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/charges/${chargeId.value}`, { method: 'DELETE', signal }))
    await router.push('/charges')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tahakkuk silinemedi.')
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
            {{ $t('pages.charges.detailTitle') }}
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ detail?.apartment?.label ?? '-' }}
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/charges"
          >
            Listeye Don
          </VBtn>

          <VBtn
            color="success"
            prepend-icon="ri-hand-coin-line"
            :disabled="!detail || detail.remaining <= 0"
            @click="openCollectDialog"
          >
            Tahsilat Al
          </VBtn>

          <VBtn
            color="error"
            variant="outlined"
            prepend-icon="ri-delete-bin-line"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteCharge"
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
                Dönem
              </div>
              <div class="font-weight-medium">
                {{ detail.period }}
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
              <div class="text-caption text-medium-emphasis">{{ $t('common.amount') }}</div>
              <div class="text-h6">
                {{ formatCurrency(detail.amount) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">{{ $t('common.paid') }}</div>
              <div class="text-h6 text-success">
                {{ formatCurrency(detail.paid_amount) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">{{ $t('common.remaining') }}</div>
              <div class="text-h6 text-error">
                {{ formatCurrency(detail.remaining) }}
              </div>
            </VCol>

            <VCol cols="12">
              <div class="text-caption text-medium-emphasis">{{ $t('common.description') }}</div>
              <div>{{ detail.description || '-' }}</div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VCardItem title="Tahsilat Hareketleri" />

        <VTable density="comfortable">
          <thead>
            <tr>
              <th>{{ $t('common.receiptNo') }}</th>
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
              v-for="item in detail?.receipt_items ?? []"
              :key="item.id"
            >
              <td>{{ item.receipt?.receipt_no ?? '-' }}</td>
              <td>{{ formatDate(item.receipt?.paid_at ?? '') }}</td>
              <td>{{ item.receipt?.method ?? '-' }}</td>
              <td>{{ item.receipt?.cash_account?.name ?? '-' }}</td>
              <td class="text-right">
                {{ formatCurrency(item.amount) }}
              </td>
            </tr>
            <tr v-if="(detail?.receipt_items ?? []).length === 0">
              <td
                colspan="5"
                class="text-center text-medium-emphasis py-6"
              >
                {{ $t('common.noCollectionRecords') }}
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>

    <VDialog
      v-model="collectDialog"
      max-width="560"
    >
      <VCard title="Tahsilat Al">
        <VCardText>
          <VForm
            ref="collectFormRef"
            @submit.prevent="submitCollect"
          >
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="collectForm.paid_at"
                  type="date"
                  :label="$t('common.collectionDate')"
                  :rules="paidAtRules"
                  :error-messages="collectErrors.paid_at ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="collectForm.method"
                  :items="paymentMethods"
                  item-title="label"
                  item-value="value"
                  :label="$t('common.method')"
                  :rules="methodRules"
                  :error-messages="collectErrors.method ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="collectForm.cash_account_id"
                  :items="cashAccounts"
                  item-title="name"
                  item-value="id"
                  :label="$t('common.cashAccount')"
                  :rules="cashAccountRules"
                  :error-messages="collectErrors.cash_account_id ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="collectForm.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  :label="$t('common.amount')"
                  :rules="amountRules"
                  :error-messages="collectErrors.amount ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="collectForm.description"
                  :label="$t('common.description')"
                  rows="2"
                  :error-messages="collectErrors.description ?? []"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="collectDialog = false"
          >
            {{ $t('common.cancel') }}
          </VBtn>
          <VBtn
            color="primary"
            type="submit"
            :loading="collecting"
            :disabled="collecting"
          >
            Tahsil Et
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VRow>
</template>

