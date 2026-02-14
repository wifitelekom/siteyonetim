<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { chargeStatusColor as statusColor, chargeStatusLabel as statusLabel } from '@/utils/statusHelpers'
import { requiredRule } from '@/utils/validators'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

interface ApartmentDetail {
  id: number
  block: string | null
  floor: number
  number: string
  m2: number | null
  arsa_payi: number | null
  is_active: boolean
  full_label: string
  users: Array<{
    id: number
    name: string
    email: string | null
    relation_type: 'owner' | 'tenant'
    relation_label: string
    start_date: string | null
    end_date: string | null
  }>
  charges: Array<{
    id: number
    period: string
    due_date: string | null
    amount: number
    paid_amount: number
    remaining: number
    status: string
    description: string | null
    account: { id: number; name: string } | null
  }>
}

interface ApartmentShowResponse {
  data: ApartmentDetail
  meta: {
    available_users: Array<{ id: number; name: string; email: string | null }>
    relation_types: Array<{ value: 'owner' | 'tenant'; label: string }>
  }
}

const route = useRoute()
const apartmentId = computed(() => Number((route.params as Record<string, unknown>).id))
const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const submittingResident = ref(false)
const removingResidentId = ref<number | null>(null)
const errorMessage = ref('')
const residentErrors = ref<Record<string, string[]>>({})

const detail = ref<ApartmentDetail | null>(null)
const availableUsers = ref<Array<{ id: number; name: string; email: string | null }>>([])
const relationTypes = ref<Array<{ value: 'owner' | 'tenant'; label: string }>>([])

const residentForm = ref({
  user_id: null as number | null,
  relation_type: 'owner' as 'owner' | 'tenant',
  start_date: new Date().toISOString().slice(0, 10),
})
const residentFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const residentUserRules = [requiredRule()]
const residentRelationRules = [requiredRule()]

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<ApartmentShowResponse>(`/apartments/${apartmentId.value}`)
    detail.value = response.data
    availableUsers.value = response.meta.available_users
    relationTypes.value = response.meta.relation_types

    if (!relationTypes.value.some(type => type.value === residentForm.value.relation_type))
      residentForm.value.relation_type = relationTypes.value[0]?.value ?? 'owner'
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire detayı alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const addResident = async () => {
  const validation = await residentFormRef.value?.validate()
  if (!validation?.valid)
    return

  submittingResident.value = true
  errorMessage.value = ''
  residentErrors.value = {}

  try {
    await $api(`/apartments/${apartmentId.value}/residents`, {
      method: 'POST',
      body: {
        user_id: residentForm.value.user_id,
        relation_type: residentForm.value.relation_type,
        start_date: residentForm.value.start_date || null,
      },
    })

    residentForm.value.user_id = null
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Sakin eklenemedi.')
    residentErrors.value = getApiFieldErrors(error)
  }
  finally {
    submittingResident.value = false
  }
}

const removeResident = async (userId: number) => {
  removingResidentId.value = userId
  errorMessage.value = ''

  try {
    await $api(`/apartments/${apartmentId.value}/residents/${userId}`, { method: 'DELETE' })
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Sakin kaldırılamadı.')
  }
  finally {
    removingResidentId.value = null
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
            {{ $t('pages.apartments.detailTitle') }}
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ detail?.full_label ?? '-' }}
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/management/apartments"
          >
            Listeye Don
          </VBtn>
          <VBtn
            color="primary"
            :to="`/management/apartments/${apartmentId}/edit`"
          >
            Düzenle
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
      cols="12"
      md="4"
    >
      <VCard :loading="loading">
        <VCardItem title="Daire Bilgileri" />
        <VCardText v-if="detail">
          <VList class="card-list">
            <VListItem>
              <VListItemTitle>Blok</VListItemTitle>
              <template #append>
                <span>{{ detail.block || '-' }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Kat</VListItemTitle>
              <template #append>
                <span>{{ detail.floor }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>No</VListItemTitle>
              <template #append>
                <span>{{ detail.number }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>m2</VListItemTitle>
              <template #append>
                <span>{{ detail.m2 ?? '-' }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>{{ $t('common.landShare') }}</VListItemTitle>
              <template #append>
                <span>{{ detail.arsa_payi ?? '-' }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Durum</VListItemTitle>
              <template #append>
                <VChip
                  size="small"
                  :color="detail.is_active ? 'success' : 'secondary'"
                  variant="tonal"
                >
                  {{ detail.is_active ? $t('common.active') : $t('common.passive') }}
                </VChip>
              </template>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      md="8"
    >
        <VCard :loading="loading">
        <VCardItem title="Sakinler" />
        <VCardText>
          <VForm
            ref="residentFormRef"
            @submit.prevent="addResident"
          >
            <VRow>
              <VCol
                cols="12"
                md="5"
              >
                <VSelect
                  v-model="residentForm.user_id"
                  :items="availableUsers"
                  item-title="name"
                  item-value="id"
                  :label="$t('common.user')"
                  :rules="residentUserRules"
                  :error-messages="residentErrors.user_id ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
              >
                <VSelect
                  v-model="residentForm.relation_type"
                  :items="relationTypes"
                  item-title="label"
                  item-value="value"
                  :label="$t('common.type')"
                  :rules="residentRelationRules"
                  :error-messages="residentErrors.relation_type ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="2"
              >
                <VTextField
                  v-model="residentForm.start_date"
                  type="date"
                  :label="$t('common.startDate')"
                  :error-messages="residentErrors.start_date ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="2"
                class="d-flex align-end"
              >
                <VBtn
                  color="primary"
                  type="submit"
                  block
                  :loading="submittingResident"
                  :disabled="submittingResident"
                >
                  Ekle
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VTable density="comfortable">
          <thead>
            <tr>
              <th>Ad</th>
              <th>{{ $t('common.type') }}</th>
              <th>{{ $t('common.startDate') }}</th>
              <th class="text-right">
                Islem
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="resident in detail?.users ?? []"
              :key="resident.id"
            >
              <td>
                <div class="font-weight-medium">
                  {{ resident.name }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ resident.email || '-' }}
                </div>
              </td>
              <td>
                <VChip
                  size="small"
                  :color="resident.relation_type === 'owner' ? 'primary' : 'warning'"
                  variant="tonal"
                >
                  {{ resident.relation_label }}
                </VChip>
              </td>
              <td>{{ formatDate(resident.start_date) }}</td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="removingResidentId === resident.id"
                  :disabled="removingResidentId === resident.id"
                  @click="removeResident(resident.id)"
                >
                  <VIcon icon="ri-close-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="(detail?.users ?? []).length === 0">
              <td
                colspan="4"
                class="text-center text-medium-emphasis py-6"
              >
                Sakin kaydı yok.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VCardItem title="Son Tahakkuklar" />
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>{{ $t('common.period') }}</th>
              <th>{{ $t('common.due') }}</th>
              <th class="text-right">
                Tutar
              </th>
              <th class="text-right">
                Kalan
              </th>
              <th>{{ $t('common.status') }}</th>
              <th class="text-right">
                Islem
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="charge in detail?.charges ?? []"
              :key="charge.id"
            >
              <td>{{ charge.period }}</td>
              <td>{{ formatDate(charge.due_date) }}</td>
              <td class="text-right">
                {{ formatCurrency(charge.amount) }}
              </td>
              <td class="text-right">
                {{ formatCurrency(charge.remaining) }}
              </td>
              <td>
                <VChip
                  size="small"
                  :color="statusColor(charge.status)"
                  variant="tonal"
                >
                  {{ statusLabel(charge.status) }}
                </VChip>
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
            <tr v-if="(detail?.charges ?? []).length === 0">
              <td
                colspan="6"
                class="text-center text-medium-emphasis py-6"
              >
                Tahakkuk kaydı yok.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>
</template>

