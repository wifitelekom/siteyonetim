<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import type { OptionItem, PaginationMeta } from '@/types/api'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { chargeStatusColor as statusColor, chargeStatusLabel as statusLabel } from '@/utils/statusHelpers'

interface ChargeItem {
  id: number
  period: string
  due_date: string
  amount: number
  paid_amount: number
  remaining: number
  description: string | null
  status: 'open' | 'paid' | 'overdue'
  charge_type: 'aidat' | 'other'
  apartment: OptionItem | null
  account: { id: number; name: string } | null
}

interface ChargesResponse {
  data: ChargeItem[]
  meta: PaginationMeta
  filters: {
    status: Array<{ value: string; label: string }>
  }
}

interface ChargesMetaResponse {
  data: {
    apartments: OptionItem[]
    accounts: OptionItem[]
    charge_types: Array<{ value: 'aidat' | 'other'; label: string }>
  }
}

const loading = ref(false)
const loadingMeta = ref(false)
const errorMessage = ref('')

const charges = ref<ChargeItem[]>([])
const apartments = ref<OptionItem[]>([])
const statusOptions = ref<Array<{ value: string; label: string }>>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  period: '',
  apartment_id: null as number | null,
  status: null as string | null,
  search: '',
})

const { withAbort } = useAbortOnUnmount()





const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await withAbort(signal => $api<ChargesMetaResponse>('/charges/meta', { signal }))
    apartments.value = response.data.apartments
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Filtre verileri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchCharges = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ChargesResponse>('/charges', {
      query: {
        page,
        period: filters.value.period || undefined,
        apartment_id: filters.value.apartment_id || undefined,
        status: filters.value.status || undefined,
        search: filters.value.search || undefined,
      },
      signal,
    }))

    charges.value = response.data
    pagination.value = response.meta
    statusOptions.value = response.filters.status
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Tahakkuk listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchCharges(1)
}

const resetFilters = async () => {
  filters.value = {
    period: '',
    apartment_id: null,
    status: null,
    search: '',
  }

  await fetchCharges(1)
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchCharges(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Tahakkuklar
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.charges.indexSubtitle') }}
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            prepend-icon="ri-stack-line"
            to="/charges/create-bulk"
          >
            Toplu Tahakkuk
          </VBtn>
          <VBtn
            color="primary"
            prepend-icon="ri-add-line"
            to="/charges/create"
          >
            Yeni Tahakkuk
          </VBtn>
        </div>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta">
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model="filters.period"
                type="month"
                :label="$t('common.period')"
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.apartment_id"
                :items="apartments"
                item-title="label"
                item-value="id"
                :label="$t('common.apartment')"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.status"
                :items="statusOptions"
                item-title="label"
                item-value="value"
                :label="$t('common.status')"
                clearable
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model="filters.search"
                :label="$t('common.search')"
                :placeholder="$t('common.searchDescriptionOrApartment')"
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
              <th>{{ $t('common.apartment') }}</th>
              <th>{{ $t('common.periodAndDue') }}</th>
              <th class="text-right">
                Tutar
              </th>
              <th class="text-right">
                {{ $t('common.paid') }}
              </th>
              <th class="text-right">
                Kalan
              </th>
              <th>{{ $t('common.status') }}</th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="charge in charges"
              :key="charge.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ charge.apartment?.label ?? '-' }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ charge.account?.name ?? '-' }}
                </div>
              </td>
              <td>
                <div>{{ charge.period }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ formatDate(charge.due_date) }}
                </div>
              </td>
              <td class="text-right">
                {{ formatCurrency(charge.amount) }}
              </td>
              <td class="text-right">
                {{ formatCurrency(charge.paid_amount) }}
              </td>
              <td class="text-right">
                {{ formatCurrency(charge.remaining) }}
              </td>
              <td>
                <VChip
                  size="small"
                  :color="statusColor(charge.status)"
                  variant="tonal"
                >
                  {{ statusLabel(charge.status) }}
                </VChip>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/charges/${charge.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="charges.length === 0">
              <td
                colspan="7"
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
            @update:model-value="fetchCharges"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

