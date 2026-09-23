export default [
    { path: '/settings/api/pipedrive',          component: () => import('../../../pages/admin/settings/api/Pipedrive.vue'),                meta: { title: 'Pipedrive',                   titleKey: 'message.pipedrive' } },
    { path: '/settings/api/recaptcha',          component: () => import('../../../pages/admin/settings/api/Recaptcha.vue'),                meta: { title: 'reCAPTCHA',                   titleKey: 'message.recaptcha' } },
    { path: '/settings/api/third-party',        component: () => import('../../../pages/admin/settings/api/ThirdPartyIntegrations.vue'),   meta: { title: 'Third-Party Integrations',    titleKey: 'message.third_party_integrations' } },
    { path: '/settings/api/msg91',              component: () => import('../../../pages/admin/settings/api/Msg91Settings.vue'),            meta: { title: 'MSG91',                       titleKey: 'message.msg91_heading' } },
    { path: '/settings/api/github',             component: () => import('../../../pages/admin/settings/api/GithubSettings.vue'),           meta: { title: 'GitHub Settings',              titleKey: 'message.github_settings' } },
    { path: '/settings/api/mailchimp',          component: () => import('../../../pages/admin/settings/api/MailchimpSettings.vue'),        meta: { title: 'Mailchimp',                   titleKey: 'message.mailchimp' } },
    { path: '/settings/api/terms',              component: () => import('../../../pages/admin/settings/api/TermsSettings.vue'),            meta: { title: 'Terms & Conditions',          titleKey: 'message.terms_heading' } },
    { path: '/settings/api/email-validation',      component: () => import('../../../pages/admin/settings/api/EmailValidationSettings.vue'), meta: { title: 'Email Validation',      titleKey: 'message.email_provider' } },
    { path: '/settings/api/email-validation/logs', component: () => import('../../../pages/admin/settings/api/EmailValidationLogs.vue'),     meta: { title: 'Email Validation Logs', titleKey: 'message.email_validation_logs', breadcrumb: [
        { title: 'Settings',              titleKey: 'message.settings',              to: '/settings' },
        { title: 'Email Validation Logs', titleKey: 'message.email_validation_logs' },
    ] } },
    { path: '/settings/api/mobile-validation',  component: () => import('../../../pages/admin/settings/api/MobileValidationSettings.vue'),meta: { title: 'Mobile Validation',           titleKey: 'message.mobile_provider' } },
    { path: '/settings/api/zoho',               component: () => import('../../../pages/admin/settings/api/ZohoSettings.vue'),         meta: { title: 'Zoho',          titleKey: 'message.zoho_integration' } },
    { path: '/settings/api/zoho/:platform',     component: () => import('../../../pages/admin/settings/api/ZohoPlatformSettings.vue'), meta: { title: 'Zoho Settings', titleKey: 'message.zoho_platform_settings' } },
]
