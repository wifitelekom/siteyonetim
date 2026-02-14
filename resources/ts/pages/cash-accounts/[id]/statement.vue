<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'

const { withAbort } = useAbortOnUnmount()

interface StatementTransaction {
  date: string
  description: string
  type: string
  direction: 'in' | 'out'
  receipt_no: string | null
  amount: number
  balance: number
}

interface StatementResponse {
  data: {
    account: {
      id: number
      name: string
      type: string
      type_label: string
      opening_balance: number
      balance: number
      is_active: boolean
    }
    from: string
    to: string
    opening_balance: number
    closing_balance: number
    transactions: StatementTransaction[]
  }
}

const route = useRoute()
const accountId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const errorMessage = ref('')
const statement = ref<StatementResponse['data'] | null>(null)

const filters = ref({
  from: '',
  to: '',
})



const fetchStatement = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<StatementResponse>(`/cash-accounts/${accountId.value}/statement`, {
      query: {
        from: filters.value.from || undefined,
        to: filters.value.to || undefined,
      },
    })

    statement.value = response.data
    filters.value.from = response.data.from
    filters.value.to = response.data.to
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Ekstre alınamadı.')
  }
  finally {
    loading.value = false
  }
}

onMounted(fetchStatement)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Hesap Ekstresi
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ statement?.account.name ?? '-' }}
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/cash-accounts"
        >
          Listeye Don
        </VBtn>
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
      <VCard :loading="loading">
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="filters.from"
                type="date"
                :label="$t('common.startDate')"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="filters.to"
                type="date"
                :label="$t('common.endDate')"
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
                @click="fetchStatement"
              >
                Sorgula
              </VBtn>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VCardText v-if="statement">
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">
                Açılış Bakiyesi
              </div>
              <div class="text-h6">
                {{ formatCurrency(statement.opening_balance) }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">
                Kapanış Bakiyesi
              </div>
              <div class="text-h6">
                {{ formatCurrency(statement.closing_balance) }}
              </div>
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">
                Dönem
              </div>
              <div class="font-weight-medium">
                {{ formatDate(statement.from) }} - {{ formatDate(statement.to) }}
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VTable density="comfortable">
          <thead>
            <tr>
              <th>{{ $t('common.date') }}</th>
              <th>{{ $t('common.description') }}</th>
              <th>{{ $t('common.document') }}</th>
              <th class="text-right">
                Giris
              </th>
              <th class="text-right">
                Cikis
              </th>
              <th class="text-right">
                Bakiye
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="transaction in statement?.transactions ?? []"
              :key="`${transaction.date}-${transaction.description}-${transaction.amount}-${transaction.balance}`"
            >
              <td>{{ formatDate(transaction.date) }}</td>
              <td>{{ transaction.description }}</td>
              <td>{{ transaction.receipt_no ?? '-' }}</td>
              <td class="text-right text-success">
                {{ transaction.direction === 'in' ? formatCurrency(transaction.amount) : '-' }}
              </td>
              <td class="text-right text-error">
                {{ transaction.direction === 'out' ? formatCurrency(transaction.amount) : '-' }}
              </td>
              <td class="text-right font-weight-medium">
                {{ formatCurrency(transaction.balance) }}
              </td>
            </tr>
            <tr v-if="(statement?.transactions ?? []).length === 0">
              <td
                colspan="6"
                class="text-center text-medium-emphasis py-6"
              >
                {{ $t('common.noTransactions') }}
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>
</template>

