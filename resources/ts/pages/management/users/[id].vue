<script setup lang="ts">
import { getApiErrorMessage, getApiErrorStatus, getApiFieldErrors } from '@/utils/errorHandler'
import { $api, getApiBaseUrl } from '@/utils/api'
import { formatCurrency, formatDateTr as formatDate } from '@/utils/formatters'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { emailRule, exactLengthRule, matchRule, minLengthRule, positiveNumberRule, requiredRule } from '@/utils/validators'
import { useAuthSession } from '@/composables/useAuthSession'

const authSession = useAuthSession()
const route = useRoute()
const userId = computed(() => Number((route.params as Record<string, unknown>).id))
const { withAbort } = useAbortOnUnmount()

// State
const loading = ref(false)
const errorMessage = ref('')
const residentApiWarning = ref('')
const activeTab = ref('debts')
const residentApiAvailable = ref(true)
const canManageUsers = computed(() => authSession.can('users.manage'))

// Resident data
const educationLabels: Record<string, string> = {
  ilkokul: 'Ilkokul',
  ortaokul: 'Ortaokul',
  lise: 'Lise',
  onlisans: 'On Lisans',
  lisans: 'Lisans',
  yuksek_lisans: 'Yuksek Lisans',
  doktora: 'Doktora',
}

interface ResidentDetail {
  id: number
  name: string
  email: string | null
  phone: string | null
  tc_kimlik: string | null
  address: string | null
  birth_date: string | null
  occupation: string | null
  education: string | null
  role: string | null
  role_label: string
  roles: string[]
  apartments: Array<{
    id: number
    label: string
    relation_type: string
    relation_label: string
    start_date: string | null
  }>
  apartment_count: number
  balance: number
  total_charged: number
  total_paid: number
  open_charges: Array<{
    id: number
    period: string
    due_date: string | null
    amount: number
    paid_amount: number
    remaining: number
    description: string | null
    status: string
    apartment: { id: number; label: string } | null
    account: { id: number; name: string } | null
  }>
  archived_at: string | null
}

interface NoteItem {
  id: number
  content: string
  created_by: { id: number; name: string } | null
  created_at: string
}

interface DocumentItem {
  id: number
  file_name: string
  file_size: number
  mime_type: string | null
  uploaded_by: { id: number; name: string } | null
  created_at: string
}

interface ReminderItem {
  id: number
  title: string
  description: string | null
  due_date: string
  completed_at: string | null
  is_completed: boolean
  created_by: { id: number; name: string } | null
  created_at: string
}

interface StatementTransaction {
  date: string
  description: string
  type: string
  direction: string
  receipt_no?: string | null
  amount: number
  balance: number
}

interface UserFallbackDetailResponse {
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
    role: string | null
    role_label: string
    roles: string[]
    apartments?: Array<{
      id: number
      label: string
      relation_type: string | null
      relation_label: string
      start_date: string | null
    }>
    apartment_count?: number
  }
}

type UserRoleValue = 'admin' | 'owner' | 'tenant' | 'vendor'

interface UserEditApartment {
  id: number
  label: string
  relation_type: 'owner' | 'tenant'
  relation_label: string
  start_date: string
  end_date: string | null
}

interface UserEditDetailResponse {
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
    role: UserRoleValue | null
    apartments: UserEditApartment[]
  }
  meta: {
    available_apartments: Array<{ id: number; label: string }>
    roles: Array<{ value: UserRoleValue; label: string }>
    relation_types: Array<{ value: 'owner' | 'tenant'; label: string }>
  }
}

const detail = ref<ResidentDetail | null>(null)
const notes = ref<NoteItem[]>([])
const documents = ref<DocumentItem[]>([])
const reminders = ref<ReminderItem[]>([])

// Statement
const statementLoading = ref(false)
const statementData = ref<{
  from: string
  to: string
  opening_balance: number
  closing_balance: number
  transactions: StatementTransaction[]
} | null>(null)
const statementFilters = ref({
  from: new Date(new Date().getFullYear(), 0, 1).toISOString().slice(0, 10),
  to: new Date().toISOString().slice(0, 10),
})

// Note form
const noteContent = ref('')
const addingNote = ref(false)

// Reminder dialog
const reminderDialog = ref(false)
const reminderForm = ref({ title: '', description: '', due_date: '' })
const addingReminder = ref(false)
const reminderErrors = ref<Record<string, string[]>>({})

// Opening balance dialog
const openingBalanceDialog = ref(false)
const openingBalanceForm = ref({ apartment_id: null as number | null, amount: null as number | null, due_date: new Date().toISOString().slice(0, 10), description: '' })
const addingOpeningBalance = ref(false)
const openingBalanceErrors = ref<Record<string, string[]>>({})

// Transfer dialog
const transferDialog = ref(false)
const transferForm = ref({ source_apartment_id: null as number | null, target_apartment_id: null as number | null, amount: null as number | null, description: '' })
const transferring = ref(false)
const transferErrors = ref<Record<string, string[]>>({})

// Actions dropdown
const otherMenu = ref(false)

// Upload
const uploading = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

// Archive
const archiving = ref(false)

// Apartment options for dialogs
const apartmentOptions = computed(() =>
  detail.value?.apartments.map(a => ({ id: a.id, label: a.label })) ?? [],
)

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

const editDialog = ref(false)
const editLoading = ref(false)
const editSaving = ref(false)
const editErrors = ref<Record<string, string[]>>({})
const editRoleOptions = ref<Array<{ value: UserRoleValue; label: string }>>([])
const editRelationTypes = ref<Array<{ value: 'owner' | 'tenant'; label: string }>>([])
const editAvailableApartments = ref<Array<{ id: number; label: string }>>([])
const editApartments = ref<UserEditApartment[]>([])
const addingEditApartment = ref(false)
const updatingEditApartmentId = ref<number | null>(null)
const removingEditApartmentId = ref<number | null>(null)
const editApartmentErrors = ref<Record<string, string[]>>({})

const editForm = ref({
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
  role: 'owner' as UserRoleValue,
})
const editFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const editApartmentForm = ref({
  apartment_id: null as number | null,
  relation_type: 'owner' as 'owner' | 'tenant',
  start_date: new Date().toISOString().slice(0, 10),
})
const editApartmentFormRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const editNameRules = [requiredRule()]
const editEmailRules = [requiredRule(), emailRule()]
const editTcKimlikRules = [
  (value: unknown) => {
    if (typeof value !== 'string' || value.length === 0)
      return true

    return exactLengthRule(11)(value)
  },
]
const editPasswordRules = [
  (value: unknown) => {
    if (typeof value !== 'string' || value.length === 0)
      return true

    return minLengthRule(8)(value)
  },
]
const editPasswordConfirmationRules = [
  (value: unknown) => {
    if (!editForm.value.password)
      return true

    return requiredRule('Sifre tekrar zorunludur.')(value)
  },
  (value: unknown) => {
    if (!editForm.value.password)
      return true

    return matchRule(() => editForm.value.password, 'Sifreler eslesmiyor.')(value)
  },
]
const editRoleRules = [requiredRule()]
const editApartmentRules = [requiredRule()]
const editRelationRules = [requiredRule()]

const isMissingResidentsRouteError = (error: unknown) => {
  if (getApiErrorStatus(error) !== 404)
    return false

  const message = getApiErrorMessage(error, '').toLowerCase()

  return message.includes('route') && message.includes('residents')
}

const applyFallbackUserDetail = (payload: UserFallbackDetailResponse['data']) => {
  detail.value = {
    id: payload.id,
    name: payload.name,
    email: payload.email,
    phone: payload.phone,
    tc_kimlik: payload.tc_kimlik,
    address: payload.address,
    birth_date: payload.birth_date,
    occupation: payload.occupation,
    education: payload.education,
    role: payload.role,
    role_label: payload.role_label,
    roles: payload.roles,
    apartments: (payload.apartments ?? []).map(apartment => ({
      id: apartment.id,
      label: apartment.label,
      relation_type: apartment.relation_type ?? '',
      relation_label: apartment.relation_label,
      start_date: apartment.start_date,
    })),
    apartment_count: payload.apartment_count ?? payload.apartments?.length ?? 0,
    balance: 0,
    total_charged: 0,
    total_paid: 0,
    open_charges: [],
    archived_at: null,
  }
}

// Fetch detail
const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await withAbort(signal =>
      $api<{ data: ResidentDetail }>(`/residents/${userId.value}`, { signal }),
    )
    residentApiAvailable.value = true
    residentApiWarning.value = ''
    detail.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return

    if (isMissingResidentsRouteError(error)) {
      try {
        const fallbackResponse = await withAbort(signal =>
          $api<UserFallbackDetailResponse>(`/users/${userId.value}`, { signal }),
        )

        residentApiAvailable.value = false
        residentApiWarning.value = 'Bu sunucuda /residents API routeu bulunmuyor. Temel kullanici bilgileri gosteriliyor.'
        applyFallbackUserDetail(fallbackResponse.data)
        return
      }
      catch (fallbackError) {
        if (isAbortError(fallbackError)) return
        errorMessage.value = getApiErrorMessage(fallbackError, 'Kullanici detayi alinamadi.')
        return
      }
    }

    errorMessage.value = getApiErrorMessage(error, 'Sakin detayi alinamadi.')
  }
  finally {
    loading.value = false
  }
}

// Fetch notes
const fetchNotes = async () => {
  if (!residentApiAvailable.value) return

  try {
    const response = await withAbort(signal =>
      $api<{ data: NoteItem[] }>(`/residents/${userId.value}/notes`, { signal }),
    )
    notes.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
  }
}

// Fetch documents
const fetchDocuments = async () => {
  if (!residentApiAvailable.value) return

  try {
    const response = await withAbort(signal =>
      $api<{ data: DocumentItem[] }>(`/residents/${userId.value}/documents`, { signal }),
    )
    documents.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
  }
}

// Fetch reminders
const fetchReminders = async () => {
  if (!residentApiAvailable.value) return

  try {
    const response = await withAbort(signal =>
      $api<{ data: ReminderItem[] }>(`/residents/${userId.value}/reminders`, { signal }),
    )
    reminders.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
  }
}

// Fetch statement
const fetchStatement = async () => {
  if (!residentApiAvailable.value) return

  statementLoading.value = true
  try {
    const response = await withAbort(signal =>
      $api<{ data: typeof statementData.value }>(`/residents/${userId.value}/statement`, {
        query: { from: statementFilters.value.from, to: statementFilters.value.to },
        signal,
      }),
    )
    statementData.value = response.data
  }
  catch (error) {
    if (isAbortError(error)) return
  }
  finally {
    statementLoading.value = false
  }
}

// Add note
const addNote = async () => {
  if (!residentApiAvailable.value) return

  if (!noteContent.value.trim()) return
  addingNote.value = true
  try {
    await $api(`/residents/${userId.value}/notes`, {
      method: 'POST',
      body: { content: noteContent.value },
    })
    noteContent.value = ''
    await fetchNotes()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Not eklenemedi.')
  }
  finally {
    addingNote.value = false
  }
}

// Delete note
const deleteNote = async (noteId: number) => {
  if (!residentApiAvailable.value) return

  try {
    await $api(`/residents/${userId.value}/notes/${noteId}`, { method: 'DELETE' })
    await fetchNotes()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Not silinemedi.')
  }
}

// Upload document
const triggerUpload = () => fileInput.value?.click()

const uploadFile = async (event: Event) => {
  if (!residentApiAvailable.value) return

  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)
    await $api(`/residents/${userId.value}/documents`, { method: 'POST', body: formData })
    await fetchDocuments()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Evrak yuklenemedi.')
  }
  finally {
    uploading.value = false
    target.value = ''
  }
}

// Download document
const downloadDocument = (docId: number) => {
  if (!residentApiAvailable.value) return

  window.open(`${getApiBaseUrl()}/residents/${userId.value}/documents/${docId}/download`, '_blank')
}

// Delete document
const deleteDocument = async (docId: number) => {
  if (!residentApiAvailable.value) return

  try {
    await $api(`/residents/${userId.value}/documents/${docId}`, { method: 'DELETE' })
    await fetchDocuments()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Evrak silinemedi.')
  }
}

// Add reminder
const submitReminder = async () => {
  if (!residentApiAvailable.value) return

  addingReminder.value = true
  reminderErrors.value = {}
  try {
    await $api(`/residents/${userId.value}/reminders`, {
      method: 'POST',
      body: reminderForm.value,
    })
    reminderDialog.value = false
    reminderForm.value = { title: '', description: '', due_date: '' }
    await fetchReminders()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hatirlatma eklenemedi.')
    reminderErrors.value = getApiFieldErrors(error)
  }
  finally {
    addingReminder.value = false
  }
}

// Toggle reminder complete
const toggleReminder = async (reminder: ReminderItem) => {
  if (!residentApiAvailable.value) return

  try {
    await $api(`/residents/${userId.value}/reminders/${reminder.id}`, {
      method: 'PUT',
      body: { completed: !reminder.is_completed },
    })
    await fetchReminders()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hatirlatma guncellenemedi.')
  }
}

// Delete reminder
const deleteReminder = async (reminderId: number) => {
  if (!residentApiAvailable.value) return

  try {
    await $api(`/residents/${userId.value}/reminders/${reminderId}`, { method: 'DELETE' })
    await fetchReminders()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Hatirlatma silinemedi.')
  }
}

// Opening balance
const submitOpeningBalance = async () => {
  if (!residentApiAvailable.value) return

  addingOpeningBalance.value = true
  openingBalanceErrors.value = {}
  try {
    await $api(`/residents/${userId.value}/opening-balance`, {
      method: 'POST',
      body: openingBalanceForm.value,
    })
    openingBalanceDialog.value = false
    openingBalanceForm.value = { apartment_id: null, amount: null, due_date: new Date().toISOString().slice(0, 10), description: '' }
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Acilis bakiyesi eklenemedi.')
    openingBalanceErrors.value = getApiFieldErrors(error)
  }
  finally {
    addingOpeningBalance.value = false
  }
}

// Transfer debt
const submitTransfer = async () => {
  if (!residentApiAvailable.value) return

  transferring.value = true
  transferErrors.value = {}
  try {
    await $api('/residents/transfer-debt', {
      method: 'POST',
      body: transferForm.value,
    })
    transferDialog.value = false
    transferForm.value = { source_apartment_id: null, target_apartment_id: null, amount: null, description: '' }
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Borc aktarma basarisiz.')
    transferErrors.value = getApiFieldErrors(error)
  }
  finally {
    transferring.value = false
  }
}

// Archive / Unarchive
const toggleArchive = async () => {
  if (!residentApiAvailable.value) return

  archiving.value = true
  try {
    const action = detail.value?.archived_at ? 'unarchive' : 'archive'
    await $api(`/residents/${userId.value}/${action}`, { method: 'PUT' })
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Islem basarisiz.')
  }
  finally {
    archiving.value = false
  }
}

const applyEditDetail = (payload: UserEditDetailResponse['data']) => {
  editForm.value = {
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
    role: (payload.role ?? 'owner') as UserRoleValue,
  }
  editApartments.value = payload.apartments.map(apartment => ({
    id: apartment.id,
    label: apartment.label,
    relation_type: apartment.relation_type,
    relation_label: apartment.relation_label,
    start_date: apartment.start_date ?? '',
    end_date: apartment.end_date,
  }))
}

const loadEditData = async () => {
  editLoading.value = true
  editErrors.value = {}
  editApartmentErrors.value = {}

  try {
    const response = await $api<UserEditDetailResponse>(`/users/${userId.value}`)

    applyEditDetail(response.data)
    editRoleOptions.value = response.meta.roles
    editRelationTypes.value = response.meta.relation_types
    editAvailableApartments.value = response.meta.available_apartments

    if (!editRelationTypes.value.some(type => type.value === editApartmentForm.value.relation_type))
      editApartmentForm.value.relation_type = editRelationTypes.value[0]?.value ?? 'owner'
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kullanici duzenleme verileri alinamadi.')
    throw error
  }
  finally {
    editLoading.value = false
  }
}

const openEditDialog = async () => {
  otherMenu.value = false

  if (!canManageUsers.value) {
    errorMessage.value = 'Bu islem icin kullanici yonetimi yetkisi gerekiyor.'
    return
  }

  try {
    await loadEditData()
    editDialog.value = true
  }
  catch {
  }
}

const submitEdit = async () => {
  const validation = await editFormRef.value?.validate()
  if (!validation?.valid)
    return

  editSaving.value = true
  editErrors.value = {}
  errorMessage.value = ''

  const payload: Record<string, unknown> = {
    name: editForm.value.name,
    email: editForm.value.email,
    phone: editForm.value.phone || null,
    tc_kimlik: editForm.value.tc_kimlik || null,
    address: editForm.value.address || null,
    birth_date: editForm.value.birth_date || null,
    occupation: editForm.value.occupation || null,
    education: editForm.value.education || null,
    role: editForm.value.role,
  }

  if (editForm.value.password) {
    payload.password = editForm.value.password
    payload.password_confirmation = editForm.value.password_confirmation
  }

  try {
    await $api(`/users/${userId.value}`, {
      method: 'PUT',
      body: payload,
    })

    editDialog.value = false
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Kullanici guncellenemedi.')
    editErrors.value = getApiFieldErrors(error)
  }
  finally {
    editSaving.value = false
  }
}

const addApartmentToEditUser = async () => {
  const validation = await editApartmentFormRef.value?.validate()
  if (!validation?.valid)
    return

  addingEditApartment.value = true
  editApartmentErrors.value = {}
  errorMessage.value = ''

  try {
    await $api(`/users/${userId.value}/apartments`, {
      method: 'POST',
      body: {
        apartment_id: editApartmentForm.value.apartment_id,
        relation_type: editApartmentForm.value.relation_type,
        start_date: editApartmentForm.value.start_date || null,
      },
    })

    editApartmentForm.value.apartment_id = null
    await loadEditData()
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire iliskisi eklenemedi.')
    editApartmentErrors.value = getApiFieldErrors(error)
  }
  finally {
    addingEditApartment.value = false
  }
}

const updateEditApartmentRelation = async (apartment: UserEditApartment) => {
  updatingEditApartmentId.value = apartment.id
  errorMessage.value = ''

  try {
    await $api(`/users/${userId.value}/apartments/${apartment.id}`, {
      method: 'PUT',
      body: {
        relation_type: apartment.relation_type,
        start_date: apartment.start_date || null,
      },
    })

    await loadEditData()
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire iliskisi guncellenemedi.')
  }
  finally {
    updatingEditApartmentId.value = null
  }
}

const removeEditApartment = async (apartmentIdToRemove: number) => {
  removingEditApartmentId.value = apartmentIdToRemove
  errorMessage.value = ''

  try {
    await $api(`/users/${userId.value}/apartments/${apartmentIdToRemove}`, { method: 'DELETE' })
    await loadEditData()
    await fetchDetail()
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Daire iliskisi kaldirilamadi.')
  }
  finally {
    removingEditApartmentId.value = null
  }
}

// Format file size
const formatFileSize = (bytes: number) => {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

onMounted(async () => {
  await fetchDetail()
  if (residentApiAvailable.value) {
    fetchNotes()
    fetchDocuments()
    fetchReminders()
  }
})
</script>

<template>
  <VRow class="align-content-start">
    <!-- Header -->
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Sakin Detay
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ detail?.name ?? '-' }}
          </p>
        </div>
        <div class="d-flex gap-2">
          <VBtn variant="outlined" to="/management/users">
            Listeye Don
          </VBtn>
          <VBtn color="primary" :disabled="!canManageUsers" @click="openEditDialog">
            Duzenle
          </VBtn>
        </div>
      </div>
    </VCol>

    <VCol v-if="errorMessage" cols="12">
      <VAlert type="error" variant="tonal" closable @click:close="errorMessage = ''">
        {{ errorMessage }}
      </VAlert>
    </VCol>

    <VCol v-if="residentApiWarning" cols="12">
      <VAlert type="warning" variant="tonal" closable @click:close="residentApiWarning = ''">
        {{ residentApiWarning }}
      </VAlert>
    </VCol>

    <!-- Left Column -->
    <VCol cols="12" md="8">
      <!-- Personal Info Card -->
      <VCard :loading="loading" class="mb-4">
        <VCardText v-if="detail">
          <VList class="card-list">
            <VListItem>
              <VListItemTitle>Hesap</VListItemTitle>
              <template #append>
                <span class="font-weight-medium">{{ detail.name }}</span>
              </template>
            </VListItem>
            <VListItem v-if="detail.apartments.length">
              <VListItemTitle>Daire</VListItemTitle>
              <template #append>
                <div class="d-flex gap-1 flex-wrap">
                  <VChip v-for="apt in detail.apartments" :key="apt.id" size="small" color="primary" variant="tonal">
                    {{ apt.label }}
                  </VChip>
                </div>
              </template>
            </VListItem>
            <VListItem v-if="detail.phone">
              <VListItemTitle>Telefon</VListItemTitle>
              <template #append>
                <div class="d-flex align-center gap-2">
                  <span>{{ detail.phone }}</span>
                  <VBtn
                    icon
                    size="x-small"
                    variant="text"
                    color="success"
                    :href="`https://wa.me/${detail.phone.replace(/[^0-9]/g, '')}`"
                    target="_blank"
                  >
                    <VIcon icon="ri-whatsapp-line" />
                  </VBtn>
                  <VBtn
                    icon
                    size="x-small"
                    variant="text"
                    color="info"
                    :href="`sms:${detail.phone}`"
                  >
                    <VIcon icon="ri-message-2-line" />
                  </VBtn>
                </div>
              </template>
            </VListItem>
            <VListItem v-if="detail.email">
              <VListItemTitle>E-posta</VListItemTitle>
              <template #append>
                <span>{{ detail.email }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Rol</VListItemTitle>
              <template #append>
                <VChip size="small" variant="tonal">
                  {{ detail.role_label }}
                </VChip>
              </template>
            </VListItem>
            <VListItem v-if="detail.occupation">
              <VListItemTitle>Meslek</VListItemTitle>
              <template #append>
                <span>{{ detail.occupation }}</span>
              </template>
            </VListItem>
            <VListItem v-if="detail.education">
              <VListItemTitle>Ogrenim</VListItemTitle>
              <template #append>
                <span>{{ educationLabels[detail.education] ?? detail.education }}</span>
              </template>
            </VListItem>
            <VListItem v-if="detail.birth_date">
              <VListItemTitle>Dogum Tarihi</VListItemTitle>
              <template #append>
                <span>{{ formatDate(detail.birth_date) }}</span>
              </template>
            </VListItem>
            <VListItem v-if="detail.address">
              <VListItemTitle>Adres</VListItemTitle>
              <template #append>
                <span class="text-right" style="max-inline-size: 250px">{{ detail.address }}</span>
              </template>
            </VListItem>
            <VListItem v-if="detail.archived_at">
              <VListItemTitle>Durum</VListItemTitle>
              <template #append>
                <VChip size="small" color="warning" variant="tonal">
                  Arsivli
                </VChip>
              </template>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>

      <!-- Tabs -->
      <VCard>
        <VTabs v-model="activeTab" class="v-tabs-pill">
          <VTab value="debts">
            Borclari
          </VTab>
          <VTab v-if="residentApiAvailable" value="notes">
            Notlar
          </VTab>
          <VTab v-if="residentApiAvailable" value="documents">
            Evraklar
          </VTab>
          <VTab v-if="residentApiAvailable" value="reminders">
            Hatirlatmalar
          </VTab>
          <VTab v-if="residentApiAvailable" value="statement" @click="fetchStatement">
            Hesap Ekstresi
          </VTab>
        </VTabs>

        <VDivider />

        <!-- Debts Tab -->
        <div v-show="activeTab === 'debts'">
          <VTable density="comfortable">
            <thead>
              <tr>
                <th>Donem</th>
                <th>Son Odeme</th>
                <th>Aciklama</th>
                <th class="text-right">
                  Tutar
                </th>
                <th class="text-right">
                  Kalan
                </th>
                <th>Durum</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="charge in detail?.open_charges ?? []" :key="charge.id">
                <td>{{ charge.period }}</td>
                <td>{{ formatDate(charge.due_date) }}</td>
                <td>
                  <div>{{ charge.description || '-' }}</div>
                  <div v-if="charge.apartment" class="text-caption text-medium-emphasis">
                    {{ charge.apartment.label }}
                  </div>
                </td>
                <td class="text-right">
                  {{ formatCurrency(charge.amount) }}
                </td>
                <td class="text-right font-weight-medium">
                  {{ formatCurrency(charge.remaining) }}
                </td>
                <td>
                  <VChip
                    size="small"
                    :color="charge.status === 'overdue' ? 'error' : charge.status === 'paid' ? 'success' : 'warning'"
                    variant="tonal"
                  >
                    {{ charge.status === 'overdue' ? 'Gecikmis' : charge.status === 'paid' ? 'Odendi' : 'Acik' }}
                  </VChip>
                </td>
              </tr>
              <tr v-if="(detail?.open_charges ?? []).length === 0">
                <td colspan="6" class="text-center text-medium-emphasis py-6">
                  Odenmemis borcu/alacagi yoktur
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>

        <!-- Notes Tab -->
        <div v-if="residentApiAvailable" v-show="activeTab === 'notes'">
          <VCardText>
            <VRow>
              <VCol cols="12" md="10">
                <VTextarea v-model="noteContent" label="Not ekleyin..." rows="2" hide-details />
              </VCol>
              <VCol cols="12" md="2" class="d-flex align-end">
                <VBtn color="primary" block :loading="addingNote" :disabled="!noteContent.trim()" @click="addNote">
                  Ekle
                </VBtn>
              </VCol>
            </VRow>
          </VCardText>
          <VDivider />
          <VList v-if="notes.length">
            <template v-for="(note, i) in notes" :key="note.id">
              <VListItem>
                <VListItemTitle>{{ note.content }}</VListItemTitle>
                <VListItemSubtitle>
                  {{ note.created_by?.name ?? '-' }} - {{ formatDate(note.created_at) }}
                </VListItemSubtitle>
                <template #append>
                  <VBtn icon size="small" variant="text" color="error" @click="deleteNote(note.id)">
                    <VIcon icon="ri-delete-bin-line" />
                  </VBtn>
                </template>
              </VListItem>
              <VDivider v-if="i < notes.length - 1" />
            </template>
          </VList>
          <VCardText v-else class="text-center text-medium-emphasis">
            Henuz not eklenmemis.
          </VCardText>
        </div>

        <!-- Documents Tab -->
        <div v-if="residentApiAvailable" v-show="activeTab === 'documents'">
          <VCardText>
            <input ref="fileInput" type="file" class="d-none" @change="uploadFile">
            <VBtn color="primary" prepend-icon="ri-upload-2-line" :loading="uploading" @click="triggerUpload">
              Evrak Yukle
            </VBtn>
          </VCardText>
          <VTable v-if="documents.length" density="comfortable">
            <thead>
              <tr>
                <th>Dosya Adi</th>
                <th>Boyut</th>
                <th>Yukleyen</th>
                <th>Tarih</th>
                <th class="text-right">
                  Islem
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="doc in documents" :key="doc.id">
                <td>{{ doc.file_name }}</td>
                <td>{{ formatFileSize(doc.file_size) }}</td>
                <td>{{ doc.uploaded_by?.name ?? '-' }}</td>
                <td>{{ formatDate(doc.created_at) }}</td>
                <td class="text-right">
                  <VBtn icon size="small" variant="text" @click="downloadDocument(doc.id)">
                    <VIcon icon="ri-download-line" />
                  </VBtn>
                  <VBtn icon size="small" variant="text" color="error" @click="deleteDocument(doc.id)">
                    <VIcon icon="ri-delete-bin-line" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
          <VCardText v-else class="text-center text-medium-emphasis">
            Henuz evrak yuklenmemis.
          </VCardText>
        </div>

        <!-- Reminders Tab -->
        <div v-if="residentApiAvailable" v-show="activeTab === 'reminders'">
          <VCardText>
            <VBtn color="primary" prepend-icon="ri-add-line" @click="reminderDialog = true">
              Hatirlatma Ekle
            </VBtn>
          </VCardText>
          <VTable v-if="reminders.length" density="comfortable">
            <thead>
              <tr>
                <th />
                <th>Baslik</th>
                <th>Tarih</th>
                <th>Durum</th>
                <th class="text-right">
                  Islem
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="reminder in reminders" :key="reminder.id">
                <td>
                  <VCheckbox
                    :model-value="reminder.is_completed"
                    hide-details
                    density="compact"
                    @update:model-value="toggleReminder(reminder)"
                  />
                </td>
                <td :class="{ 'text-decoration-line-through text-medium-emphasis': reminder.is_completed }">
                  <div>{{ reminder.title }}</div>
                  <div v-if="reminder.description" class="text-caption text-medium-emphasis">
                    {{ reminder.description }}
                  </div>
                </td>
                <td>{{ formatDate(reminder.due_date) }}</td>
                <td>
                  <VChip
                    size="small"
                    :color="reminder.is_completed ? 'success' : 'warning'"
                    variant="tonal"
                  >
                    {{ reminder.is_completed ? 'Tamamlandi' : 'Bekliyor' }}
                  </VChip>
                </td>
                <td class="text-right">
                  <VBtn icon size="small" variant="text" color="error" @click="deleteReminder(reminder.id)">
                    <VIcon icon="ri-delete-bin-line" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
          <VCardText v-else class="text-center text-medium-emphasis">
            Henuz hatirlatma eklenmemis.
          </VCardText>
        </div>

        <!-- Statement Tab -->
        <div v-if="residentApiAvailable" v-show="activeTab === 'statement'">
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <VTextField v-model="statementFilters.from" type="date" label="Baslangic" />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField v-model="statementFilters.to" type="date" label="Bitis" />
              </VCol>
              <VCol cols="12" md="4" class="d-flex align-end">
                <VBtn color="primary" block @click="fetchStatement">
                  Sorgula
                </VBtn>
              </VCol>
            </VRow>
          </VCardText>

          <template v-if="statementData">
            <VCardText>
              <VRow>
                <VCol cols="12" md="4">
                  <div class="text-caption text-medium-emphasis">
                    Acilis Bakiyesi
                  </div>
                  <div class="text-h6">
                    {{ formatCurrency(statementData.opening_balance) }}
                  </div>
                </VCol>
                <VCol cols="12" md="4">
                  <div class="text-caption text-medium-emphasis">
                    Kapanis Bakiyesi
                  </div>
                  <div class="text-h6">
                    {{ formatCurrency(statementData.closing_balance) }}
                  </div>
                </VCol>
                <VCol cols="12" md="4">
                  <div class="text-caption text-medium-emphasis">
                    Donem
                  </div>
                  <div class="font-weight-medium">
                    {{ formatDate(statementData.from) }} - {{ formatDate(statementData.to) }}
                  </div>
                </VCol>
              </VRow>
            </VCardText>
            <VTable density="comfortable">
              <thead>
                <tr>
                  <th>Tarih</th>
                  <th>Aciklama</th>
                  <th class="text-right">
                    Borc
                  </th>
                  <th class="text-right">
                    Alacak
                  </th>
                  <th class="text-right">
                    Bakiye
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(tx, i) in statementData.transactions" :key="i">
                  <td>{{ formatDate(tx.date) }}</td>
                  <td>{{ tx.description }}</td>
                  <td class="text-right text-error">
                    {{ tx.direction === 'debit' ? formatCurrency(tx.amount) : '-' }}
                  </td>
                  <td class="text-right text-success">
                    {{ tx.direction === 'credit' ? formatCurrency(tx.amount) : '-' }}
                  </td>
                  <td class="text-right font-weight-medium">
                    {{ formatCurrency(tx.balance) }}
                  </td>
                </tr>
                <tr v-if="statementData.transactions.length === 0">
                  <td colspan="5" class="text-center text-medium-emphasis py-6">
                    Bu donemde hareket bulunamadi.
                  </td>
                </tr>
              </tbody>
            </VTable>
          </template>
        </div>
      </VCard>
    </VCol>

    <!-- Right Column -->
    <VCol cols="12" md="4">
      <!-- Balance Card -->
      <VCard class="mb-4">
        <VCardText class="d-flex align-center justify-space-between">
          <div>
            <div class="text-overline text-medium-emphasis">
              BAKIYE
            </div>
            <div class="text-h4" :class="(detail?.balance ?? 0) > 0 ? 'text-error' : 'text-success'">
              {{ formatCurrency(detail?.balance ?? 0) }}
            </div>
          </div>

          <VMenu v-model="otherMenu" location="bottom end">
            <template #activator="{ props: menuProps }">
              <VBtn variant="outlined" v-bind="menuProps" append-icon="ri-arrow-down-s-line">
                Diger
              </VBtn>
            </template>
            <VList density="compact">
              <template v-if="residentApiAvailable">
                <VListItem @click="openingBalanceDialog = true">
                  Acilis Bakiyesi Ekle
                </VListItem>
                <VListItem @click="transferDialog = true">
                  Borc Aktarma
                </VListItem>
                <VDivider />
              </template>
              <VListItem :disabled="!canManageUsers" @click="openEditDialog">
                Duzenle
              </VListItem>
              <VListItem v-if="residentApiAvailable" :loading="archiving" @click="toggleArchive">
                {{ detail?.archived_at ? 'Arsivden Cikar' : 'Arsivle' }}
              </VListItem>
            </VList>
          </VMenu>
        </VCardText>
      </VCard>

      <!-- Quick Info -->
      <VCard v-if="detail">
        <VCardItem title="Ozet" />
        <VCardText>
          <VList class="card-list" density="compact">
            <VListItem>
              <VListItemTitle>Toplam Borc</VListItemTitle>
              <template #append>
                <span>{{ formatCurrency(detail.total_charged) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Toplam Odenen</VListItemTitle>
              <template #append>
                <span class="text-success">{{ formatCurrency(detail.total_paid) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Acik Borc</VListItemTitle>
              <template #append>
                <span class="text-error">{{ formatCurrency(detail.balance) }}</span>
              </template>
            </VListItem>
            <VListItem>
              <VListItemTitle>Daire Sayisi</VListItemTitle>
              <template #append>
                <span>{{ detail.apartment_count }}</span>
              </template>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <VDialog v-model="editDialog" max-width="1100">
    <VCard :loading="editLoading" title="Kullanici Duzenle">
      <VCardText>
        <VForm
          ref="editFormRef"
          @submit.prevent="submitEdit"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editForm.name"
                label="Ad Soyad"
                :rules="editNameRules"
                :error-messages="editErrors.name ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editForm.email"
                type="email"
                label="E-posta"
                :rules="editEmailRules"
                :error-messages="editErrors.email ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editForm.phone"
                label="Telefon"
                :error-messages="editErrors.phone ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editForm.tc_kimlik"
                label="TC Kimlik"
                maxlength="11"
                :rules="editTcKimlikRules"
                :error-messages="editErrors.tc_kimlik ?? []"
              />
            </VCol>

            <VCol cols="12">
              <VTextarea
                v-model="editForm.address"
                label="Adres"
                rows="2"
                :error-messages="editErrors.address ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.birth_date"
                type="date"
                label="Dogum Tarihi"
                :error-messages="editErrors.birth_date ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.occupation"
                label="Meslek"
                :error-messages="editErrors.occupation ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VSelect
                v-model="editForm.education"
                :items="educationOptions"
                item-title="label"
                item-value="value"
                label="Ogrenim Durumu"
                :error-messages="editErrors.education ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.password"
                type="password"
                label="Yeni Sifre"
                :rules="editPasswordRules"
                :error-messages="editErrors.password ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="editForm.password_confirmation"
                type="password"
                label="Sifre Tekrar"
                :rules="editPasswordConfirmationRules"
                :error-messages="editErrors.password_confirmation ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VSelect
                v-model="editForm.role"
                :items="editRoleOptions"
                item-title="label"
                item-value="value"
                label="Rol"
                :rules="editRoleRules"
                :error-messages="editErrors.role ?? []"
              />
            </VCol>
          </VRow>
        </VForm>

        <VDivider class="my-4" />

        <div class="text-subtitle-1 font-weight-medium mb-3">
          Bagli Daireler
        </div>

        <VForm
          ref="editApartmentFormRef"
          @submit.prevent="addApartmentToEditUser"
        >
          <VRow>
            <VCol
              cols="12"
              md="5"
            >
              <VSelect
                v-model="editApartmentForm.apartment_id"
                :items="editAvailableApartments"
                item-title="label"
                item-value="id"
                :label="$t('common.apartment')"
                :rules="editApartmentRules"
                :error-messages="editApartmentErrors.apartment_id ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="editApartmentForm.relation_type"
                :items="editRelationTypes"
                item-title="label"
                item-value="value"
                :label="$t('common.type')"
                :rules="editRelationRules"
                :error-messages="editApartmentErrors.relation_type ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="2"
            >
              <VTextField
                v-model="editApartmentForm.start_date"
                type="date"
                :label="$t('common.startDate')"
                :error-messages="editApartmentErrors.start_date ?? []"
              />
            </VCol>

            <VCol
              cols="12"
              md="2"
              class="d-flex align-end"
            >
              <VBtn
                color="primary"
                block
                type="submit"
                :loading="addingEditApartment"
                :disabled="addingEditApartment"
              >
                Ekle
              </VBtn>
            </VCol>
          </VRow>
        </VForm>

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
              v-for="apartment in editApartments"
              :key="apartment.id"
            >
              <td>{{ apartment.label }}</td>
              <td>
                <VSelect
                  v-model="apartment.relation_type"
                  :items="editRelationTypes"
                  item-title="label"
                  item-value="value"
                  density="compact"
                  hide-details
                  :disabled="updatingEditApartmentId === apartment.id || removingEditApartmentId === apartment.id"
                />
              </td>
              <td>
                <VTextField
                  v-model="apartment.start_date"
                  type="date"
                  density="compact"
                  hide-details
                  :disabled="updatingEditApartmentId === apartment.id || removingEditApartmentId === apartment.id"
                />
              </td>
              <td class="text-right">
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="primary"
                  :loading="updatingEditApartmentId === apartment.id"
                  :disabled="updatingEditApartmentId === apartment.id || removingEditApartmentId === apartment.id"
                  @click="updateEditApartmentRelation(apartment)"
                >
                  <VIcon icon="ri-save-line" />
                </VBtn>
                <VBtn
                  icon
                  size="small"
                  variant="text"
                  color="error"
                  :loading="removingEditApartmentId === apartment.id"
                  :disabled="removingEditApartmentId === apartment.id || updatingEditApartmentId === apartment.id"
                  @click="removeEditApartment(apartment.id)"
                >
                  <VIcon icon="ri-close-line" />
                </VBtn>
              </td>
            </tr>
            <tr v-if="editApartments.length === 0">
              <td
                colspan="4"
                class="text-center text-medium-emphasis py-6"
              >
                Bagli daire yok.
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>

      <VCardActions class="px-6 pb-4">
        <VSpacer />
        <VBtn
          variant="outlined"
          :disabled="editSaving || editLoading"
          @click="editDialog = false"
        >
          Iptal
        </VBtn>
        <VBtn
          color="primary"
          :loading="editSaving"
          :disabled="editSaving || editLoading"
          @click="submitEdit"
        >
          Kaydet
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Reminder Dialog -->
  <VDialog v-model="reminderDialog" max-width="500">
    <VCard title="Hatirlatma Ekle">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="reminderForm.title"
              label="Baslik"
              :rules="[requiredRule()]"
              :error-messages="reminderErrors.title ?? []"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea v-model="reminderForm.description" label="Aciklama" rows="2" />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="reminderForm.due_date"
              type="date"
              label="Tarih"
              :rules="[requiredRule()]"
              :error-messages="reminderErrors.due_date ?? []"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions class="px-6 pb-4">
        <VSpacer />
        <VBtn variant="outlined" @click="reminderDialog = false">
          Iptal
        </VBtn>
        <VBtn color="primary" :loading="addingReminder" @click="submitReminder">
          Ekle
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Opening Balance Dialog -->
  <VDialog v-model="openingBalanceDialog" max-width="500">
    <VCard title="Acilis Bakiyesi Ekle">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="openingBalanceForm.apartment_id"
              :items="apartmentOptions"
              item-title="label"
              item-value="id"
              label="Daire"
              :rules="[requiredRule()]"
              :error-messages="openingBalanceErrors.apartment_id ?? []"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="openingBalanceForm.amount"
              type="number"
              step="0.01"
              label="Tutar"
              :rules="[requiredRule(), positiveNumberRule()]"
              :error-messages="openingBalanceErrors.amount ?? []"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="openingBalanceForm.due_date"
              type="date"
              label="Tarih"
              :error-messages="openingBalanceErrors.due_date ?? []"
            />
          </VCol>
          <VCol cols="12">
            <VTextField v-model="openingBalanceForm.description" label="Aciklama" />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions class="px-6 pb-4">
        <VSpacer />
        <VBtn variant="outlined" @click="openingBalanceDialog = false">
          Iptal
        </VBtn>
        <VBtn color="primary" :loading="addingOpeningBalance" @click="submitOpeningBalance">
          Ekle
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Transfer Debt Dialog -->
  <VDialog v-model="transferDialog" max-width="500">
    <VCard title="Borc Aktarma">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="transferForm.source_apartment_id"
              :items="apartmentOptions"
              item-title="label"
              item-value="id"
              label="Kaynak Daire"
              :rules="[requiredRule()]"
              :error-messages="transferErrors.source_apartment_id ?? []"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="transferForm.target_apartment_id"
              :items="apartmentOptions"
              item-title="label"
              item-value="id"
              label="Hedef Daire"
              :rules="[requiredRule()]"
              :error-messages="transferErrors.target_apartment_id ?? []"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="transferForm.amount"
              type="number"
              step="0.01"
              label="Tutar"
              :rules="[requiredRule(), positiveNumberRule()]"
              :error-messages="transferErrors.amount ?? []"
            />
          </VCol>
          <VCol cols="12">
            <VTextField v-model="transferForm.description" label="Aciklama" />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions class="px-6 pb-4">
        <VSpacer />
        <VBtn variant="outlined" @click="transferDialog = false">
          Iptal
        </VBtn>
        <VBtn color="primary" :loading="transferring" @click="submitTransfer">
          Aktar
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
