export default [
    { path: '/invoices',          component: () => import('../../pages/invoices/InvoiceIndex.vue'),  meta: { title: 'Invoices',     titleKey: 'message.invoices' } },
    { path: '/invoices/create',   component: () => import('../../pages/invoices/InvoiceCreate.vue'), meta: { title: 'Create New Invoice', titleKey: 'message.create-invoice' } },
    { path: '/invoices/:id/edit', component: () => import('../../pages/invoices/InvoiceEdit.vue'),   meta: { title: 'Edit Invoice', titleKey: 'message.edit_invoice' } },
    { path: '/invoices/:id',      component: () => import('../../pages/invoices/InvoiceShow.vue'),   meta: { title: 'Invoice Details', titleKey: 'message.invoice_details' } },
]
