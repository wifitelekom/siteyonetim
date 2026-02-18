import { setupLayouts } from 'virtual:meta-layouts'
import type { App } from 'vue'

import type { RouteRecordRaw } from 'vue-router/auto'

import { createRouter, createWebHistory } from 'vue-router/auto'
import { useAuthSession } from '@/composables/useAuthSession'
import { canAccessPath } from '@/utils/access'

function recursiveLayouts(route: RouteRecordRaw): RouteRecordRaw {
  if (route.children) {
    for (let i = 0; i < route.children.length; i++)
      route.children[i] = recursiveLayouts(route.children[i])
  }

  const hasComponent = Boolean(
    (route as RouteRecordRaw & { component?: unknown; components?: unknown }).component
    || (route as RouteRecordRaw & { component?: unknown; components?: unknown }).components,
  )

  if (!hasComponent)
    return route

  return setupLayouts([route])[0]
}

const router = createRouter({
  history: createWebHistory('/'),
  scrollBehavior(to) {
    if (to.hash)
      return { el: to.hash, top: 60 }

    return { top: 0 }
  },
  extendRoutes: pages => [
    ...[...pages].map(route => recursiveLayouts(route)),
  ],
})

router.beforeEach(async to => {
  const authSession = useAuthSession()

  await authSession.ensureSession()

  const isPublicRoute = Boolean(to.meta?.public)

  if (!authSession.isAuthenticated.value && !isPublicRoute) {
    return {
      path: '/login',
      query: { redirect: to.fullPath },
    }
  }

  if (authSession.isAuthenticated.value && to.path === '/login') {
    return typeof to.query.redirect === 'string'
      ? to.query.redirect
      : '/'
  }

  if (
    authSession.isAuthenticated.value
    && authSession.hasRole('super-admin')
    && !authSession.site.value
    && !to.path.startsWith('/super/sites')
  )
    return '/super/sites'

  if (authSession.isAuthenticated.value && !canAccessPath(to.path, authSession))
    return '/'

  return true
})

export { router }

export default function (app: App) {
  app.use(router)
}
