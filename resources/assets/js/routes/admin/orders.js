export default [
    { path: '/orders',     component: () => import('../../pages/admin/orders/OrderIndex.vue'), meta: { title: 'Orders',       titleKey: 'message.orders' } },
    { path: '/orders/:id', component: () => import('../../pages/admin/orders/OrderShow.vue'),  meta: { title: 'Order Details', titleKey: 'message.orders' } },
]
