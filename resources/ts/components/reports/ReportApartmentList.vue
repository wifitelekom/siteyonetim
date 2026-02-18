<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency } from '@/utils/formatters'

interface ApartmentListResponse {
  data: {
    apartments: Array<{
      full_label: string
      block: string | null
      floor: number
      number: string
      m2: number | null
      arsa_payi: number | null
      group: string | null
      owner: string | null
      tenant: string | null
      resident_count: number
      balance: number
      is_active: boolean
    }>
    total_count: number
    active_count: number
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<ApartmentListResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ApartmentListResponse>('/reports/apartment-list', { signal }))
    report.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Daire listesi raporu alinamadi.')
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <div class="d-flex justify-end mb-4">
      <VBtn
        color="primary"
        :loading="loading"
        @click="loadReport"
      >
        {{ $t('common.reportFetch') }}
      </VBtn>
    </div>

    <VAlert
      v-if="errorMessage"
      type="error"
      variant="tonal"
      class="mb-4"
    >
      {{ errorMessage }}
    </VAlert>

    <template v-if="report">
      <div class="d-flex gap-4 flex-wrap align-center mb-4">
        <VChip variant="tonal">
          Toplam: {{ report.total_count }}
        </VChip>
        <VChip color="success" variant="tonal">
          Aktif: {{ report.active_count }}
        </VChip>
      </div>

      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Daire</th>
            <th>Grup</th>
            <th>Kat Maliki</th>
            <th>Kiraci</th>
            <th class="text-right">
              m2
            </th>
            <th class="text-right">
              Bakiye
            </th>
            <th>Durum</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, i) in report.apartments"
            :key="i"
          >
            <td class="font-weight-medium">
              {{ row.full_label }}
            </td>
            <td>{{ row.group ?? '-' }}</td>
            <td>{{ row.owner ?? '-' }}</td>
            <td>{{ row.tenant ?? '-' }}</td>
            <td class="text-right">
              {{ row.m2 ?? '-' }}
            </td>
            <td
              class="text-right font-weight-medium"
              :class="row.balance > 0 ? 'text-error' : row.balance < 0 ? 'text-success' : ''"
            >
              {{ formatCurrency(row.balance) }}
            </td>
            <td>
              <VChip
                size="small"
                :color="row.is_active ? 'success' : 'secondary'"
                variant="tonal"
              >
                {{ row.is_active ? 'Aktif' : 'Pasif' }}
              </VChip>
            </td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
