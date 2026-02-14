import type { ApiErrorData } from '@/types/api'
import { i18n } from '@/plugins/0.i18n'

const isRecord = (value: unknown): value is Record<string, unknown> =>
  typeof value === 'object' && value !== null

const getApiErrorData = (error: unknown): ApiErrorData | null => {
  if (!isRecord(error))
    return null

  const data = error.data
  if (!isRecord(data))
    return null

  return data as ApiErrorData
}

export const getApiErrorMessage = (error: unknown, fallback: string) => {
  const apiData = getApiErrorData(error)

  if (typeof apiData?.message === 'string' && apiData.message.length > 0)
    return apiData.message

  if (i18n.global.te(fallback))
    return i18n.global.t(fallback)

  return fallback
}

export const getApiFieldErrors = (error: unknown) => {
  const apiData = getApiErrorData(error)
  const errors = apiData?.errors

  if (!errors || typeof errors !== 'object')
    return {}

  const normalized: Record<string, string[]> = {}

  Object.entries(errors).forEach(([field, value]) => {
    if (Array.isArray(value)) {
      const messages = value.filter((item): item is string => typeof item === 'string')
      if (messages.length > 0)
        normalized[field] = messages
    }
    else if (typeof value === 'string') {
      normalized[field] = [value]
    }
  })

  return normalized
}

export const getApiErrorStatus = (error: unknown) => {
  if (!isRecord(error))
    return null

  const response = error.response
  if (!isRecord(response))
    return null

  return typeof response.status === 'number' ? response.status : null
}
