<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { requiredRule } from '@/utils/validators'

const { withAbort } = useAbortOnUnmount()
const route = useRoute()
const router = useRouter()
const announcementId = computed(() => Number((route.params as Record<string, unknown>).id))

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
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

const fetchDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await withAbort(signal =>
      $api<{ data: { id: number; title: string; content: string; is_published: boolean } }>(`/announcements/${announcementId.value}`, { signal }),
    )

    form.value = {
      title: response.data.title,
      content: response.data.content,
      is_published: response.data.is_published,
    }
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Duyuru detayi alinamadi.')
  }
  finally {
    loading.value = false
  }
}

const submit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid) return

  saving.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    await withAbort(signal => $api(`/announcements/${announcementId.value}`, {
      method: 'PUT',
      body: form.value,
      signal,
    }))

    await router.push('/announcements')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Duyuru guncellenemedi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    saving.value = false
  }
}

const deleteAnnouncement = async () => {
  deleting.value = true
  errorMessage.value = ''

  try {
    await withAbort(signal => $api(`/announcements/${announcementId.value}`, { method: 'DELETE', signal }))
    await router.push('/announcements')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Duyuru silinemedi.')
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
            Duyuru Duzenle
          </h4>
          <p class="text-medium-emphasis mb-0">
            Duyuruyu duzenleyin
          </p>
        </div>
        <div class="d-flex gap-2">
          <VBtn
            variant="outlined"
            to="/announcements"
          >
            Listeye Don
          </VBtn>
          <VBtn
            color="error"
            variant="outlined"
            :loading="deleting"
            :disabled="deleting"
            @click="deleteAnnouncement"
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
                  label="Yayinda"
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
