export default [
    { path: '/settings/common/tax',          component: () => import('../../../pages/settings/common/tax/Index.vue'),   meta: { title: 'Tax' } },
    { path: '/settings/common/tax/:id/edit', component: () => import('../../../pages/settings/common/tax/Edit.vue'),   meta: { title: 'Edit Tax' } },
    { path: '/settings/common/currency',     component: () => import('../../../pages/settings/common/Currency.vue'),   meta: { title: 'Currency' } },
    { path: '/settings/common/countries',    component: () => import('../../../pages/settings/common/CountryList.vue'), meta: { title: 'Countries' } },
    { path: '/settings/common/queues',       component: () => import('../../../pages/settings/common/Queues.vue'),     meta: { title: 'Queues' } },
]
