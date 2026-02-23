export default [
    { path: '/products', component: () => import('../../pages/products/Index.vue'), meta: { title: 'Products' } },
    { path: '/products/create', component: () => import('../../pages/products/Create.vue'), meta: { title: 'New Product' } },
    { path: '/products/:id/edit', component: () => import('../../pages/products/Edit.vue'), meta: { title: 'Edit Product' } },
    { path: '/products/plans', component: () => import('../../pages/products/plans/Index.vue'), meta: { title: 'Plans' } },
    { path: '/products/plans/:id/edit', component: () => import('../../pages/products/plans/Edit.vue'), meta: { title: 'Edit Plan' } },
    { path: '/products/coupons', component: () => import('../../pages/products/coupons/Index.vue'), meta: { title: 'Coupons' } },
    { path: '/products/coupons/create', component: () => import('../../pages/products/coupons/Create.vue'), meta: { title: 'New Coupon' } },
    { path: '/products/coupons/:id/edit', component: () => import('../../pages/products/coupons/Edit.vue'), meta: { title: 'Edit Coupon' } },
    { path: '/products/groups', component: () => import('../../pages/products/groups/Index.vue'), meta: { title: 'Groups' } },
    { path: '/products/groups/create', component: () => import('../../pages/products/groups/Create.vue'), meta: { title: 'New Group' } },
    { path: '/products/groups/:id/edit', component: () => import('../../pages/products/groups/Edit.vue'), meta: { title: 'Edit Group' } },
]
