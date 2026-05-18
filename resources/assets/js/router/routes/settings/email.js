export default [
    { path: '/settings/email/settings',              component: () => import('../../../pages/settings/email/EmailSettings.vue'),    meta: { title: 'Email Settings',    titleKey: 'message.email_settings' } },
    { path: '/settings/email/template-settings',     component: () => import('../../../pages/settings/email/TemplateSettings.vue'), meta: { title: 'Template Settings', titleKey: 'message.template_settings' } },
    { path: '/settings/email/templates',             component: () => import('../../../pages/settings/email/Templates.vue'),        meta: { title: 'Email Templates',   titleKey: 'message.email_templates' } },
    { path: '/settings/email/templates/:id/edit',    component: () => import('../../../pages/settings/email/TemplateEdit.vue'),     meta: { title: 'Edit Template',     titleKey: 'message.edit_template' } },
]
