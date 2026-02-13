type ValidationResult = true | string

export const requiredRule = (message = 'Bu alan zorunludur.') => (value: unknown): ValidationResult => {
  if (value === null || value === undefined)
    return message

  if (typeof value === 'string' && value.trim().length === 0)
    return message

  if (Array.isArray(value) && value.length === 0)
    return message

  return true
}

export const emailRule = (message = 'Gecerli bir e-posta adresi giriniz.') => (value: unknown): ValidationResult => {
  if (typeof value !== 'string' || value.trim().length === 0)
    return message

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  return emailPattern.test(value) ? true : message
}

export const minLengthRule = (min: number, message?: string) => (value: unknown): ValidationResult => {
  if (typeof value !== 'string')
    return message ?? `En az ${min} karakter giriniz.`

  return value.length >= min ? true : (message ?? `En az ${min} karakter giriniz.`)
}

export const maxLengthRule = (max: number, message?: string) => (value: unknown): ValidationResult => {
  if (typeof value !== 'string')
    return true

  return value.length <= max ? true : (message ?? `En fazla ${max} karakter giriniz.`)
}

export const exactLengthRule = (length: number, message?: string) => (value: unknown): ValidationResult => {
  if (typeof value !== 'string')
    return message ?? `${length} karakter olmalidir.`

  return value.length === length ? true : (message ?? `${length} karakter olmalidir.`)
}

export const positiveNumberRule = (message = 'Sifirdan buyuk bir deger giriniz.') => (value: unknown): ValidationResult => {
  if (value === null || value === undefined || value === '')
    return message

  const parsed = Number(value)

  return Number.isFinite(parsed) && parsed > 0 ? true : message
}

export const matchRule = (
  getExpectedValue: () => unknown,
  message = 'Alanlar eslesmiyor.',
) => (value: unknown): ValidationResult => (value === getExpectedValue() ? true : message)
