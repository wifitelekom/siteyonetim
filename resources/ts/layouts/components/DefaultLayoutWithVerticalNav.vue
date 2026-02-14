<script lang="ts" setup>
import baseNavItems from '@/navigation/vertical'
import { useAuthSession } from '@/composables/useAuthSession'
import { canAccessPath } from '@/utils/access'
import { useConfigStore } from '@core/stores/config'
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'
import type { VerticalNavItems } from '@layouts/types'

const configStore = useConfigStore()
const authSession = useAuthSession()

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

const navItems = computed<VerticalNavItems>(() => {
  return filterAccessibleNavItems(baseNavItems as NavItem[]) as unknown as VerticalNavItems
})

// ℹ️ Provide animation name for vertical nav collapse icon.
const verticalNavHeaderActionAnimationName = ref<'rotate-180' | 'rotate-back-180' | null>(null)

watch([
  () => configStore.isVerticalNavCollapsed,
  () => configStore.isAppRTL,
], val => {
  if (configStore.isAppRTL)
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-back-180' : 'rotate-180'
  else
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-180' : 'rotate-back-180'
}, { immediate: true })
</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n2 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon icon="ri-menu-line" />
        </IconBtn>

        <NavSearchBar class="ms-2" />

        <VSpacer />

        <NavBarI18n
          v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig"
        />
        <NavbarThemeSwitcher class="me-2" />
        <UserProfile />
      </div>
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>

<style lang="scss">
@keyframes rotate-180 {
  from { transform: rotate(0deg); }
  to { transform: rotate(180deg); }
}

@keyframes rotate-back-180 {
  from { transform: rotate(180deg); }
  to { transform: rotate(0deg); }
}

.layout-vertical-nav {
  .nav-header {
    .header-action {
      animation-duration: 0.35s;
      animation-fill-mode: forwards;
      animation-name: v-bind(verticalNavHeaderActionAnimationName);
      transform: rotate(0deg);
    }
  }
}
</style>
