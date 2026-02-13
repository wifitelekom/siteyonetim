<script setup lang="ts">
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { $api } from '@/utils/api'
import { positiveNumberRule, requiredRule } from '@/utils/validators'
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'

const router = useRouter()
const { withAbort } = useAbortOnUnmount()

const loading = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const form = ref({
  block: '',
  floor: null as number | null,
  number: '',
  m2: null as number | null,
  arsa_payi: null as number | null,
  is_active: true,
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const floorRules = [
  requiredRule(),
  (value: unknown) => Number.isInteger(Number(value)) ? true : 'Kat bilgisi tam sayi olmalidir.',
]
const numberRules = [requiredRule()]
const m2Rules = [
  (value: unknown) => {
    if (value === null || value === undefined || value === '')
      return true

    return positiveNumberRule()(value)
  },
]
const arsaPayiRules = [
  (value: unknown) => {
    if (value === null || value === undefined || value === '')
      return true

    const parsed = Number(value)

    return Number.isFinite(parsed) && parsed >= 0 ? true : 'Sifir veya daha buyuk bir deger giriniz.'
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
    await withAbort(signal => $api('/apartments', {
      method: 'POST',
      body: {
        block: form.value.block || null,
        floor: form.value.floor,
        number: form.value.number,
        m2: form.value.m2,
        arsa_payi: form.value.arsa_payi,
        is_active: form.value.is_active,
      },
      signal,
    }))

    await router.push('/management/apartments')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Daire olusturulamadi.')
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
            Yeni Daire
          </h4>
          <p class="text-medium-emphasis mb-0">
            Daire bilgilerini girin
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/management/apartments"
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
                md="4"
              >
                <VTextField
                  v-model="form.block"
                  label="Blok"
                  :error-messages="fieldErrors.block ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.floor"
                  type="number"
                  label="Kat"
                  :rules="floorRules"
                  :error-messages="fieldErrors.floor ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.number"
                  label="Daire No"
                  :rules="numberRules"
                  :error-messages="fieldErrors.number ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.m2"
                  type="number"
                  min="1"
                  step="0.01"
                  label="m2"
                  :rules="m2Rules"
                  :error-messages="fieldErrors.m2 ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.arsa_payi"
                  type="number"
                  min="0"
                  step="0.000001"
                  label="Arsa Payi"
                  :rules="arsaPayiRules"
                  :error-messages="fieldErrors.arsa_payi ?? []"
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
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/management/apartments"
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

