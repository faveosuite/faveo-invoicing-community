export default [
    { path: '/reports',          component: () => import('../../pages/admin/reports/ReportIndex.vue'),    meta: { title: 'All Reports',     titleKey: 'message.all_reports' } },
    { path: '/reports/settings', component: () => import('../../pages/admin/reports/ReportSettings.vue'), meta: { title: 'Report Settings', titleKey: 'message.report_settings' } },
]
