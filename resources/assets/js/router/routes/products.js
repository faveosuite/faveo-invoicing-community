export default [
    { path: '/products',                    component: () => import('../../pages/products/ProductIndex.vue'),               meta: { title: 'Products',     titleKey: 'message.products' } },
    { path: '/products/create',             component: () => import('../../pages/products/ProductCreate.vue'),              meta: { title: 'Create New Product', titleKey: 'message.create_new_product' } },
    { path: '/products/:id/edit',           component: () => import('../../pages/products/ProductEdit.vue'),                meta: { title: 'Edit Product', titleKey: 'message.edit_product' } },
    { path: '/products/plans',              component: () => import('../../pages/products/plans/PlanIndex.vue'),         meta: { title: 'Plans',        titleKey: 'message.plans' } },
    { path: '/products/plans/create',       component: () => import('../../pages/products/plans/PlanCreate.vue'),       meta: { title: 'Create Plan',  titleKey: 'message.plans' } },
    { path: '/products/plans/:id/edit',     component: () => import('../../pages/products/plans/PlanEdit.vue'),         meta: { title: 'Edit Plan',    titleKey: 'message.edit_plan' } },
    { path: '/products/coupons',            component: () => import('../../pages/products/coupons/CouponIndex.vue'),       meta: { title: 'Coupons',      titleKey: 'message.coupons' } },
    { path: '/products/coupons/create',     component: () => import('../../pages/products/coupons/CouponCreate.vue'),     meta: { title: 'Create New Coupon', titleKey: 'message.create_new_coupon' } },
    { path: '/products/coupons/:id/edit',   component: () => import('../../pages/products/coupons/CouponEdit.vue'),       meta: { title: 'Edit Coupon',  titleKey: 'message.edit_coupon' } },
    { path: '/products/groups',             component: () => import('../../pages/products/groups/ProductGroupIndex.vue'),        meta: { title: 'Groups',       titleKey: 'message.groups' } },
    { path: '/products/groups/create',      component: () => import('../../pages/products/groups/ProductGroupCreate.vue'),      meta: { title: 'Create Group', titleKey: 'message.create_group' } },
    { path: '/products/groups/:id/edit',    component: () => import('../../pages/products/groups/ProductGroupEdit.vue'),        meta: { title: 'Edit Group',   titleKey: 'message.edit_group' } },
]
