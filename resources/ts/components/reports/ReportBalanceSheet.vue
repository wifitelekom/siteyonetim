<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency } from '@/utils/formatters'

interface BalanceSheetResponse {
  data: {
    from: string
    to: string
    income: {
      total_charges: number
      total_collected: number
      by_account: Array<{ account: string; total: number }>
    }
    expense: {
      total_expenses: number
      total_paid: number
      by_account: Array<{ account: string; total: number }>
    }
    net: number
  }
}

const loading = ref(false)
const errorMessage = ref('')
const report = ref<BalanceSheetResponse['data'] | null>(null)
const { withAbort } = useAbortOnUnmount()

const filters = ref({
  from: new Date(new Date().getFullYear(), 0, 1).toISOString().slice(0, 10),
  to: new Date().toISOString().slice(0, 10),
})

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<BalanceSheetResponse>('/reports/balance-sheet', {
      query: { from: filters.value.from, to: filters.value.to },
      signal,
    }))
    report.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Bilanco raporu alinamadi.')
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <VRow class="mb-4">
      <VCol
        cols="12"
        md="4"
      >
        <VTextField
          v-model="filters.from"
          type="date"
          label="Baslangic"
        />
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VTextField
          v-model="filters.to"
          type="date"
          label="Bitis"
        />
      </VCol>
      <VCol
        cols="12"
        md="4"
        class="d-flex align-end"
      >
        <VBtn
          color="primary"
          block
          :loading="loading"
          @click="loadReport"
        >
          {{ $t('common.reportFetch') }}
        </VBtn>
      </VCol>
    </VRow>

    <VAlert
      v-if="errorMessage"
      type="error"
      variant="tonal"
      class="mb-4"
    >
      {{ errorMessage }}
    </VAlert>

    <template v-if="report">
      <VRow class="mb-4">
        <VCol
          cols="12"
          md="4"
        >
          <VCard variant="tonal" color="success">
            <VCardText>
              <div class="text-overline">
                TOPLAM TAHSILAT
              </div>
              <div class="text-h5">
                {{ formatCurrency(report.income.total_collected) }}
              </div>
              <div class="text-caption text-medium-emphasis">
                Tahakkuk: {{ formatCurrency(report.income.total_charges) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <VCard variant="tonal" color="error">
            <VCardText>
              <div class="text-overline">
                TOPLAM ODEME
              </div>
              <div class="text-h5">
                {{ formatCurrency(report.expense.total_paid) }}
              </div>
              <div class="text-caption text-medium-emphasis">
                Gider: {{ formatCurrency(report.expense.total_expenses) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="4"
        >
          <VCard variant="tonal" :color="report.net >= 0 ? 'success' : 'error'">
            <VCardText>
              <div class="text-overline">
                NET DURUM
              </div>
              <div class="text-h5">
                {{ formatCurrency(report.net) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VRow>
        <VCol
          cols="12"
          md="6"
        >
          <h6 class="text-h6 mb-2">
            Gelir Dagilimi
          </h6>
          <VTable density="compact">
            <thead>
              <tr>
                <th>Hesap</th>
                <th class="text-right">
                  Tutar
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in report.income.by_account"
                :key="row.account"
              >
                <td>{{ row.account }}</td>
                <td class="text-right text-success font-weight-medium">
                  {{ formatCurrency(row.total) }}
                </td>
              </tr>
              <tr v-if="report.income.by_account.length === 0">
                <td
                  colspan="2"
                  class="text-center text-medium-emphasis"
                >
                  Kayit yok
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <h6 class="text-h6 mb-2">
            Gider Dagilimi
          </h6>
          <VTable density="compact">
            <thead>
              <tr>
                <th>Hesap</th>
                <th class="text-right">
                  Tutar
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in report.expense.by_account"
                :key="row.account"
              >
                <td>{{ row.account }}</td>
                <td class="text-right text-error font-weight-medium">
                  {{ formatCurrency(row.total) }}
                </td>
              </tr>
              <tr v-if="report.expense.by_account.length === 0">
                <td
                  colspan="2"
                  class="text-center text-medium-emphasis"
                >
                  Kayit yok
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCol>
      </VRow>
    </template>
  </div>
</template>
