<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { useAuthSession } from '@/composables/useAuthSession'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { formatDateTr as formatDate } from '@/utils/formatters'
import { requiredRule } from '@/utils/validators'

interface ReplyItem {
  id: number
  message: string
  user: { id: number; name: string } | null
  created_at: string
}

interface TicketDetail {
  id: number
  subject: string
  message: string
  status: 'open' | 'in_progress' | 'resolved' | 'closed'
  priority: 'low' | 'medium' | 'high'
  created_by: { id: number; name: string } | null
  created_at: string
  replies: ReplyItem[]
}

const { withAbort } = useAbortOnUnmount()
const route = useRoute()
const router = useRouter()
const authSession = useAuthSession()

const ticketId = computed(() => Number((route.params as Record<string, unknown>).id))
const isAdmin = computed(() => authSession.hasRole('admin') || authSession.hasRole('super-admin'))

const loading = ref(false)
const replyLoading = ref(false)
const statusLoading = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const replyError = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const ticket = ref<TicketDetail | null>(null)
const replyMessage = ref('')
const replyFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const messageRules = [requiredRule()]

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

const statusOptions = [
  { title: 'Acik', value: 'open' },
  { title: 'Isleniyor', value: 'in_progress' },
  { title: 'Cozuldu', value: 'resolved' },
  { title: 'Kapali', value: 'closed' },
]

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal =>
      $api<{ data: TicketDetail }>(`/support-tickets/${ticketId.value}`, { signal }),
    )

    ticket.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Destek talebi alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const submitReply = async () => {
  const validation = await replyFormRef.value?.validate()
  if (!validation?.valid) return

  replyLoading.value = true
  replyError.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api(`/support-tickets/${ticketId.value}/reply`, {
      method: 'POST',
      body: { message: replyMessage.value },
      signal,
    }))

    replyMessage.value = ''
    await fetchDetail()
  }
  catch (error) {
    if (isAbortError(error)) return
    replyError.value = getApiErrorMessage(error, 'Cevap gonderilemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    replyLoading.value = false
  }
}

const updateStatus = async (newStatus: string) => {
  statusLoading.value = true

  try {
    await withAbort(signal => $api(`/support-tickets/${ticketId.value}/status`, {
      method: 'PUT',
      body: { status: newStatus },
      signal,
    }))

    if (ticket.value)
      ticket.value.status = newStatus as TicketDetail['status']
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Durum guncellenemedi.')
  }
  finally {
    statusLoading.value = false
  }
}

const deleteTicket = async () => {
  deleting.value = true

  try {
    await withAbort(signal => $api(`/support-tickets/${ticketId.value}`, { method: 'DELETE', signal }))
    await router.push('/support-tickets')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Destek talebi silinemedi.')
  }
  finally {
    deleting.value = false
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
            Destek Talebi Detayi
          </h4>
          <p class="text-medium-emphasis mb-0">
            Talep detayini goruntuleyin ve cevap yapin
          </p>
        </div>
        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/support-tickets"
          >
            Listeye Don
          </VBtn>
          <VBtn
            v-if="isAdmin && ticket"
            color="error"
            variant="outlined"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteTicket"
          >
            Sil
          </VBtn>
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

    <VCol
      v-if="loading"
      cols="12"
    >
      <VCard>
        <VCardText class="text-center py-8">
          <VProgressCircular indeterminate />
        </VCardText>
      </VCard>
    </VCol>

    <template v-if="ticket && !loading">
      <!-- Ticket Info -->
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardItem>
            <VCardTitle>{{ ticket.subject }}</VCardTitle>
            <template #append>
              <div class="d-flex gap-2">
                <VChip
                  size="small"
                  :color="priorityColors[ticket.priority]"
                  variant="tonal"
                >
                  {{ priorityLabels[ticket.priority] }}
                </VChip>
                <VChip
                  size="small"
                  :color="statusColors[ticket.status]"
                  variant="tonal"
                >
                  {{ statusLabels[ticket.status] }}
                </VChip>
              </div>
            </template>
          </VCardItem>
          <VDivider />
          <VCardText>
            <div class="d-flex align-center gap-2 text-sm text-medium-emphasis mb-3">
              <VIcon
                icon="ri-user-line"
                size="16"
              />
              <span>{{ ticket.created_by?.name ?? '-' }}</span>
              <span>&middot;</span>
              <span>{{ formatDate(ticket.created_at) }}</span>
            </div>
            <p class="text-body-1" style="white-space: pre-wrap;">{{ ticket.message }}</p>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Admin Controls -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard v-if="isAdmin">
          <VCardItem>
            <VCardTitle class="text-base">
              Yonetim
            </VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <VSelect
              :model-value="ticket.status"
              :items="statusOptions"
              label="Durum Degistir"
              :loading="statusLoading"
              :disabled="statusLoading"
              @update:model-value="updateStatus"
            />
          </VCardText>
        </VCard>

        <VCard :class="isAdmin ? 'mt-4' : ''">
          <VCardItem>
            <VCardTitle class="text-base">
              Bilgiler
            </VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <VList density="compact">
              <VListItem>
                <template #prepend>
                  <VIcon
                    icon="ri-user-line"
                    size="18"
                    class="me-2"
                  />
                </template>
                <VListItemTitle class="text-sm">
                  Olusturan
                </VListItemTitle>
                <VListItemSubtitle>{{ ticket.created_by?.name ?? '-' }}</VListItemSubtitle>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon
                    icon="ri-calendar-line"
                    size="18"
                    class="me-2"
                  />
                </template>
                <VListItemTitle class="text-sm">
                  Tarih
                </VListItemTitle>
                <VListItemSubtitle>{{ formatDate(ticket.created_at) }}</VListItemSubtitle>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon
                    icon="ri-chat-3-line"
                    size="18"
                    class="me-2"
                  />
                </template>
                <VListItemTitle class="text-sm">
                  Cevap Sayisi
                </VListItemTitle>
                <VListItemSubtitle>{{ ticket.replies.length }}</VListItemSubtitle>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Replies -->
      <VCol cols="12">
        <VCard>
          <VCardItem>
            <VCardTitle class="text-base">
              Cevaplar ({{ ticket.replies.length }})
            </VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <div
              v-if="ticket.replies.length === 0"
              class="text-center text-medium-emphasis py-6"
            >
              Henuz cevap yok.
            </div>

            <div
              v-for="(reply, index) in ticket.replies"
              :key="reply.id"
            >
              <VDivider
                v-if="index > 0"
                class="my-4"
              />
              <div class="d-flex gap-3">
                <VAvatar
                  size="36"
                  color="primary"
                  variant="tonal"
                >
                  <span class="text-sm">{{ reply.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}</span>
                </VAvatar>
                <div class="flex-grow-1">
                  <div class="d-flex align-center gap-2 mb-1">
                    <span class="text-body-1 font-weight-medium">{{ reply.user?.name ?? '-' }}</span>
                    <span class="text-sm text-medium-emphasis">{{ formatDate(reply.created_at) }}</span>
                  </div>
                  <p class="text-body-2 mb-0" style="white-space: pre-wrap;">{{ reply.message }}</p>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Reply Form -->
      <VCol
        v-if="ticket.status !== 'closed'"
        cols="12"
      >
        <VCard>
          <VCardItem>
            <VCardTitle class="text-base">
              Cevap Yaz
            </VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <VForm
              ref="replyFormRef"
              @submit.prevent="submitReply"
            >
              <VRow>
                <VCol
                  v-if="replyError"
                  cols="12"
                >
                  <VAlert
                    type="error"
                    variant="tonal"
                  >
                    {{ replyError }}
                  </VAlert>
                </VCol>

                <VCol cols="12">
                  <VTextarea
                    v-model="replyMessage"
                    label="Mesajiniz"
                    rows="4"
                    :rules="messageRules"
                    :error-messages="fieldErrors.message ?? []"
                  />
                </VCol>

                <VCol cols="12">
                  <div class="d-flex justify-end">
                    <VBtn
                      color="primary"
                      type="submit"
                      :loading="replyLoading"
                      :disabled="replyLoading"
                      prepend-icon="ri-send-plane-line"
                    >
                      Gonder
                    </VBtn>
                  </div>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        v-else
        cols="12"
      >
        <VAlert
          type="info"
          variant="tonal"
        >
          Bu talep kapatilmistir. Yeni cevap yazilamaz.
        </VAlert>
      </VCol>
    </template>
  </VRow>
</template>
