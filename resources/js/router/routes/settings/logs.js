export default [
    { path: '/settings/logs/system', component: () => import('@/pages/settings/logs/SystemLogs.vue') },
    { path: '/settings/logs/activity', component: () => import('@/pages/settings/logs/ActivityLogs.vue') },
    { path: '/settings/logs/payment', component: () => import('@/pages/settings/logs/PaymentLog.vue') },
    { path: '/settings/logs/msg91', component: () => import('@/pages/settings/logs/MSG91Reports.vue') },
]
