export default [
    { path: '/reports',          component: () => import('../../pages/admin/reports/ReportIndex.vue'),    meta: { title: 'Reports',         titleKey: 'message.reports' } },
    { path: '/reports/settings', component: () => import('../../pages/admin/reports/ReportSettings.vue'), meta: { title: 'Report Settings', titleKey: 'message.report_settings' } },
]
