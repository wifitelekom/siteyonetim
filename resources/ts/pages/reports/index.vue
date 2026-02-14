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
const { t } = useI18n({ useScope: 'global' })

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

    errorMessage.value = getApiErrorMessage(error, t('reports.page.errors.metaLoadFailed'))
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
            {{ $t('reports.page.title') }}
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('reports.page.subtitle') }}
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
        <VExpansionPanel :title="$t('reports.page.panels.cashStatement')">
          <VExpansionPanelText>
            <ReportCashStatement
              :cash-accounts="cashAccounts"
              :meta-loading="loadingMeta"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel :title="$t('reports.page.panels.accountStatement')">
          <VExpansionPanelText>
            <ReportAccountStatement
              :accounts="accounts"
              :meta-loading="loadingMeta"
            />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel :title="$t('reports.page.panels.collectionReport')">
          <VExpansionPanelText>
            <ReportCollections />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel :title="$t('reports.page.panels.paymentReport')">
          <VExpansionPanelText>
            <ReportPayments />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel :title="$t('reports.page.panels.debtStatus')">
          <VExpansionPanelText>
            <ReportDebtStatus />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel :title="$t('reports.page.panels.receivableStatus')">
          <VExpansionPanelText>
            <ReportReceivableStatus />
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel :title="$t('reports.page.panels.chargeList')">
          <VExpansionPanelText>
            <ReportChargeList />
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCol>
  </VRow>
</template>
