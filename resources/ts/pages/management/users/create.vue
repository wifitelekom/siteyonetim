<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { emailRule, exactLengthRule, matchRule, maxLengthRule, minLengthRule, requiredRule } from '@/utils/validators'

interface UsersMetaResponse {
  data: {
    roles: Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>
  }
}

const { withAbort } = useAbortOnUnmount()
const router = useRouter()

const loadingMeta = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const roleOptions = ref<Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>>([])

const form = ref({
  name: '',
  email: '',
  phone: '',
  tc_kimlik: '',
  password: '',
  password_confirmation: '',
  role: 'owner' as 'admin' | 'owner' | 'tenant' | 'vendor',
})
const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const nameRules = [requiredRule(), maxLengthRule(100)]
const emailRules = [requiredRule(), emailRule()]
const tcKimlikRules = [
  (value: unknown) => {
    if (typeof value !== 'string' || value.length === 0)
      return true

    return exactLengthRule(11)(value)
  },
]
const passwordRules = [requiredRule(), minLengthRule(8)]
const passwordConfirmationRules = [requiredRule(), matchRule(() => form.value.password, 'Sifreler eslesmiyor.')]
const roleRules = [requiredRule()]

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await withAbort(signal => $api<UsersMetaResponse>('/users/meta', { signal }))
    roleOptions.value = response.data.roles
    if (roleOptions.value.length > 0 && !roleOptions.value.some(role => role.value === form.value.role))
      form.value.role = roleOptions.value[0].value
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Rol listesi alinamadi.')
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
    await withAbort(signal => $api('/users', {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        phone: form.value.phone || null,
        tc_kimlik: form.value.tc_kimlik || null,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        role: form.value.role,
      },
      signal,
    }))

    await router.push('/management/users')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Kullanici olusturulamadi.')
    fieldErrors.value = getApiFieldErrors(error)
  }
  finally {
    loading.value = false
  }
}

onMounted(fetchMeta)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            Yeni Kullanici
          </h4>
          <p class="text-medium-emphasis mb-0">
            Kullanici kaydi olusturun
          </p>
        </div>

        <VBtn
          variant="outlined"
          to="/management/users"
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

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.name"
                  label="Ad Soyad"
                  :rules="nameRules"
                  :error-messages="fieldErrors.name ?? []"
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
                  v-model="form.tc_kimlik"
                  label="TC Kimlik"
                  maxlength="11"
                  :rules="tcKimlikRules"
                  :error-messages="fieldErrors.tc_kimlik ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.password"
                  type="password"
                  label="Sifre"
                  :rules="passwordRules"
                  :error-messages="fieldErrors.password ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.password_confirmation"
                  type="password"
                  label="Sifre Tekrar"
                  :rules="passwordConfirmationRules"
                  :error-messages="fieldErrors.password_confirmation ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.role"
                  :items="roleOptions"
                  item-title="label"
                  item-value="value"
                  label="Rol"
                  :rules="roleRules"
                  :error-messages="fieldErrors.role ?? []"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="outlined"
                    to="/management/users"
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
