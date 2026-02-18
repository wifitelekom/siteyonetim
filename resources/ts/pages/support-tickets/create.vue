<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { requiredRule } from '@/utils/validators'

const { withAbort } = useAbortOnUnmount()
const router = useRouter()
const loading = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const form = ref({
  subject: '',
  message: '',
  priority: 'medium',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const subjectRules = [requiredRule()]
const messageRules = [requiredRule()]

const priorityOptions = [
  { value: 'low', label: 'Dusuk' },
  { value: 'medium', label: 'Orta' },
  { value: 'high', label: 'Yuksek' },
]

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid) return

  loading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api('/support-tickets', {
      method: 'POST',
      body: form.value,
      signal,
    }))

    await router.push('/support-tickets')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Destek talebi olusturulamadi.')
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
            Yeni Destek Talebi
          </h4>
          <p class="text-medium-emphasis mb-0">
            Yeni bir destek talebi olusturun
          </p>
        </div>
        <VBtn
          variant="outlined"
          to="/support-tickets"
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
                md="8"
              >
                <VTextField
                  v-model="form.subject"
                  label="Konu"
                  :rules="subjectRules"
                  :error-messages="fieldErrors.subject ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.priority"
                  :items="priorityOptions"
                  item-title="label"
                  item-value="value"
                  label="Oncelik"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="form.message"
                  label="Mesaj"
                  rows="5"
                  :rules="messageRules"
                  :error-messages="fieldErrors.message ?? []"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/support-tickets"
                  >
                    Iptal
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="loading"
                    :disabled="loading"
                  >
                    Gonder
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
