<script lang="ts" setup>
import baseNavItems from '@/navigation/horizontal'
import { useAuthSession } from '@/composables/useAuthSession'
import { canAccessPath } from '@/utils/access'

import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'
import { HorizontalNavLayout } from '@layouts'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import type { HorizontalNavItems } from '@layouts/types'

const authSession = useAuthSession()
const { t } = useI18n({ useScope: 'global' })

type NavItem = {
  to?: string | { path?: string | null } | null
  children?: NavItem[]
  [key: string]: unknown
}

const resolveTargetPath = (item: NavItem): string | null => {
  if (typeof item.to === 'string')
    return item.to

  if (item.to && typeof item.to === 'object' && 'path' in item.to)
    return typeof item.to.path === 'string' ? item.to.path : null

  return null
}

const filterAccessibleNavItems = (items: NavItem[]): NavItem[] => {
  return items.flatMap(item => {
    if (Array.isArray(item.children)) {
      const children = filterAccessibleNavItems(item.children)
      if (!children.length)
        return []

      return [{ ...item, children }]
    }

    const targetPath = resolveTargetPath(item)
    if (!targetPath || canAccessPath(targetPath, authSession))
      return [item]

    return []
  })
}

const siteSubtitle = computed(() => {
  if (authSession.site.value?.name)
    return authSession.site.value.name

  if (authSession.hasRole('super-admin'))
    return t('navigation.centralManagement')

  return t('navigation.noSiteSelected')
})

const navItems = computed<HorizontalNavItems>(() => {
  return filterAccessibleNavItems(baseNavItems as NavItem[]) as unknown as HorizontalNavItems
})
</script>

<template>
  <HorizontalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar>
      <RouterLink
        to="/"
        class="app-logo"
      >
        <VNodeRenderer :nodes="themeConfig.app.logo" />

        <div class="app-title-content">
          <h1 class="app-logo-title leading-normal">
            {{ themeConfig.app.title }}
          </h1>
          <p class="app-logo-subtitle">
            {{ siteSubtitle }}
          </p>
        </div>
      </RouterLink>
      <VSpacer />

      <NavBarI18n
        v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
        :languages="themeConfig.app.i18n.langConfig"
      />

      <NavbarThemeSwitcher class="me-2" />
      <UserProfile />
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </HorizontalNavLayout>
</template>

<style lang="scss" scoped>
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.5rem;

  .app-title-content {
    display: flex;
    flex-direction: column;
    min-inline-size: 0;
  }

  .app-logo-title {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.3;
    text-transform: capitalize;
  }

  .app-logo-subtitle {
    overflow: hidden;
    margin: 0;
    color: rgb(var(--v-theme-on-surface), 0.62);
    font-size: 0.75rem;
    line-height: 1.2;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
</style>
