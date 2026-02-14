import type { App } from 'vue'
import { createI18n } from 'vue-i18n'
import tr from '@/locales/tr'
import { themeConfig } from '@themeConfig'

export const i18n = createI18n({
  legacy: false,
  globalInjection: true,
  locale: themeConfig.app.i18n.defaultLocale,
  fallbackLocale: 'tr',
  missingWarn: false,
  fallbackWarn: false,
  messages: {
    tr,
    en: {},
  },
})

export default function (app: App) {
  app.use(i18n)
}
