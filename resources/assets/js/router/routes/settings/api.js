export default [
    { path: '/settings/api/pipedrive',          component: () => import('../../../pages/settings/api/Pipedrive.vue'),                meta: { title: 'Pipedrive',                   titleKey: 'message.pipedrive' } },
    { path: '/settings/api/recaptcha',          component: () => import('../../../pages/settings/api/Recaptcha.vue'),                meta: { title: 'reCAPTCHA',                   titleKey: 'message.recaptcha' } },
    { path: '/settings/api/third-party',        component: () => import('../../../pages/settings/api/ThirdPartyIntegrations.vue'),   meta: { title: 'Third-Party Integrations',    titleKey: 'message.third_party_integrations' } },
    { path: '/settings/api/msg91',              component: () => import('../../../pages/settings/api/Msg91Settings.vue'),            meta: { title: 'MSG91',                       titleKey: 'message.msg91_heading' } },
    { path: '/settings/api/github',             component: () => import('../../../pages/settings/api/GithubSettings.vue'),           meta: { title: 'GitHub',                      titleKey: 'message.github_heading' } },
    { path: '/settings/api/mailchimp',          component: () => import('../../../pages/settings/api/MailchimpSettings.vue'),        meta: { title: 'Mailchimp',                   titleKey: 'message.mailchimp_heading' } },
    { path: '/settings/api/terms',              component: () => import('../../../pages/settings/api/TermsSettings.vue'),            meta: { title: 'Terms & Conditions',          titleKey: 'message.terms_heading' } },
    { path: '/settings/api/email-validation',   component: () => import('../../../pages/settings/api/EmailValidationSettings.vue'), meta: { title: 'Email Validation',            titleKey: 'message.email_provider' } },
    { path: '/settings/api/mobile-validation',  component: () => import('../../../pages/settings/api/MobileValidationSettings.vue'),meta: { title: 'Mobile Validation',           titleKey: 'message.mobile_provider' } },
]
