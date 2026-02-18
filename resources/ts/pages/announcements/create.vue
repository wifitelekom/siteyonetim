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
  title: '',
  content: '',
  is_published: false,
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const titleRules = [requiredRule()]
const contentRules = [requiredRule()]

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid) return

  loading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api('/announcements', {
      method: 'POST',
      body: form.value,
      signal,
    }))

    await router.push('/announcements')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Duyuru olusturulamadi.')
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
            Yeni Duyuru
          </h4>
          <p class="text-medium-emphasis mb-0">
            Yeni bir duyuru olusturun
          </p>
        </div>
        <VBtn
          variant="outlined"
          to="/announcements"
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

              <VCol cols="12">
                <VTextField
                  v-model="form.title"
                  label="Baslik"
                  :rules="titleRules"
                  :error-messages="fieldErrors.title ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="form.content"
                  label="Icerik"
                  rows="6"
                  :rules="contentRules"
                  :error-messages="fieldErrors.content ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="form.is_published"
                  label="Hemen Yayinla"
                  color="success"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/announcements"
                  >
                    Iptal
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="loading"
                    :disabled="loading"
                  >
                    {{ $t('common.save') }}
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
