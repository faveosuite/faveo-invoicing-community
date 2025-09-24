export default [
    { path: '/settings/api/pipedrive',   component: () => import('../../../pages/settings/api/Pipedrive.vue'),              meta: { title: 'Pipedrive',                  titleKey: 'message.pipedrive' } },
    { path: '/settings/api/recaptcha',   component: () => import('../../../pages/settings/api/Recaptcha.vue'),              meta: { title: 'reCAPTCHA',                  titleKey: 'message.recaptcha' } },
    { path: '/settings/api/third-party', component: () => import('../../../pages/settings/api/ThirdPartyIntegrations.vue'), meta: { title: 'Third-Party Integrations',   titleKey: 'message.third_party_integrations' } },
]
