<script setup lang="ts">
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import { useAuthSession } from '@/composables/useAuthSession'
import { getApiErrorMessage, getApiErrorStatus, getApiFieldErrors } from '@/utils/errorHandler'
import { requiredRule } from '@/utils/validators'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2LoginMaskDark from '@images/pages/auth-v2-login-mask-dark.png'
import authV2LoginMaskLight from '@images/pages/auth-v2-login-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const router = useRouter()
const route = useRoute()
const authSession = useAuthSession()
const { t } = useI18n({ useScope: 'global' })

const form = ref({
  identity: '',
  password: '',
  remember: false,
})

const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)
const identityRules = [requiredRule()]
const passwordRules = [requiredRule()]

const isPasswordVisible = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const authV2LoginMask = useGenerateImageVariant(authV2LoginMaskLight, authV2LoginMaskDark)
const authV2LoginIllustration = useGenerateImageVariant(
  authV2LoginIllustrationLight,
  authV2LoginIllustrationDark,
  authV2LoginIllustrationBorderedLight,
  authV2LoginIllustrationBorderedDark,
  true,
)

const parseApiError = (error: unknown) => {
  if (getApiErrorStatus(error) === 429) {
    errorMessage.value = t('auth.tooManyAttempts')

    return
  }

  const apiFieldErrors = getApiFieldErrors(error)
  fieldErrors.value = apiFieldErrors
  errorMessage.value = apiFieldErrors.identity?.[0] ?? getApiErrorMessage(error, 'auth.loginFailed')
}

const onSubmit = async () => {
  const validation = await formRef.value?.validate()
  if (!validation?.valid)
    return

  errorMessage.value = ''
  fieldErrors.value = {}
  isSubmitting.value = true

  try {
    await authSession.login(form.value.identity, form.value.password, form.value.remember)

    const redirectPath = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    await router.replace(redirectPath)
  }
  catch (error) {
    parseApiError(error)
  }
  finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  await authSession.ensureSession()

  if (authSession.isAuthenticated.value)
    await router.replace('/')
})
</script>

<template>
  <a href="javascript:void(0)">
    <div class="app-logo auth-logo">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="app-logo-title">
        {{ themeConfig.app.title }}
      </h1>
    </div>
  </a>

  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="8"
      class="d-none d-md-flex align-center justify-center position-relative"
    >
      <div class="d-flex align-center justify-center pa-10">
        <img
          :src="authV2LoginIllustration"
          class="auth-illustration w-100"
          alt="auth-illustration"
        >
      </div>
      <VImg
        :src="authV2LoginMask"
        class="d-none d-md-flex auth-footer-mask"
        alt="auth-mask"
      />
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
      style="background-color: rgb(var(--v-theme-surface));"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-5 pa-lg-7"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            {{ $t('auth.welcome') }}, <span class="text-capitalize">{{ themeConfig.app.title }}</span>
          </h4>
          <p class="mb-0">
            {{ $t('auth.loginPrompt') }}
          </p>
        </VCardText>

        <VCardText>
          <VForm
            ref="formRef"
            @submit.prevent="onSubmit"
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
                  v-model="form.identity"
                  autofocus
                  :label="$t('auth.identityLabel')"
                  :placeholder="$t('auth.identityPlaceholder')"
                  :rules="identityRules"
                  :error-messages="fieldErrors.identity ?? []"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  :label="$t('profile.currentPassword')"
                  placeholder="********"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :rules="passwordRules"
                  :error-messages="fieldErrors.password ?? []"
                  :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center justify-space-between flex-wrap my-6 gap-x-2">
                  <VCheckbox
                    v-model="form.remember"
                    :label="$t('auth.rememberMe')"
                  />

                  <span class="text-caption text-medium-emphasis">{{ $t('auth.securityNotice') }}</span>
                </div>

                <VBtn
                  block
                  type="submit"
                  :loading="isSubmitting"
                  :disabled="isSubmitting"
                >
                  {{ $t('common.login') }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
