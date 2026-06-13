export default [
    { path: '/settings/common/tax',            component: () => import('../../../pages/admin/settings/common/tax/TaxIndex.vue'),  meta: { title: 'Tax',        titleKey: 'message.tax' } },
    { path: '/settings/common/tax/create',    component: () => import('../../../pages/admin/settings/common/tax/TaxCreate.vue'), meta: { title: 'Create Tax', titleKey: 'message.tax' } },
    { path: '/settings/common/tax/:id/edit',  component: () => import('../../../pages/admin/settings/common/tax/TaxEdit.vue'),   meta: { title: 'Edit Tax',   titleKey: 'message.edit_tax' } },
    { path: '/settings/common/currency',     component: () => import('../../../pages/admin/settings/common/Currency.vue'),    meta: { title: 'Currency',   titleKey: 'message.currency' } },
    { path: '/settings/common/countries',    component: () => import('../../../pages/admin/settings/common/CountryList.vue'), meta: { title: 'Countries',  titleKey: 'message.countries' } },
    { path: '/settings/common/queues',          component: () => import('../../../pages/admin/settings/common/Queues.vue'),              meta: { title: 'Queues',                  titleKey: 'message.queues' } },
    { path: '/settings/common/queue/:id',       component: () => import('../../../pages/admin/settings/common/QueueSettings.vue'),         meta: { title: 'Queue Settings',          titleKey: 'message.queue' } },
    { path: '/settings/common/cache',           component: () => import('../../../pages/admin/settings/common/CacheSettings.vue'),         meta: { title: 'Cache',                   titleKey: 'message.cache' } },
    { path: '/settings/common/cache/:driver',   component: () => import('../../../pages/admin/settings/common/CacheDriverSettings.vue'),   meta: { title: 'Cache Driver Settings',   titleKey: 'message.cache' } },
]
