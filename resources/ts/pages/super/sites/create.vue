<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { useAuthSession } from '@/composables/useAuthSession'
import { $api } from '@/utils/api'
import { emailRule, matchRule, minLengthRule, requiredRule } from '@/utils/validators'

interface SuperSiteMetaResponse {
  data: {
    available_admins: Array<{
      id: number
      name: string
      email: string | null
      site_id: number | null
    }>
  }
}

const router = useRouter()
const authSession = useAuthSession()

const loading = ref(false)
const loadingMeta = ref(false)
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
const creatingNewAdmin = computed(() => !form.value.admin_user_id)

const requiredForNewAdmin = (value: unknown, message: string) => {
  if (!creatingNewAdmin.value)
    return true

  return requiredRule(message)(value)
}

const siteNameRules = [requiredRule()]
const adminNameRules = [(value: unknown) => requiredForNewAdmin(value, 'Yonetici adi zorunludur.')]
const adminEmailRules = [
  (value: unknown) => {
    if (!creatingNewAdmin.value)
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
    if (!creatingNewAdmin.value)
      return true

    return minLengthRule(8)(value)
  },
]
const adminPasswordConfirmationRules = [
  (value: unknown) => requiredForNewAdmin(value, 'Sifre tekrar zorunludur.'),
  (value: unknown) => {
    if (!creatingNewAdmin.value)
      return true

    return matchRule(() => form.value.admin_password, 'Sifreler eslesmiyor.')(value)
  },
]

const fetchMeta = async () => {
  loadingMeta.value = true
  try {
    const response = await $api<SuperSiteMetaResponse>('/super/sites/meta')
    availableAdmins.value = response.data.available_admins
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Yonetici listesi alinamadi.')
  }
  finally {
    loadingMeta.value = false
  }
}

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  loading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await $api('/super/sites', {
      method: 'POST',
      body: {
        name: form.value.name,
        phone: form.value.phone || null,
        tax_no: form.value.tax_no || null,
        address: form.value.address || null,
        is_active: form.value.is_active,
        admin_user_id: form.value.admin_user_id || null,
        admin_name: creatingNewAdmin.value ? form.value.admin_name : null,
        admin_email: creatingNewAdmin.value ? form.value.admin_email : null,
        admin_password: creatingNewAdmin.value ? form.value.admin_password : null,
        admin_password_confirmation: creatingNewAdmin.value ? form.value.admin_password_confirmation : null,
      },
    })

    await router.push('/super/sites')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site olusturulamadi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    loading.value = false
  }
}

onMounted(async () => {
  await authSession.ensureSession()

  if (!canManageSites.value) {
    await router.replace('/')
    return
  }

  await fetchMeta()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Yeni Site
          </h4>
          <p class="text-medium-emphasis mb-0">
            Site ve yonetici bilgilerini girin
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/super/sites"
        >
          Listeye Don
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading || loadingMeta">
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
                  label="Mevcut Yonetici Sec (opsiyonel)"
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

              <template v-if="creatingNewAdmin">
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.admin_name"
                    label="Yonetici Ad Soyad"
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
                    label="Yonetici E-posta"
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
                    label="Yonetici Sifre"
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
              </template>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/super/sites"
                  >
                    Iptal
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="loading"
                    :disabled="loading"
                  >
                    Siteyi Olustur
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

