export default [
    { path: '/orders',     component: () => import('../../pages/orders/Index.vue'), meta: { title: 'Orders',       titleKey: 'message.orders' } },
    { path: '/orders/:id', component: () => import('../../pages/orders/Show.vue'),  meta: { title: 'Order Details', titleKey: 'message.orders' } },
]
