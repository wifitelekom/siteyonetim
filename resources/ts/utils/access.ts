interface SessionAccess {
  can: (permissionName: string) => boolean
  hasRole: (roleName: string) => boolean
}

const startsWithPath = (path: string, prefix: string) => path === prefix || path.startsWith(`${prefix}/`)

const isUserCreateOrEditPath = (path: string) =>
  path === '/management/users/create'
  || /^\/management\/users\/[^/]+\/edit$/.test(path)

export const canAccessPath = (path: string, session: SessionAccess): boolean => {
  if (path === '/' || path === '/profile/password')
    return true

  if (startsWithPath(path, '/super/sites'))
    return session.can('sites.manage')

  if (startsWithPath(path, '/reports'))
    return session.can('reports.view')

  if (startsWithPath(path, '/charges'))
    return session.can('charges.view')

  if (startsWithPath(path, '/expenses'))
    return session.can('expenses.view')

  if (startsWithPath(path, '/receipts'))
    return session.can('receipts.view')

  if (startsWithPath(path, '/payments'))
    return session.can('payments.view')

  if (startsWithPath(path, '/accounts'))
    return session.can('accounts.view')

  if (startsWithPath(path, '/cash-accounts'))
    return session.can('cash-accounts.view')

  if (startsWithPath(path, '/management/apartments'))
    return session.can('apartments.view')

  if (isUserCreateOrEditPath(path))
    return session.can('users.manage')

  if (startsWithPath(path, '/management/users'))
    return session.can('users.view')

  if (startsWithPath(path, '/management/vendors'))
    return session.can('vendors.view')

  if (startsWithPath(path, '/management/site-settings'))
    return session.hasRole('admin') || session.hasRole('super-admin')

  if (startsWithPath(path, '/templates/aidat') || startsWithPath(path, '/templates/expense'))
    return session.can('templates.manage')

  return true
}
