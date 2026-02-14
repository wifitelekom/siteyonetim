<script setup lang="ts">
import { formatCurrency } from '@/utils/formatters'

interface CashAccount {
  id: number
  name: string
  type: string
  balance: number
}

interface Props {
  accounts: CashAccount[]
  totalCash: number
}

defineProps<Props>()
const { t } = useI18n({ useScope: 'global' })

const getAccountIcon = (type: string) => {
  switch (type) {
    case 'bank': return 'ri-bank-line'
    case 'cash': return 'ri-money-dollar-box-line'
    default: return 'ri-wallet-3-line'
  }
}

const getAccountColor = (type: string) => {
  switch (type) {
    case 'bank': return 'primary'
    case 'cash': return 'success'
    default: return 'info'
  }
}
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>{{ $t('dashboard.cashAccounts.title') }}</VCardTitle>
      <VCardSubtitle>{{ $t('dashboard.cashAccounts.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VCardText class="pb-2">
      <div class="d-flex align-center justify-space-between mb-4 px-2 py-3 rounded" style="background: rgba(var(--v-theme-primary), 0.08);">
        <span class="text-body-1 font-weight-medium">{{ $t('dashboard.cashAccounts.totalCash') }}</span>
        <span class="text-h6 font-weight-bold text-primary">{{ formatCurrency(totalCash) }}</span>
      </div>

      <VList
        lines="two"
        density="compact"
      >
        <VListItem
          v-for="account in accounts"
          :key="account.id"
          class="px-0"
        >
          <template #prepend>
            <VAvatar
              :color="getAccountColor(account.type)"
              variant="tonal"
              size="40"
              rounded
            >
              <VIcon
                :icon="getAccountIcon(account.type)"
                size="22"
              />
            </VAvatar>
          </template>

          <VListItemTitle class="font-weight-medium">
            {{ account.name }}
          </VListItemTitle>
          <VListItemSubtitle>
            {{ account.type === 'bank' ? t('dashboard.cashAccounts.bank') : t('dashboard.cashAccounts.cash') }}
          </VListItemSubtitle>

          <template #append>
            <span
              class="font-weight-bold"
              :class="account.balance >= 0 ? 'text-success' : 'text-error'"
            >
              {{ formatCurrency(account.balance) }}
            </span>
          </template>
        </VListItem>
      </VList>

      <div
        v-if="accounts.length === 0"
        class="text-center text-medium-emphasis py-6"
      >
        {{ $t('dashboard.cashAccounts.empty') }}
      </div>
    </VCardText>
  </VCard>
</template>
