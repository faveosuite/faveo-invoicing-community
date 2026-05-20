export default [
    { path: '/orders',     component: () => import('../../pages/orders/OrderIndex.vue'), meta: { title: 'Orders',       titleKey: 'message.orders' } },
    { path: '/orders/:id', component: () => import('../../pages/orders/OrderShow.vue'),  meta: { title: 'Order Details', titleKey: 'message.orders' } },
]
