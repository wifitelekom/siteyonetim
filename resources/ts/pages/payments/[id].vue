<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { methodLabel } from '@/utils/statusHelpers'

const { withAbort } = useAbortOnUnmount()

interface PaymentDetail {
  id: number
  paid_at: string
  method: string
  total_amount: number
  description: string | null
  vendor: { id: number; name: string } | null
  cash_account: { id: number; name: string } | null
  creator: { id: number; name: string } | null
  items: Array<{
    id: number
    amount: number
    expense: {
      id: number
      description: string | null
      account: { id: number; name: string } | null
    } | null
  }>
}

interface PaymentShowResponse {
  data: PaymentDetail
}

const route = useRoute()
const paymentId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const errorMessage = ref('')
const detail = ref<PaymentDetail | null>(null)

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<PaymentShowResponse>(`/payments/${paymentId.value}`)
    detail.value = response.data
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Odeme detayi alinamadi.')
  }
  finally {
    loading.value = false
  }
}

onMounted(fetchDetail)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Odeme Detayi
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ detail?.vendor?.name ?? 'Tedarikci yok' }}
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/payments"
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
        <VCardText v-if="detail">
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">Tarih</div>
              <div class="font-weight-medium">{{ formatDate(detail.paid_at) }}</div>
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">Yontem</div>
              <div class="font-weight-medium">{{ methodLabel(detail.method) }}</div>
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <div class="text-caption text-medium-emphasis">Kasa/Banka</div>
              <div class="font-weight-medium">{{ detail.cash_account?.name ?? '-' }}</div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="text-caption text-medium-emphasis">Toplam Tutar</div>
              <div class="text-h6 text-error">{{ formatCurrency(detail.total_amount) }}</div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-caption text-medium-emphasis">Olusturan</div>
              <div class="font-weight-medium">{{ detail.creator?.name ?? '-' }}</div>
            </VCol>

            <VCol cols="12">
              <div class="text-caption text-medium-emphasis">Aciklama</div>
              <div>{{ detail.description || '-' }}</div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VCardItem title="Odeme Kalemleri" />

        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Gider</th>
              <th>Hesap</th>
              <th class="text-right">Tutar</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in detail?.items ?? []"
              :key="item.id"
            >
              <td>{{ item.expense?.description || `Gider #${item.expense?.id ?? '-'}` }}</td>
              <td>{{ item.expense?.account?.name ?? '-' }}</td>
              <td class="text-right">{{ formatCurrency(item.amount) }}</td>
            </tr>
            <tr v-if="(detail?.items ?? []).length === 0">
              <td
                colspan="3"
                class="text-center text-medium-emphasis py-6"
              >
                Kalem bulunamadi.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>
</template>

