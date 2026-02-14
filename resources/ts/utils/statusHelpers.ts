import { i18n } from '@/plugins/0.i18n'

// Charge status helpers (charges, apartments, reports)
export const chargeStatusColor = (status: string | null) =>
  status === 'paid' ? 'success' : status === 'overdue' ? 'error' : 'warning'

export const chargeStatusLabel = (status: string | null) =>
  status === 'paid'
    ? i18n.global.t('status.charge.paid')
    : status === 'overdue'
      ? i18n.global.t('status.charge.overdue')
      : i18n.global.t('status.charge.open')

// Expense status helpers (expenses)
export const expenseStatusColor = (status: string) =>
  status === 'paid' ? 'success' : status === 'partial' ? 'info' : 'secondary'

export const expenseStatusLabel = (status: string) =>
  status === 'paid'
    ? i18n.global.t('status.expense.paid')
    : status === 'partial'
      ? i18n.global.t('status.expense.partial')
      : i18n.global.t('status.expense.unpaid')

// Payment method label (payments, receipts, reports)
export const methodLabel = (method: string) =>
  method === 'cash'
    ? i18n.global.t('status.method.cash')
    : method === 'bank'
      ? i18n.global.t('status.method.bank')
      : method

// Account/report type label (reports)
export const typeLabel = (type: string) =>
  type === 'charge'
    ? i18n.global.t('status.type.charge')
    : type === 'expense'
      ? i18n.global.t('status.type.expense')
      : type
