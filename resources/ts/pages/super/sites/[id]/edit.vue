<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { useAuthSession } from '@/composables/useAuthSession'
import { $api } from '@/utils/api'
import { emailRule, matchRule, minLengthRule, requiredRule } from '@/utils/validators'

interface SiteDetailResponse {
  data: {
    id: number
    name: string
    phone: string | null
    address: string | null
    tax_no: string | null
    is_active: boolean
    created_at: string | null
    admin: {
      id: number
      name: string
      email: string | null
    } | null
    current_admin_id: number | null
  }
  meta: {
    available_admins: Array<{
      id: number
      name: string
      email: string | null
      site_id: number | null
    }>
  }
}

const route = useRoute()
const router = useRouter()
const authSession = useAuthSession()
const siteId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const availableAdmins = ref<Array<{
  id: number
  name: string
  email: string | null
  site_id: number | null
}>>([])

const form = ref({
  name: '',
  phone: '',
  tax_no: '',
  address: '',
  is_active: true,
  admin_user_id: null as number | null,
  admin_name: '',
  admin_email: '',
  admin_password: '',
  admin_password_confirmation: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const canManageSites = computed(() => authSession.can('sites.manage'))
const wantsNewAdmin = computed(() =>
  form.value.admin_name.trim().length > 0
  || form.value.admin_email.trim().length > 0
  || form.value.admin_password.trim().length > 0
  || form.value.admin_password_confirmation.trim().length > 0,
)

const requiredForNewAdmin = (value: unknown, message: string) => {
  if (!wantsNewAdmin.value)
    return true

  return requiredRule(message)(value)
}

const siteNameRules = [requiredRule()]
const adminNameRules = [(value: unknown) => requiredForNewAdmin(value, 'Yonetici adi zorunludur.')]
const adminEmailRules = [
  (value: unknown) => {
    if (!wantsNewAdmin.value)
      return true

    const requiredValidation = requiredRule('Yonetici e-posta zorunludur.')(value)
    if (requiredValidation !== true)
      return requiredValidation

    return emailRule()(value)
  },
]
const adminPasswordRules = [
  (value: unknown) => requiredForNewAdmin(value, 'Yonetici sifresi zorunludur.'),
  (value: unknown) => {
    if (!wantsNewAdmin.value)
      return true

    return minLengthRule(8)(value)
  },
]
const adminPasswordConfirmationRules = [
  (value: unknown) => requiredForNewAdmin(value, 'Sifre tekrar zorunludur.'),
  (value: unknown) => {
    if (!wantsNewAdmin.value)
      return true

    return matchRule(() => form.value.admin_password, 'Sifreler eslesmiyor.')(value)
  },
]

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await $api<SiteDetailResponse>(`/super/sites/${siteId.value}`)
    availableAdmins.value = response.meta.available_admins
    form.value = {
      name: response.data.name,
      phone: response.data.phone ?? '',
      tax_no: response.data.tax_no ?? '',
      address: response.data.address ?? '',
      is_active: response.data.is_active,
      admin_user_id: response.data.current_admin_id ?? null,
      admin_name: '',
      admin_email: '',
      admin_password: '',
      admin_password_confirmation: '',
    }
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site detayi alinamadi.')
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

  try {
    await $api(`/super/sites/${siteId.value}`, {
      method: 'PUT',
      body: {
        name: form.value.name,
        phone: form.value.phone || null,
        tax_no: form.value.tax_no || null,
        address: form.value.address || null,
        is_active: form.value.is_active,
        admin_user_id: form.value.admin_user_id || null,
        admin_name: form.value.admin_name || null,
        admin_email: form.value.admin_email || null,
        admin_password: form.value.admin_password || null,
        admin_password_confirmation: form.value.admin_password_confirmation || null,
      },
    })

    await router.push('/super/sites')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site guncellenemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

const deleteSite = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await $api(`/super/sites/${siteId.value}`, { method: 'DELETE' })
    await router.push('/super/sites')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site silinemedi.')
  }
  finally {
    deleting.value = false
  }
}

onMounted(async () => {
  await authSession.ensureSession()

  if (!canManageSites.value) {
    await router.replace('/')
    return
  }

  await fetchDetail()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Site Duzenle
          </h4>
          <p class="text-medium-emphasis mb-0">
            Site ve yonetici bilgilerini guncelleyin
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/super/sites"
          >
            Listeye Don
          </VBtn>
          <VBtn
            color="error"
            variant="outlined"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteSite"
          >
            Sil
          </VBtn>
        </div>
      </div>
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
                <h6 class="text-h6 mb-2">
                  Site Bilgileri
                </h6>
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.name"
                  label="Site Adi"
                  :rules="siteNameRules"
                  :error-messages="fieldErrors.name ?? []"
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
                  v-model="form.tax_no"
                  label="Vergi No"
                  :error-messages="fieldErrors.tax_no ?? []"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="form.address"
                  label="Adres"
                  rows="3"
                  :error-messages="fieldErrors.address ?? []"
                />
              </VCol>
              <VCol cols="12">
                <VSwitch
                  v-model="form.is_active"
                  label="Aktif"
                  color="primary"
                />
              </VCol>

              <VCol cols="12">
                <VDivider />
              </VCol>

              <VCol cols="12">
                <h6 class="text-h6 mb-2">
                  Site Yonetici Bilgileri
                </h6>
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="form.admin_user_id"
                  :items="availableAdmins"
                  item-title="name"
                  item-value="id"
                  label="Yonetici Ata (opsiyonel)"
                  clearable
                  :error-messages="fieldErrors.admin_user_id ?? []"
                >
                  <template #item="{ props, item }">
                    <VListItem
                      v-bind="props"
                      :subtitle="item.raw.email || '-'"
                    />
                  </template>
                  <template #selection="{ item }">
                    <span>{{ item.raw.name }} - {{ item.raw.email || '-' }}</span>
                  </template>
                </VSelect>
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.admin_name"
                  label="Yeni Yonetici Ad Soyad"
                  :rules="adminNameRules"
                  :error-messages="fieldErrors.admin_name ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.admin_email"
                  type="email"
                  label="Yeni Yonetici E-posta"
                  :rules="adminEmailRules"
                  :error-messages="fieldErrors.admin_email ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.admin_password"
                  type="password"
                  label="Yeni Yonetici Sifre"
                  :rules="adminPasswordRules"
                  :error-messages="fieldErrors.admin_password ?? []"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.admin_password_confirmation"
                  type="password"
                  label="Sifre Tekrar"
                  :rules="adminPasswordConfirmationRules"
                  :error-messages="fieldErrors.admin_password_confirmation ?? []"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/super/sites"
                  >
                    Vazgec
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="saving"
                    :disabled="saving"
                  >
                    Guncelle
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

