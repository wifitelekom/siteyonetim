export const formatCurrency = (amount: number) => new Intl.NumberFormat('tr-TR', {
  style: 'currency',
  currency: 'TRY',
  minimumFractionDigits: 2,
}).format(Number(amount ?? 0))

export const formatDateTr = (date: string | null) => {
  if (!date)
    return '-'

  return new Intl.DateTimeFormat('tr-TR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date(date))
}
