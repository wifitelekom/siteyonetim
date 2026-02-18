<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'

interface HouseholdResponse {
  data: {
    apartments: Array<{
      full_label: string
      residents: Array<{
        name: string
        phone: string | null
        email: string | null
        relation_type: string | null
        relation_label: string
        family_role: string | null
      }>
    }>
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<HouseholdResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<HouseholdResponse>('/reports/household-info', { signal }))
    report.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Hane bilgileri raporu alinamadi.')
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
          Toplam Daire: {{ report.apartments.length }}
        </VChip>
      </div>

      <div
        v-for="(apt, i) in report.apartments"
        :key="i"
        class="mb-4"
      >
        <div class="font-weight-medium text-body-1 mb-2">
          {{ apt.full_label }}
        </div>
        <VTable
          v-if="apt.residents.length"
          density="compact"
        >
          <thead>
            <tr>
              <th>Ad Soyad</th>
              <th>Telefon</th>
              <th>E-posta</th>
              <th>Iliski</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(res, j) in apt.residents"
              :key="j"
            >
              <td>{{ res.name }}</td>
              <td>{{ res.phone ?? '-' }}</td>
              <td>{{ res.email ?? '-' }}</td>
              <td>
                <VChip
                  size="small"
                  :color="res.relation_type === 'owner' ? 'primary' : 'warning'"
                  variant="tonal"
                >
                  {{ res.relation_label }}
                </VChip>
              </td>
            </tr>
          </tbody>
        </VTable>
        <div
          v-else
          class="text-medium-emphasis text-body-2 mb-2"
        >
          Sakin kaydi yok.
        </div>
        <VDivider class="mt-2" />
      </div>
    </template>
  </div>
</template>
