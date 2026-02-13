<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { matchRule, minLengthRule, requiredRule } from '@/utils/validators'

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const form = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const currentPasswordRules = [requiredRule()]
const passwordRules = [requiredRule(), minLengthRule(8)]
const passwordConfirmationRules = [requiredRule(), matchRule(() => form.value.password, 'Sifreler eslesmiyor.')]

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  fieldErrors.value = {}

  try {
    await $api('/profile/password', {
      method: 'PUT',
      body: form.value,
    })

    form.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    }
    successMessage.value = 'Sifreniz basariyla guncellendi.'
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Sifre guncellenemedi.')
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
            Sifre Degistir
          </h4>
          <p class="text-medium-emphasis mb-0">
            Hesap sifrenizi guvenli sekilde guncelleyin
          </p>
        </div>
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
                  v-model="form.current_password"
                  type="password"
                  label="Mevcut Sifre"
                  :rules="currentPasswordRules"
                  :error-messages="fieldErrors.current_password ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.password"
                  type="password"
                  label="Yeni Sifre"
                  :rules="passwordRules"
                  :error-messages="fieldErrors.password ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.password_confirmation"
                  type="password"
                  label="Yeni Sifre Tekrar"
                  :rules="passwordConfirmationRules"
                  :error-messages="fieldErrors.password_confirmation ?? []"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end">
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="loading"
                    :disabled="loading"
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

