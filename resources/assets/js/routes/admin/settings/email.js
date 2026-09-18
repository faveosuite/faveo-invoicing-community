export default [
    { path: '/settings/email/settings',              component: () => import('../../../pages/admin/settings/email/EmailSettings.vue'),    meta: { title: 'Email Settings',    titleKey: 'message.email_settings' } },
    { path: '/settings/email/template-settings',     component: () => import('../../../pages/admin/settings/email/TemplateSettings.vue'), meta: { title: 'Template Settings', titleKey: 'message.template_settings' } },
    { path: '/settings/email/templates',             component: () => import('../../../pages/admin/settings/email/Templates.vue'),        meta: { title: 'All Email Templates', titleKey: 'message.all_email_templates' } },
    { path: '/settings/email/templates/:id/edit',    component: () => import('../../../pages/admin/settings/email/TemplateEdit.vue'),     meta: { title: 'Edit Template',     titleKey: 'message.edit_template' } },
]
