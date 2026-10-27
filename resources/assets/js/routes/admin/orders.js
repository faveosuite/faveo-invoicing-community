export default [
    { path: '/orders',            component: () => import('../../pages/admin/orders/OrderIndex.vue'), meta: { title: 'All Orders',   titleKey: 'message.all-orders' } },
    { path: '/orders/:id/renew',  component: () => import('../../pages/admin/orders/OrderRenew.vue'), meta: { title: 'Renew Order',   titleKey: 'message.renew_order' } },
    { path: '/orders/:id',        component: () => import('../../pages/admin/orders/OrderShow.vue'),  meta: { title: 'Order Details', titleKey: 'message.order_details' } },
]
