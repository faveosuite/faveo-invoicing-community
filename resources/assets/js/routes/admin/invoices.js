export default [
    { path: '/invoices',          component: () => import('../../pages/admin/invoices/InvoiceIndex.vue'),  meta: { title: 'All Invoices', titleKey: 'message.all-invoices' } },
    { path: '/invoices/create',   component: () => import('../../pages/admin/invoices/InvoiceCreate.vue'), meta: { title: 'Create New Invoice', titleKey: 'message.create-invoice' } },
{ path: '/invoices/:id',      component: () => import('../../pages/admin/invoices/InvoiceShow.vue'),   meta: { title: 'Invoice Details', titleKey: 'message.invoice_detail_page' } },
]
