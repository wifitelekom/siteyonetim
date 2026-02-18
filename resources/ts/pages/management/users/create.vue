<script setup lang="ts">
import { isAbortError, useAbortOnUnmount } from '@/composables/useAbortOnUnmount'
import { $api } from '@/utils/api'
import { getApiErrorMessage, getApiFieldErrors } from '@/utils/errorHandler'
import { emailRule, exactLengthRule, matchRule, maxLengthRule, minLengthRule, requiredRule } from '@/utils/validators'

interface UsersMetaResponse {
  data: {
    roles: Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>
    apartments: Array<{ id: number; label: string }>
    relation_types: Array<{ value: 'owner' | 'tenant'; label: string }>
  }
}

const { withAbort } = useAbortOnUnmount()
const router = useRouter()

const loadingMeta = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const roleOptions = ref<Array<{ value: 'admin' | 'owner' | 'tenant' | 'vendor'; label: string }>>([])
const apartmentOptions = ref<Array<{ id: number; label: string }>>([])
const relationTypes = ref<Array<{ value: 'owner' | 'tenant'; label: string }>>([])

const form = ref({
  name: '',
  email: '',
  phone: '',
  tc_kimlik: '',
  password: '',
  password_confirmation: '',
  role: 'owner' as 'admin' | 'owner' | 'tenant' | 'vendor',
  apartment_id: null as number | null,
  relation_type: 'owner' as 'owner' | 'tenant',
  start_date: new Date().toISOString().slice(0, 10),
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
const passwordConfirmationRules = [requiredRule(), matchRule(() => form.value.password, 'Şifreler eşleşmiyor.')]
const roleRules = [requiredRule()]
const relationRules = [
  (value: unknown) => {
    if (form.value.apartment_id == null)
      return true

    return requiredRule()(value)
  },
]

const fetchMeta = async () => {
  loadingMeta.value = true

  try {
    const response = await withAbort(signal => $api<UsersMetaResponse>('/users/meta', { signal }))
    roleOptions.value = response.data.roles
    apartmentOptions.value = response.data.apartments
    relationTypes.value = response.data.relation_types
    if (roleOptions.value.length > 0 && !roleOptions.value.some(role => role.value === form.value.role))
      form.value.role = roleOptions.value[0].value
    if (relationTypes.value.length > 0 && !relationTypes.value.some(type => type.value === form.value.relation_type))
      form.value.relation_type = relationTypes.value[0].value
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Rol listesi alınamadı.')
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
        apartment_id: form.value.apartment_id,
        relation_type: form.value.apartment_id == null ? undefined : form.value.relation_type,
        start_date: form.value.apartment_id == null ? undefined : (form.value.start_date || null),
      },
      signal,
    }))

    await router.push('/management/users')
  }
  catch (error) {
    if (isAbortError(error)) return
    errorMessage.value = getApiErrorMessage(error, 'Kullanıcı oluşturulamadı.')
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
            {{ $t('pages.users.newTitle') }}
          </h4>
          <p class="text-medium-emphasis mb-0">
            {{ $t('pages.users.createSubtitle') }}
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
                  label="Şifre"
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
                  label="Şifre Tekrar"
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

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.apartment_id"
                  :items="apartmentOptions"
                  item-title="label"
                  item-value="id"
                  :label="$t('common.apartment')"
                  clearable
                  :error-messages="fieldErrors.apartment_id ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="form.relation_type"
                  :items="relationTypes"
                  item-title="label"
                  item-value="value"
                  :label="$t('common.type')"
                  :rules="relationRules"
                  :disabled="form.apartment_id == null"
                  :error-messages="fieldErrors.relation_type ?? []"
                />
              </VCol>

              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="form.start_date"
                  type="date"
                  :label="$t('common.startDate')"
                  :disabled="form.apartment_id == null"
                  :error-messages="fieldErrors.start_date ?? []"
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
