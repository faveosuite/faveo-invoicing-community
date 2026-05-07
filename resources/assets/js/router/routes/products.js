export default [
    { path: '/products',                    component: () => import('../../pages/products/Index.vue'),               meta: { title: 'Products',     titleKey: 'message.products' } },
    { path: '/products/create',             component: () => import('../../pages/products/Create.vue'),              meta: { title: 'Create New Product', titleKey: 'message.create_new_product' } },
    { path: '/products/:id/edit',           component: () => import('../../pages/products/Edit.vue'),                meta: { title: 'Edit Product', titleKey: 'message.edit_product' } },
    { path: '/products/plans',              component: () => import('../../pages/products/plans/Index.vue'),         meta: { title: 'Plans',        titleKey: 'message.plans' } },
    { path: '/products/plans/create',       component: () => import('../../pages/products/plans/Create.vue'),       meta: { title: 'Create Plan',  titleKey: 'message.plans' } },
    { path: '/products/plans/:id/edit',     component: () => import('../../pages/products/plans/Edit.vue'),         meta: { title: 'Edit Plan',    titleKey: 'message.edit_plan' } },
    { path: '/products/coupons',            component: () => import('../../pages/products/coupons/Index.vue'),       meta: { title: 'Coupons',      titleKey: 'message.coupons' } },
    { path: '/products/coupons/create',     component: () => import('../../pages/products/coupons/Create.vue'),     meta: { title: 'Create New Coupon', titleKey: 'message.create_new_coupon' } },
    { path: '/products/coupons/:id/edit',   component: () => import('../../pages/products/coupons/Edit.vue'),       meta: { title: 'Edit Coupon',  titleKey: 'message.edit_coupon' } },
    { path: '/products/groups',             component: () => import('../../pages/products/groups/Index.vue'),        meta: { title: 'Groups',       titleKey: 'message.groups' } },
    { path: '/products/groups/create',      component: () => import('../../pages/products/groups/Create.vue'),      meta: { title: 'Create Group', titleKey: 'message.create_group' } },
    { path: '/products/groups/:id/edit',    component: () => import('../../pages/products/groups/Edit.vue'),        meta: { title: 'Edit Group',   titleKey: 'message.edit_group' } },
]
