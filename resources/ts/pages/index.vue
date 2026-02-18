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
    id: number
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
    uid: string
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
  apartmentStats: {
    total: number
    active: number
    total_residents: number
  } | null
  dailyCashFlow: Array<{
    date: string
    income: number
    expense: number
  }> | null
}

interface DashboardResponse {
  data: DashboardSummary
}

const loading = ref(true)
const errorMessage = ref('')
const summary = ref<DashboardSummary | null>(null)
const { t } = useI18n({ useScope: 'global' })

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

    errorMessage.value = getApiErrorMessage(error, t('dashboard.errors.loadFailed'))
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
          {{ $t('dashboard.header.title') }}
        </h4>
        <p class="text-body-1 text-medium-emphasis mb-0">
          {{ $t('dashboard.header.subtitle') }}
        </p>
      </div>

      <VBtn
        color="primary"
        variant="elevated"
        prepend-icon="ri-refresh-line"
        :loading="loading"
        @click="fetchDashboard"
      >
        {{ $t('common.refresh') }}
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
            :title="$t('dashboard.stats.totalReceivable')"
            :value="formatCurrency(summary.receivables.total)"
            :subtitle="$t('dashboard.stats.openCharges', { count: summary.receivables.count })"
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
            :title="$t('dashboard.stats.totalDebt')"
            :value="formatCurrency(summary.payables.total)"
            :subtitle="$t('dashboard.stats.vendors', { count: summary.payables.vendor_count })"
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
            :title="$t('dashboard.stats.totalCash')"
            :value="formatCurrency(summary.totalCash)"
            :subtitle="$t('dashboard.stats.accounts', { count: summary.cashAccounts?.length ?? 0 })"
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
            :title="$t('dashboard.stats.monthlyCollection')"
            :value="`${summary.monthlyReceiptCount}`"
            :subtitle="$t('dashboard.stats.collectionTransaction')"
            icon="ri-bar-chart-grouped-line"
            color="warning"
          />
        </VCol>
      </VRow>

      <!-- Row 1b: Apartment Stats (admin only) -->
      <VRow
        v-if="summary.apartmentStats"
        class="mb-2"
      >
        <VCol
          cols="12"
          sm="4"
        >
          <DashboardStatCard
            title="Toplam Daire"
            :value="`${summary.apartmentStats.total}`"
            :subtitle="`${summary.apartmentStats.active} aktif`"
            icon="ri-home-line"
            color="info"
          />
        </VCol>
        <VCol
          cols="12"
          sm="4"
        >
          <DashboardStatCard
            title="Toplam Sakin"
            :value="`${summary.apartmentStats.total_residents}`"
            subtitle="Kayitli sakin sayisi"
            icon="ri-group-line"
            color="secondary"
          />
        </VCol>
        <VCol
          cols="12"
          sm="4"
        >
          <DashboardStatCard
            v-if="summary.collectionRate != null"
            title="Tahsilat Orani"
            :value="`%${summary.collectionRate}`"
            subtitle="Bu ay icin"
            icon="ri-percent-line"
            :color="summary.collectionRate >= 70 ? 'success' : summary.collectionRate >= 40 ? 'warning' : 'error'"
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

      <!-- Row 2b: 30-Day Daily Cash Flow (admin only) -->
      <VRow
        v-if="summary.dailyCashFlow"
        class="mb-2"
      >
        <VCol cols="12">
          <DashboardDailyCashFlow :data="summary.dailyCashFlow" />
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
              <VCardTitle>{{ $t('dashboard.payables.title') }}</VCardTitle>
              <VCardSubtitle>{{ $t('dashboard.payables.subtitle') }}</VCardSubtitle>
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
                        {{ $t('dashboard.payables.notDue.title') }}
                      </p>
                      <span class="text-caption text-medium-emphasis">{{ $t('dashboard.payables.notDue.subtitle') }}</span>
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
                        {{ $t('dashboard.payables.dueToday.title') }}
                      </p>
                      <span class="text-caption text-medium-emphasis">{{ $t('dashboard.payables.dueToday.subtitle') }}</span>
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
                        {{ $t('dashboard.payables.overdue.title') }}
                      </p>
                      <span class="text-caption text-medium-emphasis">{{ $t('dashboard.payables.overdue.subtitle') }}</span>
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
              <VCardTitle>{{ $t('dashboard.templates.title') }}</VCardTitle>
              <VCardSubtitle>{{ $t('dashboard.templates.subtitle') }}</VCardSubtitle>
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
                          {{ $t('dashboard.templates.aidat') }}
                        </p>
                        <span class="text-caption text-medium-emphasis">
                          {{ $t('dashboard.templates.stats', { active: summary.aidatTemplates, total: summary.aidatTemplatesTotal }) }}
                        </span>
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
                    :model-value="(summary.aidatTemplatesTotal ?? 0) > 0 ? ((summary.aidatTemplates ?? 0) / (summary.aidatTemplatesTotal ?? 1)) * 100 : 0"
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
                          {{ $t('dashboard.templates.expense') }}
                        </p>
                        <span class="text-caption text-medium-emphasis">
                          {{ $t('dashboard.templates.stats', { active: summary.expenseTemplates, total: summary.expenseTemplatesTotal }) }}
                        </span>
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
                    :model-value="(summary.expenseTemplatesTotal ?? 0) > 0 ? ((summary.expenseTemplates ?? 0) / (summary.expenseTemplatesTotal ?? 1)) * 100 : 0"
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
              <VCardTitle>{{ $t('dashboard.recent.title') }}</VCardTitle>
              <VCardSubtitle>{{ $t('dashboard.recent.subtitle') }}</VCardSubtitle>
            </VCardItem>

            <div class="dashboard-recent-table-wrap">
              <VTable density="comfortable">
                <thead>
                  <tr>
                    <th>{{ $t('common.date') }}</th>
                    <th>{{ $t('common.description') }}</th>
                    <th>{{ $t('common.type') }}</th>
                    <th class="text-right">
                      {{ $t('common.amount') }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="transaction in summary.recentTransactions"
                    :key="`${transaction.type}-${transaction.id}`"
                  >
                    <td>
                      <span class="text-body-2">{{ formatDate(transaction.date) }}</span>
                    </td>
                    <td>
                      <span class="text-body-2 dashboard-recent-description">{{ transaction.description }}</span>
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
                        {{ transaction.type === 'receipt' ? $t('dashboard.recent.receipt') : $t('dashboard.recent.payment') }}
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
                      {{ $t('dashboard.recent.empty') }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </div>
          </VCard>
        </VCol>
      </VRow>
    </div>
  </div>
</template>

<style scoped>
.dashboard-recent-table-wrap {
  overflow-x: auto;
}

.dashboard-recent-description {
  display: inline-block;
  max-inline-size: 34ch;
  overflow: hidden;
  text-overflow: ellipsis;
  vertical-align: bottom;
  white-space: nowrap;
}
</style>
