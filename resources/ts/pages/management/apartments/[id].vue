<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { chargeStatusColor as statusColor, chargeStatusLabel as statusLabel } from '@/utils/statusHelpers'
import { positiveNumberRule, requiredRule } from '@/utils/validators'

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
    family_role: string | null
    family_role_label: string | null
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
    family_roles: Array<{ value: string; label: string }>
  }
}

const route = useRoute()
const apartmentId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const submittingResident = ref(false)
const removingResidentId = ref<number | null>(null)
const errorMessage = ref('')
const residentErrors = ref<Record<string, string[]>>({})

const detail = ref<ApartmentDetail | null>(null)
const availableUsers = ref<Array<{ id: number; name: string; email: string | null }>>([])
const relationTypes = ref<Array<{ value: 'owner' | 'tenant'; label: string }>>([])
const familyRoles = ref<Array<{ value: string; label: string }>>([])

const residentForm = ref({
  user_id: null as number | null,
  relation_type: 'owner' as 'owner' | 'tenant',
  family_role: null as string | null,
  start_date: new Date().toISOString().slice(0, 10),
})
const residentFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const residentUserRules = [requiredRule()]
const residentRelationRules = [requiredRule()]

const editDialog = ref(false)
const editing = ref(false)
const editErrors = ref<Record<string, string[]>>({})
const editForm = ref({
  block: '',
  floor: null as number | null,
  number: '',
  m2: null as number | null,
  arsa_payi: null as number | null,
  is_active: true,
})
const editFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const editFloorRules = [
  requiredRule(),
  (value: unknown) => Number.isInteger(Number(value)) ? true : 'Kat bilgisi tam sayi olmalidir.',
]
const editNumberRules = [requiredRule()]
const editM2Rules = [
  (value: unknown) => {
    if (value === null || value === undefined || value === '')
      return true

    return positiveNumberRule()(value)
  },
]
const editArsaPayiRules = [
  (value: unknown) => {
    if (value === null || value === undefined || value === '')
      return true

    const parsed = Number(value)

    return Number.isFinite(parsed) && parsed >= 0 ? true : 'Sifir veya daha buyuk bir deger giriniz.'
  },
]

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<ApartmentShowResponse>(`/apartments/${apartmentId.value}`)
    detail.value = response.data
    availableUsers.value = response.meta.available_users
    relationTypes.value = response.meta.relation_types
    familyRoles.value = response.meta.family_roles

    if (!relationTypes.value.some(type => type.value === residentForm.value.relation_type))
      residentForm.value.relation_type = relationTypes.value[0]?.value ?? 'owner'
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire detayi alinamadi.')
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
        family_role: residentForm.value.family_role || null,
        start_date: residentForm.value.start_date || null,
      },
    })

    residentForm.value.user_id = null
    residentForm.value.family_role = null
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
    errorMessage.value = getApiErrorMessage(error, 'Sakin kaldirilamadi.')
  }
  finally {
    removingResidentId.value = null
  }
}

const openEditDialog = () => {
  if (!detail.value)
    return

  editErrors.value = {}
  editForm.value = {
    block: detail.value.block ?? '',
    floor: detail.value.floor,
    number: detail.value.number,
    m2: detail.value.m2,
    arsa_payi: detail.value.arsa_payi,
    is_active: detail.value.is_active,
  }
  editDialog.value = true
}

const submitEdit = async () => {
  const validation = await editFormRef.value?.validate()
  if (!validation?.valid)
    return

  editing.value = true
  editErrors.value = {}
  errorMessage.value = ''

  try {
    await $api(`/apartments/${apartmentId.value}`, {
      method: 'PUT',
      body: {
        block: editForm.value.block || null,
        floor: editForm.value.floor,
        number: editForm.value.number,
        m2: editForm.value.m2,
        arsa_payi: editForm.value.arsa_payi,
        is_active: editForm.value.is_active,
      },
    })

    editDialog.value = false
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire guncellenemedi.')
    editErrors.value = getApiFieldErrors(error)
  }
  finally {
    editing.value = false
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
            @click="openEditDialog"
          >
            Duzenle
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
                md="4"
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
                md="2"
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
                <VSelect
                  v-model="residentForm.family_role"
                  :items="familyRoles"
                  item-title="label"
                  item-value="value"
                  label="Aile Rolu"
                  clearable
                  :error-messages="residentErrors.family_role ?? []"
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
              <th>Aile Rolu</th>
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
              <td>{{ resident.family_role_label || '-' }}</td>
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
                colspan="5"
                class="text-center text-medium-emphasis py-6"
              >
                Sakin kaydi yok.
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
                Tahakkuk kaydi yok.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>

  <VDialog
    v-model="editDialog"
    max-width="760"
  >
    <VCard title="Daire Duzenle">
      <VCardText>
        <VForm
          ref="editFormRef"
          @submit.prevent="submitEdit"
        >
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.block"
                label="Blok"
                :error-messages="editErrors.block ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.floor"
                type="number"
                label="Kat"
                :rules="editFloorRules"
                :error-messages="editErrors.floor ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.number"
                :label="$t('common.apartmentNo')"
                :rules="editNumberRules"
                :error-messages="editErrors.number ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editForm.m2"
                type="number"
                min="1"
                step="0.01"
                label="m2"
                :rules="editM2Rules"
                :error-messages="editErrors.m2 ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editForm.arsa_payi"
                type="number"
                min="0"
                step="0.000001"
                :label="$t('common.landShare')"
                :rules="editArsaPayiRules"
                :error-messages="editErrors.arsa_payi ?? []"
              />
            </VCol>

            <VCol cols="12">
              <VSwitch
                v-model="editForm.is_active"
                :label="$t('common.active')"
                color="primary"
              />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions class="px-6 pb-4">
        <VSpacer />
        <VBtn
          variant="outlined"
          :disabled="editing"
          @click="editDialog = false"
        >
          Iptal
        </VBtn>
        <VBtn
          color="primary"
          :loading="editing"
          :disabled="editing"
          @click="submitEdit"
        >
          Kaydet
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
