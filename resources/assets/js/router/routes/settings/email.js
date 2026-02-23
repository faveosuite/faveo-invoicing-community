export default [
    { path: '/settings/email/settings', component: () => import('../../../pages/settings/email/EmailSettings.vue') },
    { path: '/settings/email/template-settings', component: () => import('../../../pages/settings/email/TemplateSettings.vue') },
    { path: '/settings/email/templates', component: () => import('../../../pages/settings/email/Templates.vue') },
]
