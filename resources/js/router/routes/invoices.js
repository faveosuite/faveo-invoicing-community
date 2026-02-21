export default [
    { path: '/invoices', component: () => import('@/pages/invoices/Index.vue'), meta: { title: 'Invoices' } },
    { path: '/invoices/create', component: () => import('@/pages/invoices/Create.vue'), meta: { title: 'New Invoice' } },
    { path: '/invoices/:id/edit', component: () => import('@/pages/invoices/Edit.vue'), meta: { title: 'Edit Invoice' } },
]
