export default [
    { path: '/reports', component: () => import('../../pages/reports/Index.vue'), meta: { title: 'Reports' } },
    { path: '/reports/settings', component: () => import('../../pages/reports/Settings.vue'), meta: { title: 'Report Settings' } },
]
