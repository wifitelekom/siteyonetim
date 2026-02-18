export default [
  {
    title: 'navigation.dashboard',
    to: { path: '/' },
    icon: { icon: 'ri-home-smile-2-line' },
  },
  {
    title: 'navigation.charges',
    to: { path: '/charges' },
    icon: { icon: 'ri-file-list-3-line' },
  },
  {
    title: 'navigation.expenses',
    to: { path: '/expenses' },
    icon: { icon: 'ri-wallet-3-line' },
  },
  {
    title: 'navigation.reports',
    icon: { icon: 'ri-bar-chart-grouped-line' },
    children: [
      {
        title: 'navigation.generalReports',
        to: { path: '/reports' },
        icon: { icon: 'ri-bar-chart-grouped-line' },
      },
      {
        title: 'navigation.receipts',
        to: { path: '/receipts' },
        icon: { icon: 'ri-receipt-line' },
      },
      {
        title: 'navigation.payments',
        to: { path: '/payments' },
        icon: { icon: 'ri-secure-payment-line' },
      },
    ],
  },
  {
    title: 'navigation.accounts',
    to: { path: '/accounts' },
    icon: { icon: 'ri-bank-card-line' },
  },
  {
    title: 'navigation.cashAccounts',
    to: { path: '/cash-accounts' },
    icon: { icon: 'ri-wallet-line' },
  },
  {
    title: 'navigation.apartments',
    to: { path: '/management/apartments' },
    icon: { icon: 'ri-building-line' },
  },
  {
    title: 'navigation.users',
    to: { path: '/management/users' },
    icon: { icon: 'ri-group-line' },
  },
  {
    title: 'navigation.vendors',
    to: { path: '/management/vendors' },
    icon: { icon: 'ri-store-2-line' },
  },
  {
    title: 'Duyurular',
    to: { path: '/announcements' },
    icon: { icon: 'ri-megaphone-line' },
  },
  {
    title: 'Destek Talepleri',
    to: { path: '/support-tickets' },
    icon: { icon: 'ri-customer-service-2-line' },
  },
  {
    title: 'navigation.siteSettings',
    icon: { icon: 'ri-settings-3-line' },
    children: [
      {
        title: 'navigation.generalSiteSettings',
        to: { path: '/management/site-settings' },
        icon: { icon: 'ri-settings-3-line' },
      },
      {
        title: 'navigation.aidatTemplates',
        to: { path: '/templates/aidat' },
        icon: { icon: 'ri-file-copy-2-line' },
      },
      {
        title: 'navigation.expenseTemplates',
        to: { path: '/templates/expense' },
        icon: { icon: 'ri-file-list-2-line' },
      },
    ],
  },
]
