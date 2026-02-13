// Charge status helpers (charges, apartments, reports)
export const chargeStatusColor = (status: string | null) =>
  status === 'paid' ? 'success' : status === 'overdue' ? 'error' : 'warning'

export const chargeStatusLabel = (status: string | null) =>
  status === 'paid' ? 'Odendi' : status === 'overdue' ? 'Gecikmis' : 'Acik'

// Expense status helpers (expenses)
export const expenseStatusColor = (status: string) =>
  status === 'paid' ? 'success' : status === 'partial' ? 'info' : 'secondary'

export const expenseStatusLabel = (status: string) =>
  status === 'paid' ? 'Odendi' : status === 'partial' ? 'Kismi' : 'Odenmedi'

// Payment method label (payments, receipts, reports)
export const methodLabel = (method: string) =>
  method === 'cash' ? 'Nakit' : method === 'bank' ? 'Banka' : method

// Account/report type label (reports)
export const typeLabel = (type: string) =>
  type === 'charge' ? 'Tahakkuk' : type === 'expense' ? 'Gider' : type
