const hasAbortErrorName = (value: unknown): value is { name: string } =>
  typeof value === 'object'
  && value !== null
  && 'name' in value
  && typeof (value as { name: unknown }).name === 'string'

export const isAbortError = (error: unknown) => {
  if (error instanceof Error && error.name === 'AbortError')
    return true

  return hasAbortErrorName(error) && error.name === 'AbortError'
}

export const useAbortOnUnmount = () => {
  const controllers = new Set<AbortController>()

  const withAbort = async <T>(request: (signal: AbortSignal) => Promise<T>) => {
    const controller = new AbortController()
    controllers.add(controller)

    try {
      return await request(controller.signal)
    }
    finally {
      controllers.delete(controller)
    }
  }

  const abortAll = () => {
    controllers.forEach(controller => controller.abort())
    controllers.clear()
  }

  onBeforeUnmount(abortAll)

  return {
    withAbort,
    abortAll,
  }
}
