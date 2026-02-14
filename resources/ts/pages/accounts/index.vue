<script setup lang="ts">
import type { PaginationMeta } from '@/types/api'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { requiredRule } from '@/utils/validators'

const { withAbort } = useAbortOnUnmount()

interface AccountItem {
  id: number
  code: string
  name: string
  type: 'income' | 'expense' | 'asset' | 'liability'
  type_label: string
  is_active: boolean
  full_name: string
}

interface AccountsResponse {
  data: AccountItem[]
  meta: PaginationMeta
}

interface AccountsMetaResponse {
  data: {
    types: Array<{ value: 'income' | 'expense' | 'asset' | 'liability'; label: string }>
  }
}

const loading = ref(false)
const loadingMeta = ref(false)
const submitting = ref(false)
const deletingId = ref<number | null>(null)

const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const accounts = ref<AccountItem[]>([])
const typeOptions = ref<Array<{ value: 'income' | 'expense' | 'asset' | 'liability'; label: string }>>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const filters = ref({
  type: null as string | null,
  search: '',
})

const dialogOpen = ref(false)
const isEdit = ref(false)
const currentId = ref<number | null>(null)
const form = ref({
  code: '',
  name: '',
  type: 'income' as 'income' | 'expense' | 'asset' | 'liability',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const codeRules = [requiredRule()]
const nameRules = [requiredRule()]
const typeRules = [requiredRule()]

const typeColor = (type: string) => {
  if (type === 'income')
    return 'success'

  if (type === 'expense')
    return 'error'

  if (type === 'asset')
    return 'primary'

  return 'warning'
}

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await $api<AccountsMetaResponse>('/accounts/meta')
    typeOptions.value = response.data.types
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hesap turleri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchAccounts = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<AccountsResponse>('/accounts', {
      query: {
        page,
        type: filters.value.type || undefined,
        search: filters.value.search || undefined,
      },
    })

    accounts.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hesaplar alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const resetForm = () => {
  isEdit.value = false
  currentId.value = null
  form.value = {
    code: '',
    name: '',
    type: 'income',
  }
  fieldErrors.value = {}
}

const openCreate = () => {
  resetForm()
  dialogOpen.value = true
}

const openEdit = (account: AccountItem) => {
  isEdit.value = true
  currentId.value = account.id
  form.value = {
    code: account.code,
    name: account.name,
    type: account.type,
  }
  fieldErrors.value = {}
  dialogOpen.value = true
}

const submitAccount = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  submitting.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    if (isEdit.value && currentId.value) {
      await $api(`/accounts/${currentId.value}`, {
        method: 'PUT',
        body: form.value,
      })
    }
    else {
      await $api('/accounts', {
        method: 'POST',
        body: form.value,
      })
    }

    dialogOpen.value = false
    await fetchAccounts(pagination.value.current_page)
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hesap kaydedilemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    submitting.value = false
  }
}

const deleteAccount = async (account: AccountItem) => {
  deletingId.value = account.id
  errorMessage.value = ''

  try {
    await $api(`/accounts/${account.id}`, { method: 'DELETE' })
    await fetchAccounts(pagination.value.current_page)
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hesap silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

const applyFilters = async () => {
  await fetchAccounts(1)
}

const resetFilters = async () => {
  filters.value = {
    type: null,
    search: '',
  }

  await fetchAccounts(1)
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchAccounts(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Hesaplar
          </h4>
          <p class="text-medium-emphasis mb-0">
            Gelir, gider ve diger hesap plani
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
                :label="$t('common.type')"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="8"
            >
              <VTextField
                v-model="filters.search"
                :label="$t('common.search')"
                placeholder="Kod veya hesap adi"
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex gap-3 justify-end">
                <VBtn
                  variant="outlined"
                  @click="resetFilters"
                >
                  {{ $t('common.clear') }}
                </VBtn>
                <VBtn
                  color="primary"
                  @click="applyFilters"
                >
                  {{ $t('common.filter') }}
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
              <th>{{ $t('common.code') }}</th>
              <th>{{ $t('common.accountName') }}</th>
              <th>{{ $t('common.type') }}</th>
              <th class="text-right">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="account in accounts"
              :key="account.id"
            >
              <td><code>{{ account.code }}</code></td>
              <td>{{ account.name }}</td>
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
                  @click="deleteAccount(account)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="accounts.length === 0">
              <td
                colspan="4"
                class="text-center text-medium-emphasis py-6"
              >
                {{ $t('common.noRecords') }}
              </td>
            </tr>
          </tbody>
        </VTable>

        <VCardText class="d-flex justify-space-between align-center flex-wrap gap-3">
          <span class="text-sm text-medium-emphasis">{{ $t('common.totalRecords', { count: pagination.total }) }}</span>

          <VPagination
            :model-value="pagination.current_page"
            :length="pagination.last_page"
            :total-visible="7"
            @update:model-value="fetchAccounts"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VDialog
      v-model="dialogOpen"
      max-width="560"
    >
      <VCard :title="isEdit ? 'Hesap Düzenle' : 'Yeni Hesap'">
        <VCardText>
          <VForm
            ref="formRef"
            @submit.prevent="submitAccount"
          >
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.code"
                  :label="$t('common.accountCode')"
                  :rules="codeRules"
                  :error-messages="fieldErrors.code ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  :label="$t('common.accountName')"
                  :rules="nameRules"
                  :error-messages="fieldErrors.name ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="form.type"
                  :items="typeOptions"
                  item-title="label"
                  item-value="value"
                  :label="$t('common.type')"
                  :rules="typeRules"
                  :error-messages="fieldErrors.type ?? []"
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
            {{ $t('common.cancel') }}
          </VBtn>
          <VBtn
            color="primary"
            :loading="submitting"
            :disabled="submitting"
            @click="submitAccount"
          >
            {{ $t('common.save') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VRow>
</template>

