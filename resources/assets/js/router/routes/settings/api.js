export default [
    { path: '/settings/api/pipedrive', component: () => import('../../../pages/settings/api/Pipedrive.vue') },
    { path: '/settings/api/recaptcha', component: () => import('../../../pages/settings/api/Recaptcha.vue') },
    { path: '/settings/api/third-party', component: () => import('../../../pages/settings/api/ThirdPartyIntegrations.vue') },
]
