export default [
    { path: '/reports',          component: () => import('../../pages/reports/ReportIndex.vue'),    meta: { title: 'Reports',         titleKey: 'message.reports' } },
    { path: '/reports/settings', component: () => import('../../pages/reports/ReportSettings.vue'), meta: { title: 'Report Settings', titleKey: 'message.report_settings' } },
]
