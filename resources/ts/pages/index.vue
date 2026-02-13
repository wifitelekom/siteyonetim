<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'

interface DashboardSummary {
  receivables: {
    not_due: number
    due_today: number
    overdue: number
    total: number
    count: number
  } | null
  payables: {
    not_due: number
    due_today: number
    overdue: number
    total: number
    vendor_count: number
  } | null
  totalCash: number | null
  cashAccounts: Array<{ id: number; name: string; type: string; balance: number }> | null
  recentTransactions: Array<{
    date: string
    type: 'receipt' | 'payment'
    description: string
    amount: number
    receipt_no: string | null
  }>
  monthlyReceiptCount: number | null
  aidatTemplates: number | null
  aidatTemplatesTotal: number | null
  expenseTemplates: number | null
  expenseTemplatesTotal: number | null
  timeline: Array<{
    date: string
    date_display: string
    type: 'receivable' | 'payable' | 'past_receipt'
    title: string
    subtitle: string
    amount: string
    dot_class: string
  }> | null
  monthlyTrend: Array<{
    month: string
    income: number
    expense: number
  }> | null
  collectionRate: number | null
}

interface DashboardResponse {
  data: DashboardSummary
}

const loading = ref(true)
const errorMessage = ref('')
const summary = ref<DashboardSummary | null>(null)

const { withAbort } = useAbortOnUnmount()

const fetchDashboard = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<DashboardResponse>('/dashboard', { signal }))
    summary.value = response.data
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Panel verileri alinamadi.')
  }
  finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">
          Genel Bakis
        </h4>
        <p class="text-body-1 text-medium-emphasis mb-0">
          Tahsilat, gider ve nakit ozetleri
        </p>
      </div>

      <VBtn
        color="primary"
        variant="elevated"
        prepend-icon="ri-refresh-line"
        :loading="loading"
        @click="fetchDashboard"
      >
        Yenile
      </VBtn>
    </div>

    <!-- Error Alert -->
    <VAlert
      v-if="errorMessage"
      type="error"
      variant="tonal"
      class="mb-6"
    >
      {{ errorMessage }}
    </VAlert>

    <!-- Skeleton loader -->
    <div v-if="loading && !summary">
      <VRow>
        <VCol
          v-for="n in 4"
          :key="n"
          cols="12"
          sm="6"
          lg="3"
        >
          <VSkeletonLoader type="card" />
        </VCol>
      </VRow>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="summary">
      <!-- Row 1: Statistics Cards -->
      <VRow class="mb-2">
        <VCol
          v-if="summary.receivables"
          cols="12"
          sm="6"
          lg="3"
        >
          <DashboardStatCard
            title="Toplam Alacak"
            :value="formatCurrency(summary.receivables.total)"
            :subtitle="`${summary.receivables.count} acik tahakkuk`"
            icon="ri-money-dollar-circle-line"
            color="success"
          />
        </VCol>

        <VCol
          v-if="summary.payables"
          cols="12"
          sm="6"
          lg="3"
        >
          <DashboardStatCard
            title="Toplam Borc"
            :value="formatCurrency(summary.payables.total)"
            :subtitle="`${summary.payables.vendor_count} tedarikci`"
            icon="ri-shopping-cart-2-line"
            color="error"
          />
        </VCol>

        <VCol
          v-if="summary.totalCash != null"
          cols="12"
          sm="6"
          lg="3"
        >
          <DashboardStatCard
            title="Toplam Nakit"
            :value="formatCurrency(summary.totalCash)"
            :subtitle="`${summary.cashAccounts?.length ?? 0} hesap`"
            icon="ri-wallet-3-line"
            color="primary"
          />
        </VCol>

        <VCol
          v-if="summary.monthlyReceiptCount != null"
          cols="12"
          sm="6"
          lg="3"
        >
          <DashboardStatCard
            title="Bu Ay Tahsilat"
            :value="`${summary.monthlyReceiptCount}`"
            subtitle="Tahsilat islemi"
            icon="ri-bar-chart-grouped-line"
            color="warning"
          />
        </VCol>
      </VRow>

      <!-- Row 2: Charts (admin only) -->
      <VRow
        v-if="summary.monthlyTrend && summary.collectionRate != null"
        class="mb-2"
      >
        <VCol
          cols="12"
          lg="8"
        >
          <DashboardMonthlyTrend :data="summary.monthlyTrend" />
        </VCol>

        <VCol
          cols="12"
          lg="4"
        >
          <DashboardCollectionRate :rate="summary.collectionRate" />
        </VCol>
      </VRow>

      <!-- Row 3: Donut + Timeline + Cash -->
      <VRow class="mb-2">
        <VCol
          v-if="summary.receivables"
          cols="12"
          md="4"
        >
          <DashboardReceivablesChart
            :not-due="summary.receivables.not_due"
            :due-today="summary.receivables.due_today"
            :overdue="summary.receivables.overdue"
            :total="summary.receivables.total"
          />
        </VCol>

        <VCol
          v-if="summary.timeline"
          cols="12"
          md="4"
        >
          <DashboardTimeline :items="summary.timeline" />
        </VCol>

        <VCol
          v-if="summary.cashAccounts"
          cols="12"
          md="4"
        >
          <DashboardCashAccounts
            :accounts="summary.cashAccounts"
            :total-cash="summary.totalCash ?? 0"
          />
        </VCol>
      </VRow>

      <!-- Row 4: Payables Summary + Template Stats -->
      <VRow
        v-if="summary.payables || summary.aidatTemplates != null"
        class="mb-2"
      >
        <VCol
          v-if="summary.payables"
          cols="12"
          md="6"
        >
          <VCard>
            <VCardItem>
              <VCardTitle>Borc Durumu</VCardTitle>
              <VCardSubtitle>Vade bazli borc dagilimi</VCardSubtitle>
            </VCardItem>

            <VCardText>
              <div class="d-flex flex-column gap-4">
                <div class="d-flex align-center justify-space-between">
                  <div class="d-flex align-center gap-3">
                    <VAvatar
                      color="info"
                      variant="tonal"
                      size="40"
                      rounded
                    >
                      <VIcon
                        icon="ri-time-line"
                        size="22"
                      />
                    </VAvatar>
                    <div>
                      <p class="text-body-1 font-weight-medium mb-0">
                        Vadesi Gelmemis
                      </p>
                      <span class="text-caption text-medium-emphasis">Henuz vadesi gelmemis borclar</span>
                    </div>
                  </div>
                  <span class="text-body-1 font-weight-bold">{{ formatCurrency(summary.payables.not_due) }}</span>
                </div>

                <VDivider />

                <div class="d-flex align-center justify-space-between">
                  <div class="d-flex align-center gap-3">
                    <VAvatar
                      color="warning"
                      variant="tonal"
                      size="40"
                      rounded
                    >
                      <VIcon
                        icon="ri-alarm-line"
                        size="22"
                      />
                    </VAvatar>
                    <div>
                      <p class="text-body-1 font-weight-medium mb-0">
                        Bugun Vadeli
                      </p>
                      <span class="text-caption text-medium-emphasis">Bugun odemesi gereken borclar</span>
                    </div>
                  </div>
                  <span class="text-body-1 font-weight-bold">{{ formatCurrency(summary.payables.due_today) }}</span>
                </div>

                <VDivider />

                <div class="d-flex align-center justify-space-between">
                  <div class="d-flex align-center gap-3">
                    <VAvatar
                      color="error"
                      variant="tonal"
                      size="40"
                      rounded
                    >
                      <VIcon
                        icon="ri-error-warning-line"
                        size="22"
                      />
                    </VAvatar>
                    <div>
                      <p class="text-body-1 font-weight-medium mb-0">
                        Gecikmis
                      </p>
                      <span class="text-caption text-medium-emphasis">Vadesi gecmis odenecek borclar</span>
                    </div>
                  </div>
                  <span class="text-body-1 font-weight-bold text-error">
                    {{ formatCurrency(summary.payables.overdue) }}
                  </span>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <VCol
          v-if="summary.aidatTemplates != null"
          cols="12"
          md="6"
        >
          <VCard>
            <VCardItem>
              <VCardTitle>Sablon Durumu</VCardTitle>
              <VCardSubtitle>Aidat ve gider sablonlari</VCardSubtitle>
            </VCardItem>

            <VCardText>
              <div class="d-flex flex-column gap-5">
                <div>
                  <div class="d-flex align-center justify-space-between mb-2">
                    <div class="d-flex align-center gap-3">
                      <VAvatar
                        color="primary"
                        variant="tonal"
                        size="40"
                        rounded
                      >
                        <VIcon
                          icon="ri-file-list-3-line"
                          size="22"
                        />
                      </VAvatar>
                      <div>
                        <p class="text-body-1 font-weight-medium mb-0">
                          Aidat Sablonlari
                        </p>
                        <span class="text-caption text-medium-emphasis">{{ summary.aidatTemplates }} aktif / {{ summary.aidatTemplatesTotal }} toplam</span>
                      </div>
                    </div>
                    <VChip
                      color="primary"
                      variant="tonal"
                      size="small"
                    >
                      {{ summary.aidatTemplates }}
                    </VChip>
                  </div>
                  <VProgressLinear
                    :model-value="summary.aidatTemplatesTotal > 0 ? (summary.aidatTemplates / summary.aidatTemplatesTotal) * 100 : 0"
                    color="primary"
                    rounded
                    height="6"
                  />
                </div>

                <div>
                  <div class="d-flex align-center justify-space-between mb-2">
                    <div class="d-flex align-center gap-3">
                      <VAvatar
                        color="warning"
                        variant="tonal"
                        size="40"
                        rounded
                      >
                        <VIcon
                          icon="ri-receipt-line"
                          size="22"
                        />
                      </VAvatar>
                      <div>
                        <p class="text-body-1 font-weight-medium mb-0">
                          Gider Sablonlari
                        </p>
                        <span class="text-caption text-medium-emphasis">{{ summary.expenseTemplates }} aktif / {{ summary.expenseTemplatesTotal }} toplam</span>
                      </div>
                    </div>
                    <VChip
                      color="warning"
                      variant="tonal"
                      size="small"
                    >
                      {{ summary.expenseTemplates }}
                    </VChip>
                  </div>
                  <VProgressLinear
                    :model-value="summary.expenseTemplatesTotal > 0 ? (summary.expenseTemplates / summary.expenseTemplatesTotal) * 100 : 0"
                    color="warning"
                    rounded
                    height="6"
                  />
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- Row 5: Recent Transactions -->
      <VRow>
        <VCol cols="12">
          <VCard>
            <VCardItem>
              <VCardTitle>Son Hareketler</VCardTitle>
              <VCardSubtitle>Son 10 tahsilat ve odeme islemi</VCardSubtitle>
            </VCardItem>

            <VTable density="comfortable">
              <thead>
                <tr>
                  <th>Tarih</th>
                  <th>Aciklama</th>
                  <th>Tip</th>
                  <th class="text-right">
                    Tutar
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="transaction in summary.recentTransactions"
                  :key="`${transaction.date}-${transaction.type}-${transaction.amount}`"
                >
                  <td>
                    <span class="text-body-2">{{ formatDate(transaction.date) }}</span>
                  </td>
                  <td>
                    <span class="text-body-2">{{ transaction.description }}</span>
                  </td>
                  <td>
                    <VChip
                      size="small"
                      :color="transaction.type === 'receipt' ? 'success' : 'error'"
                      variant="tonal"
                    >
                      <VIcon
                        :icon="transaction.type === 'receipt' ? 'ri-arrow-down-line' : 'ri-arrow-up-line'"
                        start
                        size="14"
                      />
                      {{ transaction.type === 'receipt' ? 'Tahsilat' : 'Odeme' }}
                    </VChip>
                  </td>
                  <td class="text-right">
                    <span
                      class="font-weight-bold"
                      :class="transaction.type === 'receipt' ? 'text-success' : 'text-error'"
                    >
                      {{ transaction.type === 'receipt' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                    </span>
                  </td>
                </tr>
                <tr v-if="summary.recentTransactions.length === 0">
                  <td
                    colspan="4"
                    class="text-center text-medium-emphasis py-6"
                  >
                    Hareket bulunamadi.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>
    </div>
  </div>
</template>
