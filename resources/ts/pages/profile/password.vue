<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { matchRule, minLengthRule, requiredRule } from '@/utils/validators'

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const { t } = useI18n({ useScope: 'global' })

const form = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const currentPasswordRules = [requiredRule()]
const passwordRules = [requiredRule(), minLengthRule(8)]
const passwordConfirmationRules = [requiredRule(), matchRule(() => form.value.password, t('profile.passwordsDoNotMatch'))]

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
    successMessage.value = t('profile.updated')
  }
  catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'profile.updateFailed')
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
            {{ $t('navigation.passwordChange') }}
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('profile.subtitle') }}
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
                  :label="$t('profile.currentPassword')"
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
                  :label="$t('profile.newPassword')"
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
                  :label="$t('profile.repeatNewPassword')"
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
