<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatDateTr as formatDate } from '@/utils/formatters'
import type { PaginationMeta } from '@/types/api'

interface AnnouncementItem {
  id: number
  title: string
  content: string
  is_published: boolean
  published_at: string | null
  created_by: { id: number; name: string } | null
  created_at: string
}

interface AnnouncementsResponse {
  data: AnnouncementItem[]
  meta: PaginationMeta
}

const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const deletingId = ref<number | null>(null)
const errorMessage = ref('')

const announcements = ref<AnnouncementItem[]>([])
const pagination = ref({ current_page: 1, last_page: 1, per_page: 20, total: 0 })

const fetchAnnouncements = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<AnnouncementsResponse>('/announcements', {
      query: { page },
      signal,
    }))

    announcements.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Duyurular alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const deleteAnnouncement = async (id: number) => {
  deletingId.value = id
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/announcements/${id}`, { method: 'DELETE', signal }))
    await fetchAnnouncements(pagination.value.current_page)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Duyuru silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

onMounted(() => fetchAnnouncements(1))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Duyurular
          </h4>
          <p class="text-medium-emphasis mb-0">
            Site duyurularini yonetin
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          to="/announcements/create"
        >
          Yeni Duyuru
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
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Baslik</th>
              <th>Olusturan</th>
              <th>Tarih</th>
              <th>Durum</th>
              <th class="text-right">
                Islemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in announcements"
              :key="item.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ item.title }}
                </div>
                <div class="text-caption text-medium-emphasis" style="max-inline-size: 40ch; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  {{ item.content }}
                </div>
              </td>
              <td>{{ item.created_by?.name ?? '-' }}</td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td>
                <VChip
                  size="small"
                  :color="item.is_published ? 'success' : 'secondary'"
                  variant="tonal"
                >
                  {{ item.is_published ? 'Yayinda' : 'Taslak' }}
                </VChip>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/announcements/${item.id}`"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="deletingId === item.id"
                  :disabled="deletingId === item.id"
                  @click="deleteAnnouncement(item.id)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="announcements.length === 0">
              <td
                colspan="5"
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
            @update:model-value="fetchAnnouncements"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
