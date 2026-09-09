export default [
    { path: '/settings/logs/system',   component: () => import('../../../../../../app/BillingLog/views/SystemLogs.vue'),   meta: { title: 'All System Logs',   titleKey: 'message.all_system_logs' } },
    { path: '/settings/logs/activity', component: () => import('../../../pages/admin/settings/logs/ActivityLogs.vue'), meta: { title: 'All Activity Logs', titleKey: 'message.all_activity_logs' } },
    { path: '/settings/logs/payment',  component: () => import('../../../pages/admin/settings/logs/PaymentLog.vue'),   meta: { title: 'All Payment Logs',  titleKey: 'message.all_payment_logs' } },
    { path: '/settings/logs/msg91',    component: () => import('../../../pages/admin/settings/logs/MSG91Reports.vue'), meta: { title: 'All MSG91 Reports', titleKey: 'message.all_msg_reports' } },
]
