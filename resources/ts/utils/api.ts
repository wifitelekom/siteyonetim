import { ofetch } from 'ofetch'

const resolveCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
const resolveXsrfCookieToken = () => {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api/v1'

export const getApiBaseUrl = () => apiBaseUrl

export const $api = ofetch.create({
  baseURL: apiBaseUrl,
  credentials: 'same-origin',
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  async onRequest({ options }) {
    const method = (options.method || 'GET').toString().toUpperCase()
    const headers = new Headers(options.headers as HeadersInit)

    if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
      const xsrfCookieToken = resolveXsrfCookieToken()

      // Prefer cookie-based token so SPA session regeneration does not leave stale meta tokens.
      if (xsrfCookieToken) {
        headers.set('X-XSRF-TOKEN', xsrfCookieToken)
      } else {
        const csrfToken = resolveCsrfToken()
        if (csrfToken)
          headers.set('X-CSRF-TOKEN', csrfToken)
      }
    }

    options.headers = headers
  },
})
