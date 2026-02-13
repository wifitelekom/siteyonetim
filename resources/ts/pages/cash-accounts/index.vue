<script setup lang="ts">
import type { PaginationMeta } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { formatCurrency } from '@/utils/formatters'
import { requiredRule } from '@/utils/validators'

const { withAbort } = useAbortOnUnmount()

interface CashAccountItem {
  id: number
  name: string
  type: 'cash' | 'bank'
  type_label: string
  opening_balance: number
  balance: number
  is_active: boolean
}

interface CashAccountsResponse {
  data: CashAccountItem[]
  meta: PaginationMeta
}

interface CashAccountsMetaResponse {
  data: {
    types: Array<{ value: 'cash' | 'bank'; label: string }>
  }
}

const loading = ref(false)
const loadingMeta = ref(false)
const submitting = ref(false)
const deletingId = ref<number | null>(null)

const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const cashAccounts = ref<CashAccountItem[]>([])
const typeOptions = ref<Array<{ value: 'cash' | 'bank'; label: string }>>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const filters = ref({
  type: null as 'cash' | 'bank' | null,
  search: '',
})

const dialogOpen = ref(false)
const isEdit = ref(false)
const currentId = ref<number | null>(null)
const form = ref({
  name: '',
  type: 'cash' as 'cash' | 'bank',
  opening_balance: 0,
  is_active: true,
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const nameRules = [requiredRule()]
const typeRules = [requiredRule()]
const openingBalanceRules = [
  (value: unknown) => {
    const parsed = Number(value)
    return Number.isFinite(parsed) && parsed >= 0 ? true : 'Sifir veya daha buyuk bir deger giriniz.'
  },
]


const typeColor = (type: string) => (type === 'cash' ? 'success' : 'primary')

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await $api<CashAccountsMetaResponse>('/cash-accounts/meta')
    typeOptions.value = response.data.types
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hesap turleri alinamadi.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchCashAccounts = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<CashAccountsResponse>('/cash-accounts', {
      query: {
        page,
        type: filters.value.type || undefined,
        search: filters.value.search || undefined,
      },
    })

    cashAccounts.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kasa/Banka hesaplari alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const resetForm = () => {
  isEdit.value = false
  currentId.value = null
  form.value = {
    name: '',
    type: 'cash',
    opening_balance: 0,
    is_active: true,
  }
  fieldErrors.value = {}
}

const openCreate = () => {
  resetForm()
  dialogOpen.value = true
}

const openEdit = (account: CashAccountItem) => {
  isEdit.value = true
  currentId.value = account.id
  form.value = {
    name: account.name,
    type: account.type,
    opening_balance: Number(account.opening_balance),
    is_active: account.is_active,
  }
  fieldErrors.value = {}
  dialogOpen.value = true
}

const submitCashAccount = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  submitting.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    if (isEdit.value && currentId.value) {
      await $api(`/cash-accounts/${currentId.value}`, {
        method: 'PUT',
        body: form.value,
      })
    }
    else {
      await $api('/cash-accounts', {
        method: 'POST',
        body: form.value,
      })
    }

    dialogOpen.value = false
    await fetchCashAccounts(pagination.value.current_page)
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hesap kaydedilemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    submitting.value = false
  }
}

const deleteCashAccount = async (account: CashAccountItem) => {
  deletingId.value = account.id
  errorMessage.value = ''

  try {
    await $api(`/cash-accounts/${account.id}`, { method: 'DELETE' })
    await fetchCashAccounts(pagination.value.current_page)
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hesap silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

const applyFilters = async () => {
  await fetchCashAccounts(1)
}

const resetFilters = async () => {
  filters.value = {
    type: null,
    search: '',
  }

  await fetchCashAccounts(1)
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchCashAccounts(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Kasa/Banka
          </h4>
          <p class="text-medium-emphasis mb-0">
            Kasa ve banka hesaplarini yonetin
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          @click="openCreate"
        >
          Yeni Hesap
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta">
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <VSelect
                v-model="filters.type"
                :items="typeOptions"
                item-title="label"
                item-value="value"
                label="Tur"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="8"
            >
              <VTextField
                v-model="filters.search"
                label="Arama"
                placeholder="Hesap adi"
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex gap-3 justify-end">
                <VBtn
                  variant="outlined"
                  @click="resetFilters"
                >
                  Temizle
                </VBtn>
                <VBtn
                  color="primary"
                  @click="applyFilters"
                >
                  Filtrele
                </VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
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
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Hesap</th>
              <th>Tur</th>
              <th class="text-right">
                Acilis
              </th>
              <th class="text-right">
                Guncel Bakiye
              </th>
              <th>Durum</th>
              <th class="text-right">
                Islemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="account in cashAccounts"
              :key="account.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ account.name }}
                </div>
              </td>
              <td>
                <VChip
                  size="small"
                  :color="typeColor(account.type)"
                  variant="tonal"
                >
                  {{ account.type_label }}
                </VChip>
              </td>
              <td class="text-right">
                {{ formatCurrency(account.opening_balance) }}
              </td>
              <td class="text-right font-weight-medium">
                {{ formatCurrency(account.balance) }}
              </td>
              <td>
                <VChip
                  size="small"
                  :color="account.is_active ? 'success' : 'secondary'"
                  variant="tonal"
                >
                  {{ account.is_active ? 'Aktif' : 'Pasif' }}
                </VChip>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/cash-accounts/${account.id}/statement`"
                >
                  <VIcon icon="ri-file-chart-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  @click="openEdit(account)"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="deletingId === account.id"
                  :disabled="deletingId === account.id"
                  @click="deleteCashAccount(account)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="cashAccounts.length === 0">
              <td
                colspan="6"
                class="text-center text-medium-emphasis py-6"
              >
                Kayit bulunamadi.
              </td>
            </tr>
          </tbody>
        </VTable>

        <VCardText class="d-flex justify-space-between align-center flex-wrap gap-3">
          <span class="text-sm text-medium-emphasis">Toplam {{ pagination.total }} kayit</span>

          <VPagination
            :model-value="pagination.current_page"
            :length="pagination.last_page"
            :total-visible="7"
            @update:model-value="fetchCashAccounts"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VDialog
      v-model="dialogOpen"
      max-width="560"
    >
      <VCard :title="isEdit ? 'Hesap Duzenle' : 'Yeni Hesap'">
        <VCardText>
          <VForm
            ref="formRef"
            @submit.prevent="submitCashAccount"
          >
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  label="Hesap Adi"
                  :rules="nameRules"
                  :error-messages="fieldErrors.name ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="form.type"
                  :items="typeOptions"
                  item-title="label"
                  item-value="value"
                  label="Tur"
                  :rules="typeRules"
                  :error-messages="fieldErrors.type ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.opening_balance"
                  type="number"
                  min="0"
                  step="0.01"
                  label="Acilis Bakiyesi"
                  :rules="openingBalanceRules"
                  :error-messages="fieldErrors.opening_balance ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="form.is_active"
                  label="Aktif"
                  color="primary"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="dialogOpen = false"
          >
            Vazgec
          </VBtn>
          <VBtn
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            @click="submitCashAccount"
          >
            Kaydet
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VRow>
</template>
