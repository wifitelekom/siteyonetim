<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { requiredRule } from '@/utils/validators'

const { t } = useI18n({ useScope: 'global' })

const activeTab = ref(0)

// ============ TAB 1: Site Ayarlari ============
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const form = ref({
  name: '',
  phone: '',
  address: '',
  city: '',
  district: '',
  zip_code: '',
  tax_no: '',
  tax_office: '',
  contact_person: '',
  contact_email: '',
  contact_phone: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)
const nameRules = [requiredRule()]

const fetchSettings = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await $api<{ data: any }>('/site-settings')
    if (response.data) {
      form.value = {
        name: response.data.name ?? '',
        phone: response.data.phone ?? '',
        address: response.data.address ?? '',
        city: response.data.city ?? '',
        district: response.data.district ?? '',
        zip_code: response.data.zip_code ?? '',
        tax_no: response.data.tax_no ?? '',
        tax_office: response.data.tax_office ?? '',
        contact_person: response.data.contact_person ?? '',
        contact_email: response.data.contact_email ?? '',
        contact_phone: response.data.contact_phone ?? '',
      }
      regionalForm.value = {
        country: response.data.country ?? 'Türkiye',
        language: response.data.language ?? 'tr',
        timezone: response.data.timezone ?? 'Europe/Istanbul',
        currency: response.data.currency ?? 'TRY',
      }
    }
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'siteSettings.loadFailed')
  }
  finally {
    loading.value = false
  }
}

const submitSettings = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid) return
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  fieldErrors.value = {}
  try {
    await $api('/site-settings', {
      method: 'PUT',
      body: {
        name: form.value.name,
        phone: form.value.phone || null,
        address: form.value.address || null,
        city: form.value.city || null,
        district: form.value.district || null,
        zip_code: form.value.zip_code || null,
        tax_no: form.value.tax_no || null,
        tax_office: form.value.tax_office || null,
        contact_person: form.value.contact_person || null,
        contact_email: form.value.contact_email || null,
        contact_phone: form.value.contact_phone || null,
      },
    })
    successMessage.value = t('siteSettings.updated')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'siteSettings.updateFailed')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

// ============ TAB 2: Bolgesel Ayarlar ============
const regionalForm = ref({
  country: 'Türkiye',
  language: 'tr',
  timezone: 'Europe/Istanbul',
  currency: 'TRY',
})
const savingRegional = ref(false)
const regionalSuccess = ref('')
const regionalError = ref('')

const countryOptions = ['Türkiye', 'Kıbrıs', 'Almanya', 'Hollanda', 'İngiltere', 'ABD']
const languageOptions = [
  { title: 'Türkçe', value: 'tr' },
  { title: 'English', value: 'en' },
]
const timezoneOptions = [
  { title: 'Europe/Istanbul', value: 'Europe/Istanbul' },
  { title: 'Europe/Berlin', value: 'Europe/Berlin' },
  { title: 'Europe/London', value: 'Europe/London' },
  { title: 'America/New_York', value: 'America/New_York' },
]
const currencyOptions = [
  { title: 'Türk Lirası', value: 'TRY' },
  { title: 'Euro', value: 'EUR' },
  { title: 'US Dollar', value: 'USD' },
  { title: 'British Pound', value: 'GBP' },
]

const submitRegional = async () => {
  savingRegional.value = true
  regionalSuccess.value = ''
  regionalError.value = ''
  try {
    await $api('/site-settings/regional', {
      method: 'PUT',
      body: regionalForm.value,
    })
    regionalSuccess.value = 'Bölgesel ayarlar güncellendi.'
  }
  catch (error) {
    regionalError.value = getApiErrorMessage(error, 'Bölgesel ayarlar güncellenemedi.')
  }
  finally {
    savingRegional.value = false
  }
}

// ============ TAB 3: Kategoriler ============
interface Category {
  id: number
  name: string
  type: string
  color: string | null
  sort_order: number
}

const categories = ref<Record<string, Category[]>>({})
const loadingCategories = ref(false)
const newCategoryName = ref('')
const newCategoryType = ref('income')
const savingCategory = ref(false)

const categoryTypes = [
  { key: 'income', label: 'Gelirler' },
  { key: 'expense', label: 'Giderler' },
  { key: 'cash_in', label: 'Para Girişi' },
  { key: 'cash_out', label: 'Para Çıkışı' },
  { key: 'maintenance', label: 'Periyodik Bakım' },
]

const activeCategoryType = ref('income')

const fetchCategories = async () => {
  loadingCategories.value = true
  try {
    const response = await $api<{ data: Record<string, Category[]> }>('/categories')
    categories.value = response.data ?? {}
  }
  catch { /* silent */ }
  finally {
    loadingCategories.value = false
  }
}

const addCategory = async () => {
  if (!newCategoryName.value.trim()) return
  savingCategory.value = true
  try {
    const response = await $api<{ data: Category }>('/categories', {
      method: 'POST',
      body: { name: newCategoryName.value, type: activeCategoryType.value },
    })
    if (!categories.value[activeCategoryType.value])
      categories.value[activeCategoryType.value] = []
    categories.value[activeCategoryType.value].push(response.data)
    newCategoryName.value = ''
  }
  catch { /* silent */ }
  finally {
    savingCategory.value = false
  }
}

const deleteCategory = async (cat: Category) => {
  try {
    await $api(`/categories/${cat.id}`, { method: 'DELETE' })
    const list = categories.value[cat.type]
    if (list) {
      const idx = list.findIndex(c => c.id === cat.id)
      if (idx >= 0) list.splice(idx, 1)
    }
  }
  catch { /* silent */ }
}

const categoryColors: Record<string, string> = {
  income: 'warning',
  expense: 'success',
  cash_in: 'info',
  cash_out: 'secondary',
  maintenance: 'primary',
}

// ============ TAB 4: Daire Gruplari ============
interface ApartmentGroup {
  id: number
  name: string
  description: string | null
  multiplier: string
  apartments_count: number
}

const groups = ref<ApartmentGroup[]>([])
const loadingGroups = ref(false)
const newGroupName = ref('')
const savingGroup = ref(false)
const editingGroup = ref<ApartmentGroup | null>(null)
const editForm = ref({ name: '', description: '', multiplier: '1' })

const fetchGroups = async () => {
  loadingGroups.value = true
  try {
    const response = await $api<{ data: ApartmentGroup[] }>('/apartment-groups')
    groups.value = response.data ?? []
  }
  catch { /* silent */ }
  finally {
    loadingGroups.value = false
  }
}

const addGroup = async () => {
  if (!newGroupName.value.trim()) return
  savingGroup.value = true
  try {
    const response = await $api<{ data: ApartmentGroup }>('/apartment-groups', {
      method: 'POST',
      body: { name: newGroupName.value, multiplier: 1 },
    })
    groups.value.push(response.data)
    newGroupName.value = ''
  }
  catch { /* silent */ }
  finally {
    savingGroup.value = false
  }
}

const startEdit = (group: ApartmentGroup) => {
  editingGroup.value = group
  editForm.value = { name: group.name, description: group.description ?? '', multiplier: group.multiplier }
}

const saveGroupEdit = async () => {
  if (!editingGroup.value) return
  try {
    await $api(`/apartment-groups/${editingGroup.value.id}`, {
      method: 'PUT',
      body: editForm.value,
    })
    const idx = groups.value.findIndex(g => g.id === editingGroup.value!.id)
    if (idx >= 0) {
      groups.value[idx] = { ...groups.value[idx], ...editForm.value }
    }
    editingGroup.value = null
  }
  catch { /* silent */ }
}

const deleteGroup = async (group: ApartmentGroup) => {
  try {
    await $api(`/apartment-groups/${group.id}`, { method: 'DELETE' })
    groups.value = groups.value.filter(g => g.id !== group.id)
  }
  catch { /* silent */ }
}

// ============ TAB 5: Yoneticiler ============
interface Manager {
  id: number
  name: string
  email: string
}

const managers = ref<Manager[]>([])
const auditors = ref<Manager[]>([])
const loadingManagers = ref(false)

const fetchManagers = async () => {
  loadingManagers.value = true
  try {
    const response = await $api<{ data: { managers: Manager[]; auditors: Manager[] } }>('/site-settings/managers')
    managers.value = response.data.managers ?? []
    auditors.value = response.data.auditors ?? []
  }
  catch { /* silent */ }
  finally {
    loadingManagers.value = false
  }
}

// ============ TAB 6: Yetkiler ============
const permissions = ref<Record<string, boolean>>({})
const loadingPermissions = ref(false)
const savingPermissions = ref(false)
const permissionsSuccess = ref('')

const permissionLabels: Record<string, string> = {
  auditor_can_create: 'Site denetçileri yeni kayıt oluşturabilir; kayıtları düzenleyebilir ve silebilir',
  auditor_can_view_reports: 'Site denetçileri raporlara erişebilir',
  auditor_can_edit_settings: 'Site denetçileri sistem ayarlarını değiştirebilir',
  auditor_can_view_support: 'Site denetçileri destek taleplerini görüntüleyebilir',
  auditor_can_view_personal_info: 'Site denetçileri daire sakinlerinin kişisel bilgilerini görüntüleyebilir',
  resident_can_view_balance: 'Daire sakinleri bilançoya erişebilir',
  resident_can_view_receipts: 'Daire sakinleri yapılan tahsilatları görebilir',
  resident_can_view_payments: 'Daire sakinleri yapılan ödemeleri görebilir',
  resident_can_view_cash_accounts: 'Daire sakinleri kasa ve banka listesini ve bakiyelerini görüntüleyebilir',
  resident_can_view_all_accounts: 'Daire sakinleri tüm finansal hesapları görebilir',
  resident_cannot_edit_profile: 'Daire sakinleri kullanıcı bilgilerini değiştiremez',
  resident_can_view_plates: 'Daire sakinleri, diğer daire sakinlerinin plakalarını görebilir',
  support_team_access: 'Biyos destek ekibi hesabıma erişebilir',
}

const fetchPermissions = async () => {
  loadingPermissions.value = true
  try {
    const response = await $api<{ data: Record<string, boolean> }>('/site-settings/permissions')
    permissions.value = response.data ?? {}
  }
  catch { /* silent */ }
  finally {
    loadingPermissions.value = false
  }
}

const submitPermissions = async () => {
  savingPermissions.value = true
  permissionsSuccess.value = ''
  try {
    await $api('/site-settings/permissions', {
      method: 'PUT',
      body: permissions.value,
    })
    permissionsSuccess.value = 'Yetkiler güncellendi.'
  }
  catch { /* silent */ }
  finally {
    savingPermissions.value = false
  }
}

// ============ Tab degisikliginde veri yukleme ============
watch(activeTab, (tab) => {
  if (tab === 2 && Object.keys(categories.value).length === 0) fetchCategories()
  if (tab === 3 && groups.value.length === 0) fetchGroups()
  if (tab === 4 && managers.value.length === 0) fetchManagers()
  if (tab === 5 && Object.keys(permissions.value).length === 0) fetchPermissions()
})

onMounted(fetchSettings)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Ayarlar
          </h4>
          <p class="text-medium-emphasis mb-0">
            Site yapılandırma ve yönetim ayarları
          </p>
        </div>
      </div>
    </VCol>

    <VCol cols="12">
      <VTabs v-model="activeTab" class="mb-4">
        <VTab>Site Ayarları</VTab>
        <VTab>Bölgesel Ayarlar</VTab>
        <VTab>Kategoriler</VTab>
        <VTab>Daire Grupları</VTab>
        <VTab>Yöneticiler</VTab>
        <VTab>Yetkiler</VTab>
      </VTabs>

      <VWindow v-model="activeTab">
        <!-- TAB 1: Site Ayarlari -->
        <VWindowItem :value="0">
          <VCard>
            <VCardText>
              <VForm ref="formRef" @submit.prevent="submitSettings">
                <VRow>
                  <VCol v-if="errorMessage" cols="12">
                    <VAlert type="error" variant="tonal">{{ errorMessage }}</VAlert>
                  </VCol>
                  <VCol v-if="successMessage" cols="12">
                    <VAlert type="success" variant="tonal">{{ successMessage }}</VAlert>
                  </VCol>

                  <VCol cols="12">
                    <h6 class="text-h6 mb-4">Apartman / Site Bilgileri</h6>
                  </VCol>

                  <VCol cols="12">
                    <VTextField
                      v-model="form.name"
                      label="Apartman / Site Adı"
                      :rules="nameRules"
                      :error-messages="fieldErrors.name ?? []"
                      :loading="loading"
                    />
                  </VCol>

                  <VCol cols="12">
                    <VTextarea
                      v-model="form.address"
                      label="Adres"
                      rows="2"
                      :error-messages="fieldErrors.address ?? []"
                      :loading="loading"
                    />
                  </VCol>

                  <VCol cols="12" md="4">
                    <VTextField
                      v-model="form.district"
                      label="İl"
                      :error-messages="fieldErrors.district ?? []"
                      :loading="loading"
                    />
                  </VCol>
                  <VCol cols="12" md="4">
                    <VTextField
                      v-model="form.city"
                      label="İlçe"
                      :error-messages="fieldErrors.city ?? []"
                      :loading="loading"
                    />
                  </VCol>
                  <VCol cols="12" md="4">
                    <VTextField
                      v-model="form.zip_code"
                      label="Posta Kodu"
                      :error-messages="fieldErrors.zip_code ?? []"
                      :loading="loading"
                    />
                  </VCol>

                  <VCol cols="12" class="mt-4">
                    <h6 class="text-h6 mb-4">Vergi Bilgileri</h6>
                  </VCol>

                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.tax_office"
                      label="Vergi Dairesi"
                      :error-messages="fieldErrors.tax_office ?? []"
                      :loading="loading"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.tax_no"
                      label="Vergi No"
                      :error-messages="fieldErrors.tax_no ?? []"
                      :loading="loading"
                    />
                  </VCol>

                  <VCol cols="12" class="mt-4">
                    <h6 class="text-h6 mb-4">İletişim Bilgileri</h6>
                  </VCol>

                  <VCol cols="12">
                    <VTextField
                      v-model="form.contact_person"
                      label="Yetkili Kişi"
                      :error-messages="fieldErrors.contact_person ?? []"
                      :loading="loading"
                    />
                  </VCol>

                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.contact_email"
                      label="E-posta"
                      type="email"
                      :error-messages="fieldErrors.contact_email ?? []"
                      :loading="loading"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.contact_phone"
                      label="Telefon"
                      :error-messages="fieldErrors.contact_phone ?? []"
                      :loading="loading"
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
                        {{ $t('common.save') }}
                      </VBtn>
                    </div>
                  </VCol>
                </VRow>
              </VForm>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- TAB 2: Bolgesel Ayarlar -->
        <VWindowItem :value="1">
          <VCard>
            <VCardText>
              <VForm @submit.prevent="submitRegional">
                <VRow>
                  <VCol v-if="regionalError" cols="12">
                    <VAlert type="error" variant="tonal">{{ regionalError }}</VAlert>
                  </VCol>
                  <VCol v-if="regionalSuccess" cols="12">
                    <VAlert type="success" variant="tonal">{{ regionalSuccess }}</VAlert>
                  </VCol>

                  <VCol cols="12">
                    <VSelect
                      v-model="regionalForm.country"
                      :items="countryOptions"
                      label="Ülke"
                      :loading="loading"
                    />
                    <p class="text-medium-emphasis text-caption mt-1">
                      Yönetim şirketinizin faaliyet gösterdiği ülkeyi seçiniz.
                    </p>
                  </VCol>

                  <VCol cols="12">
                    <VSelect
                      v-model="regionalForm.language"
                      :items="languageOptions"
                      item-title="title"
                      item-value="value"
                      label="Sistem Dili"
                      :loading="loading"
                    />
                    <p class="text-medium-emphasis text-caption mt-1">
                      Yönetim ekranınızın ön tanımlı dilidir. Sistem kullanıcılarınız, site yöneticileriniz ve daire sakinleriniz kendi dillerini değiştirebilirler.
                    </p>
                  </VCol>

                  <VCol cols="12">
                    <VSelect
                      v-model="regionalForm.timezone"
                      :items="timezoneOptions"
                      item-title="title"
                      item-value="value"
                      label="Zaman Dilimi"
                      :loading="loading"
                    />
                    <p class="text-medium-emphasis text-caption mt-1">
                      Bulunduğunuz bölgede kullanılan zaman dilimini seçiniz.
                    </p>
                  </VCol>

                  <VCol cols="12">
                    <VSelect
                      v-model="regionalForm.currency"
                      :items="currencyOptions"
                      item-title="title"
                      item-value="value"
                      label="Para Birimi"
                      :loading="loading"
                    />
                    <p class="text-medium-emphasis text-caption mt-1">
                      Bulunduğunuz bölgede kullanılan para birimidir. Tüm sistem genelinde belirlediğiniz para birimi geçerli olur.
                    </p>
                  </VCol>

                  <VCol cols="12">
                    <VBtn
                      color="success"
                      type="submit"
                      :loading="savingRegional"
                    >
                      {{ $t('common.save') }}
                    </VBtn>
                  </VCol>
                </VRow>
              </VForm>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- TAB 3: Kategoriler -->
        <VWindowItem :value="2">
          <VCard>
            <VCardText>
              <VRow>
                <VCol cols="12">
                  <div class="d-flex gap-2 flex-wrap mb-4">
                    <VBtn
                      v-for="ct in categoryTypes"
                      :key="ct.key"
                      :variant="activeCategoryType === ct.key ? 'flat' : 'outlined'"
                      :color="activeCategoryType === ct.key ? 'primary' : undefined"
                      size="small"
                      @click="activeCategoryType = ct.key"
                    >
                      {{ ct.label }}
                    </VBtn>
                  </div>
                </VCol>

                <VCol v-for="ct in categoryTypes" :key="ct.key" cols="12" md="6" lg="4">
                  <h6 class="text-subtitle-1 font-weight-bold mb-3">{{ ct.label }}</h6>

                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <VChip
                      v-for="cat in (categories[ct.key] ?? [])"
                      :key="cat.id"
                      :color="categoryColors[ct.key] ?? 'default'"
                      closable
                      @click:close="deleteCategory(cat)"
                    >
                      {{ cat.name }}
                    </VChip>
                    <span v-if="!(categories[ct.key]?.length)" class="text-medium-emphasis text-body-2">
                      Kategori yok
                    </span>
                  </div>

                  <div v-if="activeCategoryType === ct.key" class="d-flex gap-2 align-center">
                    <VTextField
                      v-model="newCategoryName"
                      density="compact"
                      placeholder="Kategori adı"
                      hide-details
                      @keyup.enter="addCategory"
                    />
                    <VBtn
                      size="small"
                      color="primary"
                      :loading="savingCategory"
                      @click="addCategory"
                    >
                      Ekle
                    </VBtn>
                  </div>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- TAB 4: Daire Gruplari -->
        <VWindowItem :value="3">
          <VCard>
            <VCardText>
              <div class="d-flex gap-2 align-center mb-4">
                <VTextField
                  v-model="newGroupName"
                  density="compact"
                  placeholder="Daire grubu ekle"
                  hide-details
                  @keyup.enter="addGroup"
                />
                <VBtn
                  color="primary"
                  :loading="savingGroup"
                  @click="addGroup"
                >
                  Ekle
                </VBtn>
              </div>

              <VTable v-if="groups.length" density="comfortable">
                <thead>
                  <tr>
                    <th>Grup Başlığı</th>
                    <th>Açıklama</th>
                    <th>Grup Çarpanı</th>
                    <th class="text-end">İşlemler</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="group in groups" :key="group.id">
                    <template v-if="editingGroup?.id === group.id">
                      <td>
                        <VTextField v-model="editForm.name" density="compact" hide-details />
                      </td>
                      <td>
                        <VTextField v-model="editForm.description" density="compact" hide-details />
                      </td>
                      <td>
                        <VTextField v-model="editForm.multiplier" type="number" density="compact" hide-details />
                      </td>
                      <td class="text-end">
                        <VBtn icon size="small" variant="text" color="success" @click="saveGroupEdit">
                          <VIcon icon="ri-check-line" />
                        </VBtn>
                        <VBtn icon size="small" variant="text" @click="editingGroup = null">
                          <VIcon icon="ri-close-line" />
                        </VBtn>
                      </td>
                    </template>
                    <template v-else>
                      <td>{{ group.name }}</td>
                      <td>{{ group.description ?? '-' }}</td>
                      <td>{{ group.multiplier }}</td>
                      <td class="text-end">
                        <VBtn icon size="small" variant="text" @click="startEdit(group)">
                          <VIcon icon="ri-edit-line" />
                        </VBtn>
                        <VBtn icon size="small" variant="text" color="error" @click="deleteGroup(group)">
                          <VIcon icon="ri-delete-bin-line" />
                        </VBtn>
                      </td>
                    </template>
                  </tr>
                </tbody>
              </VTable>

              <p v-else-if="!loadingGroups" class="text-medium-emphasis">
                Henüz daire grubu tanımlanmadı.
              </p>
              <div v-else class="d-flex justify-center py-4">
                <VProgressCircular indeterminate />
              </div>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- TAB 5: Yoneticiler -->
        <VWindowItem :value="4">
          <VCard>
            <VCardText>
              <div v-if="loadingManagers" class="d-flex justify-center py-4">
                <VProgressCircular indeterminate />
              </div>
              <template v-else>
                <div class="mb-6">
                  <h6 class="text-h6 mb-1">
                    <VIcon icon="ri-star-fill" color="error" size="18" class="me-1" />
                    Site Yöneticileri
                  </h6>
                  <p class="text-medium-emphasis text-body-2 mb-3">
                    Yasaya göre apartman site yöneticileri kiracı, kat maliki veya dışarıdan belirlenen bir kişi olabilir, 8 ve üzeri bağımsız bölüm bulunan yapılarda yönetici olması zorunludur.
                  </p>
                  <div v-for="(mgr, i) in managers" :key="mgr.id" class="d-flex align-center gap-2 mb-2">
                    <span class="font-weight-medium">{{ i + 1 }}.</span>
                    <VTextField
                      :model-value="mgr.name"
                      density="compact"
                      hide-details
                      readonly
                    />
                  </div>
                  <p v-if="!managers.length" class="text-medium-emphasis">
                    Tanımlı yönetici bulunamadı.
                  </p>
                </div>

                <VDivider class="my-4" />

                <div>
                  <h6 class="text-h6 mb-1">
                    <VIcon icon="ri-star-line" color="warning" size="18" class="me-1" />
                    Site Denetçileri
                  </h6>
                  <p class="text-medium-emphasis text-body-2 mb-3">
                    Denetçi, kat malikleri kurulu adına bina yönetici ve yönetim kurulunu denetlemekle görevli kişi veya kişilerdir.
                  </p>
                  <div v-for="(aud, i) in auditors" :key="aud.id" class="d-flex align-center gap-2 mb-2">
                    <span class="font-weight-medium">{{ i + 1 }}.</span>
                    <VTextField
                      :model-value="aud.name"
                      density="compact"
                      hide-details
                      readonly
                    />
                  </div>
                  <p v-if="!auditors.length" class="text-medium-emphasis">
                    Tanımlı denetçi bulunamadı.
                  </p>
                </div>
              </template>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- TAB 6: Yetkiler -->
        <VWindowItem :value="5">
          <VCard>
            <VCardText>
              <p class="text-medium-emphasis text-body-2 mb-4">
                Sisteme tanımlı olan tüm kullanıcıların bağlı oldukları yetkileri aşağıdaki form aracılığı ile düzenleyebilirsiniz. Değişiklikler kullanıcıların bir sonraki oturumunda geçerli olacaktır.
              </p>

              <VAlert v-if="permissionsSuccess" type="success" variant="tonal" class="mb-4">
                {{ permissionsSuccess }}
              </VAlert>

              <div v-if="loadingPermissions" class="d-flex justify-center py-4">
                <VProgressCircular indeterminate />
              </div>
              <template v-else>
                <div
                  v-for="(label, key) in permissionLabels"
                  :key="key"
                  class="d-flex align-center justify-space-between py-3"
                  style="border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))"
                >
                  <span>{{ label }}</span>
                  <VSwitch
                    v-model="permissions[key]"
                    hide-details
                    density="compact"
                    color="warning"
                  />
                </div>

                <div class="d-flex justify-end mt-4">
                  <VBtn
                    color="primary"
                    :loading="savingPermissions"
                    @click="submitPermissions"
                  >
                    {{ $t('common.save') }}
                  </VBtn>
                </div>
              </template>
            </VCardText>
          </VCard>
        </VWindowItem>
      </VWindow>
    </VCol>
  </VRow>
</template>
