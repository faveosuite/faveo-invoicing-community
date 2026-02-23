export default [
    // /settings resolves as the "Settings" breadcrumb parent segment.
    // Renders the same SystemSettings page; sidebar always links to /settings/system.
    { path: '/settings',                          component: () => import('../../../pages/settings/settings/SystemSettings.vue'),       meta: { title: 'Settings' } },
    { path: '/settings/system',                   component: () => import('../../../pages/settings/settings/SystemSettings.vue'),       meta: { title: 'System Settings' } },
    { path: '/settings/cron',                     component: () => import('../../../pages/settings/settings/Cron.vue'),                 meta: { title: 'Cron Jobs' } },
    { path: '/settings/license-type',             component: () => import('../../../pages/settings/settings/licenseType/Index.vue'),    meta: { title: 'License Types' } },
    { path: '/settings/license-type/:id/edit',    component: () => import('../../../pages/settings/settings/licenseType/Edit.vue'),    meta: { title: 'Edit License Type' } },
    { path: '/settings/license-permissions',      component: () => import('../../../pages/settings/settings/LicensePermissions.vue'),  meta: { title: 'License Permissions' } },
    { path: '/settings/file-storage',             component: () => import('../../../pages/settings/settings/FileStorage.vue'),         meta: { title: 'File Storage' } },
    { path: '/settings/payment-gateway',          component: () => import('../../../pages/settings/settings/paymentGateway/Index.vue'), meta: { title: 'Payment Gateways' } },
    { path: '/settings/payment-gateway/:id/edit', component: () => import('../../../pages/settings/settings/paymentGateway/Edit.vue'), meta: { title: 'Edit Payment Gateway' } },
    { path: '/settings/system-managers',          component: () => import('../../../pages/settings/settings/SystemManagers.vue'),       meta: { title: 'System Managers' } },
    { path: '/settings/third-party-apps',         component: () => import('../../../pages/settings/settings/thirdPartyApps/Index.vue'), meta: { title: 'Third-Party Apps' } },
    { path: '/settings/cloud-details',            component: () => import('../../../pages/settings/settings/CloudDetails.vue'),         meta: { title: 'Cloud Details' } },
    { path: '/settings/localized-license',        component: () => import('../../../pages/settings/settings/LocalizedLicense.vue'),    meta: { title: 'Localized License' } },
    { path: '/settings/debugging',                component: () => import('../../../pages/settings/settings/DebuggingSettings.vue'),   meta: { title: 'Debugging' } },
    { path: '/settings/social-logins',            component: () => import('../../../pages/settings/settings/socialLogins/Index.vue'),  meta: { title: 'Social Logins' } },
    { path: '/settings/social-logins/:id/edit',   component: () => import('../../../pages/settings/settings/socialLogins/Edit.vue'),  meta: { title: 'Edit Social Login' } },
    { path: '/settings/language',                 component: () => import('../../../pages/settings/settings/Language.vue'),            meta: { title: 'Language' } },
    { path: '/settings/whatsapp-users',           component: () => import('../../../pages/settings/settings/WhatsappUsers.vue'),       meta: { title: 'WhatsApp Users' } },
    { path: '/settings/contact-options',          component: () => import('../../../pages/settings/settings/ContactOptions.vue'),      meta: { title: 'Contact Options' } },
]
