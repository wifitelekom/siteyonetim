<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { emailRule, requiredRule } from '@/utils/validators'

const { withAbort } = useAbortOnUnmount()
const router = useRouter()

const loading = ref(false)
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

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  loading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api('/vendors', {
      method: 'POST',
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
    errorMessage.value = getApiErrorMessage(error, 'Tedarikci olusturulamadi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Yeni Tedarikci
          </h4>
          <p class="text-medium-emphasis mb-0">
            Tedarikci bilgilerini girin
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/management/vendors"
        >
          Listeye Don
        </VBtn>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard :loading="loading">
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
                  label="Firma Adi"
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
                    Iptal
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="loading"
                    :disabled="loading"
                  >
                    Kaydet
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

