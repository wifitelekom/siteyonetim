<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'

interface ReportOptionItem {
  id: number
  name: string
  code?: string
  full_name?: string
  type?: string
}

interface ReportsMetaResponse {
  data: {
    cash_accounts: ReportOptionItem[]
    accounts: ReportOptionItem[]
  }
}

const activePanels = ref<number[]>([0])
const loadingMeta = ref(false)
const errorMessage = ref('')

const cashAccounts = ref<ReportOptionItem[]>([])
const accounts = ref<ReportOptionItem[]>([])
const { withAbort } = useAbortOnUnmount()

const fetchMeta = async () => {
  loadingMeta.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ReportsMetaResponse>('/reports/meta', { signal }))
    cashAccounts.value = response.data.cash_accounts
    accounts.value = response.data.accounts
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Rapor metadatasi alinamadi.')
  }
  finally {
    loadingMeta.value = false
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
            Raporlar
          </h4>
          <p class="text-medium-emphasis mb-0">
            Finansal raporlarinizi tek ekrandan olusturun
          </p>
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
      <VExpansionPanels
        v-model="activePanels"
        multiple
      >
        <VExpansionPanel title="Kasa/Banka Ekstresi">
          <VExpansionPanelText>
            <ReportCashStatement
              :cash-accounts="cashAccounts"
              :meta-loading="loadingMeta"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="Hesap Ekstresi">
          <VExpansionPanelText>
            <ReportAccountStatement
              :accounts="accounts"
              :meta-loading="loadingMeta"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="Tahsilat Raporu">
          <VExpansionPanelText>
            <ReportCollections />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="Odeme Raporu">
          <VExpansionPanelText>
            <ReportPayments />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="Borc Durumu">
          <VExpansionPanelText>
            <ReportDebtStatus />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="Alacak Durumu">
          <VExpansionPanelText>
            <ReportReceivableStatus />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="Tahakkuk Listesi">
          <VExpansionPanelText>
            <ReportChargeList />
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCol>
  </VRow>
</template>
