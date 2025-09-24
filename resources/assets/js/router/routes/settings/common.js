export default [
    { path: '/settings/common/tax',          component: () => import('../../../pages/settings/common/tax/Index.vue'),    meta: { title: 'Tax',        titleKey: 'message.tax' } },
    { path: '/settings/common/tax/:id/edit', component: () => import('../../../pages/settings/common/tax/Edit.vue'),    meta: { title: 'Edit Tax',   titleKey: 'message.edit_tax' } },
    { path: '/settings/common/currency',     component: () => import('../../../pages/settings/common/Currency.vue'),    meta: { title: 'Currency',   titleKey: 'message.currency' } },
    { path: '/settings/common/countries',    component: () => import('../../../pages/settings/common/CountryList.vue'), meta: { title: 'Countries',  titleKey: 'message.countries' } },
    { path: '/settings/common/queues',       component: () => import('../../../pages/settings/common/Queues.vue'),      meta: { title: 'Queues',     titleKey: 'message.queues' } },
]
