<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import type { OptionItem, PaginationMeta } from '@/types/api'
import { $api } from '@/utils/api'
import { getApiErrorMessage } from '@/utils/errorHandler'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'

interface ChargeItem {
  id: number
  period: string
  due_date: string
  amount: number
  paid_amount: number
  remaining: number
  description: string | null
  status: 'open' | 'paid' | 'overdue'
  charge_type: 'aidat' | 'other'
  apartment: OptionItem | null
  account: { id: number; name: string } | null
  debtor: { id: number; name: string; type: 'owner' | 'tenant'; type_label: string } | null
}

interface ChargesResponse {
  data: ChargeItem[]
  meta: PaginationMeta
}

interface ChargesMetaResponse {
  data: {
    apartments: OptionItem[]
    accounts: OptionItem[]
    charge_types: Array<{ value: 'aidat' | 'other'; label: string }>
  }
}

const loading = ref(false)
const loadingMeta = ref(false)
const errorMessage = ref('')

const charges = ref<ChargeItem[]>([])
const apartments = ref<OptionItem[]>([])
const chargeTypes = ref<Array<{ value: 'aidat' | 'other'; label: string }>>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  period: '',
  apartment_id: null as number | null,
  status: null as 'paid' | 'open' | 'overdue' | null,
  search: '',
})

const showAdvancedFilters = ref(false)
const selectedChargeIds = ref<number[]>([])
const bulkDeleting = ref(false)
const bulkUpdating = ref(false)
const bulkMessage = ref('')
const categoryDialog = ref(false)
const dueDateDialog = ref(false)
const descriptionDialog = ref(false)
const bulkForm = ref({
  charge_type: null as 'aidat' | 'other' | null,
  due_date: '',
  description: '',
})
const { withAbort } = useAbortOnUnmount()

const quickStatuses: Array<{ label: string; value: 'paid' | 'open' | 'overdue' }> = [
  { label: 'Ödenen', value: 'paid' },
  { label: 'Ödenmeyen', value: 'open' },
  { label: 'Geciken', value: 'overdue' },
]

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await withAbort(signal => $api<ChargesMetaResponse>('/charges/meta', { signal }))
    apartments.value = response.data.apartments
    chargeTypes.value = response.data.charge_types
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Filtre verileri alınamadı.')
  }
  finally {
    loadingMeta.value = false
  }
}

const fetchCharges = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<ChargesResponse>('/charges', {
      query: {
        page,
        period: filters.value.period || undefined,
        apartment_id: filters.value.apartment_id || undefined,
        status: filters.value.status || undefined,
        search: filters.value.search || undefined,
      },
      signal,
    }))

    charges.value = response.data
    pagination.value = response.meta
  }
  catch (error) {
    if (isAbortError(error))
      return

    errorMessage.value = getApiErrorMessage(error, 'Tahakkuk listesi alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await fetchCharges(1)
}

const toggleQuickStatus = async (status: 'paid' | 'open' | 'overdue') => {
  filters.value.status = filters.value.status === status ? null : status
  await fetchCharges(1)
}

const resetFilters = async () => {
  filters.value = {
    period: '',
    apartment_id: null,
    status: null,
    search: '',
  }

  selectedChargeIds.value = []
  bulkMessage.value = ''
  await fetchCharges(1)
}

const isQuickStatusActive = (status: 'paid' | 'open' | 'overdue') => filters.value.status === status

const visibleChargeIds = computed(() => charges.value.map(charge => charge.id))

const allVisibleSelected = computed(() =>
  visibleChargeIds.value.length > 0
  && visibleChargeIds.value.every(id => selectedChargeIds.value.includes(id)))

const someVisibleSelected = computed(() =>
  visibleChargeIds.value.some(id => selectedChargeIds.value.includes(id))
  && !allVisibleSelected.value)

const toggleSelectAll = (checked: boolean) => {
  bulkMessage.value = ''

  if (checked) {
    const merged = new Set([...selectedChargeIds.value, ...visibleChargeIds.value])
    selectedChargeIds.value = Array.from(merged)

    return
  }

  selectedChargeIds.value = selectedChargeIds.value.filter(id => !visibleChargeIds.value.includes(id))
}

const toggleRowSelection = (id: number, checked: boolean) => {
  bulkMessage.value = ''

  if (checked) {
    if (!selectedChargeIds.value.includes(id))
      selectedChargeIds.value.push(id)

    return
  }

  selectedChargeIds.value = selectedChargeIds.value.filter(selectedId => selectedId !== id)
}

const monthNames = [
  'Ocak',
  'Şubat',
  'Mart',
  'Nisan',
  'Mayıs',
  'Haziran',
  'Temmuz',
  'Ağustos',
  'Eylül',
  'Ekim',
  'Kasım',
  'Aralık',
]

const looksCorruptedText = (value: string) => /�|Ã.|Ä.|Å./.test(value)

const buildChargeTitle = (charge: ChargeItem) => {
  const description = charge.description?.trim() ?? ''
  if (description && !looksCorruptedText(description))
    return description

  const [year, month] = charge.period.split('-')
  const monthIndex = Number(month) - 1
  const monthLabel = monthNames[monthIndex] ?? month ?? ''

  return `${year} - ${monthLabel} ayı aidatı`
}

const buildChargeSubtitle = (charge: ChargeItem) => {
  const apartment = charge.apartment?.label ?? '-'
  const debtor = charge.debtor?.name ?? '-'

  return `${apartment} - ${debtor}`
}

const paymentIcon = (status: ChargeItem['status']) => {
  if (status === 'paid')
    return 'ri-check-line'
  if (status === 'overdue')
    return 'ri-alert-line'

  return 'ri-alarm-line'
}

const paymentClass = (status: ChargeItem['status']) => {
  if (status === 'paid')
    return 'text-success'
  if (status === 'overdue')
    return 'text-error'

  return 'text-warning'
}

const paymentText = (charge: ChargeItem) => {
  if (charge.status === 'paid')
    return 'Ödendi'

  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dueDate = new Date(`${charge.due_date}T00:00:00`)
  const diff = Math.floor((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))

  if (charge.status === 'overdue') {
    const overdueDays = Math.abs(diff)

    return overdueDays === 0 ? 'Bugün gecikti' : `${overdueDays} gün gecikti`
  }

  if (diff === 0)
    return 'Bugün'

  return `${diff} gün sonra`
}

const selectedCount = computed(() => selectedChargeIds.value.length)
const firstSelectedCharge = computed(() =>
  charges.value.find(charge => selectedChargeIds.value.includes(charge.id)) ?? null)

const normalizeDescription = (description: string | null | undefined) => {
  const value = description?.trim() ?? ''

  return value.length > 0 ? value : null
}

const getChargeForBulkUpdate = async (id: number): Promise<ChargeItem | null> => {
  const listed = charges.value.find(charge => charge.id === id)
  if (listed)
    return listed

  try {
    const response = await withAbort(signal => $api<{ data: ChargeItem }>(`/charges/${id}`, { signal }))

    return response.data
  }
  catch (error) {
    if (isAbortError(error))
      return null

    return null
  }
}

const runBulkDelete = async (action: 'sil' | 'arsivle') => {
  if (selectedChargeIds.value.length === 0 || bulkDeleting.value || bulkUpdating.value)
    return

  const total = selectedChargeIds.value.length
  const actionLabel = action === 'arsivle' ? 'arşivlensin' : 'silinsin'
  const confirmed = window.confirm(`${total} seçili tahakkuk kaydı ${actionLabel} mi?`)
  if (!confirmed)
    return

  bulkDeleting.value = true
  bulkMessage.value = ''
  errorMessage.value = ''

  try {
    const results = await Promise.allSettled(selectedChargeIds.value.map(id =>
      withAbort(signal => $api(`/charges/${id}`, { method: 'DELETE', signal })),
    ))

    const success = results.filter(result => result.status === 'fulfilled').length
    const failed = total - success

    selectedChargeIds.value = []
    await fetchCharges(pagination.value.current_page)

    if (success > 0)
      bulkMessage.value = action === 'arsivle' ? `${success} kayıt arşivlendi.` : `${success} kayıt silindi.`

    if (failed > 0)
      errorMessage.value = `${failed} kayıt silinemedi.`
  }
  finally {
    bulkDeleting.value = false
  }
}

const archiveSelectedCharges = async () => {
  await runBulkDelete('arsivle')
}

const deleteSelectedCharges = async () => {
  await runBulkDelete('sil')
}

const applyBulkChargeChanges = async (overrides: Partial<Pick<ChargeItem, 'charge_type' | 'due_date' | 'description'>>, successText: string) => {
  if (selectedChargeIds.value.length === 0 || bulkUpdating.value || bulkDeleting.value)
    return

  bulkUpdating.value = true
  bulkMessage.value = ''
  errorMessage.value = ''

  try {
    const results = await Promise.allSettled(selectedChargeIds.value.map(async id => {
      const charge = await getChargeForBulkUpdate(id)
      const apartmentId = charge?.apartment?.id ?? null
      const accountId = charge?.account?.id ?? null

      if (!charge || !apartmentId || !accountId)
        throw new Error('missing-related-data')

      await withAbort(signal => $api(`/charges/${id}`, {
        method: 'PUT',
        body: {
          apartment_id: apartmentId,
          account_id: accountId,
          charge_type: overrides.charge_type ?? charge.charge_type,
          period: charge.period,
          due_date: overrides.due_date ?? charge.due_date,
          amount: charge.amount,
          description: Object.prototype.hasOwnProperty.call(overrides, 'description')
            ? normalizeDescription(overrides.description ?? null)
            : normalizeDescription(charge.description),
        },
        signal,
      }))
    }))

    const success = results.filter(result => result.status === 'fulfilled').length
    const failed = selectedChargeIds.value.length - success

    selectedChargeIds.value = []
    await fetchCharges(pagination.value.current_page)

    if (success > 0)
      bulkMessage.value = `${success} kayıt için ${successText}`

    if (failed > 0)
      errorMessage.value = `${failed} kayıt güncellenemedi.`
  }
  finally {
    bulkUpdating.value = false
  }
}

const openCategoryDialog = () => {
  bulkMessage.value = ''
  bulkForm.value.charge_type = firstSelectedCharge.value?.charge_type ?? chargeTypes.value[0]?.value ?? 'aidat'
  categoryDialog.value = true
}

const openDueDateDialog = () => {
  bulkMessage.value = ''
  bulkForm.value.due_date = firstSelectedCharge.value?.due_date ?? new Date().toISOString().slice(0, 10)
  dueDateDialog.value = true
}

const openDescriptionDialog = () => {
  bulkMessage.value = ''
  bulkForm.value.description = firstSelectedCharge.value?.description ?? ''
  descriptionDialog.value = true
}

const submitBulkCategory = async () => {
  if (!bulkForm.value.charge_type)
    return

  await applyBulkChargeChanges({ charge_type: bulkForm.value.charge_type }, 'kategori güncellendi.')
  categoryDialog.value = false
}

const submitBulkDueDate = async () => {
  if (!bulkForm.value.due_date)
    return

  await applyBulkChargeChanges({ due_date: bulkForm.value.due_date }, 'ödeme tarihi güncellendi.')
  dueDateDialog.value = false
}

const submitBulkDescription = async () => {
  await applyBulkChargeChanges({ description: bulkForm.value.description }, 'borç açıklaması güncellendi.')
  descriptionDialog.value = false
}

onMounted(async () => {
  await Promise.allSettled([fetchMeta(), fetchCharges(1)])
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between flex-wrap gap-3 mb-2">
        <h4 class="text-h4 mb-0">
          Aidatlar
        </h4>

        <div class="d-flex gap-2 flex-wrap">
          <VBtn
            variant="outlined"
            color="primary"
            to="/charges/create"
          >
            Borç Ekle
          </VBtn>
          <VBtn
            variant="outlined"
            color="primary"
            to="/charges/create-bulk"
          >
            Toplu Borç Ekle
          </VBtn>
        </div>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loadingMeta">
        <VCardText class="py-3">
          <div class="d-flex align-center justify-space-between flex-wrap gap-3">
            <div class="d-flex align-center flex-wrap gap-2">
              <VBtnGroup divided>
                <VBtn
                  v-for="item in quickStatuses"
                  :key="item.value"
                  :variant="isQuickStatusActive(item.value) ? 'flat' : 'outlined'"
                  :color="isQuickStatusActive(item.value) ? 'primary' : undefined"
                  @click="toggleQuickStatus(item.value)"
                >
                  {{ item.label }}
                </VBtn>
              </VBtnGroup>

              <VBtn
                variant="outlined"
                prepend-icon="ri-filter-3-line"
                @click="showAdvancedFilters = !showAdvancedFilters"
              >
                Süz
              </VBtn>
            </div>

            <VTextField
              v-model="filters.search"
              class="aidat-search"
              label="Hesap ara"
              prepend-inner-icon="ri-search-line"
              clearable
              hide-details
              @keyup.enter="applyFilters"
              @click:clear="applyFilters"
            />
          </div>

          <VExpandTransition>
            <div v-show="showAdvancedFilters" class="mt-4">
              <VRow>
                <VCol
                  cols="12"
                  md="3"
                >
                  <VTextField
                    v-model="filters.period"
                    type="month"
                    :label="$t('common.period')"
                    hide-details
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="4"
                >
                  <VSelect
                    v-model="filters.apartment_id"
                    :items="apartments"
                    item-title="label"
                    item-value="id"
                    :label="$t('common.apartment')"
                    hide-details
                    clearable
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="5"
                  class="d-flex align-end justify-end gap-2"
                >
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
                </VCol>
              </VRow>
            </div>
          </VExpandTransition>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-if="selectedCount > 0"
      cols="12"
    >
      <VCard>
        <VCardText class="d-flex align-center flex-wrap gap-2 py-3">
          <span class="text-body-2 text-medium-emphasis">
            {{ selectedCount }} kayıt seçildi
          </span>

          <VBtn
            size="small"
            variant="outlined"
            :loading="bulkDeleting"
            :disabled="bulkDeleting || bulkUpdating"
            @click="archiveSelectedCharges"
          >
            Arşivle
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            color="error"
            :loading="bulkDeleting"
            :disabled="bulkDeleting || bulkUpdating"
            @click="deleteSelectedCharges"
          >
            Sil
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            :disabled="bulkDeleting || bulkUpdating"
            @click="openCategoryDialog"
          >
            Kategori Ekle
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            :disabled="bulkDeleting || bulkUpdating"
            @click="openDueDateDialog"
          >
            Ödeme Tarihini Değiştir
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            :disabled="bulkDeleting || bulkUpdating"
            @click="openDescriptionDialog"
          >
            Borç Açıklamalarını Değiştir
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-if="bulkMessage"
      cols="12"
    >
      <VAlert
        type="info"
        variant="tonal"
      >
        {{ bulkMessage }}
      </VAlert>
    </VCol>

    <VDialog
      v-model="categoryDialog"
      max-width="460"
    >
      <VCard title="Kategori Ekle">
        <VCardText>
          <VSelect
            v-model="bulkForm.charge_type"
            :items="chargeTypes"
            item-title="label"
            item-value="value"
            label="Kategori"
          />
        </VCardText>
        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="categoryDialog = false"
          >
            Vazgeç
          </VBtn>
          <VBtn
            color="primary"
            :loading="bulkUpdating"
            :disabled="bulkUpdating || !bulkForm.charge_type"
            @click="submitBulkCategory"
          >
            Kaydet
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="dueDateDialog"
      max-width="460"
    >
      <VCard title="Ödeme Tarihini Değiştir">
        <VCardText>
          <VTextField
            v-model="bulkForm.due_date"
            type="date"
            label="Yeni Ödeme Tarihi"
          />
        </VCardText>
        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="dueDateDialog = false"
          >
            Vazgeç
          </VBtn>
          <VBtn
            color="primary"
            :loading="bulkUpdating"
            :disabled="bulkUpdating || !bulkForm.due_date"
            @click="submitBulkDueDate"
          >
            Kaydet
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-model="descriptionDialog"
      max-width="560"
    >
      <VCard title="Borç Açıklamalarını Değiştir">
        <VCardText>
          <VTextarea
            v-model="bulkForm.description"
            label="Yeni Açıklama"
            rows="3"
            hint="Boş bırakırsanız açıklama temizlenir."
            persistent-hint
          />
        </VCardText>
        <VCardActions class="px-6 pb-4">
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="descriptionDialog = false"
          >
            Vazgeç
          </VBtn>
          <VBtn
            color="primary"
            :loading="bulkUpdating"
            :disabled="bulkUpdating"
            @click="submitBulkDescription"
          >
            Kaydet
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

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
              <th class="text-center" style="width: 50px;">
                <VCheckboxBtn
                  :model-value="allVisibleSelected"
                  :indeterminate="someVisibleSelected"
                  @update:model-value="value => toggleSelectAll(!!value)"
                />
              </th>
              <th>Açıklama</th>
              <th>Kategori</th>
              <th>Düzenleme</th>
              <th>Ödeme</th>
              <th class="text-right">
                Toplam
              </th>
              <th class="text-right">
                Kalan
              </th>
              <th class="text-right">
                {{ $t('common.actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="charge in charges"
              :key="charge.id"
            >
              <td class="text-center">
                <VCheckboxBtn
                  :model-value="selectedChargeIds.includes(charge.id)"
                  @update:model-value="value => toggleRowSelection(charge.id, !!value)"
                />
              </td>
              <td>
                <div class="font-weight-medium">
                  <RouterLink
                    :to="`/charges/${charge.id}`"
                    class="text-decoration-none text-primary"
                  >
                    {{ buildChargeTitle(charge) }}
                  </RouterLink>
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ buildChargeSubtitle(charge) }}
                </div>
              </td>
              <td>
                <VChip
                  size="small"
                  variant="tonal"
                  :color="charge.charge_type === 'aidat' ? 'info' : 'secondary'"
                >
                  {{ charge.charge_type === 'aidat' ? 'Aidat' : 'Diğer' }}
                </VChip>
              </td>
              <td>
                {{ formatDate(charge.due_date) }}
              </td>
              <td>
                <div class="d-flex align-center gap-2" :class="paymentClass(charge.status)">
                  <VIcon :icon="paymentIcon(charge.status)" size="18" />
                  <span>{{ paymentText(charge) }}</span>
                </div>
              </td>
              <td class="text-right">
                <span :class="charge.status === 'paid' ? 'text-success' : ''">
                  {{ formatCurrency(charge.amount) }}
                </span>
              </td>
              <td class="text-right">
                <span :class="charge.remaining <= 0 ? 'text-success' : 'text-error'">
                  {{ formatCurrency(charge.remaining) }}
                </span>
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  :to="`/charges/${charge.id}`"
                >
                  <VIcon icon="ri-eye-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="charges.length === 0">
              <td
                colspan="8"
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
            @update:model-value="fetchCharges"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style scoped>
.aidat-search {
  inline-size: 320px;
  max-inline-size: 100%;
}

@media (max-width: 960px) {
  .aidat-search {
    inline-size: 100%;
  }
}
</style>
