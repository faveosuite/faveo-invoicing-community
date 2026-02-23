export default [
    { path: '/settings/logs/system',   component: () => import('../../../pages/settings/logs/SystemLogs.vue'),   meta: { title: 'System Logs' } },
    { path: '/settings/logs/activity', component: () => import('../../../pages/settings/logs/ActivityLogs.vue'), meta: { title: 'Activity Logs' } },
    { path: '/settings/logs/payment',  component: () => import('../../../pages/settings/logs/PaymentLog.vue'),   meta: { title: 'Payment Logs' } },
    { path: '/settings/logs/msg91',    component: () => import('../../../pages/settings/logs/MSG91Reports.vue'), meta: { title: 'MSG91 Reports' } },
]
