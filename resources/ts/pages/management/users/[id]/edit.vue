<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { formatDateTr as formatDate } from '@/utils/formatters'
import { emailRule, exactLengthRule, matchRule, minLengthRule, requiredRule } from '@/utils/validators'

interface UserDetailResponse {
  data: {
    id: number
    name: string
    email: string | null
    phone: string | null
    tc_kimlik: string | null
    address: string | null
    birth_date: string | null
    occupation: string | null
    education: string | null
    role: 'admin' | 'owner' | 'tenant' | 'vendor' | null
    role_label: string
    roles: string[]
    apartments: Array<{
      id: number
      label: string
      relation_type: 'owner' | 'tenant'
      relation_label: string
      start_date: string | null
      end_date: string | null
    }>
    apartment_count: number
  }
  meta: {
    available_apartments: Array<{ id: number; label: string }>
    roles: Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>
    relation_types: Array<{ value: 'owner' | 'tenant'; label: string }>
  }
}

const route = useRoute()
const router = useRouter()
const userId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const addingApartment = ref(false)
const removingApartmentId = ref<number | null>(null)
const updatingApartmentId = ref<number | null>(null)

const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const apartmentErrors = ref<Record<string, string[]>>({})

const roleOptions = ref<Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>>([])
const relationTypes = ref<Array<{ value: 'owner' | 'tenant'; label: string }>>([])
const availableApartments = ref<Array<{ id: number; label: string }>>([])
const apartments = ref<Array<{
  id: number
  label: string
  relation_type: 'owner' | 'tenant'
  relation_label: string
  start_date: string | null
  end_date: string | null
}>>([])

const educationOptions = [
  { value: '', label: 'Seciniz' },
  { value: 'ilkokul', label: 'Ilkokul' },
  { value: 'ortaokul', label: 'Ortaokul' },
  { value: 'lise', label: 'Lise' },
  { value: 'onlisans', label: 'On Lisans' },
  { value: 'lisans', label: 'Lisans' },
  { value: 'yuksek_lisans', label: 'Yuksek Lisans' },
  { value: 'doktora', label: 'Doktora' },
]

const form = ref({
  name: '',
  email: '',
  phone: '',
  tc_kimlik: '',
  address: '',
  birth_date: '',
  occupation: '',
  education: '',
  password: '',
  password_confirmation: '',
  role: 'owner' as 'admin' | 'owner' | 'tenant' | 'vendor',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const apartmentForm = ref({
  apartment_id: null as number | null,
  relation_type: 'owner' as 'owner' | 'tenant',
  start_date: new Date().toISOString().slice(0, 10),
})
const apartmentFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const nameRules = [requiredRule()]
const emailRules = [requiredRule(), emailRule()]
const tcKimlikRules = [
  (value: unknown) => {
    if (typeof value !== 'string' || value.length === 0)
      return true

    return exactLengthRule(11)(value)
  },
]
const passwordRules = [
  (value: unknown) => {
    if (typeof value !== 'string' || value.length === 0)
      return true

    return minLengthRule(8)(value)
  },
]
const passwordConfirmationRules = [
  (value: unknown) => {
    if (!form.value.password)
      return true

    return requiredRule('Şifre tekrar zorunludur.')(value)
  },
  (value: unknown) => {
    if (!form.value.password)
      return true

    return matchRule(() => form.value.password, 'Şifreler eşleşmiyor.')(value)
  },
]
const roleRules = [requiredRule()]
const apartmentRules = [requiredRule()]
const relationRules = [requiredRule()]

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<UserDetailResponse>(`/users/${userId.value}`)
    const payload = response.data

    form.value = {
      name: payload.name,
      email: payload.email ?? '',
      phone: payload.phone ?? '',
      tc_kimlik: payload.tc_kimlik ?? '',
      address: payload.address ?? '',
      birth_date: payload.birth_date ?? '',
      occupation: payload.occupation ?? '',
      education: payload.education ?? '',
      password: '',
      password_confirmation: '',
      role: (payload.role ?? 'owner') as 'admin' | 'owner' | 'tenant' | 'vendor',
    }

    apartments.value = payload.apartments
    availableApartments.value = response.meta.available_apartments
    roleOptions.value = response.meta.roles
    relationTypes.value = response.meta.relation_types

    if (!relationTypes.value.some(type => type.value === apartmentForm.value.relation_type))
      apartmentForm.value.relation_type = relationTypes.value[0]?.value ?? 'owner'
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kullanıcı detayı alınamadı.')
  }
  finally {
    loading.value = false
  }
}

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  saving.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  const payload: Record<string, unknown> = {
    name: form.value.name,
    email: form.value.email,
    phone: form.value.phone || null,
    tc_kimlik: form.value.tc_kimlik || null,
    address: form.value.address || null,
    birth_date: form.value.birth_date || null,
    occupation: form.value.occupation || null,
    education: form.value.education || null,
    role: form.value.role,
  }

  if (form.value.password) {
    payload.password = form.value.password
    payload.password_confirmation = form.value.password_confirmation
  }

  try {
    await $api(`/users/${userId.value}`, {
      method: 'PUT',
      body: payload,
    })

    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kullanıcı güncellenemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

const deleteUser = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await $api(`/users/${userId.value}`, { method: 'DELETE' })
    await router.push('/management/users')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kullanıcı silinemedi.')
  }
  finally {
    deleting.value = false
  }
}

const addApartment = async () => {
  const validation = await apartmentFormRef.value?.validate()
  if (!validation?.valid)
    return

  addingApartment.value = true
  errorMessage.value = ''
  apartmentErrors.value = {}

  try {
    await $api(`/users/${userId.value}/apartments`, {
      method: 'POST',
      body: {
        apartment_id: apartmentForm.value.apartment_id,
        relation_type: apartmentForm.value.relation_type,
        start_date: apartmentForm.value.start_date || null,
      },
    })

    apartmentForm.value.apartment_id = null
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire ilişkisi eklenemedi.')
    apartmentErrors.value = getApiFieldErrors(error)
  }
  finally {
    addingApartment.value = false
  }
}

const removeApartment = async (apartmentId: number) => {
  removingApartmentId.value = apartmentId
  errorMessage.value = ''

  try {
    await $api(`/users/${userId.value}/apartments/${apartmentId}`, { method: 'DELETE' })
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire ilişkisi kaldırılamadı.')
  }
  finally {
    removingApartmentId.value = null
  }
}

const updateApartmentRelation = async (apartment: {
  id: number
  relation_type: 'owner' | 'tenant'
  start_date: string | null
}) => {
  updatingApartmentId.value = apartment.id
  errorMessage.value = ''

  try {
    await $api(`/users/${userId.value}/apartments/${apartment.id}`, {
      method: 'PUT',
      body: {
        relation_type: apartment.relation_type,
        start_date: apartment.start_date || null,
      },
    })
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire iliskisi guncellenemedi.')
  }
  finally {
    updatingApartmentId.value = null
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
            {{ $t('pages.users.editTitle') }}
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.users.editSubtitle') }}
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/management/users"
          >
            Listeye Don
          </VBtn>
          <VBtn
            color="error"
            variant="outlined"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteUser"
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

    <VCol cols="12">
      <VCard :loading="loading || saving">
        <VCardText>
          <VForm
            ref="formRef"
            @submit.prevent="submit"
          >
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.name"
                  label="Ad Soyad"
                  :rules="nameRules"
                  :error-messages="fieldErrors.name ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.email"
                  type="email"
                  label="E-posta"
                  :rules="emailRules"
                  :error-messages="fieldErrors.email ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.phone"
                  label="Telefon"
                  :error-messages="fieldErrors.phone ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.tc_kimlik"
                  label="TC Kimlik"
                  maxlength="11"
                  :rules="tcKimlikRules"
                  :error-messages="fieldErrors.tc_kimlik ?? []"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="form.address"
                  label="Adres"
                  rows="2"
                  :error-messages="fieldErrors.address ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.birth_date"
                  type="date"
                  label="Dogum Tarihi"
                  :error-messages="fieldErrors.birth_date ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.occupation"
                  label="Meslek"
                  :error-messages="fieldErrors.occupation ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.education"
                  :items="educationOptions"
                  item-title="label"
                  item-value="value"
                  label="Ogrenim Durumu"
                  :error-messages="fieldErrors.education ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.password"
                  type="password"
                  label="Yeni Şifre"
                  :rules="passwordRules"
                  :error-messages="fieldErrors.password ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.password_confirmation"
                  type="password"
                  label="Şifre Tekrar"
                  :rules="passwordConfirmationRules"
                  :error-messages="fieldErrors.password_confirmation ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.role"
                  :items="roleOptions"
                  item-title="label"
                  item-value="value"
                  label="Rol"
                  :rules="roleRules"
                  :error-messages="fieldErrors.role ?? []"
                />
              </VCol>
              <VCol cols="12">
                <div class="d-flex justify-end">
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="saving"
                    :disabled="saving"
                  >
                    {{ $t('common.update') }}
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
        <VCardItem title="Bağlı Daireler" />
        <VCardText>
          <VForm
            ref="apartmentFormRef"
            @submit.prevent="addApartment"
          >
            <VRow>
              <VCol
                cols="12"
                md="5"
              >
                <VSelect
                  v-model="apartmentForm.apartment_id"
                  :items="availableApartments"
                  item-title="label"
                  item-value="id"
                  :label="$t('common.apartment')"
                  :rules="apartmentRules"
                  :error-messages="apartmentErrors.apartment_id ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
              >
                <VSelect
                  v-model="apartmentForm.relation_type"
                  :items="relationTypes"
                  item-title="label"
                  item-value="value"
                  :label="$t('common.type')"
                  :rules="relationRules"
                  :error-messages="apartmentErrors.relation_type ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="2"
              >
                <VTextField
                  v-model="apartmentForm.start_date"
                  type="date"
                  :label="$t('common.startDate')"
                  :error-messages="apartmentErrors.start_date ?? []"
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
                  :loading="addingApartment"
                  :disabled="addingApartment"
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
              <th>{{ $t('common.apartment') }}</th>
              <th>{{ $t('common.type') }}</th>
              <th>{{ $t('common.startDate') }}</th>
              <th class="text-right">
                Islem
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="apartment in apartments"
              :key="apartment.id"
            >
              <td>{{ apartment.label }}</td>
              <td>
                <VSelect
                  v-model="apartment.relation_type"
                  :items="relationTypes"
                  item-title="label"
                  item-value="value"
                  density="compact"
                  hide-details
                  :disabled="updatingApartmentId === apartment.id || removingApartmentId === apartment.id"
                />
              </td>
              <td>{{ formatDate(apartment.start_date) }}</td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="primary"
                  :loading="updatingApartmentId === apartment.id"
                  :disabled="updatingApartmentId === apartment.id || removingApartmentId === apartment.id"
                  @click="updateApartmentRelation(apartment)"
                >
                  <VIcon icon="ri-save-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="removingApartmentId === apartment.id"
                  :disabled="removingApartmentId === apartment.id || updatingApartmentId === apartment.id"
                  @click="removeApartment(apartment.id)"
                >
                  <VIcon icon="ri-close-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="apartments.length === 0">
              <td
                colspan="4"
                class="text-center text-medium-emphasis py-6"
              >
                Bağlı daire yok.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>
</template>
