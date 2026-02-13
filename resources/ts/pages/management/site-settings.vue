<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { requiredRule } from '@/utils/validators'

interface SiteSettingsResponse {
  data: {
    id: number
    name: string
    address: string | null
    phone: string | null
    tax_no: string | null
    is_active: boolean
  }
}

const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const form = ref({
  name: '',
  phone: '',
  address: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const nameRules = [requiredRule()]

const fetchSettings = async () => {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await $api<SiteSettingsResponse>('/site-settings')
    form.value = {
      name: response.data.name,
      phone: response.data.phone ?? '',
      address: response.data.address ?? '',
    }
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site ayarlari alinamadi.')
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
  successMessage.value = ''
  fieldErrors.value = {}

  try {
    await $api('/site-settings', {
      method: 'PUT',
      body: {
        name: form.value.name,
        phone: form.value.phone || null,
        address: form.value.address || null,
      },
    })

    successMessage.value = 'Site ayarlari guncellendi.'
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Site ayarlari guncellenemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

onMounted(fetchSettings)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Site Ayarlari
          </h4>
          <p class="text-medium-emphasis mb-0">
            Site temel bilgilerini guncelleyin
          </p>
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
                v-if="successMessage"
                cols="12"
              >
                <VAlert
                  type="success"
                  variant="tonal"
                >
                  {{ successMessage }}
                </VAlert>
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  label="Site Adi"
                  :rules="nameRules"
                  :error-messages="fieldErrors.name ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.phone"
                  label="Telefon"
                  :error-messages="fieldErrors.phone ?? []"
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
                <div class="d-flex justify-end">
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

