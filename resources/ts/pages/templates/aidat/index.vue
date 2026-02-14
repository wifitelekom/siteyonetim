<script setup lang="ts">
import { getApiErrorMessage } from '@/utils/errorHandler'
import type { PaginationMeta } from '@/types/api'
import { $api } from '@/utils/api'
import { formatCurrency } from '@/utils/formatters'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

interface AidatTemplateItem {
  id: number
  name: string
  amount: number
  due_day: number
  scope: 'all' | 'selected'
  scope_label: string
  is_active: boolean
  apartments_count: number
  account: { id: number; label: string } | null
}

interface AidatTemplatesResponse {
  data: AidatTemplateItem[]
  meta: PaginationMeta
}

const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const deletingId = ref<number | null>(null)
const errorMessage = ref('')

const templates = ref<AidatTemplateItem[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const filters = ref({
  scope: null as 'all' | 'selected' | null,
  is_active: null as boolean | null,
  search: '',
})

const fetchTemplates = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<AidatTemplatesResponse>('/templates/aidat', {
      query: {
        page,
        scope: filters.value.scope || undefined,
        is_active: filters.value.is_active === null ? undefined : filters.value.is_active ? 1 : 0,
        search: filters.value.search || undefined,
      },
      signal,
    }))

    templates.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Aidat şablonları alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchTemplates(1)
}

const resetFilters = async () => {
  filters.value = {
    scope: null,
    is_active: null,
    search: '',
  }

  await fetchTemplates(1)
}

const deleteTemplate = async (template: AidatTemplateItem) => {
  deletingId.value = template.id
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/templates/aidat/${template.id}`, { method: 'DELETE', signal }))
    await fetchTemplates(pagination.value.current_page)
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Aidat şablonu silinemedi.')
  }
  finally {
    deletingId.value = null
  }
}

onMounted(() => fetchTemplates(1))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Aidat Şablonları
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.aidatTemplates.indexSubtitle') }}
          </p>
        </div>

        <VBtn
          color="primary"
          prepend-icon="ri-add-line"
          to="/templates/aidat/create"
        >
          Yeni Şablon
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.scope"
                :items="[
                  { title: $t('common.allApartments'), value: 'all' },
                  { title: $t('common.selectedApartments'), value: 'selected' },
                ]"
                :label="$t('common.scope')"
                clearable
              />
            </VCol>
            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="filters.is_active"
                :items="[
                  { title: $t('common.active'), value: true },
                  { title: $t('common.passive'), value: false },
                ]"
                :label="$t('common.status')"
                clearable
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="filters.search"
                :label="$t('common.search')"
                :placeholder="$t('common.templateNamePlaceholder')"
              />
            </VCol>
            <VCol cols="12">
              <div class="d-flex gap-3 justify-end">
                <VBtn
                  variant="outlined"
                  @click="resetFilters"
                >
                  {{ $t('common.clear') }}
                </VBtn>
                <VBtn
                  color="primary"
                  @click="applyFilters"
                >
                  {{ $t('common.filter') }}
                </VBtn>
              </div>
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
              <th>{{ $t('common.template') }}</th>
              <th>{{ $t('common.account') }}</th>
              <th class="text-right">
                Tutar
              </th>
              <th>{{ $t('common.due') }}</th>
              <th>{{ $t('common.scope') }}</th>
              <th>{{ $t('common.status') }}</th>
              <th class="text-right">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="template in templates"
              :key="template.id"
            >
              <td>{{ template.name }}</td>
              <td>{{ template.account?.label ?? '-' }}</td>
              <td class="text-right">
                {{ formatCurrency(template.amount) }}
              </td>
              <td>{{ $t('common.nthDay', { day: template.due_day }) }}</td>
              <td>
                <VChip
                  size="small"
                  :color="template.scope === 'all' ? 'info' : 'warning'"
                  variant="tonal"
                >
                  {{ template.scope === 'all' ? $t('common.allApartments') : $t('common.apartmentCount', { count: template.apartments_count }) }}
                </VChip>
              </td>
              <td>
                <VChip
                  size="small"
                  :color="template.is_active ? 'success' : 'secondary'"
                  variant="tonal"
                >
                  {{ template.is_active ? $t('common.active') : $t('common.passive') }}
                </VChip>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/templates/aidat/${template.id}/edit`"
                >
                  <VIcon icon="ri-edit-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="deletingId === template.id"
                  :disabled="deletingId === template.id"
                  @click="deleteTemplate(template)"
                >
                  <VIcon icon="ri-delete-bin-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="templates.length === 0">
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
            @update:model-value="fetchTemplates"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>


