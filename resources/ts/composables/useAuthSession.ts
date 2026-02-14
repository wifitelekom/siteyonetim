import { $api } from '@/utils/api'
import { getApiErrorStatus } from '@/utils/errorHandler'

export interface AuthUser {
  id: number
  name: string
  email: string | null
  phone: string | null
  tc_kimlik: string | null
  site_id: number | null
}

export interface AuthSite {
  id: number
  name: string
}

interface AuthPayload {
  user: AuthUser
  roles: string[]
  permissions: string[]
  site: AuthSite | null
}

interface AuthResponse {
  data: AuthPayload
}

const user = ref<AuthUser | null>(null)
const site = ref<AuthSite | null>(null)
const roles = ref<string[]>([])
const permissions = ref<string[]>([])
const initialized = ref(false)
const loading = ref(false)
let pendingSessionPromise: Promise<void> | null = null

const isAuthenticated = computed(() => Boolean(user.value))

const applyAuthPayload = (payload: AuthPayload | null) => {
  user.value = payload?.user ?? null
  site.value = payload?.site ?? null
  roles.value = payload?.roles ?? []
  permissions.value = payload?.permissions ?? []
}

const clearAuthState = () => {
  applyAuthPayload(null)
}

const isUnauthorizedError = (error: unknown) => {
  const status = getApiErrorStatus(error)
  return status === 401 || status === 403
}

const ensureSession = async () => {
  if (initialized.value)
    return

  if (pendingSessionPromise)
    return pendingSessionPromise

  loading.value = true

  pendingSessionPromise = $api<AuthResponse>('/auth/me')
    .then(response => {
      applyAuthPayload(response.data)
    })
    .catch(error => {
      if (isUnauthorizedError(error))
        clearAuthState()
      else
        throw error
    })
    .finally(() => {
      initialized.value = true
      loading.value = false
      pendingSessionPromise = null
    })

  return pendingSessionPromise
}

const login = async (identity: string, password: string, remember = false) => {
  await $api('/auth/login', {
    method: 'POST',
    body: {
      identity,
      password,
      remember,
    },
  })

  initialized.value = false
  await ensureSession()
}

const switchSiteContext = async (siteId: number | null) => {
  const response = await $api<AuthResponse>('/auth/site-context', {
    method: 'PUT',
    body: {
      site_id: siteId,
    },
  })

  applyAuthPayload(response.data)
  initialized.value = true
}

const logout = async () => {
  try {
    await $api('/auth/logout', { method: 'POST' })
  }
  finally {
    clearAuthState()
    initialized.value = true
  }
}

const hasRole = (roleName: string) => roles.value.includes(roleName)
const can = (permissionName: string) => permissions.value.includes(permissionName)

export const useAuthSession = () => ({
  user: readonly(user),
  site: readonly(site),
  roles: readonly(roles),
  permissions: readonly(permissions),
  initialized: readonly(initialized),
  loading: readonly(loading),
  isAuthenticated,
  ensureSession,
  login,
  switchSiteContext,
  logout,
  hasRole,
  can,
})
