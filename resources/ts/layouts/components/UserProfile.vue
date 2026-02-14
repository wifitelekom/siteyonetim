<script setup lang="ts">
import { useAuthSession } from '@/composables/useAuthSession'

const router = useRouter()
const authSession = useAuthSession()
const isLoggingOut = ref(false)
const { t } = useI18n({ useScope: 'global' })

const userName = computed(() => authSession.user.value?.name ?? t('common.user'))
const roleName = computed(() => authSession.roles.value[0] ?? t('common.user'))
const siteName = computed(() => authSession.site.value?.name ?? t('navigation.noSiteAssigned'))
const canManageSiteSettings = computed(() => authSession.roles.value.includes('admin') || authSession.roles.value.includes('super-admin'))
const canManageSites = computed(() => authSession.can('sites.manage'))
const userInitials = computed(() => {
  const parts = userName.value.trim().split(' ').filter(Boolean)
  return parts.slice(0, 2).map(part => part[0]).join('').toUpperCase() || 'K'
})

const handleLogout = async () => {
  isLoggingOut.value = true

  try {
    await authSession.logout()
    await router.replace('/login')
  }
  finally {
    isLoggingOut.value = false
  }
}

onMounted(async () => {
  await authSession.ensureSession()
})
</script>

<template>
  <VBadge
    dot
    bordered
    location="bottom right"
    offset-x="2"
    offset-y="2"
    color="success"
    class="user-profile-badge"
  >
    <VAvatar
      class="cursor-pointer"
      size="38"
      color="primary"
      variant="tonal"
    >
      <span class="text-caption font-weight-bold">{{ userInitials }}</span>

      <VMenu
        activator="parent"
        width="260"
        location="bottom end"
        offset="15px"
      >
        <VList>
          <VListItem class="px-4 py-3">
            <div class="d-flex gap-x-3 align-center">
              <VAvatar
                color="primary"
                variant="tonal"
              >
                <span class="text-caption font-weight-bold">{{ userInitials }}</span>
              </VAvatar>

              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  {{ userName }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ roleName }}
                </div>
                <div class="text-caption text-disabled">
                  {{ siteName }}
                </div>
              </div>
            </div>
          </VListItem>

          <VDivider class="my-1" />

          <VListItem
            class="px-4"
            :to="'/'"
          >
            <template #prepend>
              <VIcon
                icon="ri-home-4-line"
                size="22"
              />
            </template>
            <VListItemTitle>{{ $t('common.home') }}</VListItemTitle>
          </VListItem>

          <VListItem
            class="px-4"
            :to="'/profile/password'"
          >
            <template #prepend>
              <VIcon
                icon="ri-lock-password-line"
                size="22"
              />
            </template>
            <VListItemTitle>{{ $t('navigation.passwordChange') }}</VListItemTitle>
          </VListItem>

          <VListItem
            v-if="canManageSiteSettings"
            class="px-4"
            :to="'/management/site-settings'"
          >
            <template #prepend>
              <VIcon
                icon="ri-settings-3-line"
                size="22"
              />
            </template>
            <VListItemTitle>{{ $t('navigation.siteSettings') }}</VListItemTitle>
          </VListItem>

          <VListItem
            v-if="canManageSites"
            class="px-4"
            :to="'/super/sites'"
          >
            <template #prepend>
              <VIcon
                icon="ri-shield-user-line"
                size="22"
              />
            </template>
            <VListItemTitle>{{ $t('navigation.siteManagement') }}</VListItemTitle>
          </VListItem>

          <VListItem class="px-4 pt-2 pb-3">
            <VBtn
              block
              color="error"
              size="small"
              append-icon="ri-logout-box-r-line"
              :loading="isLoggingOut"
              :disabled="isLoggingOut"
              @click="handleLogout"
            >
              {{ $t('common.logout') }}
            </VBtn>
          </VListItem>
        </VList>
      </VMenu>
    </VAvatar>
  </VBadge>
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>
