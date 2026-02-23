export default [
    { path: '/settings/common/tax', component: () => import('../../../pages/settings/common/tax/Index.vue') },
    { path: '/settings/common/tax/:id/edit', component: () => import('../../../pages/settings/common/tax/Edit.vue') },
    { path: '/settings/common/currency', component: () => import('../../../pages/settings/common/Currency.vue') },
    { path: '/settings/common/countries', component: () => import('../../../pages/settings/common/CountryList.vue') },
    { path: '/settings/common/queues', component: () => import('../../../pages/settings/common/Queues.vue') },
]
