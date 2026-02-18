<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatDateTr as formatDate } from '@/utils/formatters'
import type { PaginationMeta } from '@/types/api'

interface TicketItem {
  id: number
  subject: string
  status: 'open' | 'in_progress' | 'resolved' | 'closed'
  priority: 'low' | 'medium' | 'high'
  replies_count: number
  created_by: { id: number; name: string } | null
  created_at: string
  updated_at: string
}

const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const errorMessage = ref('')
const tickets = ref<TicketItem[]>([])
const pagination = ref({ current_page: 1, last_page: 1, per_page: 20, total: 0 })
const statusFilter = ref<string | null>(null)

const statusLabels: Record<string, string> = {
  open: 'Acik',
  in_progress: 'Isleniyor',
  resolved: 'Cozuldu',
  closed: 'Kapali',
}

const statusColors: Record<string, string> = {
  open: 'warning',
  in_progress: 'info',
  resolved: 'success',
  closed: 'secondary',
}

const priorityLabels: Record<string, string> = {
  low: 'Dusuk',
  medium: 'Orta',
  high: 'Yuksek',
}

const priorityColors: Record<string, string> = {
  low: 'success',
  medium: 'warning',
  high: 'error',
}

const fetchTickets = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<{ data: TicketItem[]; meta: PaginationMeta }>('/support-tickets', {
      query: {
        page,
        status: statusFilter.value || undefined,
      },
      signal,
    }))

    tickets.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Destek talepleri alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const applyFilter = () => fetchTickets(1)

onMounted(() => fetchTickets(1))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Destek Talepleri
          </h4>
          <p class="text-medium-emphasis mb-0">
            Destek taleplerini yonetin
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          to="/support-tickets/create"
        >
          Yeni Talep
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <VSelect
                v-model="statusFilter"
                :items="[
                  { title: 'Acik', value: 'open' },
                  { title: 'Isleniyor', value: 'in_progress' },
                  { title: 'Cozuldu', value: 'resolved' },
                  { title: 'Kapali', value: 'closed' },
                ]"
                label="Durum"
                clearable
                @update:model-value="applyFilter"
              />
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
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
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Konu</th>
              <th>Olusturan</th>
              <th>Oncelik</th>
              <th>Durum</th>
              <th>Cevap</th>
              <th>Tarih</th>
              <th class="text-right">
                Islem
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="ticket in tickets"
              :key="ticket.id"
            >
              <td>
                <RouterLink
                  :to="`/support-tickets/${ticket.id}`"
                  class="font-weight-medium text-decoration-none"
                >
                  {{ ticket.subject }}
                </RouterLink>
              </td>
              <td>{{ ticket.created_by?.name ?? '-' }}</td>
              <td>
                <VChip
                  size="small"
                  :color="priorityColors[ticket.priority]"
                  variant="tonal"
                >
                  {{ priorityLabels[ticket.priority] }}
                </VChip>
              </td>
              <td>
                <VChip
                  size="small"
                  :color="statusColors[ticket.status]"
                  variant="tonal"
                >
                  {{ statusLabels[ticket.status] }}
                </VChip>
              </td>
              <td>{{ ticket.replies_count }}</td>
              <td>{{ formatDate(ticket.created_at) }}</td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/support-tickets/${ticket.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="tickets.length === 0">
              <td
                colspan="7"
                class="text-center text-medium-emphasis py-6"
              >
                {{ $t('common.noRecords') }}
              </td>
            </tr>
          </tbody>
        </VTable>

        <VCardText class="d-flex justify-space-between align-center flex-wrap gap-3">
          <span class="text-sm text-medium-emphasis">{{ $t('common.totalRecords', { count: pagination.total }) }}</span>

          <VPagination
            :model-value="pagination.current_page"
            :length="pagination.last_page"
            :total-visible="7"
            @update:model-value="fetchTickets"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
