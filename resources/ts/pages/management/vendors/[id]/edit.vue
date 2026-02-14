<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { emailRule, requiredRule } from '@/utils/validators'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

interface VendorDetailResponse {
  data: {
    id: number
    name: string
    tax_no: string | null
    phone: string | null
    email: string | null
    address: string | null
    is_active: boolean
    expenses_count: number
  }
}

const route = useRoute()
const router = useRouter()
const vendorId = computed(() => Number((route.params as Record<string, unknown>).id))
const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const form = ref({
  name: '',
  tax_no: '',
  phone: '',
  email: '',
  address: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const nameRules = [requiredRule()]
const emailRules = [
  (value: unknown) => {
    if (typeof value !== 'string' || value.trim().length === 0)
      return true

    return emailRule()(value)
  },
]

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal => $api<VendorDetailResponse>(`/vendors/${vendorId.value}`, { signal }))
    form.value = {
      name: response.data.name,
      tax_no: response.data.tax_no ?? '',
      phone: response.data.phone ?? '',
      email: response.data.email ?? '',
      address: response.data.address ?? '',
    }
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tedarikçi detayı alınamadı.')
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
    await withAbort(signal => $api(`/vendors/${vendorId.value}`, {
      method: 'PUT',
      body: {
        name: form.value.name,
        tax_no: form.value.tax_no || null,
        phone: form.value.phone || null,
        email: form.value.email || null,
        address: form.value.address || null,
      },
      signal,
    }))

    await router.push('/management/vendors')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tedarikçi güncellenemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

const deleteVendor = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/vendors/${vendorId.value}`, { method: 'DELETE', signal }))
    await router.push('/management/vendors')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Tedarikçi silinemedi.')
  }
  finally {
    deleting.value = false
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
            Tedarikçi Düzenle
          </h4>
          <p class="text-medium-emphasis mb-0">
            Tedarikçi kaydını güncelleyin
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/management/vendors"
          >
            Listeye Don
          </VBtn>
          <VBtn
            color="error"
            variant="outlined"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteVendor"
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

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.name"
                  :label="$t('common.companyName')"
                  :rules="nameRules"
                  :error-messages="fieldErrors.name ?? []"
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
                  v-model="form.email"
                  type="email"
                  label="E-posta"
                  :rules="emailRules"
                  :error-messages="fieldErrors.email ?? []"
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
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/management/vendors"
                  >
                    {{ $t('common.cancel') }}
                  </VBtn>
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
  </VRow>
</template>

