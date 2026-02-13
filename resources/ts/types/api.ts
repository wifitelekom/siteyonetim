/** Shared dropdown option shape (id + label) */
export interface OptionItem {
  id: number
  label: string
}

/** Standard paginated response meta from Laravel */
export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

/** Standard API error shape from Laravel */
export interface ApiErrorData {
  message?: string
  errors?: Record<string, string[]>
}
